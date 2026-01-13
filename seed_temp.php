$order = \App\Models\Order::create([
    'order_number' => 'TEST-' . rand(10000, 99999),
    'user_id' => 9,
    'technician_id' => 1,
    'service_id' => 3, 
    'status' => 'scheduled',
    'total_price' => 100,
    'payment_method' => 'cash',
    'address' => 'Test Address',
    'city_id' => 1
]);

$appointment = \App\Models\Appointment::create([
    'order_id' => $order->id,
    'technician_id' => 1,
    'appointment_date' => '2026-01-25 10:00:00',
    'status' => 'scheduled'
]);

echo "SUCCESS: Created Order ID {$order->id} and Appointment ID {$appointment->id}";
