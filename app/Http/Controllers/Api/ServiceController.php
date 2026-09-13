<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    use \App\Traits\HandlesLocation, \App\Traits\HasAutoAssignment;

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

        // Category Filter (Single choice)
        if ($request->filled('category_id')) {
            $query->where('parent_id', $request->category_id);
        } elseif ($request->filled('category_ids')) {
            $catIds = is_array($request->category_ids) ? $request->category_ids : explode(',', $request->category_ids);
            $query->whereIn('parent_id', $catIds);
        }

        // Service Filter (Multi choice)
        if ($request->filled('service_ids')) {
            $svcIds = is_array($request->service_ids) ? $request->service_ids : explode(',', $request->service_ids);
            $query->whereIn('id', $svcIds);
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

        // Determine City ID for filtering
        $cityId = $request->city_id;
        
        // 1. Detect from coordinates if provided
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $detectedCityId = $this->detectCityFromCoords($request->latitude, $request->longitude);
            if ($detectedCityId) {
                $cityId = $detectedCityId;
            }
        }
        
        // 2. Fallback to auth user's city if still null
        if (!$cityId && auth('sanctum')->check()) {
            $cityId = auth('sanctum')->user()->city_id;
        }

        // Filter by city
        if ($cityId) {
            $query->where(function($q) use ($cityId) {
                // Case 1: Service is explicitly assigned to this city
                $q->where('city_id', $cityId)
                  // Case 2: Service is global (city_id is null) but has active technicians in this city
                  ->orWhere(function($q2) use ($cityId) {
                      $q2->whereNull('city_id')
                         ->whereHas('technicians', function($q3) use ($cityId) {
                             $q3->whereHas('user', function($q4) use ($cityId) {
                                 $q4->where('city_id', $cityId)->where('status', 'active');
                             });
                         });
                  });
            });
        }


        // NEW: District Filter (OR Logic for multi-select)
        if ($request->filled('district_ids')) {
            $districtIds = is_array($request->district_ids) ? $request->district_ids : explode(',', $request->district_ids);
            $query->whereHas('technicians', function($q) use ($districtIds) {
                $q->where(function($q2) use ($districtIds) {
                    foreach($districtIds as $dId) {
                        $q2->orWhereJsonContains('districts', (string)$dId);
                    }
                });
            });
        }

        // NEW: Rating Filter (Average of technician reviews)
        if ($request->filled('min_rating')) {
            $minRating = $request->min_rating;
            $query->whereHas('technicians', function($q) use ($minRating) {
                $q->whereHas('reviews', function($q2) use ($minRating) {
                    // This is still a bit simplified, but checks if tech has ANY review matching? 
                    // No, we need technicians WHOSE AVERAGE is >= min_rating.
                })->whereRaw('(SELECT AVG(rating) FROM reviews WHERE reviews.technician_id = technicians.id) >= ?', [$minRating]);
            });
        }

        // NEW: Availability Filter (Based on schedule conflicts)
        if ($request->has('availability')) {
            $isAvailable = $request->availability === 'available';
            
            $scheduledAt = null;
            if ($request->filled('scheduled_at')) {
                $scheduledAt = \Carbon\Carbon::parse($request->scheduled_at);
            } elseif ($request->filled('date')) {
                $scheduledAt = \Carbon\Carbon::parse($request->date);
                if ($request->filled('hour')) {
                    $scheduledAt->setHour($request->hour)->setMinute(0)->setSecond(0);
                }
            } else {
                $scheduledAt = now();
            }

            $start = (clone $scheduledAt)->subHours(1)->addMinute();
            $end = (clone $scheduledAt)->addHours(1)->subMinute();

            if ($isAvailable) {
                // Service must have at least one technician WHO IS NOT BUSY at this time
                $query->whereHas('technicians', function($q) use ($start, $end) {
                    $q->whereDoesntHave('orders', function($q2) use ($start, $end) {
                        $q2->whereIn('status', ['accepted', 'scheduled', 'in_progress'])
                           ->whereBetween('scheduled_at', [$start, $end]);
                    })->whereDoesntHave('appointments', function($q2) use ($start, $end) {
                        $q2->whereIn('status', ['scheduled', 'in_progress'])
                           ->whereBetween('appointment_date', [$start, $end]);
                    });
                });
            } else {
                // Service where ALL technicians ARE BUSY at this time
                $query->whereDoesntHave('technicians', function($q) use ($start, $end) {
                    $q->whereDoesntHave('orders', function($q2) use ($start, $end) {
                        $q2->whereIn('status', ['accepted', 'scheduled', 'in_progress'])
                           ->whereBetween('scheduled_at', [$start, $end]);
                    })->whereDoesntHave('appointments', function($q2) use ($start, $end) {
                        $q2->whereIn('status', ['scheduled', 'in_progress'])
                           ->whereBetween('appointment_date', [$start, $end]);
                    });
                });
            }
        }

        // Apply city filtering to child services as well if cityId is determined
        $services = $query->with(['children' => function($q) use ($cityId) {
            if ($cityId) {
                $q->where(function($q2) use ($cityId) {
                    $q2->where('city_id', $cityId)
                      ->orWhere(function($q3) use ($cityId) {
                          $q3->whereNull('city_id')
                             ->whereHas('technicians', function($q4) use ($cityId) {
                                 $q4->whereHas('user', function($q5) use ($cityId) {
                                     $q5->where('city_id', $cityId)->where('status', 'active');
                                 });
                             });
                      });
                });
            }
        }, 'city'])->get();

        // Add is_favorite status
        $user = auth('sanctum')->user();
        if ($user) {
            $userFavorites = $user->favorites()->pluck('service_id')->toArray();
            $services->each(function ($service) use ($userFavorites) {
                $service->is_favorite = in_array($service->id, $userFavorites);
            });
        } else {
            $services->each(function ($service) {
                $service->is_favorite = false;
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
            
            // Suggested services (top levels)
            $data['suggested_services'] = Service::whereNull('parent_id')->take(4)->get();
        }

        return response()->json([
            'status' => true, 
            'message' => __('Services retrieved successfully'), 
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $service = Service::with(['city', 'children'])->withCount(['children', 'technicians'])->find($id);

        if (!$service) {
            return response()->json(['status' => false, 'message' => __('Service not found')], 404);
        }

        // Add is_favorite status
        $user = auth('sanctum')->user();
        if ($user) {
            $userFavorites = $user->favorites()->pluck('service_id')->toArray();
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
        if ($user) {
            $relatedServices->each(function ($rs) use ($userFavorites) {
                $rs->is_favorite = in_array($rs->id, $userFavorites);
            });
        }

        return response()->json([
            'status' => true,
            'message' => __('Service retrieved successfully'),
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
            return response()->json(['status' => true, 'message' => __('Search history deleted')]);
        }
        return response()->json(['status' => false, 'message' => __('History not found')], 404);
    }

    public function clearSearchHistory()
    {
        \App\Models\SearchHistory::where('user_id', auth()->id())->delete();
        return response()->json(['status' => true, 'message' => __('Search history cleared')]);
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
            return response()->json(['status' => true, 'message' => __('Removed from favorites'), 'is_favorite' => false]);
        } else {
            Favorite::create([
                'user_id' => $userId,
                'service_id' => $serviceId,
            ]);
            return response()->json(['status' => true, 'message' => __('Added to favorites'), 'is_favorite' => true]);
        }
    }

    public function favorites(Request $request)
    {
        $userId = auth()->id();
        
        // Get IDs of favorited services
        $favoriteIds = Favorite::where('user_id', $userId)->pluck('service_id');

        $query = Service::whereIn('id', $favoriteIds);

        // Filter by Location (City)
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Filter by Category (Parent IDs - Multi-choice)
        if ($request->filled('category_ids') && is_array($request->category_ids)) {
            $query->whereIn('parent_id', $request->category_ids);
        }

        $services = $query->with(['city', 'parent'])->get();

        // Map to add is_favorite = true (since they are favorites)
        $services->map(function ($service) {
            $service->is_favorite = true;
            return $service;
        });

        return response()->json([
            'status' => true,
            'message' => __('Favorite services retrieved successfully'),
            'data' => $services
        ]);
    }

    public function availableSlots(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['status' => false, 'message' => __('Service not found')], 404);
        }

        $dateStr = $request->input('date', now()->format('Y-m-d'));
        try {
            $date = \Carbon\Carbon::parse($dateStr);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => __('Invalid date format. Use YYYY-MM-DD')], 422);
        }

        $user = $request->user();
        $cityId = $request->city_id ?? $this->detectCityFromCoords($request->latitude, $request->longitude);
        if (!$cityId && $user) {
            $cityId = $user->city_id;
        }

        $lat = $request->latitude ?? ($user->latitude ?? null);
        $lng = $request->longitude ?? ($user->longitude ?? null);

        // Working hours range (08:00 to 22:00)
        $startHour = (int) \App\Models\Setting::getByKey('working_hours_start', 8);
        $endHour = (int) \App\Models\Setting::getByKey('working_hours_end', 22);

        // Group slots by technician and by period
        $techniciansMap = [];
        $periodsMap = [
            'morning' => [],
            'afternoon' => [],
            'evening' => [],
        ];
        $availableTimes = [];
        $allSlots = [];

        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $slotTime = $date->copy()->setHour($hour)->setMinute(0)->setSecond(0);

            // Skip past slots or slots less than 1 hour away if today
            if ($date->isToday() && $slotTime->lte(now()->addMinutes(59))) {
                continue;
            }
            if ($slotTime->isPast()) {
                continue;
            }

            $availableTech = $this->findAvailableTechnician($service->id, $cityId, $slotTime, $lat, $lng);

            if ($availableTech) {
                $techUser = $availableTech->user;
                $period = ($hour < 12) ? 'morning' : (($hour < 17) ? 'afternoon' : 'evening');
                $periodLabels = [
                    'morning' => 'الفترة الصباحية',
                    'afternoon' => 'فترة الظهيرة والمساء الباكر',
                    'evening' => 'الفترة المسائية',
                ];

                $slotData = [
                    'time' => $slotTime->format('H:i'),
                    'datetime' => $slotTime->toDateTimeString(),
                    'formatted_time' => $slotTime->translatedFormat('h:i a'),
                    'period' => $period,
                    'period_label' => $periodLabels[$period],
                    'technician_id' => $availableTech->id,
                ];

                $availableTimes[] = $slotTime->format('H:i');
                $periodsMap[$period][] = $slotData;
                $allSlots[] = $slotData;

                $techId = $availableTech->id;
                if (!isset($techniciansMap[$techId])) {
                    $techniciansMap[$techId] = [
                        'id' => $availableTech->id,
                        'name' => $techUser->name ?? ($availableTech->name ?? 'فني معتمد'),
                        'avatar' => $techUser->avatar_url ?? null,
                        'rating' => round((float) ($availableTech->reviews()->avg('rating') ?? 5.0), 1),
                        'category' => $availableTech->category->name_ar ?? ($service->name_ar ?? ''),
                        'available_times' => [],
                        'slots' => []
                    ];
                }
                $techniciansMap[$techId]['available_times'][] = $slotTime->format('H:i');
                $techniciansMap[$techId]['slots'][] = [
                    'time' => $slotTime->format('H:i'),
                    'datetime' => $slotTime->toDateTimeString(),
                    'formatted_time' => $slotTime->translatedFormat('h:i a'),
                    'period' => $period,
                    'period_label' => $periodLabels[$period],
                ];
            }
        }

        $techniciansList = array_values($techniciansMap);

        return response()->json([
            'status' => true,
            'message' => __('Available slots retrieved successfully'),
            'data' => [
                'date' => $date->format('Y-m-d'),
                'formatted_date' => $date->translatedFormat('l, d F Y'),
                'total_available_slots' => count($allSlots),
                'available_times' => $availableTimes,
                'technicians' => $techniciansList,
                'periods' => $periodsMap,
                'available_slots' => $allSlots
            ]
        ]);
    }

}
