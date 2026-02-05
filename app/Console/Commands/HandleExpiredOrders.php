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

        $expiredOrders = \App\Models\Order::where('status', 'scheduled')
            ->where('updated_at', '<', now()->subMinutes($duration))
            ->get();

        foreach ($expiredOrders as $order) {
            $this->info("Order #{$order->id} (ORD-{$order->order_number}) has expired based on global setting of {$duration} mins. Resetting.");
            $order->update([
                'status' => 'new',
                'technician_id' => null,
                'maintenance_company_id' => null,
            ]);
        }
    }
}
