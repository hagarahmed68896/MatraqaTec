<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\User;
use App\Models\Notification as NotificationModel;

class FcmService
{
    /**
     * Send notification to a specific user
     */
    public function sendToUser(User $user, $details, $saveToDb = true)
    {
        $type = $details['type'] ?? 'notification';
        $data = $details['data'] ?? [];
        $titleAr = $details['title_ar'] ?? '';
        $titleEn = $details['title_en'] ?? '';
        $bodyAr = $details['body_ar'] ?? '';
        $bodyEn = $details['body_en'] ?? '';

        // Select title/body based on user language
        $lang = $user->language ?? 'ar';
        $title = ($lang === 'en' && !empty($titleEn)) ? $titleEn : $titleAr;
        $body = ($lang === 'en' && !empty($bodyEn)) ? $bodyEn : $bodyAr;

        // Check if user has notifications enabled
        if (!$user->notification_enabled) {
             \Log::info("FCM: User {$user->id} has notifications disabled.");
             if ($saveToDb) {
                 return $this->saveToDatabase($user, $type, $titleAr, $titleEn, $bodyAr, $bodyEn, $data, 'unsent');
             }
             return null;
        }

        if (!$user->fcm_token) {
            \Log::info("FCM: User {$user->id} has no FCM token. Notification saved to database only.");
            if ($saveToDb) {
                return $this->saveToDatabase($user, $type, $titleAr, $titleEn, $bodyAr, $bodyEn, $data, 'unsent');
            }
            return null;
        }

        try {
            \Log::info("FCM: Attempting to send to user {$user->id} with token: " . substr($user->fcm_token, 0, 10) . "...");
            $messaging = app('firebase.messaging');
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification($notification)
                ->withData(array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'type' => $type
                ]));

            $response = $messaging->send($message);
            
            \Log::info("FCM: Sent successfully to user {$user->id}. Response: " . json_encode($response));

            if ($saveToDb) {
                return $this->saveToDatabase($user, $type, $titleAr, $titleEn, $bodyAr, $bodyEn, $data, 'sent');
            }
            return true;
        } catch (\Exception $e) {
            \Log::error("FCM Error for user {$user->id}: " . $e->getMessage());
            \Log::error("FCM Trace: " . $e->getTraceAsString());
            if ($saveToDb) {
                return $this->saveToDatabase($user, $type, $titleAr, $titleEn, $bodyAr, $bodyEn, $data, 'unsent');
            }
            return false;
        }
    }

    /**
     * Send notification to a topic (e.g., 'all_technicians')
     */
    public function sendToTopic($topic, $title, $body, $data = [], $type = 'notification')
    {
        try {
            $messaging = app('firebase.messaging');
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification)
                ->withData(array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'type' => $type
                ]));

            $messaging->send($message);
            
            \Log::info("FCM: Sent to topic {$topic}");
            return true;
        } catch (\Exception $e) {
            \Log::error("FCM Topic Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save notification to database for in-app history
     */
    private function saveToDatabase(User $user, $type, $titleAr, $titleEn, $bodyAr, $bodyEn, $data, $status = 'unsent')
    {
        $targetAudience = $data['target_audience'] ?? null;
        
        if (!$targetAudience) {
            $targetAudience = match ($user->type) {
                'technician' => 'technicians',
                'maintenance_company' => 'companies',
                'individual', 'corporate_customer' => 'clients',
                default => 'individual_user',
            };
        }

        return NotificationModel::create([
            'user_id' => $user->id,
            'type' => $type,
            'title_ar' => $titleAr,
            'title_en' => $titleEn,
            'body_ar' => $bodyAr,
            'body_en' => $bodyEn,
            'data' => $data,
            'status' => $status,
            'target_audience' => $targetAudience,
        ]);
    }
}
