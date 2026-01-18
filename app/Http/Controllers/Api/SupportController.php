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

        return response()->json([
            'status' => true,
            'message' => 'Support information retrieved',
            'data' => [
                'contact' => $contact,
                'social_links' => $socialLinks
            ]
        ]);
    }

    /**
     * Get FAQs filtered for companies
     */
    public function faqs()
    {
        $faqs = Faq::where('status', 'active')
            ->where(function($q) {
                $q->whereIn('target_group', ['company', 'all', 'both']);
            })
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
    public function terms()
    {
        $terms = Term::where('status', 'active')
            ->where(function($q) {
                $q->whereIn('target_group', ['company', 'all', 'both']);
            })
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
    public function privacy()
    {
        $policies = PrivacyPolicy::where('status', 'active')
            ->where(function($q) {
                $q->whereIn('target_group', ['company', 'all', 'both']);
            })
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Privacy Policy retrieved',
            'data' => $policies
        ]);
    }
}
