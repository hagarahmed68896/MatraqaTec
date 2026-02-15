<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCompany;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MaintenanceCompanyController extends Controller
{
    use \App\Traits\HasAutoAssignment;

    public function show(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $company = MaintenanceCompany::with(['user', 'technicians', 'districts', 'services', 'city'])->where('user_id', $user->id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);
        
        return response()->json(['status' => true, 'message' => 'Company retrieved', 'data' => $company]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $company = MaintenanceCompany::where('user_id', $user->id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|min:8|confirmed',
            'phone' => 'sometimes|string|max:20',
            'company_name_ar' => 'sometimes|string|max:255',
            'company_name_en' => 'sometimes|string|max:255',
            'bank_name' => 'sometimes|string|max:255',
            'account_name' => 'sometimes|string|max:255',
            'account_number' => 'sometimes|string|max:255',
            'bank_address' => 'sometimes|string|max:500',
            'iban' => 'sometimes|string|max:50',
            'swift_code' => 'sometimes|string|max:20',
            'city_id' => 'sometimes|exists:cities,id',
            'address' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $userModel = $company->user;
        if ($userModel) {
            if ($request->has('name')) $userModel->name = $request->name;
            if ($request->has('email')) $userModel->email = $request->email;
            if ($request->has('password') && $request->password) $userModel->password = Hash::make($request->password);
            if ($request->has('phone')) $userModel->phone = $request->phone;
            $userModel->save();
        }

        $company->update($request->only([
            'company_name_ar', 'company_name_en', 'bank_name', 'account_name', 
            'account_number', 'bank_address', 'iban', 'swift_code', 'city_id', 'address'
        ]));
        
        return response()->json([
            'status' => true, 
            'message' => 'Company profile updated successfully', 
            'data' => $company->load('user', 'city')
        ]);
    }

    /**
     * Update company password
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Current password is incorrect'], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password updated successfully'
        ]);
    }


    /**
     * List all technicians for the company (for "Show More" feature)
     */
    public function listTechnicians(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $company = MaintenanceCompany::where('user_id', $user->id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);

        $query = \App\Models\Technician::with(['user:id,name,avatar,phone,is_online', 'service:id,name_ar'])
            ->where('maintenance_company_id', $company->id)
            ->withCount(['orders as completed_orders_count' => function($q) {
                $q->where('status', 'completed');
            }])
            ->withAvg('reviews', 'rating');

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Online/Offline Filter
        if ($request->has('is_online')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('is_online', $request->boolean('is_online'));
            });
        }

        // Apply Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        } elseif ($request->filled('category_ids')) {
            $catIds = is_array($request->category_ids) ? $request->category_ids : explode(',', $request->category_ids);
            $query->whereIn('category_id', $catIds);
        }

        if ($request->filled('service_ids')) {
            $svcIds = is_array($request->service_ids) ? $request->service_ids : explode(',', $request->service_ids);
            $query->whereIn('service_id', $svcIds);
        }

        if ($request->filled('district_ids')) {
            $distIds = is_array($request->district_ids) ? $request->district_ids : explode(',', $request->district_ids);
            $query->where(function($q) use ($distIds) {
                foreach($distIds as $dId) {
                    $q->orWhereJsonContains('districts', (string)$dId);
                }
            });
        }

        if ($request->has('availability')) {
            $query->where('availability_status', $request->availability === 'available' ? 'available' : 'unavailable');
        }

        if ($request->filled('min_rating')) {
            $minRating = $request->min_rating;
            $query->whereRaw('(SELECT AVG(rating) FROM reviews WHERE reviews.technician_id = technicians.id) >= ?', [$minRating]);
        }

        $technicians = $query->orderByDesc('completed_orders_count')->get();

        return response()->json(['status' => true, 'message' => 'Technicians retrieved', 'data' => $technicians]);
    }
    /**
     * Submit a request to add a new technician for the company
     */
    public function addTechnician(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $company = MaintenanceCompany::where('user_id', $user->id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'iqama_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'required|exists:services,id',
            'service_id' => 'required|exists:services,id',
            'districts' => 'required|array',
            'districts.*' => 'exists:districts,id',
            'years_experience' => 'required|integer|min:0',
            'bio_ar' => 'required|string',
            'bio_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        // Handle File Uploads
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('technician_requests/photos', 'public');
        }

        $iqamaPath = null;
        if ($request->hasFile('iqama_photo')) {
            $iqamaPath = $request->file('iqama_photo')->store('technician_requests/iqamas', 'public');
        }

        $techRequest = \App\Models\TechnicianRequest::create([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'name' => $request->name_ar, // For backward compatibility
            'phone' => $request->phone,
            'email' => $request->email,
            'photo' => $imagePath,
            'iqama_photo' => $iqamaPath,
            'category_id' => $request->category_id,
            'service_id' => $request->service_id,
            'years_experience' => $request->years_experience,
            'bio_ar' => $request->bio_ar,
            'bio_en' => $request->bio_en,
            'districts' => $request->districts,
            'maintenance_company_id' => $company->id,
            'status' => 'pending',
        ]);

        // Notify Admins
        $admins = \App\Models\User::where('type', 'admin')->get();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type' => 'alert',
                'title_ar' => 'طلب إضافة فني جديد',
                'title_en' => 'New Technician Request',
                'body_ar' => "قامت شركة {$company->company_name_ar} بإرسال طلب إضافة فني جديد: {$request->name_ar}",
                'body_en' => "Company {$company->company_name_en} sent a request to add a new technician: {$request->name_en}",
                'target_audience' => 'all',
                'data' => [
                    'type' => 'technician_request',
                    'request_id' => $techRequest->id,
                    'company_id' => $company->id
                ],
                'status' => 'sent'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال طلب إضافة الفني. سيتم تفعيل حسابه بعد مراجعة وموافقة إدارة مطرقة تك',
            'data' => $techRequest
        ]);
    }

    /**
     * Get featured technicians (أبرز الفنيين) - Top rated with most orders
     */
    public function getFeaturedTechnicians(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $company = MaintenanceCompany::where('user_id', $user->id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);

        $limit = $request->get('limit', 5);

        $technicians = \App\Models\Technician::with(['user:id,name,avatar,phone,is_online', 'service:id,name_ar'])
            ->where('maintenance_company_id', $company->id)
            ->withCount(['orders as completed_orders_count' => function($q) {
                $q->where('status', 'completed');
            }])
            ->withAvg('reviews', 'rating')
            ->get()
            ->sortByDesc(function($tech) {
                return ($tech->reviews_avg_rating ?? 0) * 0.7 + ($tech->completed_orders_count ?? 0) * 0.3;
            })
            ->take($limit)
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Featured technicians retrieved',
            'data' => $technicians
        ]);
    }

    /**
     * Show complete technician details with reviews
     */
    public function showTechnician(Request $request, $id)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $company = MaintenanceCompany::where('user_id', $user->id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);

        $technician = \App\Models\Technician::with([
            'user:id,name,email,phone,avatar,is_online,created_at',
            'service:id,name_ar,name_en,parent_id',
            'service.parent:id,name_ar,name_en',
            'reviews.user:id,name,avatar',
            'reviews.order:id,order_number'
        ])
        ->where('id', $id)
        ->where('maintenance_company_id', $company->id)
        ->withCount([
            'orders as total_orders_count',
            'orders as completed_orders_count' => function($q) {
                $q->where('status', 'completed');
            },
            'orders as in_progress_orders_count' => function($q) {
                $q->whereIn('status', ['accepted', 'scheduled', 'in_progress']);
            }
        ])
        ->withAvg('reviews', 'rating')
        ->first();

        if (!$technician) {
            return response()->json(['status' => false, 'message' => 'Technician not found or not authorized'], 404);
        }

        // Format response with additional stats
        $data = $technician->toArray();
        $data['stats'] = [
            'total_orders' => $technician->total_orders_count ?? 0,
            'completed_orders' => $technician->completed_orders_count ?? 0,
            'in_progress_orders' => $technician->in_progress_orders_count ?? 0,
            'average_rating' => round($technician->reviews_avg_rating ?? 0, 1),
            'total_reviews' => $technician->reviews->count(),
        ];

        // Format reviews
        $data['reviews'] = $technician->reviews->map(function($review) {
            return [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => $review->user->id ?? null,
                    'name' => $review->user->name ?? 'Unknown',
                    'avatar' => $review->user->avatar ?? null,
                ],
                'order_number' => $review->order->order_number ?? null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Technician details retrieved',
            'data' => $data
        ]);
    }

    /**
     * Update technician information
     */
    public function updateTechnician(Request $request, $id)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $company = MaintenanceCompany::where('user_id', $user->id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);

        $technician = \App\Models\Technician::where('id', $id)
            ->where('maintenance_company_id', $company->id)
            ->first();

        if (!$technician) {
            return response()->json(['status' => false, 'message' => 'Technician not found or not authorized'], 404);
        }

        $request->validate([
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'category_id' => 'sometimes|exists:services,id',
            'service_id' => 'sometimes|exists:services,id',
            'districts' => 'sometimes|array',
            'availability_status' => 'sometimes|in:available,unavailable,busy',
        ]);

        $technician->update($request->only([
            'name_ar', 'name_en', 'category_id', 'service_id', 
            'districts', 'availability_status'
        ]));

        // Update user info if provided
        if ($technician->user && ($request->has('name_ar') || $request->has('phone'))) {
            $technician->user->update([
                'name' => $request->name_ar ?? $technician->user->name,
                'phone' => $request->phone ?? $technician->user->phone,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Technician updated successfully',
            'data' => $technician->load('user', 'service')
        ]);
    }

    /**
     * Delete technician
     */
    public function deleteTechnician($id)
    {
        $user = auth()->user();
        if ($user->type !== 'maintenance_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $company = MaintenanceCompany::where('user_id', $user->id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);

        $technician = \App\Models\Technician::where('id', $id)
            ->where('maintenance_company_id', $company->id)
            ->first();

        if (!$technician) {
            return response()->json(['status' => false, 'message' => 'Technician not found or not authorized'], 404);
        }

        // Check if technician has active orders
        $activeOrders = \App\Models\Order::where('technician_id', $technician->id)
            ->whereIn('status', ['new', 'accepted', 'scheduled', 'in_progress'])
            ->count();

        if ($activeOrders > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete technician with active orders'
            ], 422);
        }

        $technician->delete();

        return response()->json([
            'status' => true,
            'message' => 'Technician deleted successfully'
        ]);
    }

    /**
     * Unified Search for Company: Orders and Technicians
     */
    public function search(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $company = MaintenanceCompany::where('user_id', $user->id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);

        $queryStr = trim($request->get('query'));
        
        if (!$queryStr) {
            return response()->json([
                'status' => true,
                'message' => 'Query is required',
                'data' => [
                    'orders' => [],
                    'technicians' => []
                ]
            ]);
        }

        // Save to Search History
        \App\Models\SearchHistory::updateOrCreate([
            'user_id' => $user->id,
            'query' => $queryStr
        ], [
            'updated_at' => now()
        ]);

        // Search Orders
        $orders = \App\Models\Order::with(['user', 'service', 'technician.user'])
            ->where('maintenance_company_id', $company->id)
            ->where(function($q) use ($queryStr) {
                $q->where('order_number', 'like', "%{$queryStr}%")
                  ->orWhereHas('user', function($q2) use ($queryStr) {
                      $q2->where('name', 'like', "%{$queryStr}%");
                  })
                  ->orWhereHas('technician.user', function($q3) use ($queryStr) {
                      $q3->where('name', 'like', "%{$queryStr}%");
                  });
            })
            ->latest()
            ->take(20)
            ->get();

        // Search Technicians
        $techQuery = \App\Models\Technician::with(['user'])
            ->where('maintenance_company_id', $company->id)
            ->where(function($q) use ($queryStr) {
                $q->where('name_ar', 'like', "%{$queryStr}%")
                  ->orWhere('name_en', 'like', "%{$queryStr}%")
                  ->orWhereHas('user', function($q2) use ($queryStr) {
                      $q2->where('name', 'like', "%{$queryStr}%")
                        ->orWhere('phone', 'like', "%{$queryStr}%");
                  });
            });

        // Apply Filters to Technicians Search
        if ($request->filled('category_id')) {
            $techQuery->where('category_id', $request->category_id);
        } elseif ($request->filled('category_ids')) {
            $catIds = is_array($request->category_ids) ? $request->category_ids : explode(',', $request->category_ids);
            $techQuery->whereIn('category_id', $catIds);
        }

        if ($request->filled('service_ids')) {
            $svcIds = is_array($request->service_ids) ? $request->service_ids : explode(',', $request->service_ids);
            $techQuery->whereIn('service_id', $svcIds);
        }

        if ($request->filled('district_ids')) {
            $distIds = is_array($request->district_ids) ? $request->district_ids : explode(',', $request->district_ids);
            $techQuery->where(function($q) use ($distIds) {
                foreach($distIds as $dId) {
                    $q->orWhereJsonContains('districts', (string)$dId);
                }
            });
        }

        if ($request->has('availability')) {
            $techQuery->where('availability_status', $request->availability === 'available' ? 'available' : 'unavailable');
        }

        if ($request->filled('min_rating')) {
            $minRating = $request->min_rating;
            $techQuery->whereRaw('(SELECT AVG(rating) FROM reviews WHERE reviews.technician_id = technicians.id) >= ?', [$minRating]);
        }

        $technicians = $techQuery->latest()->take(20)->get();

        return response()->json([
            'status' => true,
            'message' => 'Search results retrieved successfully',
            'data' => [
                'orders' => $orders,
                'technicians' => $technicians
            ]
        ]);
    }

    /**
     * Get available technicians for order assignment
     * Filtered by service, district, availability, sorted by rating
     */
    public function getAvailableTechnicians(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $company = MaintenanceCompany::where('user_id', $user->id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);

        $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'district_id' => 'nullable|exists:districts,id',
            'order_id' => 'nullable|exists:orders,id',
            'scheduled_at' => 'nullable|date',
        ]);

        $query = \App\Models\Technician::with(['user:id,name,avatar,phone,is_online', 'service:id,name_ar,name_en'])
            ->where('maintenance_company_id', $company->id);

        // Filter by service if provided
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by district if provided
        if ($request->filled('district_id')) {
            $query->whereJsonContains('districts', (string)$request->district_id);
        }

        // --- NEW: Schedule Conflict Check ---
        $scheduledAt = null;
        if ($request->filled('order_id')) {
            $order = \App\Models\Order::find($request->order_id);
            $scheduledAt = $order->scheduled_at;
        } elseif ($request->filled('scheduled_at')) {
            $scheduledAt = \Carbon\Carbon::parse($request->scheduled_at);
        }

        if ($scheduledAt) {
            $start = (clone $scheduledAt)->subHours(1)->addMinute();
            $end = (clone $scheduledAt)->addHours(1)->subMinute();

            $query->whereDoesntHave('orders', function($q) use ($start, $end) {
                $q->whereIn('status', ['accepted', 'scheduled', 'in_progress'])
                  ->whereBetween('scheduled_at', [$start, $end]);
            })->whereDoesntHave('appointments', function($q) use ($start, $end) {
                $q->whereIn('status', ['scheduled', 'in_progress'])
                  ->whereBetween('appointment_date', [$start, $end]);
            });
        }

        // Fallback to online status ONLY if no specific time is provided or explicitly requested
        if ($request->boolean('available_only', false) && !$scheduledAt) {
            $query->whereHas('user', function($q) {
                $q->where('is_online', true);
            });
        }

        // Get technicians with average rating
        $technicians = $query->withAvg('reviews', 'rating')
            ->get()
            ->sortByDesc('reviews_avg_rating')
            ->values();

        // Format response
        $technicians = $technicians->map(function($tech) {
            return [
                'id' => $tech->id,
                'name' => $tech->user->name ?? $tech->name_ar,
                'avatar' => $tech->user->avatar ? asset('storage/' . $tech->user->avatar) : null,
                'phone' => $tech->user->phone,
                'rating' => round($tech->reviews_avg_rating ?? 0, 1),
                'is_online' => $tech->user->is_online ?? false,
                'districts' => $tech->districts,
                'service_name' => $tech->service->name_ar ?? '',
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Available technicians retrieved',
            'data' => $technicians
        ]);
    }

    /**
     * Get recent search history for the company user
     */
    public function getSearchHistory(Request $request)
    {
        $user = $request->user();
        $history = \App\Models\SearchHistory::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Search history retrieved',
            'data' => $history
        ]);
    }
}
