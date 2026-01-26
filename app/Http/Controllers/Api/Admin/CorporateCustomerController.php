<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorporateCustomer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Payment;

class CorporateCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = CorporateCustomer::with('user');

        // 1. Search Logic
        if ($request->has('search')) {
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
        if ($request->has('status')) {
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

        $customers = $query->paginate(10);
        return response()->json(['status' => true, 'message' => 'Corporate customers retrieved', 'data' => $customers]);
    }

    public function blockedIndex()
    {
        $customers = CorporateCustomer::whereHas('user', function ($query) {
            $query->where('status', 'blocked');
        })->with('user')->orderBy('created_at', 'desc')->paginate(10);
        
        return response()->json(['status' => true, 'message' => 'Blocked corporate customers retrieved', 'data' => $customers]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name_ar' => 'required|string|max:255',
            'company_name_en' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'phone' => 'required|string|unique:users',
            'commercial_record_number' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $password = $request->password ?? Str::random(10);
        
        $user = User::create([
            'name' => $request->company_name_en,
            'email' => $request->email,
            'password' => Hash::make($password),
            'type' => 'corporate_customer',
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        $profile = CorporateCustomer::create([
            'user_id' => $user->id,
            'company_name_ar' => $request->company_name_ar,
            'company_name_en' => $request->company_name_en,
            'commercial_record_number' => $request->commercial_record_number,
            'tax_number' => $request->tax_number,
            'address' => $request->address,
        ]);
        
        $profile->load('user');

        return response()->json(['status' => true, 'message' => 'Corporate Customer created successfully. Password: ' . $password, 'data' => $profile]);
    }

    public function show($id)
    {
        $profile = CorporateCustomer::with(['user.city'])->where('user_id', $id)->orWhere('id', $id)->first();
        if (!$profile) return response()->json(['status' => false, 'message' => 'Profile not found'], 404);

        $userId = $profile->user_id;

        // Statistics Summary
        $totalPayments = Payment::where('user_id', $userId)
            ->where('status', 'paid')
            ->sum('amount');

        $orderCount = Order::where('user_id', $userId)->count();

        // Performance Trend (Last 30 days)
        $now = Carbon::now();
        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $chartData[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'value' => Order::where('user_id', $userId)
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->count()
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Profile retrieved',
            'data' => [
                'profile' => $profile,
                'statistics' => [
                    'total_payments' => $totalPayments,
                    'order_count' => $orderCount,
                    'performance_chart' => $chartData
                ]
            ]
        ]);
    }

    public function statistics($id)
    {
        return $this->show($id);
    }

    public function update(Request $request, $id)
    {
        $profile = CorporateCustomer::where('user_id', $id)->orWhere('id', $id)->first();
        if (!$profile) return response()->json(['status' => false, 'message' => 'Profile not found'], 404);
        
        $user = $profile->user;
        if ($user) {
            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('password') && $request->password) $user->password = Hash::make($request->password);
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('status')) $user->status = $request->status;
            
            if ($request->has('company_name_en')) {
                $user->name = $request->company_name_en;
            }
            
            $user->save();
        }

        $profile->update($request->except(['email', 'password', 'phone', 'status', 'type']));
        
        return response()->json(['status' => true, 'message' => 'Profile updated', 'data' => $profile->load('user')]);
    }

    public function destroy($id)
    {
        $profile = CorporateCustomer::where('user_id', $id)->orWhere('id', $id)->first();
        if (!$profile) return response()->json(['status' => false, 'message' => 'Profile not found'], 404);

        if ($profile->user) {
            $profile->user->delete();
        } else {
            $profile->delete();
        }

        return response()->json(['status' => true, 'message' => 'Corporate customer deleted successfully']);
    }

    public function download()
    {
        $customers = CorporateCustomer::with('user')->get();
        return $this->generateCsv($customers, "corporate_customers.csv");
    }

    private function generateCsv($customers, $filename)
    {
        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID', 'Company Name', 'Email', 'Phone', 'Created At']); 

        foreach ($customers as $customer) {
            fputcsv($handle, [
                $customer->id,
                $customer->company_name_ar . ' (' . $customer->company_name_en . ')',
                $customer->user ? $customer->user->email : '',
                $customer->user ? $customer->user->phone : '',
                $customer->created_at,
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
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
