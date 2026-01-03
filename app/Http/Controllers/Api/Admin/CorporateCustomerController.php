<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorporateCustomer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CorporateCustomerController extends Controller
{
    public function index()
    {
        $customers = CorporateCustomer::with('user')->orderBy('created_at', 'desc')->paginate(10);
        return response()->json(['status' => true, 'message' => 'Customers retrieved', 'data' => $customers]);
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
            'company_name_en' => 'required|string|max:255',
            'company_name_ar' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|unique:users',
            'commercial_record_number' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $password = $request->password ?? Str::random(10);
        // Use English company name as the main User name
        $name = $request->company_name_en;

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'type' => 'corporate_company',
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        $profile = CorporateCustomer::create([
            'user_id' => $user->id,
            'company_name_en' => $request->company_name_en,
            'company_name_ar' => $request->company_name_ar,
            'commercial_record_number' => $request->commercial_record_number,
            'tax_number' => $request->tax_number,
            'address' => $request->address,
        ]);
        
        $profile->load('user');

        return response()->json(['status' => true, 'message' => 'Customer created successfully. Password: ' . $password, 'data' => $profile]);
    }
    
    public function show($id)
    {
        $profile = CorporateCustomer::with('user')->where('user_id', $id)->orWhere('id', $id)->first();
        if (!$profile) return response()->json(['status' => false, 'message' => 'Profile not found'], 404);
        return response()->json(['status' => true, 'message' => 'Profile retrieved', 'data' => $profile]);
    }

    public function update(Request $request, $id)
    {
        $profile = CorporateCustomer::where('user_id', $id)->orWhere('id', $id)->first();
        if (!$profile) return response()->json(['status' => false, 'message' => 'Profile not found'], 404);
        
        $user = $profile->user;
        if ($user) {
            if ($request->has('name')) $user->name = $request->name;
            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('password') && $request->password) $user->password = Hash::make($request->password);
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('status')) $user->status = $request->status;
            $user->save();
        }

        $profile->update($request->except(['name', 'email', 'password', 'phone', 'status', 'type']));
        
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

        return response()->json(['status' => true, 'message' => 'Customer deleted successfully']);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids; 
        if (!is_array($ids)) {
             return response()->json(['status' => false, 'message' => 'IDs must be an array'], 422);
        }
        
        $count = 0;
        foreach($ids as $id) {
             $profile = CorporateCustomer::where('id', $id)->orWhere('user_id', $id)->first();
             if ($profile) {
                 if ($profile->user) $profile->user->delete();
                 else $profile->delete();
                 $count++;
             }
        }

        return response()->json(['status' => true, 'message' => "$count Customers deleted successfully"]);
    }

    public function download()
    {
        $customers = CorporateCustomer::with('user')->get();
        return $this->generateCsv($customers, "corporate_customers.csv");
    }

    public function downloadBlocked()
    {
        $customers = CorporateCustomer::whereHas('user', function ($query) {
            $query->where('status', 'blocked');
        })->with('user')->get();
        
        return $this->generateCsv($customers, "blocked_corporate_customers.csv");
    }

    private function generateCsv($customers, $filename)
    {
        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID', 'Company Name (AR)', 'Company Name (EN)', 'Email', 'Phone']); 

        foreach ($customers as $customer) {
            fputcsv($handle, [
                $customer->id,
                $customer->company_name_ar,
                $customer->company_name_en,
                $customer->user ? $customer->user->email : '',
                $customer->user ? $customer->user->phone : '',
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
