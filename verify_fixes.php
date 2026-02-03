<?php

use App\Models\Order;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Running Verification...\n";

// 1. Check if reschedule method exists
$controller = new \App\Http\Controllers\Api\OrderController();
if (method_exists($controller, 'reschedule')) {
    echo "[PASS] OrderController::reschedule exists.\n";
} else {
    echo "[FAIL] OrderController::reschedule missing.\n";
}

// 2. Check AppointmentController method exists
$appointmentController = new \App\Http\Controllers\Api\AppointmentController();
if (method_exists($appointmentController, 'store')) {
    echo "[PASS] AppointmentController::store exists.\n";
}

echo "Verification complete.\n";
