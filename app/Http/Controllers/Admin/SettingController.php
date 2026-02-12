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
        ]);

        $settings = $request->only([
            'default_language',
            'system_mode',
            'order_acceptance_duration',
            'required_photos_before_count',
            'required_photos_after_count',
            'reminder_type',
            'reminder_custom_value',
        ]);

        foreach ($settings as $key => $value) {
            if ($value !== null) {
                Setting::setByKey($key, $value, 'platform');
            }
        }

        return redirect()->route('admin.settings.index')->with('success', __('Settings updated successfully.'));
    }
}
