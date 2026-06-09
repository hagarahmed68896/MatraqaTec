<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorporateCustomer;
use App\Models\IndividualCustomer;
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
        
        // Stats with growth compared to last week
        $totalCustomers = User::whereIn('type', ['individual', 'corporate_customer'])->count();
        $totalCustomersLastWeek = User::whereIn('type', ['individual', 'corporate_customer'])->where('created_at', '<', Carbon::now()->subWeek())->count();
        $totalCustomersGrowth = $totalCustomersLastWeek > 0 ? (($totalCustomers - $totalCustomersLastWeek) / $totalCustomersLastWeek) * 100 : ($totalCustomers > 0 ? 100 : 0);

        $totalIndividual = User::where('type', 'individual')->count();
        $totalIndividualLastWeek = User::where('type', 'individual')->where('created_at', '<', Carbon::now()->subWeek())->count();
        $totalIndividualGrowth = $totalIndividualLastWeek > 0 ? (($totalIndividual - $totalIndividualLastWeek) / $totalIndividualLastWeek) * 100 : ($totalIndividual > 0 ? 100 : 0);

        $totalInactive = User::whereIn('type', ['individual', 'corporate_customer'])->whereIn('status', ['blocked', 'inactive'])->count();
        $totalInactiveLastWeek = User::whereIn('type', ['individual', 'corporate_customer'])->whereIn('status', ['blocked', 'inactive'])->where('created_at', '<', Carbon::now()->subWeek())->count();
        $totalInactiveGrowth = $totalInactiveLastWeek > 0 ? (($totalInactive - $totalInactiveLastWeek) / $totalInactiveLastWeek) * 100 : ($totalInactive > 0 ? 100 : 0);

        $totalCorporate = User::where('type', 'corporate_customer')->count();
        $totalCorporateLastWeek = User::where('type', 'corporate_customer')->where('created_at', '<', Carbon::now()->subWeek())->count();
        $totalCorporateGrowth = $totalCorporateLastWeek > 0 ? (($totalCorporate - $totalCorporateLastWeek) / $totalCorporateLastWeek) * 100 : ($totalCorporate > 0 ? 100 : 0);

        $stats = [
            'total_all' => $totalCustomers,
            'total_all_trend' => ($totalCustomersGrowth >= 0 ? '+' : '') . number_format($totalCustomersGrowth, 2) . '%',
            'total_all_trend_value' => $totalCustomersGrowth,
            'total_individual' => $totalIndividual,
            'total_individual_trend' => ($totalIndividualGrowth >= 0 ? '+' : '') . number_format($totalIndividualGrowth, 2) . '%',
            'total_individual_trend_value' => $totalIndividualGrowth,
            'total_inactive' => $totalInactive,
            'total_inactive_trend' => ($totalInactiveGrowth >= 0 ? '+' : '') . number_format($totalInactiveGrowth, 2) . '%',
            'total_inactive_trend_value' => $totalInactiveGrowth,
            'total_corporate' => $totalCorporate,
            'total_corporate_trend' => ($totalCorporateGrowth >= 0 ? '+' : '') . number_format($totalCorporateGrowth, 2) . '%',
            'total_corporate_trend_value' => $totalCorporateGrowth,
        ];
        
        return view('admin.customers.index', compact('items', 'stats'))->with('type', 'corporate');
    }

    public function create()
    {
        return view('admin.corporate_customers.create');
    }

    public function store(Request $request)
    {
        // 1. Phone Normalization
        $phone = $request->input('phone');
        if ($phone) {
            $phone = preg_replace('/\D/', '', $phone);
            if (str_starts_with($phone, '00966')) {
                $phone = substr($phone, 5);
            }
            if (str_starts_with($phone, '966')) {
                $phone = substr($phone, 3);
            }
            $phone = ltrim($phone, '0');
            $request->merge(['phone' => $phone]);
        }

        $rules = [
            'name'                      => 'required|string|max:255|unique:corporate_customers,company_name_ar|unique:corporate_customers,company_name_en',
            'email'                     => 'required|string|email|max:255|unique:users',
            'phone'                     => ['required', 'string', 'unique:users', 'regex:/^[0-9]{9}$/'],
            'tax_number'                => 'nullable|digits_between:1,20|unique:corporate_customers,tax_number',
            'commercial_record_number'  => 'required|digits_between:1,20|unique:corporate_customers,commercial_record_number',
            'commercial_record_file'    => 'required|file|mimes:pdf,jpg,png,jpeg,doc,docx|max:10240',
        ];

        $messages = [
            'name.required'                         => 'يجب إدخال اسم الشركة',
            'name.unique'                           => 'اسم الشركة مسجل مسبقاً في النظام',
            'email.required'                        => 'يجب إدخال البريد الإلكتروني',
            'email.email'                           => 'يرجى إدخال بريد إلكتروني صحيح',
            'email.unique'                          => 'هذا البريد الإلكتروني مسجل مسبقاً',
            'phone.required'                        => 'يجب إدخال رقم الجوال',
            'phone.unique'                          => 'رقم الجوال مسجل مسبقاً',
            'phone.regex'                           => 'يجب أن يتكون رقم الجوال من 9 أرقام بدون مفتاح الدولة (مثل: 501234567)',
            'tax_number.digits_between'             => 'الرقم الضريبي يجب أن يحتوي على أرقام فقط',
            'tax_number.unique'                     => 'الرقم الضريبي مسجل مسبقاً في النظام',
            'commercial_record_number.required'     => 'يجب إدخال رقم السجل التجاري',
            'commercial_record_number.digits_between' => 'رقم السجل التجاري يجب أن يحتوي على أرقام فقط',
            'commercial_record_number.unique'       => 'رقم السجل التجاري مسجل مسبقاً في النظام',
            'commercial_record_file.required'       => 'يجب رفع صورة السجل التجاري',
            'commercial_record_file.mimes'          => 'يجب أن يكون الملف بصيغة PDF أو صورة (JPG, PNG) أو مستند Word (doc, docx)',
            'commercial_record_file.max'            => 'حجم الملف يجب ألا يتجاوز 10 ميجابايت',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
             return back()->withErrors($validator)->withInput();
        }

        $password = Str::random(10);
        
        \Illuminate\Support\Facades\DB::beginTransaction();
        $filePath = null;
        try {
            $status = $request->filled('status') ? $request->status : 'active';

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($password),
                'type' => 'corporate_customer',
                'phone' => $request->phone,
                'status' => $status,
            ]);

            if ($request->hasFile('commercial_record_file')) {
                $fileName = time() . '_' . uniqid() . '.' . $request->file('commercial_record_file')->getClientOriginalExtension();
                $request->file('commercial_record_file')->move(public_path('uploads/corporate/cr'), $fileName);
                $filePath = 'uploads/corporate/cr/' . $fileName;
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

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('admin.corporate-customers.index')->with('success', __('Customer created successfully.'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            if ($filePath && file_exists(public_path($filePath))) {
                @unlink(public_path($filePath));
            }

            \Illuminate\Support\Facades\Log::error('Error creating corporate customer: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->except(['password', 'commercial_record_file'])
            ]);

            return back()->with('error', __('An error occurred while creating the corporate customer: ') . $e->getMessage())->withInput();
        }
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
        
        // 1. Phone Normalization
        $phone = $request->input('phone');
        if ($phone) {
            $phone = preg_replace('/\D/', '', $phone);
            if (str_starts_with($phone, '00966')) {
                $phone = substr($phone, 5);
            }
            if (str_starts_with($phone, '966')) {
                $phone = substr($phone, 3);
            }
            $phone = ltrim($phone, '0');
            $request->merge(['phone' => $phone]);
        }

        $rules = [
            'email'                     => 'required|string|email|max:255|unique:users,email,'.$profile->user_id,
            'phone'                     => ['required', 'string', 'unique:users,phone,'.$profile->user_id, 'regex:/^[0-9]{9}$/'],
            'name'                      => 'required|string|max:255|unique:corporate_customers,company_name_ar,'.$profile->id.'|unique:corporate_customers,company_name_en,'.$profile->id,
            'tax_number'                => 'nullable|digits_between:1,20|unique:corporate_customers,tax_number,'.$profile->id,
            'commercial_record_number'  => 'required|digits_between:1,20|unique:corporate_customers,commercial_record_number,'.$profile->id,
            'commercial_record_file'    => 'nullable|file|mimes:pdf,jpg,png,jpeg,doc,docx|max:10240',
        ];

        $messages = [
            'name.required'                             => 'يجب إدخال اسم الشركة',
            'name.unique'                               => 'اسم الشركة مسجل مسبقاً في النظام',
            'email.required'                            => 'يجب إدخال البريد الإلكتروني',
            'email.email'                               => 'يرجى إدخال بريد إلكتروني صحيح',
            'email.unique'                              => 'هذا البريد الإلكتروني مسجل مسبقاً',
            'phone.required'                            => 'يجب إدخال رقم الجوال',
            'phone.unique'                              => 'رقم الجوال مسجل مسبقاً',
            'phone.regex'                               => 'يجب أن يتكون رقم الجوال من 9 أرقام بدون مفتاح الدولة (مثل: 501234567)',
            'tax_number.digits_between'                 => 'الرقم الضريبي يجب أن يحتوي على أرقام فقط',
            'tax_number.unique'                         => 'الرقم الضريبي مسجل مسبقاً في النظام',
            'commercial_record_number.required'         => 'يجب إدخال رقم السجل التجاري',
            'commercial_record_number.digits_between'   => 'رقم السجل التجاري يجب أن يحتوي على أرقام فقط',
            'commercial_record_number.unique'           => 'رقم السجل التجاري مسجل مسبقاً في النظام',
            'commercial_record_file.mimes'              => 'يجب أن يكون الملف بصيغة PDF أو صورة (JPG, PNG) أو مستند Word (doc, docx)',
            'commercial_record_file.max'                => 'حجم الملف يجب ألا يتجاوز 10 ميجابايت',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        
        if ($validator->fails()) {
             return back()->withErrors($validator)->withInput();
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        $newFilePath = null;
        try {
            $user = $profile->user;
            if ($user) {
                $user->email = $request->email;
                $user->phone = $request->phone;
                if ($request->filled('status')) {
                    $user->status = $request->status;
                }
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
                $fileName = time() . '_' . uniqid() . '.' . $request->file('commercial_record_file')->getClientOriginalExtension();
                $request->file('commercial_record_file')->move(public_path('uploads/corporate/cr'), $fileName);
                $newFilePath = 'uploads/corporate/cr/' . $fileName;
                $data['commercial_record_file'] = $newFilePath;
            }

            $oldFilePath = $profile->commercial_record_file;

            $profile->update($data);

            \Illuminate\Support\Facades\DB::commit();

            if ($newFilePath && $oldFilePath && file_exists(public_path($oldFilePath))) {
                @unlink(public_path($oldFilePath));
            }

            return redirect()->route('admin.corporate-customers.index')->with('success', __('Customer updated successfully.'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            if ($newFilePath && file_exists(public_path($newFilePath))) {
                @unlink(public_path($newFilePath));
            }

            \Illuminate\Support\Facades\Log::error('Error updating corporate customer: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->except(['commercial_record_file'])
            ]);

            return back()->with('error', __('An error occurred while updating the corporate customer: ') . $e->getMessage())->withInput();
        }
    }

    public function download(Request $request)
    {
        $query = CorporateCustomer::with('user');
        
        if ($request->has('ids') && !empty($request->ids)) {
            $ids = explode(',', $request->ids);
            $query->whereIn('id', $ids);
        }
        
        $items = $query->get();
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
            $profile->user->update(['status' => 'inactive']);
            $message = __('Customer deactivated successfully.');
        } else {
            $profile->delete();
            $message = __('Customer profile deleted successfully.');
        }
        return redirect()->route('admin.corporate-customers.index')->with('success', $message);
    }

    public function toggleBlock($id)
    {
        // Attempt to locate a corporate customer with its related user.
        $customer = CorporateCustomer::with('user')->find($id);
        $user = null;
        if ($customer && $customer->user) {
            $user = $customer->user;
        } else {
            // Fallback: treat the supplied id as a direct user id.
            $user = User::find($id);
        }

        if (!$user) {
            return back()->with('error', __('User record not found'));
        }

        // Toggle status between active, blocked, and inactive.
        if ($user->status === 'blocked' || $user->status === 'inactive') {
            $user->status = 'active';
            $user->blocked_at = null;
            $message = __('Account activated successfully');
        } else {
            $user->status = 'blocked';
            $user->blocked_at = now();
            $message = __('Account blocked successfully');
        }

        $user->save();
        // logger()->info('Corporate customer status toggled', ['account_id' => $user->id, 'new_status' => $user->status]);
        return redirect()->route('admin.corporate-customers.index')->with('success', $message);
    }
    
    public function bulkBlock(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $customers = CorporateCustomer::whereIn('id', $request->ids)->with('user')->get();
        
        foreach ($customers as $customer) {
            if ($customer->user) {
                // Inactive is used as blocked for corporate in some places based on toggleBlock logic above
                $customer->user->update(['status' => 'inactive']); 
            }
        }
        
        return back()->with('success', __('Customers blocked successfully.'));
    }

    public function bulkUnblock(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $customers = CorporateCustomer::whereIn('id', $request->ids)->with('user')->get();
        
        foreach ($customers as $customer) {
            if ($customer->user) {
                $customer->user->update(['status' => 'active']);
            }
        }
        
        return back()->with('success', __('Customers unblocked successfully.'));
    }
}
