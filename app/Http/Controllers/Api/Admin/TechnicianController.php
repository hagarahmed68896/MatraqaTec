<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Review;
 
class TechnicianController extends Controller
{
  public function index(Request $request)
{
    $query = Technician::with('user', 'service');

    // 1. Search Logic (Existing)
    if ($request->has('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name_en', 'like', "%{$search}%")
              ->orWhere('name_ar', 'like', "%{$search}%")
              ->orWhereHas('user', function ($q2) use ($search) {
                  $q2->where('email', 'like', "%{$search}%")
                     ->orWhere('phone', 'like', "%{$search}%")
                     ->orWhere('name', 'like', "%{$search}%");
              });
        });
    }

    // 2. NEW: Filter by Status (Active/Inactive)
    // This will filter the results to only show active or inactive
    if ($request->has('status')) {
        $status = $request->status; // 'active' or 'inactive'
        $query->whereHas('user', function ($q) use ($status) {
            $q->where('status', $status);
        });
    }

    // 3. Sorting Logic (Existing)
    if ($request->has('sort_by')) {
        switch ($request->sort_by) {
            case 'name':
                $query->orderBy('name_en', 'asc');
                break;
            case 'status':
                $query->join('users', 'technicians.user_id', '=', 'users.id')
                      ->orderBy('users.status', 'asc')
                      ->select('technicians.*');
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

    $technicians = $query->paginate(10);
    return response()->json(['status' => true, 'message' => 'Technicians retrieved', 'data' => $technicians]);
}

    public function blockedIndex()
    {
        $technicians = Technician::whereHas('user', function ($query) {
            $query->where('status', 'blocked');
        })->with('user', 'service')->orderBy('created_at', 'desc')->paginate(10);
        
        return response()->json(['status' => true, 'message' => 'Blocked technicians retrieved', 'data' => $technicians]);
    }

    public function store(Request $request)
    {
        $locale = app()->getLocale();
        $validator = Validator::make($request->all(), [
            'name_en' => $locale == 'en' ? 'required|string|max:255' : 'nullable|string|max:255',
            'name_ar' => $locale == 'ar' ? 'required|string|max:255' : 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|unique:users',
            'service_id' => 'nullable|exists:services,id',
            'years_experience' => 'nullable|integer',
            'status' => 'nullable|string', // Account status for user
            'bio_en' => 'nullable|string',
            'bio_ar' => 'nullable|string',
            'image' => 'nullable|image|max:2048', // Allow image upload
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $password = $request->password ?? Str::random(10);
        $name = $request->name_en ?? $request->name_ar; // Use provided name for User model

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'type' => 'technician',
            'phone' => $request->phone,
            'status' => $request->status ?? 'active',
        ]);
        
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('technicians', 'public');
        }

        $technician = Technician::create([
            'user_id' => $user->id,
            'service_id' => $request->service_id,
            'maintenance_company_id' => $request->maintenance_company_id,
            'national_id' => $request->national_id, // Ensure this is handled if passed
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'bio_en' => $request->bio_en,
            'bio_ar' => $request->bio_ar,
            'years_experience' => $request->years_experience ?? 0,
            'image' => $imagePath,
        ]);
        
        $technician->load('user');

        return response()->json(['status' => true, 'message' => 'Technician created successfully. Password: ' . $password, 'data' => $technician]);
    }

    public function show($id)
    {
        $technician = Technician::with('user', 'service', 'maintenanceCompany')->where('user_id', $id)->orWhere('id', $id)->first();
        if (!$technician) return response()->json(['status' => false, 'message' => 'Technician not found'], 404);

        // 1. Statistics Summary
        $totalCompletedOrders = Order::where('technician_id', $technician->id)
            ->where('status', 'completed')
            ->count();
        $totalRevenue = Order::where('technician_id', $technician->id)
            ->where('status', 'completed')
            ->sum('total_price');
        $averageRating = Review::where('technician_id', $technician->id)->avg('rating') ?? 0;

        // 2. Performance Trend (Last 30 days)
        $now = \Carbon\Carbon::now();
        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $chartData[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'value' => Order::where('technician_id', $technician->id)
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->count()
            ];
        }

        return response()->json([
            'status' => true, 
            'message' => 'Technician retrieved', 
            'data' => [
                'profile' => $technician,
                'statistics' => [
                    'total_completed_orders' => $totalCompletedOrders,
                    'total_revenue' => $totalRevenue,
                    'average_rating' => round($averageRating, 1),
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
        $technician = Technician::where('user_id', $id)->orWhere('id', $id)->first();
        if (!$technician) return response()->json(['status' => false, 'message' => 'Technician not found'], 404);
        
        $validator = Validator::make($request->all(), [
            'email' => 'nullable|string|email|max:255|unique:users,email,'.$technician->user_id,
            'phone' => 'nullable|string|unique:users,phone,'.$technician->user_id,
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
             return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }
        
        $user = $technician->user;
        if ($user) {
            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('password') && $request->password) $user->password = Hash::make($request->password);
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('status')) $user->status = $request->status;
            
            // Sync user name if names are updated
            if ($request->has('name_en') || $request->has('name_ar')) {
                $user->name = $request->name_en ?? $request->name_ar ?? $technician->name_en ?? $technician->name_ar;
            }
            
            $user->save();
        }

        $data = $request->except(['email', 'password', 'phone', 'status', 'type', 'image']);
        
        if ($request->hasFile('image')) {
            // Delete old image if exists? 
            // if ($technician->image) Storage::disk('public')->delete($technician->image);
            $data['image'] = $request->file('image')->store('technicians', 'public');
        }

        $technician->update($data);
        
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
        return $this->generateCsv($technicians, "technicians.csv");
    }

    public function downloadBlocked()
    {
        $technicians = Technician::whereHas('user', function ($query) {
            $query->where('status', 'blocked');
        })->with('user')->get();
        
        return $this->generateCsv($technicians, "blocked_technicians.csv");
    }

    private function generateCsv($technicians, $filename)
    {
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
