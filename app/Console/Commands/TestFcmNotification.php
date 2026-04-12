<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestFcmNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:test {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test FCM notification to a specific user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User not found!");
            return;
        }

        if (!$user->fcm_token) {
            $this->warn("User has no FCM token. Notification will be saved to DB but not sent via Firebase.");
        }

        $this->info("Sending test notification to User ID: {$userId} ({$user->name})...");

        $result = $user->sendFcmNotification([
            'type' => 'test_notification',
            'title_ar' => 'اختبار التنبيهات',
            'title_en' => 'FCM Test',
            'body_ar' => 'هذا تنبيه تجريبي للتأكد من عمل نظام FCM بنجاح',
            'body_en' => 'This is a test notification to ensure FCM system is working successfully',
            'data' => ['test' => true, 'sent_at' => now()->toDateTimeString()]
        ]);

        if ($result && $result->status === 'sent') {
            $this->info("Success! Notification sent and logged to database.");
        } else {
            $this->warn("Notification logged to database but might not have been sent to Firebase (Check logs for errors).");
        }
    }
}
