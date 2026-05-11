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
                $q->whereRaw("( 6371 * acos( cos( radians(?) ) * cos( radians(latitude) ) * cos( radians(longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(latitude) ) ) ) < 50", [$lat, $lng, $lat]);
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
            ->where('users.is_online', true)
            ->where(function($q) use ($serviceId, $categoryId) {
                $q->where('technicians.service_id', $serviceId)
                  ->orWhere('technicians.category_id', $categoryId);
            })
            ->whereDoesntHave('orders', function($q) use ($start, $end) {
                $q->whereIn('status', ['accepted', 'scheduled', 'in_progress'])
                  ->whereBetween('scheduled_at', [$start, $end]);
            })
            ->whereDoesntHave('appointments', function($q) use ($start, $end) {
                $q->whereIn('status', ['scheduled', 'in_progress'])
                  ->whereBetween('appointment_date', [$start, $end]);
            });

        // Location filtering: Use distance if coords provided, else city
        if ($lat && $lng) {
            $query->selectRaw("technicians.*, ( 6371 * acos( cos( radians(?) ) * cos( radians(users.latitude) ) * cos( radians(users.longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(users.latitude) ) ) ) AS distance", [$lat, $lng, $lat])
                  ->having('distance', '<', 50) // 50km radius
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
            ->where('users.is_online', true)
            ->where(function($q) use ($serviceId, $categoryId) {
                $q->where('technicians.service_id', $serviceId)
                  ->orWhere('technicians.category_id', $categoryId);
            });

        if ($lat && $lng) {
            $hasAnyTechQuery->whereRaw("( 6371 * acos( cos( radians(?) ) * cos( radians(users.latitude) ) * cos( radians(users.longitude) - radians(?) ) + sin( radians(?) ) * sin( radians(users.latitude) ) ) ) < 50", [$lat, $lng, $lat]);
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
}
