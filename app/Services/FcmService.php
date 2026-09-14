<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\User;
use App\Models\Notification as NotificationModel;

class FcmService
{
    protected static $sentTokens = [];

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

        // Prevent duplicate FCM sending to the same token for the same message in a single request execution
        if ($user->fcm_token) {
            $messageHash = md5($title . $body . $user->fcm_token);
            if (in_array($messageHash, self::$sentTokens)) {
                \Log::info("FcmService: Duplicate message to token already sent in this request: " . substr($user->fcm_token, 0, 10));
                if ($saveToDb) {
                    return $this->saveToDatabase($user, $type, $titleAr, $titleEn, $bodyAr, $bodyEn, $data, 'sent');
                }
                return true;
            }
            self::$sentTokens[] = $messageHash;
        }

        // Check if user has explicitly disabled notifications
        if ($user->notification_enabled === false || $user->notification_enabled === 0 || $user->notification_enabled === '0') {
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
            
            // Prepare data payload - Ensure all values are strings for FCM compatibility
            $payloadData = array_merge($data, [
                'title' => (string) $title,
                'body' => (string) $body,
                'type' => (string) $type,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]);

            // Cast all values to strings recursively if needed
            $payloadData = array_map(function($val) {
                return (is_array($val) || is_object($val)) ? json_encode($val) : (string)$val;
            }, $payloadData);

            $notification = \Kreait\Firebase\Messaging\Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification($notification)
                ->withData($payloadData)
                ->withAndroidConfig([
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'default_sound' => true,
                        'default_vibrate_timings' => true,
                        'notification_priority' => 'PRIORITY_HIGH',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'channel_id' => 'high_importance_channel',
                    ],
                ])
                ->withApnsConfig([
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                            'sound' => 'default',
                            'badge' => 1,
                            'content-available' => 1,
                        ],
                    ],
                ]);

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
            
            // Prepare data payload - Ensure all values are strings
            $payloadData = array_merge($data, [
                'title' => (string) $title,
                'body' => (string) $body,
                'type' => (string) $type,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]);

            $payloadData = array_map(function($val) {
                return (is_array($val) || is_object($val)) ? json_encode($val) : (string)$val;
            }, $payloadData);

            $notification = \Kreait\Firebase\Messaging\Notification::create($title, $body);

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification)
                ->withData($payloadData)
                ->withAndroidConfig([
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'default_sound' => true,
                        'default_vibrate_timings' => true,
                        'notification_priority' => 'PRIORITY_HIGH',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'channel_id' => 'high_importance_channel',
                    ],
                ])
                ->withApnsConfig([
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                            'sound' => 'default',
                            'badge' => 1,
                            'content-available' => 1,
                        ],
                    ],
                ]);

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
        $targetAudience = $data['target_audience'] ?? 'individual_user';

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
