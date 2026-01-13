<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        // If search is active, save to history
        if ($request->filled('search')) {
            $search = $request->search;
            
            // Save search history for authenticated users
            if (auth()->check()) {
                \App\Models\SearchHistory::updateOrCreate(
                    ['user_id' => auth()->id(), 'query' => $search],
                    ['updated_at' => now()]
                );
            }

            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        // Filter by parent (categories vs sub-services)
        if ($request->has('parent_id')) {
            if ($request->parent_id === 'none') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        } elseif ($request->boolean('is_root')) {
            $query->whereNull('parent_id');
        }

        // Filter by featured (prominent services)
        if ($request->boolean('is_featured')) {
            $query->where('is_featured', true);
        }

        // Filter by price
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by city
        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        $services = $query->with(['children', 'city'])->get();

        // Add is_favorite status
        if (auth()->check()) {
            $userFavorites = auth()->user()->favorites()->pluck('service_id')->toArray();
            $services->map(function ($service) use ($userFavorites) {
                $service->is_favorite = in_array($service->id, $userFavorites);
                return $service;
            });
        } else {
            $services->map(function ($service) {
                $service->is_favorite = false;
                return $service;
            });
        }

        $data = [
            'results' => $services,
            'results_count' => $services->count(),
            'search_term' => $request->search ?? null,
        ];

        // If no search is active, provide history and suggestions
        if (!$request->filled('search')) {
            if (auth()->check()) {
                $data['search_history'] = \App\Models\SearchHistory::where('user_id', auth()->id())
                    ->latest()
                    ->take(5)
                    ->get();
            }
            
            // Suggested services (e.g., top level or marked as popular)
            $data['suggested_services'] = Service::whereNull('parent_id')->take(4)->get();
        }

        return response()->json([
            'status' => true, 
            'message' => 'Services retrieved successfully', 
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $service = Service::with(['city'])->withCount(['children', 'technicians'])->find($id);

        if (!$service) {
            return response()->json(['status' => false, 'message' => 'Service not found'], 404);
        }

        // Add is_favorite status
        if (auth()->check()) {
            $userFavorites = auth()->user()->favorites()->pluck('service_id')->toArray();
            $service->is_favorite = in_array($service->id, $userFavorites);
        } else {
            $service->is_favorite = false;
        }

        // Fetch related services (siblings)
        $relatedServices = Service::where('parent_id', $service->parent_id)
            ->where('id', '!=', $service->id)
            ->take(6)
            ->get();

        // Add favorite status to related services too
        if (auth()->check()) {
            $relatedServices->map(function ($rs) use ($userFavorites) {
                $rs->is_favorite = in_array($rs->id, $userFavorites);
                return $rs;
            });
        }

        return response()->json([
            'status' => true,
            'message' => 'Service retrieved successfully',
            'data' => [
                'service' => $service,
                'related_services' => $relatedServices
            ]
        ]);
    }

    public function destroySearchHistory($id)
    {
        $history = \App\Models\SearchHistory::where('user_id', auth()->id())->where('id', $id)->first();
        if ($history) {
            $history->delete();
            return response()->json(['status' => true, 'message' => 'Search history deleted']);
        }
        return response()->json(['status' => false, 'message' => 'History not found'], 404);
    }

    public function clearSearchHistory()
    {
        \App\Models\SearchHistory::where('user_id', auth()->id())->delete();
        return response()->json(['status' => true, 'message' => 'Search history cleared']);
    }

    public function toggleFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $userId = auth()->id();
        $serviceId = $request->service_id;

        $favorite = Favorite::where('user_id', $userId)
            ->where('service_id', $serviceId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['status' => true, 'message' => 'Removed from favorites', 'is_favorite' => false]);
        } else {
            Favorite::create([
                'user_id' => $userId,
                'service_id' => $serviceId,
            ]);
            return response()->json(['status' => true, 'message' => 'Added to favorites', 'is_favorite' => true]);
        }
    }

// Methods removed. Read-only controller.
}
