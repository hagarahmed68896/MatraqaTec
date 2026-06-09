<?php

namespace App\Traits;

use App\Models\Order;
use App\Models\Technician;
use App\Models\Service;
use Carbon\Carbon;

trait HasAutoAssignment
{
    /**
     * Check overall or service-specific availability.
     */
    protected function checkAvailability($cityId, $time, $serviceId = null, $lat = null, $lng = null)
    {
        if ($serviceId) {
            return $this->findAvailableTechnician($serviceId, $cityId, $time, $lat, $lng) !== null;
        }

        // Fallback logic if no service provided (e.g., general capacity check)
        $start = (clone $time)->startOfHour();
        $end = (clone $time)->endOfHour();
        
        $orderCount = Order::where(function($q) use ($cityId, $lat, $lng) {
                if ($lat && $lng) {
                    // Approximate check for orders in same area (within 50km)
                    $q->whereRaw("( 6371 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) < 50", [$lat, $lng, $lat]);
                } else {
                    $q->where('city_id', $cityId);
                }
            })
            ->whereBetween('scheduled_at', [$start, $end])
            ->whereIn('status', ['new', 'accepted', 'scheduled', 'in_progress'])
            ->count();

        $techQuery = Technician::whereHas('user', function($q) use ($cityId, $lat, $lng) {
            $q->where('status', 'active');
            if ($lat && $lng) {
                $q->where(function($sub) use ($lat, $lng, $cityId) {
                    $sub->whereRaw("( 6371 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) < 50", [$lat, $lng, $lat])
                        ->orWhere(function($inner) use ($cityId) {
                            $inner->whereNull('latitude')
                                  ->whereNull('longitude')
                                  ->when($cityId, function($cQ) use ($cityId) {
                                      $cQ->where('city_id', $cityId);
                                  });
                        });
                });
            } else {
                $q->where('city_id', $cityId);
            }
        });

        $techCount = $techQuery->count() ?: 1;

        return $orderCount < ($techCount * 2);
    }

    /**
     * Find the first available technician for a given service and time.
     */
    protected function findAvailableTechnician($serviceId, $cityId, $time, $lat = null, $lng = null)
    {
        $service = Service::find($serviceId);
        if (!$service) return null;
        $categoryId = $service->parent_id ?? $service->id;

        // Job protection window (e.g., +/- 1 hour from requested time)
        $start = (clone $time)->subHours(1)->addMinute();
        $end = (clone $time)->addHours(1)->subMinute();

        $query = Technician::query()
            ->join('users', 'technicians.user_id', '=', 'users.id')
            ->where('technicians.availability_status', 'available')
            ->where('users.status', 'active')
            ->where(function($q) use ($serviceId, $categoryId) {
                $q->where('technicians.service_id', $serviceId)
                  ->orWhere('technicians.category_id', $categoryId)
                  ->orWhereJsonContains('technicians.service_ids', (int)$serviceId)
                  ->orWhereJsonContains('technicians.service_ids', (string)$serviceId);
            })
            ->whereDoesntHave('orders', function($q) use ($start, $end) {
                $q->whereIn('status', ['new', 'accepted', 'scheduled', 'in_progress'])
                  ->whereBetween('scheduled_at', [$start, $end]);
            })
            ->whereDoesntHave('appointments', function($q) use ($start, $end) {
                $q->whereIn('status', ['scheduled', 'in_progress'])
                  ->whereBetween('appointment_date', [$start, $end]);
            });

        // Location filtering: Use distance if coords provided, else city
        if ($lat && $lng) {
            $query->where(function($q) use ($lat, $lng, $cityId) {
                $q->whereRaw("( 6371 * acos( cos( radians(?) ) * cos( radians(users.latitude) ) * cos( radians(users.longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(users.latitude) ) ) ) < 50", [$lat, $lng, $lat])
                  ->orWhere(function($sub) use ($cityId) {
                      $sub->whereNull('users.latitude')
                          ->whereNull('users.longitude')
                          ->when($cityId, function($cQ) use ($cityId) {
                              $cQ->where('users.city_id', $cityId);
                          });
                  });
            });
            $query->selectRaw("technicians.*, (CASE WHEN users.latitude IS NOT NULL AND users.longitude IS NOT NULL THEN ( 6371 * acos( cos( radians(?) ) * cos( radians(users.latitude) ) * cos( radians(users.longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(users.latitude) ) ) ) ELSE 9999 END) AS distance", [$lat, $lng, $lat])
                  ->orderBy('distance');
        } else {
            $query->where('users.city_id', $cityId)
                  ->select('technicians.*');
        }

        return $query->first();
    }

