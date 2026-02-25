<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SocialLinkController extends Controller
{
    /**
     * Get all contact settings (Phone, Email, Social Links) for the unified UI page.
     */
    public function index()
    {
        $firstRecord = SocialLink::first();
        
        $contact = [
            'mobile' => $firstRecord ? $firstRecord->mobile : '',
            'email' => $firstRecord ? $firstRecord->email : '',
        ];

        $socialLinks = SocialLink::whereNotNull('name')->get();

        return view('admin.social_links.index', compact('contact', 'socialLinks'));
    }

    /**
     * Update all contact settings at once (Sync).
     */
    public function update(Request $request)
    {
        $request->validate([
            'contact_mobile' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'social_links' => 'nullable|array',
            'social_links.*.name' => 'required_with:social_links|string',
            'social_links.*.url' => 'required_with:social_links|url',
            'social_links.*.platform' => 'required_with:social_links|string',
        ]);

        \DB::transaction(function() use ($request) {
            // Store common info
            $mobile = $request->contact_mobile;
            $email = $request->contact_email;

            // Delete all existing to sync
            SocialLink::query()->delete();

            if ($request->has('social_links') && !empty($request->social_links)) {
                foreach ($request->social_links as $linkData) {
                    SocialLink::create([
                        'mobile' => $mobile,
                        'email' => $email,
                        'name' => $linkData['name'],
                        'url' => $linkData['url'],
                        'icon' => $linkData['platform'], // Using 'platform' as 'icon' or identifier
                    ]);
                }
            } else {
                // If no social links, create one record for mobile/email only
                SocialLink::create([
                    'mobile' => $mobile,
                    'email' => $email,
                ]);
            }
        });

        return redirect()->back()->with('success', __('Social links updated successfully.'));
    }
}
