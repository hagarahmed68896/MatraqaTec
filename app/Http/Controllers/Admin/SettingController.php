<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        
        // Ensure default values if not set
        $defaults = [
            'default_language' => 'ar',
            'system_mode' => 'light',
            'order_acceptance_duration' => 30,
            'required_photos_before_count' => 1,
            'required_photos_after_count' => 1,
            'reminder_type' => 'day',
            'reminder_custom_value' => null,
            'hourly_booking_capacity' => 5,
            'copyright_text_ar' => '© ' . date('Y') . ' مطرقة تيك - جميع الحقوق محفوظة',
            'copyright_text_en' => '© ' . date('Y') . ' MatraqaTec - All rights reserved.',
            
            // 1. Client App (Customer)
            'client_android_latest_version' => $settings['android_latest_version'] ?? '1.0.0',
            'client_android_minimum_version' => $settings['android_minimum_version'] ?? '1.0.0',
            'client_android_store_url' => $settings['android_store_url'] ?? 'https://play.google.com/store/apps/details?id=com.isky.matraqa&pcampaignid=web_share',
            'client_ios_latest_version' => $settings['ios_latest_version'] ?? '1.0.0',
            'client_ios_minimum_version' => $settings['ios_minimum_version'] ?? '1.0.0',
            'client_ios_store_url' => $settings['ios_store_url'] ?? 'https://apps.apple.com/app/matraqa-tech/id6766811584',
            
            // 2. Company App (Corporate / Maintenance Companies)
            'company_android_latest_version' => '1.0.0',
            'company_android_minimum_version' => '1.0.0',
            'company_android_store_url' => 'https://play.google.com/store/apps/details?id=com.isky.matraqa.company&pcampaignid=web_share',
            'company_ios_latest_version' => '1.0.6',
            'company_ios_minimum_version' => '1.0.6',
            'company_ios_store_url' => 'https://apps.apple.com/app/matraqa-company/id6766896302',

            // 3. Technician App
            'technician_android_latest_version' => '1.0.0',
            'technician_android_minimum_version' => '1.0.0',
            'technician_android_store_url' => 'https://play.google.com/store/apps/details?id=com.isky.matraqa.technician&pcampaignid=web_share',
            'technician_ios_latest_version' => '1.0.0',
            'technician_ios_minimum_version' => '1.0.0',
            'technician_ios_store_url' => 'https://apps.apple.com/app/matraqa-technician/id6766959106',

            // Legacy keys fallback
            'android_latest_version' => '1.0.0',
            'android_minimum_version' => '1.0.0',
            'android_store_url' => 'https://play.google.com/store/apps/details?id=com.isky.matraqa&pcampaignid=web_share',
            'ios_latest_version' => '1.0.0',
            'ios_minimum_version' => '1.0.0',
            'ios_store_url' => 'https://apps.apple.com/app/matraqa-tech/id6766811584',

            'app_update_message_ar' => 'توجد نسخة جديدة من التطبيق. يرجى التحديث للمتابعة.',
            'app_update_message_en' => 'A new version of the app is available. Please update to continue.',
        ];

        $items = array_merge($defaults, $settings->toArray());

        return view('admin.settings.index', compact('items'));
    }

    /**
     * Store or update settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'default_language' => 'nullable|in:ar,en',
            'system_mode' => 'nullable|in:light,dark,auto',
            'order_acceptance_duration' => 'nullable|integer|min:1',
            'required_photos_before_count' => 'nullable|integer|min:0',
            'required_photos_after_count' => 'nullable|integer|min:0',
            'reminder_type' => 'nullable|in:day,hour,custom',
            'reminder_custom_value' => 'nullable|integer|min:1',
            'hourly_booking_capacity' => 'nullable|integer|min:1',
            'copyright_text_ar' => 'nullable|string|max:255',
            'copyright_text_en' => 'nullable|string|max:255',

            // Client App
            'client_android_latest_version' => 'nullable|string|max:50',
            'client_android_minimum_version' => 'nullable|string|max:50',
            'client_android_store_url' => 'nullable|url|max:500',
            'client_ios_latest_version' => 'nullable|string|max:50',
            'client_ios_minimum_version' => 'nullable|string|max:50',
            'client_ios_store_url' => 'nullable|url|max:500',

            // Company App
            'company_android_latest_version' => 'nullable|string|max:50',
            'company_android_minimum_version' => 'nullable|string|max:50',
            'company_android_store_url' => 'nullable|url|max:500',
            'company_ios_latest_version' => 'nullable|string|max:50',
            'company_ios_minimum_version' => 'nullable|string|max:50',
            'company_ios_store_url' => 'nullable|url|max:500',

            // Technician App
            'technician_android_latest_version' => 'nullable|string|max:50',
            'technician_android_minimum_version' => 'nullable|string|max:50',
            'technician_android_store_url' => 'nullable|url|max:500',
            'technician_ios_latest_version' => 'nullable|string|max:50',
            'technician_ios_minimum_version' => 'nullable|string|max:50',
            'technician_ios_store_url' => 'nullable|url|max:500',

            'app_update_message_ar' => 'nullable|string|max:500',
            'app_update_message_en' => 'nullable|string|max:500',
        ]);

        $settings = $request->only([
            'default_language',
            'system_mode',
            'order_acceptance_duration',
            'required_photos_before_count',
            'required_photos_after_count',
            'reminder_type',
            'reminder_custom_value',
            'hourly_booking_capacity',
            'copyright_text_ar',
            'copyright_text_en',

            'client_android_latest_version',
            'client_android_minimum_version',
            'client_android_store_url',
            'client_ios_latest_version',
            'client_ios_minimum_version',
            'client_ios_store_url',

            'company_android_latest_version',
            'company_android_minimum_version',
            'company_android_store_url',
            'company_ios_latest_version',
            'company_ios_minimum_version',
            'company_ios_store_url',

            'technician_android_latest_version',
            'technician_android_minimum_version',
            'technician_android_store_url',
            'technician_ios_latest_version',
            'technician_ios_minimum_version',
            'technician_ios_store_url',

            'app_update_message_ar',
            'app_update_message_en',
        ]);

        // Keep legacy keys in sync with client keys
        if (isset($settings['client_android_latest_version'])) {
            $settings['android_latest_version'] = $settings['client_android_latest_version'];
        }
        if (isset($settings['client_android_minimum_version'])) {
            $settings['android_minimum_version'] = $settings['client_android_minimum_version'];
        }
        if (isset($settings['client_android_store_url'])) {
            $settings['android_store_url'] = $settings['client_android_store_url'];
        }
        if (isset($settings['client_ios_latest_version'])) {
            $settings['ios_latest_version'] = $settings['client_ios_latest_version'];
        }
        if (isset($settings['client_ios_minimum_version'])) {
            $settings['ios_minimum_version'] = $settings['client_ios_minimum_version'];
        }
        if (isset($settings['client_ios_store_url'])) {
            $settings['ios_store_url'] = $settings['client_ios_store_url'];
        }

        foreach ($settings as $key => $value) {
            if ($value !== null) {
                Setting::setByKey($key, $value, 'platform');
                
                // Immediate effect for current user
                if ($key === 'default_language') {
                    session()->put('locale', $value);
                    app()->setLocale($value);
                }
                
                if ($key === 'system_mode' && $value !== 'auto') {
                    // Force theme sync for current session in localStorage via meta or flash
                    session()->flash('force_theme', $value);
                }
            }
        }

        return redirect()->route('admin.settings.index', ['tab' => $request->input('tab', 'app_versions')])->with('success', __('Settings updated successfully.'));
    }
}
