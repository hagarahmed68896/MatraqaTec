<?php

namespace App\Traits;

use App\Models\Order;
use App\Models\Technician;
use App\Models\Service;
use Carbon\Carbon;

trait HasAutoAssignment
{
    /**
     * Check overall or service-specific availability in a city.
     */
    protected function checkAvailability($cityId, $time, $serviceId = null)
    {
        if ($serviceId) {
            return $this->findAvailableTechnician($serviceId, $cityId, $time) !== null;
        }

        // Fallback logic if no service provided (e.g., general capacity check)
        $start = (clone $time)->startOfHour();
        $end = (clone $time)->endOfHour();
        
        $orderCount = Order::where('city_id', $cityId)
            ->whereBetween('scheduled_at', [$start, $end])
            ->whereIn('status', ['new', 'accepted', 'scheduled', 'in_progress'])
            ->count();

        $techCount = Technician::whereHas('user', function($q) use ($cityId) {
            $q->where('city_id', $cityId);
        })->count() ?: 1;

        return $orderCount < ($techCount * 2);
    }

    /**
     * Find the first available technician for a given service and time.
     */
    protected function findAvailableTechnician($serviceId, $cityId, $time)
    {
        $service = Service::find($serviceId);
        if (!$service) return null;
        $categoryId = $service->parent_id ?? $service->id;

        // Job protection window (e.g., +/- 1 hour from requested time)
        $start = (clone $time)->subHours(1)->addMinute();
        $end = (clone $time)->addHours(1)->subMinute();

        return Technician::where('availability_status', 'available') 
            ->whereHas('user', function($q) use ($cityId) {
                $q->where('city_id', $cityId)
                  ->where('status', 'active');
            })
            ->where(function($q) use ($serviceId, $categoryId) {
                $q->where('service_id', $serviceId)
                  ->orWhere('category_id', $categoryId);
            })
            ->whereDoesntHave('orders', function($q) use ($start, $end) {
                $q->whereIn('status', ['accepted', 'scheduled', 'in_progress'])
                  ->whereBetween('scheduled_at', [$start, $end]);
            })
            ->whereDoesntHave('appointments', function($q) use ($start, $end) {
                $q->whereIn('status', ['scheduled', 'in_progress'])
                  ->whereBetween('appointment_date', [$start, $end]);
            })
            ->first();
    }

    /**
     * Suggest the next available time slot for a technician.
     */
    protected function getSuggestedTime($serviceId, $cityId, $requestedTime)
    {
        $suggestion = (clone $requestedTime)->addHours(1);
        $attempts = 0;
        while ($attempts < 24) { 
            if ($this->findAvailableTechnician($serviceId, $cityId, $suggestion)) {
                return $suggestion;
            }
            $suggestion->addHours(1);
            $attempts++;
        }
        return $requestedTime->addDay(); 
    }
}
