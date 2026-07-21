<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Services\FcmService;
use App\Models\User;

class SendScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-scheduled-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled FCM notifications that are due';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $notifications = Notification::where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->get();

        if ($notifications->isEmpty()) {
            $this->info('No pending scheduled notifications.');
            return;
        }

        $fcmService = app(FcmService::class);

        foreach ($notifications as $notification) {
            $this->info("Sending scheduled notification ID: {$notification->id}...");

            $details = [
                'type' => $notification->type,
                'title_ar' => $notification->title_ar,
                'title_en' => $notification->title_en,
                'body_ar' => $notification->body_ar,
                'body_en' => $notification->body_en,
                'data' => $notification->data ?? [],
            ];

            try {
                if ($notification->target_audience === 'individual_user' && $notification->user_id) {
                    if ($notification->user) {
                        $fcmService->sendToUser($notification->user, $details, false);
                    }
                } else {
                    $query = User::whereNotNull('fcm_token');
                    
                    switch ($notification->target_audience) {
                        case 'clients':
                            $query->whereIn('type', ['individual', 'corporate_customer']);
                            break;
                        case 'technicians':
                            $query->where('type', 'technician');
                            break;
                        case 'companies':
                            $query->where('type', 'maintenance_company');
                            break;
                    }
                    
                    $query->chunk(100, function ($users) use ($fcmService, $details) {
                        foreach ($users as $user) {
                            $fcmService->sendToUser($user, $details, false);
                        }
                    });
                }

                $notification->status = 'sent';
                $notification->save();
                $this->info("Successfully sent notification ID: {$notification->id}");
            } catch (\Exception $e) {
                $this->error("Failed to send notification ID: {$notification->id}. Error: " . $e->getMessage());
            }
        }
    }
}
