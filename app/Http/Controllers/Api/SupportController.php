<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\PrivacyPolicy;
use App\Models\SocialLink;
use App\Models\Term;
use App\Models\Setting;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * Get consolidated support info (Contact & Social Links)
     */
    public function index()
    {
        $socialLinks = SocialLink::all();
        
        // Get contact info from settings or first social link entry
        $contact = [
            'phone' => Setting::getByKey('contact_phone', $socialLinks->first()->mobile ?? ''),
            'email' => Setting::getByKey('contact_email', $socialLinks->first()->email ?? 'support@matraqa.sa'),
        ];

        // Global VAT and Commission configurations
        $settings = [
            'vat_rate_services' => (float) Setting::getByKey('vat_rate_services', 15),
            'platform_commission_services' => (float) Setting::getByKey('platform_commission_services', 15),
            'vat_rate_parts' => (float) Setting::getByKey('vat_rate_parts', 15),
            'platform_commission_parts' => (float) Setting::getByKey('platform_commission_parts', 15),
            // Backwards compatibility fallbacks
            'vat_rate' => (float) Setting::getByKey('vat_rate', 15),
            'platform_commission' => (float) Setting::getByKey('platform_commission', 15),
            // General settings
            'order_acceptance_duration' => (int) Setting::getByKey('order_acceptance_duration', 30),
            'required_photos_before_count' => (int) Setting::getByKey('required_photos_before_count', 1),
            'required_photos_after_count' => (int) Setting::getByKey('required_photos_after_count', 1),
            'reminder_type' => Setting::getByKey('reminder_type', 'day'),
            'reminder_custom_value' => Setting::getByKey('reminder_custom_value', null),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Support information retrieved',
            'data' => [
                'contact' => $contact,
                'social_links' => $socialLinks,
                'settings' => $settings
            ]
        ]);
    }

    /**
     * Get FAQs filtered for companies
     */
    public function faqs(Request $request)
    {
        $user = $request->user('sanctum') ?? auth('sanctum')->user();
        $type = $user ? $user->type : 'client';
        if ($type === 'individual') $type = 'client';

        $groups = ['all', 'both', $type];
        // Handle plurals if present in DB (e.g. 'clients', 'technicians')
        if ($type === 'client') $groups[] = 'clients';
        if ($type === 'technician') $groups[] = 'technicians';

        $faqs = Faq::where('status', 'active')
            ->whereIn('target_group', $groups)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'FAQs retrieved',
            'data' => $faqs
        ]);
    }

    /**
     * Get Terms and Conditions filtered for companies
     */
    public function terms(Request $request)
    {
        $user = $request->user('sanctum') ?? auth('sanctum')->user();
        $type = $user ? $user->type : 'client';
        if ($type === 'individual') $type = 'client';

        $groups = ['all', 'both', $type];
        if ($type === 'client') $groups[] = 'clients';
        if ($type === 'technician') $groups[] = 'technicians';

        $terms = Term::where('status', 'active')
            ->whereIn('target_group', $groups)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Terms and Conditions retrieved',
            'data' => $terms
        ]);
    }

    /**
     * Get Privacy Policy filtered for companies
     */
    public function privacy(Request $request)
    {
        $user = $request->user('sanctum') ?? auth('sanctum')->user();
        $type = $user ? $user->type : 'client';
        if ($type === 'individual') $type = 'client';

        $groups = ['all', 'both', $type];
        if ($type === 'client') $groups[] = 'clients';
        if ($type === 'technician') $groups[] = 'technicians';

        $policies = PrivacyPolicy::where('status', 'active')
            ->whereIn('target_group', $groups)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Privacy Policy retrieved',
            'data' => $policies
        ]);
    }
}
