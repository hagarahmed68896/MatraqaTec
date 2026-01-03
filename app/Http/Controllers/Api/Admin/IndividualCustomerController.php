<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\IndividualCustomer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class IndividualCustomerController extends Controller
{
    public function index()
    {
        $customers = IndividualCustomer::with('user')->orderBy('created_at', 'desc')->paginate(10);
        return response()->json(['status' => true, 'message' => 'Customers retrieved', 'data' => $customers]);
    }

    public function blockedIndex()
    {
        $customers = IndividualCustomer::whereHas('user', function ($query) {
            $query->where('status', 'blocked');
        })->with('user')->orderBy('created_at', 'desc')->paginate(10);
        
        return response()->json(['status' => true, 'message' => 'Blocked customers retrieved', 'data' => $customers]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name_en' => 'required|string|max:255',
            'last_name_en' => 'required|string|max:255',
            'first_name_ar' => 'required|string|max:255',
            'last_name_ar' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|unique:users',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $password = $request->password ?? Str::random(10);
        $name = $request->first_name_en . ' ' . $request->last_name_en;

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'type' => 'individual',
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        $profile = IndividualCustomer::create([
            'user_id' => $user->id,
            'first_name_en' => $request->first_name_en,
            'first_name_ar' => $request->first_name_ar,
            'last_name_en' => $request->last_name_en,
            'last_name_ar' => $request->last_name_ar,
            'address' => $request->address,
        ]);
        
        $profile->load('user');

        return response()->json(['status' => true, 'message' => 'Customer created successfully. Password: ' . $password, 'data' => $profile]);
    }

    public function show($id)
    {
        $profile = IndividualCustomer::with('user')->where('user_id', $id)->orWhere('id', $id)->first();
        if (!$profile) return response()->json(['status' => false, 'message' => 'Profile not found'], 404);
        return response()->json(['status' => true, 'message' => 'Profile retrieved', 'data' => $profile]);
    }

    public function update(Request $request, $id)
    {
        $profile = IndividualCustomer::where('user_id', $id)->orWhere('id', $id)->first();
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
        $profile = IndividualCustomer::where('user_id', $id)->orWhere('id', $id)->first();
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
             $profile = IndividualCustomer::where('id', $id)->orWhere('user_id', $id)->first();
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
        $customers = IndividualCustomer::with('user')->get();
        return $this->generateCsv($customers, "individual_customers.csv");
    }

    public function downloadBlocked()
    {
        $customers = IndividualCustomer::whereHas('user', function ($query) {
            $query->where('status', 'blocked');
        })->with('user')->get();
        
        return $this->generateCsv($customers, "blocked_individual_customers.csv");
    }

    private function generateCsv($customers, $filename)
    {
        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Created At']); 

        foreach ($customers as $customer) {
            fputcsv($handle, [
                $customer->id,
                $customer->user ? $customer->user->name : '',
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
