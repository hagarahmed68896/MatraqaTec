<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find all completed orders or orders with completed payments
        $completedOrders = DB::table('orders')
            ->where('status', 'completed')
            ->orWhereIn('id', function ($query) {
                $query->select('order_id')
                    ->from('payments')
                    ->where('status', 'completed')
                    ->whereNotNull('order_id');
            })
            ->get();

        foreach ($completedOrders as $order) {
            // Check if invoice already exists
            $exists = DB::table('invoices')->where('order_id', $order->id)->exists();
            if (!$exists) {
                $total = (float)$order->total_price;
                $amount = round($total / 1.15, 2);
                $tax = round($total - $amount, 2);

                DB::table('invoices')->insert([
                    'order_id' => $order->id,
                    'invoice_number' => 'INV-' . ($order->order_number ?? $order->id),
                    'issue_date' => $order->updated_at ?? now(),
                    'status' => 'not_sent',
                    'amount' => $amount,
                    'tax' => $tax,
                    'total' => $total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for data synchronization
    }
};
