<?php

namespace App\Traits;

use App\Models\Order;
use App\Models\User;
use App\Models\Notification;
use App\Models\Setting;

trait HandlesRejectionPenalty
{
    /**
     * Check and handle penalties for frequent order rejections.
     * 
     * @param Order $order
     * @param User $user The user who rejected the order (Technician or Company User)
     */
    /**
     * Check if user is allowed to reject orders (Max 5 rejections in last 30 days).
     */
    protected function checkRejectionLimit(User $user)
    {
        $blockThreshold = (int) Setting::getByKey('rejection_block_threshold', 5);
        $rejectionCount = Order::where('rejected_by_user_id', $user->id)
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();

        return $rejectionCount < $blockThreshold;
    }

    protected function handleRejectionPenalty(Order $order, User $user)
    {
        // 1. Record the rejection on the order
        $order->update(['rejected_by_user_id' => $user->id]);

        // 2. Count rejections for this user in the last 30 days
        $rejectionCount = Order::where('rejected_by_user_id', $user->id)
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();

        $warningThreshold = (int) Setting::getByKey('rejection_warning_threshold', 3);
        $blockThreshold = (int) Setting::getByKey('rejection_block_threshold', 5);

        // 3. Logic for Penalties
        if ($rejectionCount == $warningThreshold) {
            $this->sendRejectionWarning($user, $rejectionCount);
        } elseif ($rejectionCount >= $blockThreshold) {
            $this->blockUserForExcessiveRejections($user, $rejectionCount);
        }
    }

    private function sendRejectionWarning(User $user, $count)
    {
        // Notify User
        $this->sendNotification($user->id, [
            'type' => Notification::TYPE_STATUS_UPDATE,
            'title_ar' => 'تحذير: كثرة رفض الطلبات',
            'title_en' => 'Warning: High Rejection Rate',
            'body_ar' => "لقد قمت برفض $count طلبات خلال الفترة الأخيرة. كثرة الرفض قد تؤدي إلى حظر حسابك تلقائياً.",
            'body_en' => "You have rejected $count orders recently. High rejection rates may lead to automatic account suspension.",
            'data' => ['rejection_count' => $count]
        ]);

        // Notify Admins
        $admins = User::where('type', 'admin')->get();
        foreach ($admins as $admin) {
            $this->sendNotification($admin->id, [
                'type' => Notification::TYPE_STATUS_UPDATE,
                'title_ar' => 'تنبيه أداء: كثرة رفض',
                'title_en' => 'Performance Alert: High Rejections',
                'body_ar' => "المستخدم {$user->name} (ID: {$user->id}) وصل إلى $count حالات رفض للطلبات.",
                'body_en' => "User {$user->name} (ID: {$user->id}) has reached $count order rejections.",
                'data' => ['user_id' => $user->id, 'rejection_count' => $count]
            ]);
        }
    }

    private function blockUserForExcessiveRejections(User $user, $count)
    {
        // Block User
        $user->update([
            'status' => 'inactive',
            'blocked_at' => now(),
        ]);

        // Notify User
        $this->sendNotification($user->id, [
            'type' => Notification::TYPE_STATUS_UPDATE,
            'title_ar' => 'تم حظر الحساب',
            'title_en' => 'Account Suspended',
            'body_ar' => "تم حظر حسابك مؤقتاً بسبب تجاوز الحد المسموح لرفض الطلبات ($count طلب). يرجى التواصل مع الإدارة.",
            'body_en' => "Your account has been suspended for exceeding the allowed rejection limit ($count orders). Please contact support.",
            'data' => ['rejection_count' => $count]
        ]);

        // Notify Admins
        $admins = User::where('type', 'admin')->get();
        foreach ($admins as $admin) {
            $this->sendNotification($admin->id, [
                'type' => Notification::TYPE_STATUS_UPDATE,
                'title_ar' => 'تم حظر مستخدم تلقائياً',
                'title_en' => 'User Automatically Blocked',
                'body_ar' => "تم حظر المستخدم {$user->name} تلقائياً بسبب وصوله لـ $count حالات رفض.",
                'body_en' => "User {$user->name} has been automatically blocked for reaching $count rejections.",
                'data' => ['user_id' => $user->id, 'rejection_count' => $count]
            ]);
        }
    }
}
