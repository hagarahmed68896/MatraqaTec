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
        
        $items = [
            'contact_mobile' => $firstRecord ? $firstRecord->mobile : null,
            'contact_email' => $firstRecord ? $firstRecord->email : null,
            'social_links' => SocialLink::whereNotNull('name')->get(),
        ];

        return view('admin.social_links.index', compact('items'));
    }

    /**
     * Update all contact settings at once (Sync).
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_mobile' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'social_links' => 'nullable|array',
            'social_links.*.name' => 'required_with:social_links|string',
            'social_links.*.url' => 'required_with:social_links|url',
            'social_links.*.icon' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Drop all existing to sync
        SocialLink::truncate();

        $mobile = $request->contact_mobile;
        $email = $request->contact_email;

        if ($request->has('social_links') && !empty($request->social_links)) {
            foreach ($request->social_links as $linkData) {
                SocialLink::create([
                    'mobile' => $mobile,
                    'email' => $email,
                    'name' => $linkData['name'],
                    'url' => $linkData['url'],
                    'icon' => $linkData['icon'] ?? null,
                ]);
            }
        } else {
            // If no social links, create one record for mobile/email only
            SocialLink::create([
                'mobile' => $mobile,
                'email' => $email,
            ]);
        }

        return redirect()->route('admin.social-links.index')->with('success', __('Social links updated successfully.'));
    }
}
