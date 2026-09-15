<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCompany;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MaintenanceCompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceCompany::whereHas('user', function ($q) {
            $q->where('type', 'maintenance_company');
        })->with(['user', 'technicians.service', 'orders', 'financialSettlements']);

        // 1. Search Logic
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name_en', 'like', "%{$search}%")
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
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status; 
            $query->whereHas('user', function ($q) use ($status) {
                if ($status === 'inactive' || $status === 'blocked') {
                    $q->whereIn('status', ['inactive', 'blocked']);
                } else {
                    $q->where('status', $status);
                }
            });
        }

        // 3. Sorting Logic
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
                case 'status':
                    $query->join('users', 'maintenance_companies.user_id', '=', 'users.id')
                          ->orderBy('users.status', 'asc')
                          ->select('maintenance_companies.*');
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

        $items = $query->paginate(10);
        
        // Extract unique services
        $items->getCollection()->transform(function ($company) {
            $company->services = $company->technicians->pluck('service')->unique('id')->values();
            return $company;
        });

        // Statistics for Index Page with growth compared to last week
        $totalCompanies = MaintenanceCompany::count();
        $totalCompaniesLastWeek = MaintenanceCompany::where('created_at', '<', Carbon::now()->subWeek())->count();
        $totalCompaniesGrowth = $totalCompaniesLastWeek > 0 ? (($totalCompanies - $totalCompaniesLastWeek) / $totalCompaniesLastWeek) * 100 : ($totalCompanies > 0 ? 100 : 0);

        $totalTechs = \App\Models\Technician::whereNotNull('maintenance_company_id')->count();
        $totalTechsLastWeek = \App\Models\Technician::whereNotNull('maintenance_company_id')->where('created_at', '<', Carbon::now()->subWeek())->count();
        $totalTechsGrowth = $totalTechsLastWeek > 0 ? (($totalTechs - $totalTechsLastWeek) / $totalTechsLastWeek) * 100 : ($totalTechs > 0 ? 100 : 0);

        $totalServicesOffered = \App\Models\Technician::whereNotNull('maintenance_company_id')->distinct('service_id')->count('service_id');
        $totalServicesOfferedLastWeek = \App\Models\Technician::whereNotNull('maintenance_company_id')->where('created_at', '<', Carbon::now()->subWeek())->distinct('service_id')->count('service_id');
        $totalServicesOfferedGrowth = $totalServicesOfferedLastWeek > 0 ? (($totalServicesOffered - $totalServicesOfferedLastWeek) / $totalServicesOfferedLastWeek) * 100 : ($totalServicesOffered > 0 ? 100 : 0);

        $totalOrders = Order::whereNotNull('maintenance_company_id')->count();
        $totalOrdersLastWeek = Order::whereNotNull('maintenance_company_id')->where('created_at', '<', Carbon::now()->subWeek())->count();
        $totalOrdersGrowth = $totalOrdersLastWeek > 0 ? (($totalOrders - $totalOrdersLastWeek) / $totalOrdersLastWeek) * 100 : ($totalOrders > 0 ? 100 : 0);

        $stats = [
            'total_companies' => $totalCompanies,
            'total_companies_trend' => ($totalCompaniesGrowth >= 0 ? '+' : '') . number_format($totalCompaniesGrowth, 2) . '%',
            'total_companies_trend_value' => $totalCompaniesGrowth,
            'total_technicians_companies' => $totalTechs,
            'total_technicians_companies_trend' => ($totalTechsGrowth >= 0 ? '+' : '') . number_format($totalTechsGrowth, 2) . '%',
            'total_technicians_companies_trend_value' => $totalTechsGrowth,
            'total_services_offered' => $totalServicesOffered,
            'total_services_offered_trend' => ($totalServicesOfferedGrowth >= 0 ? '+' : '') . number_format($totalServicesOfferedGrowth, 2) . '%',
            'total_services_offered_trend_value' => $totalServicesOfferedGrowth,
            'total_orders' => $totalOrders,
            'total_orders_trend' => ($totalOrdersGrowth >= 0 ? '+' : '') . number_format($totalOrdersGrowth, 2) . '%',
            'total_orders_trend_value' => $totalOrdersGrowth,
        ];

        return view('admin.maintenance_companies.index', compact('items', 'stats'));
    }

    public function create()
    {
        $cities = \App\Models\City::all();
        return view('admin.maintenance_companies.create', compact('cities'));
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

        // 2. IBAN Normalization
        $iban = $request->input('iban');
        if ($iban) {
            $iban = strtoupper(str_replace(' ', '', $iban));
            $request->merge(['iban' => $iban]);
        }

        $locale = app()->getLocale();
        $validator = Validator::make($request->all(), [
            'name'                      => 'required|string|max:255|unique:maintenance_companies,name|unique:maintenance_companies,company_name_ar|unique:maintenance_companies,company_name_en',
            'email'                     => 'required|string|email|max:255|unique:users',
            'password'                  => 'nullable|string|min:8',
            'phone'                     => ['required', 'string', 'unique:users', 'regex:/^[0-9]{9}$/'],
            'city_id'                   => 'nullable|exists:cities,id',
            'tax_number'                => 'nullable|digits_between:1,20|unique:maintenance_companies,tax_number',
            'commercial_record_number'  => 'required|digits_between:1,20|unique:maintenance_companies,commercial_record_number',
            'commercial_record_file'    => 'required|file|mimes:pdf,jpg,png,jpeg,doc,docx|max:10240',
            'bank_name'                 => 'nullable|string|max:255',
            'account_name'              => 'nullable|string|max:255',
            'account_number'            => 'nullable|string|max:255',
            'iban'                      => ['nullable', 'string', 'regex:/^SA[0-9]{22}$/'],
            'swift_code'               => 'nullable|string|max:255',
            'bank_address'             => 'nullable|string|max:255',
        ], [
            'password.min'                          => __('The password is too short. It must be at least 8 characters.'),
            'iban.regex'                            => __('The IBAN must start with SA and be exactly 24 characters long (e.g., SAXXXXXXXXXXXXXXXXXXXXXX).'),
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
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        $filePath = null;
        try {
            $password = $request->password ?? Str::random(10);
            $name = $request->company_name_ar ?? $request->name;
            $status = $request->filled('status') ? $request->status : 'active';

            $user = User::create([
                'name' => $name,
                'email' => $request->email,
                'password' => Hash::make($password),
                'type' => 'maintenance_company',
                'phone' => $request->phone,
                'city_id' => $request->city_id,
                'status' => $status,
            ]);

            if ($request->hasFile('commercial_record_file')) {
                $fileName = time() . '_' . uniqid() . '.' . $request->file('commercial_record_file')->getClientOriginalExtension();
                $request->file('commercial_record_file')->move(public_path('uploads/maintenance/cr'), $fileName);
                $filePath = 'uploads/maintenance/cr/' . $fileName;
            }

            MaintenanceCompany::create([
                'user_id' => $user->id,
                'city_id' => $request->city_id,
                'name' => $request->name,
                'company_name_en' => $request->name,
                'company_name_ar' => $request->name,
                'commercial_record_number' => $request->commercial_record_number,
                'commercial_record_file' => $filePath,
                'tax_number' => $request->tax_number,
                'address' => $request->address,
                'bank_name' => $request->bank_name,
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
                'iban' => $request->iban,
                'swift_code' => $request->swift_code,
                'bank_address' => $request->bank_address,
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('admin.maintenance-companies.index')->with('success', __('Company created successfully.'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            if ($filePath && file_exists(public_path($filePath))) {
                @unlink(public_path($filePath));
            }

            \Illuminate\Support\Facades\Log::error('Error creating maintenance company: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->except(['password', 'commercial_record_file'])
            ]);

            return back()->with('error', __('An error occurred while creating the company: ') . $e->getMessage())->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $company = MaintenanceCompany::with(['user', 'orders.service', 'financialSettlements', 'user.city', 'schedules'])
            ->where('user_id', $id)
            ->orWhere('id', $id)
            ->firstOrFail();
        
        $technicians = $company->technicians()->whereHas('user', function($q) {
            $q->where('status', 'active');
        })->with(['user', 'service', 'category'])->get();
        $orders = Order::where('maintenance_company_id', $company->id)->with(['service', 'user', 'technician.user'])->latest()->get();
        $settlements = $company->financialSettlements()->latest()->get();
        
        // Get invoices from orders
        $invoices = \App\Models\Invoice::whereHas('order', function($q) use ($company) {
            $q->where('maintenance_company_id', $company->id);
        })->with(['order.service', 'order.technician.user', 'order.user'])->latest()->get();
        
        // Get payments from orders
        $payments = \App\Models\Payment::whereHas('order', function($q) use ($company) {
            $q->where('maintenance_company_id', $company->id);
        })->with(['order.service', 'order.technician.user', 'order.user'])->latest()->get();
        
        // Get reviews for company technicians
        $reviews = \App\Models\Review::whereHas('technician', function($q) use ($company) {
            $q->where('maintenance_company_id', $company->id);
        })->with(['technician.user', 'order', 'service'])->latest()->get();
        
        $company_services = $technicians->pluck('service')->unique('id')->values();

        // Statistics Summary
        $stats = [
            'total_technicians' => $technicians->count(),
            'total_services' => $technicians->pluck('service_id')->unique()->count(),
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->where('status', 'completed')->sum('total_price'),
        ];

        // Performance Chart Data (Revenue)
        $chartType = $request->get('chart_type', 'weekly');
        $performanceData = [];
        $locale = app()->getLocale();
        
        if ($chartType === 'yearly') {
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $label = $date->locale($locale)->translatedFormat('M');
                $sum = Order::where('maintenance_company_id', $company->id)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('total_price');
                $performanceData[] = ['label' => $label, 'count' => $sum];
            }
        } elseif ($chartType === 'monthly') {
            for ($i = 3; $i >= 0; $i--) {
                $start = Carbon::now()->subWeeks($i)->startOfWeek();
                $end = $start->copy()->endOfWeek();
                $weekNum = 4 - $i;
                $label = __('Week :number', ['number' => $weekNum]);
                $sum = Order::where('maintenance_company_id', $company->id)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
                    ->sum('total_price');
                $performanceData[] = ['label' => $label, 'count' => $sum];
            }
        } else { // weekly
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $label = $date->locale($locale)->translatedFormat('l');
                $sum = Order::where('maintenance_company_id', $company->id)
                    ->where('status', 'completed')
                    ->whereDate('created_at', $date->toDateString())
                    ->sum('total_price');
                $performanceData[] = ['label' => $label, 'count' => $sum];
            }
        }

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        if ($request->ajax()) {
            return response()->json(['performanceData' => $performanceData]);
        }
        
        return view('admin.maintenance_companies.show', compact('company', 'stats', 'technicians', 'orders', 'settlements', 'invoices', 'payments', 'reviews', 'performanceData', 'chartType', 'company_services', 'days'));
    }

    public function updateSchedules(Request $request, $id)
    {
        $company = MaintenanceCompany::where('user_id', $id)->orWhere('id', $id)->firstOrFail();

        $request->validate([
            'schedules' => 'nullable|array',
        ]);

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        // Validate first
        foreach ($days as $day) {
            $input = $request->input("schedules.{$day}");
            if ($input && !empty($input['active'])) {
                if (empty($input['start_time']) || empty($input['end_time'])) {
                    return back()->withErrors(["schedules.{$day}.start_time" => __('Start and end times are required for active days.')])->withInput();
                }
                if ($input['end_time'] <= $input['start_time']) {
                    return back()->withErrors(["schedules.{$day}.end_time" => __('End time must be after start time.')])->withInput();
                }
            }
        }

        // Sync to database
        foreach ($days as $day) {
            $input = $request->input("schedules.{$day}");

            if ($input && !empty($input['active'])) {
                $company->schedules()->updateOrCreate(
                    ['day' => $day],
                    [
                        'start_time' => $input['start_time'],
                        'end_time' => $input['end_time']
                    ]
                );
            } else {
                $company->schedules()->where('day', $day)->delete();
            }
        }

        return redirect()->route('admin.maintenance-companies.show', $company->id)->with('success', __('Working hours updated successfully.'));
    }

    public function edit($id)
    {
        $company = MaintenanceCompany::with(['user', 'services'])->findOrFail($id);
        $cities = \App\Models\City::all();
        $services = \App\Models\Service::whereNull('parent_id')->get();
        return view('admin.maintenance_companies.edit', compact('company', 'cities', 'services'));
    }

    public function update(Request $request, $id)
    {
        $company = MaintenanceCompany::findOrFail($id);
        
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

        // 2. IBAN Normalization
        $iban = $request->input('iban');
        if ($iban) {
            $iban = strtoupper(str_replace(' ', '', $iban));
            $request->merge(['iban' => $iban]);
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($company) {
                    $exists = \App\Models\MaintenanceCompany::where('id', '<>', $company->id)
                        ->whereHas('user', function ($q) {
                            $q->where('type', 'maintenance_company');
                        })
                        ->where(function ($q) use ($value) {
                            $q->where('name', $value)
                              ->orWhere('company_name_ar', $value)
                              ->orWhere('company_name_en', $value);
                        })
                        ->exists();
                    if ($exists) {
                        $fail('اسم الشركة مسجل مسبقاً في النظام كشركة صيانة');
                    }
                }
            ],
            'email'                     => 'required|string|email|max:255|unique:users,email,'.$company->user_id,
            'password'                  => 'nullable|string|min:8',
            'phone'                     => ['required', 'string', 'unique:users,phone,'.$company->user_id, 'regex:/^[0-9]{9}$/'],
            'city_id'                   => 'nullable|exists:cities,id',
            'tax_number'                => 'nullable|digits_between:1,20|unique:maintenance_companies,tax_number,'.$company->id,
            'commercial_record_number'  => 'required|digits_between:1,20|unique:maintenance_companies,commercial_record_number,'.$company->id,
            'commercial_record_file'    => 'nullable|file|mimes:pdf,jpg,png,jpeg,doc,docx|max:10240',
            'bank_name'                 => 'nullable|string|max:255',
            'account_name'              => 'nullable|string|max:255',
            'account_number'            => 'nullable|string|max:255',
            'iban'                      => ['nullable', 'string', 'regex:/^SA[0-9]{22}$/'],
            'swift_code'               => 'nullable|string|max:255',
            'bank_address'             => 'nullable|string|max:255',
        ], [
            'password.min'                          => __('The password is too short. It must be at least 8 characters.'),
            'iban.regex'                            => __('The IBAN must start with SA and be exactly 24 characters long (e.g., SAXXXXXXXXXXXXXXXXXXXXXX).'),
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
            'commercial_record_file.mimes'          => 'يجب أن يكون الملف بصيغة PDF أو صورة (JPG, PNG) أو مستند Word (doc, docx)',
            'commercial_record_file.max'            => 'حجم الملف يجب ألا يتجاوز 10 ميجابايت',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        $newFilePath = null;
        try {
            $user = $company->user;
            if ($user) {
                $user->email = $request->email;
                if ($request->filled('password')) $user->password = Hash::make($request->password);
                if ($request->has('phone')) $user->phone = $request->phone;
                if ($request->has('name')) $user->name = $request->name;
                if ($request->has('status')) $user->status = $request->status;
                if ($request->has('city_id')) $user->city_id = $request->city_id;
                $user->save();
            }

            $data = $request->except(['email', 'password', 'phone', 'status', 'type', 'commercial_record_file']);

            if ($request->hasFile('commercial_record_file')) {
                $fileName = time() . '_' . uniqid() . '.' . $request->file('commercial_record_file')->getClientOriginalExtension();
                $request->file('commercial_record_file')->move(public_path('uploads/maintenance/cr'), $fileName);
                $newFilePath = 'uploads/maintenance/cr/' . $fileName;
                $data['commercial_record_file'] = $newFilePath;
            }

            $oldFilePath = $company->commercial_record_file;

            $company->update($data);

            if ($request->has('services')) {
                $syncData = [];
                $existingServices = $company->services->keyBy('id');
                foreach ($request->input('services') as $serviceId) {
                    $existing = $existingServices->get($serviceId);
                    $syncData[$serviceId] = [
                        'district_ids' => $existing ? $existing->pivot->district_ids : json_encode([])
                    ];
                }
                $company->services()->sync($syncData);
            } else {
                $company->services()->sync([]);
            }

            \Illuminate\Support\Facades\DB::commit();

            if ($newFilePath && $oldFilePath && file_exists(public_path($oldFilePath))) {
                @unlink(public_path($oldFilePath));
            }

            return redirect()->route('admin.maintenance-companies.index')->with('success', __('Company updated successfully.'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            if ($newFilePath && file_exists(public_path($newFilePath))) {
                @unlink(public_path($newFilePath));
            }

            \Illuminate\Support\Facades\Log::error('Error updating maintenance company: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->except(['password', 'commercial_record_file'])
            ]);

            return back()->with('error', __('An error occurred while updating the company: ') . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        // Try to find a maintenance company first
        $company = MaintenanceCompany::find($id);
        if ($company && $company->user) {
            $company->user->update(['status' => 'inactive']);
            $message = __('Company deleted successfully.');
        } else {
            // Fallback: treat the id as a direct user id and deactivate the user
            $user = User::findOrFail($id);
            $user->update(['status' => 'inactive']);
            $message = __('Company deleted successfully.');
        }
        return redirect()->route('admin.maintenance-companies.index')->with('success', $message);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->with('error', __('No companies selected.'));
        }

        $companies = MaintenanceCompany::whereIn('id', $ids)->get();
        foreach ($companies as $company) {
            if ($company->user) {
                $company->user->update(['status' => 'inactive']);
            } else {
                $company->delete();
            }
        }

        return back()->with('success', __('Selected companies deleted successfully.'));
    }

    public function toggleBlock($id)
    {
        // Try to find a maintenance company first
        $company = MaintenanceCompany::find($id);
        $user = null;
        if ($company && $company->user) {
            $user = $company->user;
        } else {
            // Fallback: treat the id as a direct user id
            $user = User::find($id);
        }

        if (!$user) {
            return back()->with('error', __('User record not found'));
        }

        // Toggle status between active, blocked, and inactive
        // NOTE: إذا تم تعطيل حساب (status = 'inactive'), يجب إظهار الحالة "معطل" في الواجهة.
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
        // Optionally log for support
        // logger()->info('Account status toggled', ['account_id' => $user->id, 'new_status' => $user->status]);
        return back()->with('success', $message);
    }

    public function bulkBlock(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:maintenance_companies,id',
        ]);

        $companies = MaintenanceCompany::whereIn('id', $request->ids)->with('user')->get();
        foreach ($companies as $company) {
            if ($company->user && $company->user->status !== 'blocked') {
                $company->user->status = 'blocked';
                $company->user->blocked_at = now();
                $company->user->save();
            }
        }

        return back()->with('success', __('Customers blocked successfully.'));
    }

    public function bulkUnblock(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:maintenance_companies,id',
        ]);

        $companies = MaintenanceCompany::whereIn('id', $request->ids)->with('user')->get();
        foreach ($companies as $company) {
            if ($company->user && $company->user->status === 'blocked') {
                $company->user->status = 'active';
                $company->user->blocked_at = null;
                $company->user->save();
            }
        }

        return back()->with('success', __('Customers unblocked successfully.'));
    }

    public function download(Request $request)
    {
        $query = MaintenanceCompany::with(['user', 'technicians', 'orders']);

        if ($request->has('ids') && !empty($request->ids)) {
            // Assume ids might be a comma-separated string if passed directly in URL from javascript array.join(',')
            $ids = is_string($request->ids) ? explode(',', $request->ids) : $request->ids;
            $query->whereIn('id', $ids);
        }

        // Apply same filters as index
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

        if ($request->has('status') && $request->status) {
             $status = $request->status; 
            $query->whereHas('user', function ($q) use ($status) {
                if ($status === 'inactive') {
                    $q->whereIn('status', ['inactive', 'blocked']);
                } else {
                    $q->where('status', $status);
                }
            });
        }

        $items = $query->latest()->get();

        $csvFileName = 'maintenance_companies_' . date('Y-m-d_H-i') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream(function () use ($items) {
            $handle = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 compatibility
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header Row
            fputcsv($handle, [
                __('ID'),
                __('Company Name (AR)'),
                __('Company Name (EN)'),
                __('Email'),
                __('Phone'),
                __('Address'),
                __('Commercial Record'),
                __('Tax Number'),
                __('Technicians Count'),
                __('Orders Count'),
                __('Status'),
                __('Created At'),
            ]);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->id,
                    $item->company_name_ar,
                    $item->company_name_en,
                    $item->user->email ?? '',
                    $item->user->phone ?? '',
                    $item->address,
                    $item->commercial_record_number,
                    $item->tax_number,
                    $item->technicians->count(),
                    $item->orders->count(),
                    __($item->user->status ?? 'active'),
                    $item->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
     }

    public function viewCR($id)
    {
        $company = MaintenanceCompany::where('user_id', $id)->orWhere('id', $id)->firstOrFail();
        
        $file = $company->commercial_record_file;
        if (!$file) {
            abort(404, __('Commercial record file not found.'));
        }

        $fileName = basename($file);
        
        $pathsToTry = [
            // Standard public path checks
            public_path($file),
            base_path('public/' . $file),
            base_path('../public_html/' . $file),
            base_path('../' . $file),
            
            // Storage checks
            storage_path('app/public/' . $file),
            storage_path('app/' . $file),
            base_path('storage/app/public/' . $file),
            base_path('storage/app/' . $file),
            public_path('storage/' . $file),
            
            // Subfolder custom upload checks
            public_path('uploads/maintenance/cr/' . $fileName),
            public_path('uploads/commercial_records/' . $fileName),
            public_path($fileName),
            base_path('public/uploads/maintenance/cr/' . $fileName),
            base_path('../public_html/uploads/maintenance/cr/' . $fileName),
        ];

        $filePath = null;
        foreach ($pathsToTry as $path) {
            if (file_exists($path) && !is_dir($path)) {
                $filePath = $path;
                break;
            }
        }
        
        if (!$filePath) {
            $debugInfo = [
                'Database Value' => $file,
                'Public Path' => public_path(),
                'Base Path' => base_path(),
                'Paths Searched' => []
            ];
            foreach ($pathsToTry as $path) {
                $debugInfo['Paths Searched'][] = [
                    'path' => $path,
                    'exists' => file_exists($path) ? 'Yes' : 'No',
                    'is_dir' => is_dir($path) ? 'Yes' : 'No'
                ];
            }
            
            $html = "<html><head><title>Debug CR File</title></head><body style='font-family: Arial; padding: 20px; direction: rtl; text-align: right;'>";
            $html .= "<h2 style='color: red;'>الملف غير موجود في السيرفر (File Not Found)</h2>";
            $html .= "<p>قيمة الحقل في قاعدة البيانات: <strong>" . htmlspecialchars($debugInfo['Database Value']) . "</strong></p>";
            $html .= "<p>مسار public_path: <code>" . htmlspecialchars($debugInfo['Public Path']) . "</code></p>";
            $html .= "<p>مسار base_path: <code>" . htmlspecialchars($debugInfo['Base Path']) . "</code></p>";
            $html .= "<h3>المسارات التي تم البحث فيها:</h3><ul>";
            foreach ($debugInfo['Paths Searched'] as $search) {
                $statusColor = $search['exists'] === 'Yes' ? 'green' : 'red';
                $html .= "<li style='margin-bottom: 8px;'><code>" . htmlspecialchars($search['path']) . "</code> - موجود: <strong style='color: {$statusColor};'>" . $search['exists'] . "</strong> (مجلد: " . $search['is_dir'] . ")</li>";
            }
            $html .= "</ul></body></html>";
            return response($html, 404);
        }
        
        return response()->file($filePath);
    }
}
