<?php

namespace App\Traits;

use App\Models\Setting;
use Illuminate\Http\Request;

trait ValidatesOrderPhotos
{
    /**
     * Validate if the order has the required number of photos for the given phase.
     *
     * @param \App\Models\Order $order
     * @param string $phase 'before' or 'after'
     * @return bool|string True if valid, or an error message.
     */
    protected function validatePhotoCount($order, $phase)
    {
        $settingKey = $phase === 'before' ? 'required_photos_before_count' : 'required_photos_after_count';
        $requiredCount = Setting::getByKey($settingKey, 1);

        $currentCount = $order->attachments()->where('type', $phase)->count();

        if ($currentCount < $requiredCount) {
            return "At least {$requiredCount} photos are required {$phase} work.";
        }

        return true;
    }
}
