<?php

use App\Models\Order;
use App\Models\WalletTransaction;
use App\Models\MaintenanceCompany;
use Carbon\Carbon;

$companyId = 4;
$company = MaintenanceCompany::find($companyId);

if (!$company) {
    echo "Company not found\n";
    exit;
}

$userId = $company->user_id;
$now = Carbon::now();

// 1. Create/Update some orders to 'completed' for the current month
$ordersCurrent = Order::where('maintenance_company_id', $companyId)
    ->limit(5)
    ->get();

foreach ($ordersCurrent as $index => $order) {
    if ($order->status !== 'completed') {
        $order->update([
            'status' => 'completed',
            'total_price' => 200 + ($index * 50),
            'created_at' => $now->copy()->subDays($index + 1),
        ]);
    }

    // Create wallet transaction if not exists for this order
    if (!WalletTransaction::where('reference_id', $order->id)->exists()) {
        WalletTransaction::create([
            'user_id' => $userId,
            'amount' => $order->total_price,
            'type' => 'payment',
            'note' => "Payment for order #{$order->order_number}",
            'reference_id' => $order->id,
            'reference_type' => 'order',
            'created_at' => $order->created_at,
        ]);
    }
}

// 2. Create/Update some orders for the previous month to show trends
$previousMonth = $now->copy()->subMonth();
$ordersPrevious = Order::where('maintenance_company_id', $companyId)
    ->offset(5)
    ->limit(3)
    ->get();

foreach ($ordersPrevious as $index => $order) {
    $order->update([
        'status' => 'completed',
        'total_price' => 150 + ($index * 30),
        'created_at' => $previousMonth->copy()->subDays($index + 5),
    ]);

    // Create wallet transaction
    if (!WalletTransaction::where('reference_id', $order->id)->exists()) {
        WalletTransaction::create([
            'user_id' => $userId,
            'amount' => $order->total_price,
            'type' => 'payment',
            'note' => "Payment for order #{$order->order_number}",
            'reference_id' => $order->id,
            'reference_type' => 'order',
            'created_at' => $order->created_at,
        ]);
    }
}

echo "Success: Seeded data for Company ID 4 (User ID {$userId})\n";
