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
        $minRequired = $phase === 'before' ? 2 : 3;
        
        // Get from setting, but ensure it's at least our minimum design requirement
        $dbCount = Setting::getByKey($settingKey, $minRequired);
        $requiredCount = max((int)$dbCount, $minRequired);

        $currentCount = $order->attachments()->where('type', $phase)->count();

        if ($currentCount < $requiredCount) {
             $phaseTrans = $phase === 'before' ? __('before') : __('after');
             return __("At least :count photos are required :phase work.", [
                 'count' => $requiredCount, 
                 'phase' => $phaseTrans
             ]);
        }

        return true;
    }
}
