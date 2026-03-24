<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MaintenanceCompany;
use App\Models\Notification;

$company = MaintenanceCompany::find(9);
if (!$company) {
    echo "Company not found\n";
    exit;
}

$userId = $company->user_id;
$types = [
    'alert' => ['title_ar' => 'تنبيه أمان', 'title_en' => 'Security Alert'],
    'promotion' => ['title_ar' => 'عرض خاص', 'title_en' => 'Special Offer'],
    'status_update' => ['title_ar' => 'تحديث حالة', 'title_en' => 'Status Update'],
    'reminder' => ['title_ar' => 'تذكير موعد', 'title_en' => 'Appointment Reminder'],
    'system_message' => ['title_ar' => 'رسالة نظام', 'title_en' => 'System Message'],
];

foreach ($types as $type => $titles) {
    Notification::create([
        'user_id' => $userId,
        'type' => $type,
        'title_ar' => $titles['title_ar'],
        'title_en' => $titles['title_en'],
        'body_ar' => 'هذا إشعار تجريبي من نوع ' . $type . ' لاختبار النظام',
        'body_en' => 'This is a test notification of type ' . $type,
        'status' => 'sent',
        'is_read' => false
    ]);
}

echo "Dummy notifications (All Types) generated successfully for User ID {$userId}\n";
