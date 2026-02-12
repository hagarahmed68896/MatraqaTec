<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('adminProfile');
        return view('admin.profile.show', compact('user'));
    }

    public function edit(Request $request)
    {
        $user = $request->user()->load('adminProfile');
        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'phone' => 'nullable|string',
            'first_name_ar' => 'nullable|string',
            'last_name_ar' => 'nullable|string',
            'first_name_en' => 'nullable|string',
            'last_name_en' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048', // 2MB max
        ]);

        // Update User fields
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && File::exists(public_path($user->avatar))) {
                File::delete(public_path($user->avatar));
            }
            
            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('avatars'), $filename);
            
            $user->avatar = 'avatars/' . $filename;
        }

        $user->save();

        // Update Admin Profile fields
        $profileData = $request->only(['first_name_ar', 'last_name_ar', 'first_name_en', 'last_name_en']);
        
        if ($user->adminProfile) {
            $user->adminProfile->update($profileData);
        } else {
            // Create if not exists
            $user->adminProfile()->create($profileData);
        }

        // Sync name to main user table
        $user->refresh(); // ensure we have latest relation
        $adminProfile = $user->adminProfile;
        
        $name = null;
        if ($adminProfile && ($adminProfile->first_name_en || $adminProfile->last_name_en)) {
            $name = trim(($adminProfile->first_name_en ?? '') . ' ' . ($adminProfile->last_name_en ?? ''));
        } elseif ($adminProfile && ($adminProfile->first_name_ar || $adminProfile->last_name_ar)) {
            $name = trim(($adminProfile->first_name_ar ?? '') . ' ' . ($adminProfile->last_name_ar ?? ''));
        }

        if ($name) {
            $user->name = $name;
            $user->save();
        }

        return redirect()->route('admin.profile.show')->with('success', __('Profile updated successfully.'));
    }

    public function deleteAvatar(Request $request)
    {
        $user = $request->user();
        
        if ($user->avatar) {
            if (File::exists(public_path($user->avatar))) {
                File::delete(public_path($user->avatar));
            }
            $user->avatar = null;
            $user->save();
        }

        return back()->with('success', __('Avatar deleted successfully.'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', __('Password updated successfully.'));
    }
}
