<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-appointment-reminders';
    protected $description = 'Send reminders for upcoming appointments based on platform settings';

    public function handle()
    {
        $reminderType = \App\Models\Setting::getByKey('reminder_type', 'day');
        $customValue = \App\Models\Setting::getByKey('reminder_custom_value');

        $now = now();
        $query = \App\Models\Appointment::where('status', 'scheduled');

        if ($reminderType === 'day') {
            $target = $now->copy()->addDay();
            $query->whereDate('appointment_date', $target->toDateString());
        } elseif ($reminderType === 'hour') {
            $targetStart = $now->copy()->addHour();
            $targetEnd = $now->copy()->addHour()->addMinutes(5); // 5 min window
            $query->whereBetween('appointment_date', [$targetStart, $targetEnd]);
        } elseif ($reminderType === 'custom' && $customValue) {
            $targetStart = $now->copy()->addMinutes($customValue);
            $targetEnd = $now->copy()->addMinutes($customValue + 5);
            $query->whereBetween('appointment_date', [$targetStart, $targetEnd]);
        }

        $appointments = $query->get();

        foreach ($appointments as $appointment) {
            // Use Cache to prevent multiple reminders for the same appointment phase
            $cacheKey = "appointment_reminder_sent_{$appointment->id}_{$reminderType}";
            if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                $this->info("Sending reminder for appointment #{$appointment->id} via global setting: {$reminderType}");
                
                $order = $appointment->order;
                if ($order && $order->user_id) {
                    $titleAr = $reminderType === 'day' ? 'موعدك غداً' : 'تذكير بموعد الزيارة';
                    $titleEn = $reminderType === 'day' ? 'Your appointment is tomorrow' : 'Appointment Reminder';

                    \App\Models\Notification::create([
                        'user_id' => $order->user_id,
                        'type' => 'reminder',
                        'title_ar' => $titleAr,
                        'title_en' => $titleEn,
                        'body_ar' => 'تذكير بزيارة الفني حسب الموعد المحدد',
                        'body_en' => 'Reminder of the technician\'s visit as scheduled.',
                        'data' => ['appointment_id' => $appointment->id, 'order_id' => $order->id],
                        'status' => 'sent',
                        'is_read' => false
                    ]);
                }

                // Mark as sent in cache for 24 hours
                \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addDay());
            }
        }
    }
}