    /**
     * Suggest the next available time slot for a technician.
     * Returns null if no technicians are assigned to this service at all.
     */
    protected function getSuggestedTime($serviceId, $cityId, $requestedTime, $lat = null, $lng = null)
    {
        // First, check if there are ANY technicians for this service/category
        $service = Service::find($serviceId);
        if (!$service) return null;
        $categoryId = $service->parent_id ?? $service->id;

        $hasAnyTechQuery = Technician::join('users', 'technicians.user_id', '=', 'users.id')
            ->where('users.status', 'active')
            ->where(function($q) use ($serviceId, $categoryId) {
                $q->where('technicians.service_id', $serviceId)
                  ->orWhere('technicians.category_id', $categoryId)
                  ->orWhereJsonContains('technicians.service_ids', (int)$serviceId)
                  ->orWhereJsonContains('technicians.service_ids', (string)$serviceId);
            });

        if ($lat && $lng) {
            $hasAnyTechQuery->where(function($q) use ($lat, $lng, $cityId) {
                $q->whereRaw("( 6371 * acos( cos( radians(?) ) * cos( radians(users.latitude) ) * cos( radians(users.longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(users.latitude) ) ) ) < 50", [$lat, $lng, $lat])
                  ->orWhere(function($sub) use ($cityId) {
                      $sub->whereNull('users.latitude')
                          ->whereNull('users.longitude')
                          ->when($cityId, function($cQ) use ($cityId) {
                              $cQ->where('users.city_id', $cityId);
                          });
                  });
            });
        } else {
            $hasAnyTechQuery->where('users.city_id', $cityId);
        }

        if (!$hasAnyTechQuery->exists()) return null;

        $suggestion = (clone $requestedTime)->addHours(1);
        $attempts = 0;
        while ($attempts < 24) { 
            if ($this->findAvailableTechnician($serviceId, $cityId, $suggestion, $lat, $lng)) {
                return $suggestion;
            }
            $suggestion->addHours(1);
            $attempts++;
        }
        return $requestedTime->addDay(); 
    }

    /**
     * Get diagnostic information for matching technicians.
     */
    protected function getDiagnostics($serviceId, $cityId, $time, $lat = null, $lng = null)
    {
        $service = Service::find($serviceId);
        if (!$service) {
            return ["error" => "Service ID {$serviceId} not found"];
        }
        $categoryId = $service->parent_id ?? $service->id;

        $start = (clone $time)->subHours(1)->addMinute();
        $end = (clone $time)->addHours(1)->subMinute();

        $techs = Technician::with('user')->get();
        $details = [];

        foreach ($techs as $tech) {
            $user = $tech->user;
            if (!$user) {
                $details[] = "Tech ID {$tech->id}: No associated User record";
                continue;
            }

            $rejectionReasons = [];

            if ($user->status !== 'active') {
                $rejectionReasons[] = "User status is not active (is: {$user->status})";
            }

            if ($tech->availability_status !== 'available') {
                $rejectionReasons[] = "Availability status is not available (is: {$tech->availability_status})";
            }

            $serviceMatch = ($tech->service_id == $serviceId || 
                             $tech->category_id == $categoryId || 
                             (is_array($tech->service_ids) && in_array($serviceId, $tech->service_ids)) ||
                             (is_array($tech->service_ids) && in_array((string)$serviceId, $tech->service_ids)));

            if (!$serviceMatch) {
                $rejectionReasons[] = "Service ID {$serviceId} or Category ID {$categoryId} not matching tech service_id (" . ($tech->service_id ?? 'NULL') . "), category_id (" . ($tech->category_id ?? 'NULL') . "), or service_ids (" . json_encode($tech->service_ids) . ")";
            }

            $locationMatch = false;
            $locReason = "";
            if ($lat && $lng) {
                if ($user->latitude !== null && $user->longitude !== null) {
                    $theta = $lng - $user->longitude;
                    $dist = sin(deg2rad($lat)) * sin(deg2rad($user->latitude)) +  cos(deg2rad($lat)) * cos(deg2rad($user->latitude)) * cos(deg2rad($theta));
                    $dist = acos(min(max($dist, -1.0), 1.0)); // prevent NaN
                    $dist = rad2deg($dist);
                    $miles = $dist * 60 * 1.1515;
                    $km = $miles * 1.609344;
                    
                    if ($km < 50) {
                        $locationMatch = true;
                    } else {
                        $locReason = "Too far (KM: " . round($km, 2) . " > 50km limit)";
                    }
                } else {
                    if ($cityId && $user->city_id == $cityId) {
                        $locationMatch = true;
                    } else {
                        $locReason = "Tech coords NULL and City ID mismatch (User City ID: " . ($user->city_id ?? 'NULL') . ", Booking City ID: {$cityId})";
                    }
                }
            } else {
                if ($user->city_id == $cityId) {
                    $locationMatch = true;
                } else {
                    $locReason = "City ID mismatch (User City ID: " . ($user->city_id ?? 'NULL') . ", Booking City ID: {$cityId})";
                }
            }

            if (!$locationMatch) {
                $rejectionReasons[] = "Location mismatch: " . $locReason;
            }

            $hasOrderConflict = Order::where('technician_id', $tech->id)
                ->whereIn('status', ['new', 'accepted', 'scheduled', 'in_progress'])
                ->whereBetween('scheduled_at', [$start, $end])
                ->exists();
            if ($hasOrderConflict) {
                $rejectionReasons[] = "Has conflicting order between {$start->toDateTimeString()} and {$end->toDateTimeString()}";
            }

            $hasAppointmentConflict = \App\Models\Appointment::where('technician_id', $tech->id)
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->whereBetween('appointment_date', [$start, $end])
                ->exists();
            if ($hasAppointmentConflict) {
                $rejectionReasons[] = "Has conflicting appointment between {$start->toDateTimeString()} and {$end->toDateTimeString()}";
            }

            if (empty($rejectionReasons)) {
                $details[] = "Tech ID {$tech->id} ({$user->name}): AVAILABLE";
            } else {
                $details[] = "Tech ID {$tech->id} ({$user->name}): REJECTED because: " . implode(" AND ", $rejectionReasons);
            }
        }

        return [
            "booking_parameters" => [
                "service_id" => $serviceId,
                "category_id" => $categoryId,
                "city_id" => $cityId,
                "latitude" => $lat,
                "longitude" => $lng,
                "scheduled_at" => $time->toDateTimeString(),
            ],
            "technicians_diagnostics" => $details
        ];
    }
}
