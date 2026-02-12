<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorporateCustomer;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CorporateCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = CorporateCustomer::with('user');

        // 1. Search Logic
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name_en', 'like', "%{$search}%")
                  ->orWhere('company_name_ar', 'like', "%{$search}%")
                  ->orWhere('commercial_record_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Filter by Status
        if ($request->has('status') && $request->status) {
            $status = $request->status;
            $query->whereHas('user', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        // 3. Sorting Logic
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'name':
                    $query->orderBy('company_name_ar', 'asc');
                    break;
                case 'status':
                    $query->join('users', 'corporate_customers.user_id', '=', 'users.id')
                          ->orderBy('users.status', 'asc')
                          ->select('corporate_customers.*');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $items = $query->paginate(15);
        
        // Stats
        $stats = [
            'total' => CorporateCustomer::count(),
            'active' => CorporateCustomer::whereHas('user', function ($q) {
                $q->where('status', 'active');
            })->count(),
            'blocked' => CorporateCustomer::whereHas('user', function ($q) {
                $q->where('status', 'blocked');
            })->count(),
            'new_this_week' => CorporateCustomer::where('created_at', '>=', Carbon::now()->subWeek())->count()
        ];
        
        return view('admin.customers.index', compact('items', 'stats'))->with('type', 'corporate');
    }

    public function create()
    {
        return view('admin.corporate_customers.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => ['required', 'string', 'unique:users', 'regex:/^[0-9]{9}$/'],
            'tax_number' => 'nullable|string|max:255',
            'commercial_record_number' => 'nullable|string|max:255',
            'commercial_record_file' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
        ];

        $messages = [
            'name.required' => 'يجب إدخال اسم الشركة',
            'email.required' => 'يجب إدخال البريد الإلكتروني',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'email.unique' => 'هذا البريد الإلكتروني مسجل مسبقاً',
            'phone.required' => 'يجب إدخال رقم الجوال',
            'phone.unique' => 'رقم الجوال مسجل مسبقاً',
            'phone.regex' => 'يجب أن يتكون رقم الجوال من 9 أرقام بدون مفتاح الدولة (مثل: 501234567)',
            'commercial_record_file.mimes' => 'يجب أن يكون الملف بصيغة PDF أو صورة (JPG, PNG)',
            'commercial_record_file.max' => 'حجم الملف يجب ألا يتجاوز 2 ميجابايت',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
             return back()->withErrors($validator)->withInput();
        }

        $password = Str::random(10);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'type' => 'corporate_customer',
            'phone' => $request->phone,
            'status' => $request->status ?? 'active',
        ]);

        $filePath = null;
        if ($request->hasFile('commercial_record_file')) {
            $filePath = $request->file('commercial_record_file')->store('corporate/cr', 'public');
        }

        CorporateCustomer::create([
            'user_id' => $user->id,
            'company_name_ar' => $request->name,
            'company_name_en' => $request->name,
            'commercial_record_number' => $request->commercial_record_number,
            'commercial_record_file' => $filePath,
            'tax_number' => $request->tax_number,
            'address' => '',
        ]);

        return redirect()->route('admin.corporate-customers.index')->with('success', __('Customer created successfully.'));
    }

    public function show(Request $request, $id)
    {
        $item = CorporateCustomer::with(['user.city'])->where('user_id', $id)->orWhere('id', $id)->firstOrFail();
        $userId = $item->user_id;

        // Fetch related data for tabs
        $orders = Order::where('user_id', $userId)->with(['service', 'technician'])->latest()->get();
        
        $orderIds = $orders->pluck('id');
        $invoices = Invoice::whereIn('order_id', $orderIds)->latest()->get();
        $payments = Payment::where('user_id', $userId)->latest()->get();
        $reviews = Review::where('user_id', $userId)->with(['service', 'technician', 'order'])->latest()->get();
        
        // Contracts - In current schema, contracts are linked to maintenance companies.
        $contracts = collect(); 

        // Statistics Summary
        $stats = [
            'total_payments' => Payment::where('user_id', $userId)->where('status', 'paid')->sum('amount'),
            'order_count' => $orders->count(),
        ];

        // Performance Chart Data
        $chartType = $request->get('chart_type', 'monthly');
        $performanceData = [];
        
        if ($chartType === 'weekly') {
            for ($i = 7; $i >= 0; $i--) {
                $date = Carbon::now()->subWeeks($i);
                $label = __('Week') . ' ' . $date->weekOfYear;
                $count = Order::where('user_id', $userId)
                    ->whereBetween('created_at', [$date->startOfWeek()->toDateTimeString(), $date->endOfWeek()->toDateTimeString()])
                    ->count();
                $performanceData[] = ['label' => $label, 'count' => $count];
            }
        } elseif ($chartType === 'yearly') {
            for ($i = 2; $i >= 0; $i--) {
                $date = Carbon::now()->subYears($i);
                $label = $date->year;
                $count = Order::where('user_id', $userId)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $performanceData[] = ['label' => $label, 'count' => $count];
            }
        } else { // monthly
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $label = $date->translatedFormat('F');
                $count = Order::where('user_id', $userId)
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                $performanceData[] = ['label' => $label, 'count' => $count];
            }
        }

        if ($request->ajax()) {
            return response()->json(['performanceData' => $performanceData]);
        }

        return view('admin.corporate_customers.show', compact('item', 'stats', 'orders', 'invoices', 'payments', 'reviews', 'contracts', 'performanceData', 'chartType'));
    }

    public function edit($id)
    {
        $item = CorporateCustomer::with('user')->findOrFail($id);
        return view('admin.corporate_customers.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $profile = CorporateCustomer::with('user')->findOrFail($id);
        
        $rules = [
            'email' => 'required|string|email|max:255|unique:users,email,'.$profile->user_id,
            'phone' => ['required', 'string', 'unique:users,phone,'.$profile->user_id, 'regex:/^[0-9]{9}$/'],
            'name' => 'required|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'commercial_record_number' => 'nullable|string|max:255',
            'commercial_record_file' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
        ];

        $messages = [
            'name.required' => 'يجب إدخال اسم الشركة',
            'email.required' => 'يجب إدخال البريد الإلكتروني',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'email.unique' => 'هذا البريد الإلكتروني مسجل مسبقاً',
            'phone.required' => 'يجب إدخال رقم الجوال',
            'phone.unique' => 'رقم الجوال مسجل مسبقاً',
            'phone.regex' => 'يجب أن يتكون رقم الجوال من 9 أرقام بدون مفتاح الدولة (مثل: 501234567)',
            'commercial_record_file.mimes' => 'يجب أن يكون الملف بصيغة PDF أو صورة (JPG, PNG)',
            'commercial_record_file.max' => 'حجم الملف يجب ألا يتجاوز 2 ميجابايت',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        
        if ($validator->fails()) {
             return back()->withErrors($validator)->withInput();
        }

        $user = $profile->user;
        if ($user) {
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->status = $request->status;
            $user->name = $request->name;
            $user->save();
        }

        $data = [
            'company_name_ar' => $request->name,
            'company_name_en' => $request->name,
            'tax_number' => $request->tax_number,
            'commercial_record_number' => $request->commercial_record_number,
        ];

        if ($request->hasFile('commercial_record_file')) {
            $data['commercial_record_file'] = $request->file('commercial_record_file')->store('corporate/cr', 'public');
        }

        $profile->update($data);
        
        return redirect()->route('admin.corporate-customers.index')->with('success', __('Customer updated successfully.'));
    }

    public function download()
    {
        $items = CorporateCustomer::with('user')->get();
        $filename = "corporate_customers_" . date('Y-m-d') . ".csv";
        $handle = fopen('php://temp', 'w+');
        fputs($handle, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); // UTF-8 BOM

        fputcsv($handle, [
            __('ID'),
            __('Company Name'),
            __('Commercial Record'),
            __('Tax Number'),
            __('Email'),
            __('Phone'),
            __('Address'),
            __('Status'),
            __('Created At'),
        ]);

        foreach ($items as $item) {
            fputcsv($handle, [
                $item->id,
                $item->company_name_ar ?? $item->company_name_en,
                $item->commercial_record_number,
                $item->tax_number,
                $item->user->email ?? '',
                $item->user->phone ?? '',
                $item->address,
                $item->user->status ?? '',
                $item->created_at,
            ]);
        }

        fseek($handle, 0);

        return response()->stream(
            function () use ($handle) {
                fpassthru($handle);
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }

    public function destroy($id)
    {
        $profile = CorporateCustomer::findOrFail($id);
        if ($profile->user) {
            $profile->user->delete();
        } else {
            $profile->delete();
        }
        return redirect()->route('admin.corporate-customers.index')->with('success', __('Customer deleted successfully.'));
    }

    public function toggleBlock($id)
    {
        $customer = CorporateCustomer::findOrFail($id);
        $user = $customer->user;
        
        $user->status = ($user->status == 'active') ? 'inactive' : 'active';
        $user->save();

        $message = ($user->status == 'inactive') ? __('Account deactivated successfully.') : __('Account activated successfully.');
        return back()->with('success', $message);
    }
}
