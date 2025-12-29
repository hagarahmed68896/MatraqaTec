<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TechnicianController extends Controller
{
    public function index()
    {
        $technicians = Technician::with('user', 'service')->orderBy('created_at', 'desc')->paginate(10);
        return response()->json(['status' => true, 'message' => 'Technicians retrieved', 'data' => $technicians]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $password = $request->password ?? Str::random(10);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'type' => 'technician',
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        $technician = Technician::create([
            'user_id' => $user->id,
            'service_id' => $request->service_id,
            'maintenance_company_id' => $request->maintenance_company_id,
            'national_id' => $request->national_id,
        ]);
        
        $technician->load('user');

        return response()->json(['status' => true, 'message' => 'Technician created successfully. Password: ' . $password, 'data' => $technician]);
    }

    public function show($id)
    {
        $technician = Technician::with('user', 'service', 'maintenanceCompany')->where('user_id', $id)->orWhere('id', $id)->first();
        if (!$technician) return response()->json(['status' => false, 'message' => 'Technician not found'], 404);
        return response()->json(['status' => true, 'message' => 'Technician retrieved', 'data' => $technician]);
    }

    public function update(Request $request, $id)
    {
        $technician = Technician::where('user_id', $id)->orWhere('id', $id)->first();
        if (!$technician) return response()->json(['status' => false, 'message' => 'Technician not found'], 404);
        
        $user = $technician->user;
        if ($user) {
            if ($request->has('name')) $user->name = $request->name;
            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('password') && $request->password) $user->password = Hash::make($request->password);
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('status')) $user->status = $request->status;
            $user->save();
        }

        $technician->update($request->except(['name', 'email', 'password', 'phone', 'status', 'type']));
        
        return response()->json(['status' => true, 'message' => 'Technician updated', 'data' => $technician->load('user')]);
    }

    public function destroy($id)
    {
        $technician = Technician::where('user_id', $id)->orWhere('id', $id)->first();
        if (!$technician) return response()->json(['status' => false, 'message' => 'Technician not found'], 404);

        if ($technician->user) {
            $technician->user->delete();
        } else {
            $technician->delete();
        }

        return response()->json(['status' => true, 'message' => 'Technician deleted successfully']);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids; 
        if (!is_array($ids)) {
             return response()->json(['status' => false, 'message' => 'IDs must be an array'], 422);
        }
        
        $count = 0;
        foreach($ids as $id) {
             $technician = Technician::where('id', $id)->orWhere('user_id', $id)->first();
             if ($technician) {
                 if ($technician->user) $technician->user->delete();
                 else $technician->delete();
                 $count++;
             }
        }

        return response()->json(['status' => true, 'message' => "$count Technicians deleted successfully"]);
    }
    
    public function download()
    {
        $technicians = Technician::with('user')->get();
        $filename = "technicians.csv";
        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Service ID', 'National ID']); 

        foreach ($technicians as $tech) {
            fputcsv($handle, [
                $tech->id,
                $tech->user ? $tech->user->name : '',
                $tech->user ? $tech->user->email : '',
                $tech->user ? $tech->user->phone : '',
                $tech->service_id,
                $tech->national_id,
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
