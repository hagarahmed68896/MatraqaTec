<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HandleExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:handle-expired-orders';
    protected $description = 'Reset orders that were not accepted by technicians within the allowed duration';

    public function handle()
    {
        $duration = (int) \App\Models\Setting::getByKey('order_acceptance_duration', 15);

        // 1. Handle Unaccepted Assignments (Existing logic)
        $unacceptedOrders = \App\Models\Order::whereNotNull('technician_id')
            ->whereIn('status', ['new', 'scheduled'])
            ->where('assigned_at', '<', now()->subMinutes($duration))
            ->get();

        foreach ($unacceptedOrders as $order) {
            $this->info("Order #{$order->id} (ORD-{$order->order_number}) acceptance timeout. Resetting to new.");
            $order->update([
                'status' => 'new',
                'technician_id' => null,
                'maintenance_company_id' => $order->maintenance_company_id, // Keep company if it was assigned by admin to a company
                'assigned_at' => null
            ]);
        }

        // 2. Handle Overdue Appointments (New logic)
        // Orders that are scheduled/accepted but the time has passed (e.g., by 1 hour) and work hasn't started.
        $overdueOrders = \App\Models\Order::whereIn('status', ['scheduled', 'accepted'])
            ->where('scheduled_at', '<', now()->subHours(1))
            ->whereNull('sub_status') // Only if no work has started (no on_way, arrived, etc.)
            ->get();

        foreach ($overdueOrders as $order) {
            $this->info("Order #{$order->id} (ORD-{$order->order_number}) is overdue. Marking as expired/needs reschedule.");
            $order->update([
                'sub_status' => 'expired'
            ]);
        }
    }
}
