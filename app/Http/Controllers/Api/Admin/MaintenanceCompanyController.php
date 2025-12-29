<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCompany;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MaintenanceCompanyController extends Controller
{
    public function index()
    {
        $companies = MaintenanceCompany::with('user')->orderBy('created_at', 'desc')->paginate(10);
        return response()->json(['status' => true, 'message' => 'Companies retrieved', 'data' => $companies]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name_en' => 'required|string|max:255',
            'company_name_ar' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $password = $request->password ?? Str::random(10);
        $name = $request->company_name_en;

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'type' => 'maintenance_company',
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        $company = MaintenanceCompany::create([
            'user_id' => $user->id,
            'company_name_en' => $request->company_name_en,
            'company_name_ar' => $request->company_name_ar,
            'commercial_record_number' => $request->commercial_record_number,
            'tax_number' => $request->tax_number,
            'address' => $request->address,
        ]);
        
        $company->load('user');

        return response()->json(['status' => true, 'message' => 'Company created successfully. Password: ' . $password, 'data' => $company]);
    }

    public function show($id)
    {
        $company = MaintenanceCompany::with('user')->where('user_id', $id)->orWhere('id', $id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);
        return response()->json(['status' => true, 'message' => 'Company retrieved', 'data' => $company]);
    }

    public function update(Request $request, $id)
    {
        $company = MaintenanceCompany::where('user_id', $id)->orWhere('id', $id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);
        
        $user = $company->user;
        if ($user) {
            if ($request->has('name')) $user->name = $request->name;
            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('password') && $request->password) $user->password = Hash::make($request->password);
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('status')) $user->status = $request->status;
            $user->save();
        }

        $company->update($request->except(['name', 'email', 'password', 'phone', 'status', 'type']));
        
        return response()->json(['status' => true, 'message' => 'Company updated', 'data' => $company->load('user')]);
    }

    public function destroy($id)
    {
        $company = MaintenanceCompany::where('user_id', $id)->orWhere('id', $id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);

        if ($company->user) {
            $company->user->delete();
        } else {
            $company->delete();
        }

        return response()->json(['status' => true, 'message' => 'Company deleted successfully']);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids; 
        if (!is_array($ids)) {
             return response()->json(['status' => false, 'message' => 'IDs must be an array'], 422);
        }
        
        $count = 0;
        foreach($ids as $id) {
             $company = MaintenanceCompany::where('id', $id)->orWhere('user_id', $id)->first();
             if ($company) {
                 if ($company->user) $company->user->delete();
                 else $company->delete();
                 $count++;
             }
        }

        return response()->json(['status' => true, 'message' => "$count Companies deleted successfully"]);
    }
    
    public function download()
    {
        $companies = MaintenanceCompany::with('user')->get();
        $filename = "maintenance_companies.csv";
        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID', 'Name (AR)', 'Name (EN)', 'Email', 'Phone']); 

        foreach ($companies as $comp) {
            fputcsv($handle, [
                $comp->id,
                $comp->company_name_ar,
                $comp->company_name_en,
                $comp->user ? $comp->user->email : '',
                $comp->user ? $comp->user->phone : '',
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
