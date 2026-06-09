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
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048', // 2MB max
        ]);

        // Update User fields
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
             if ($user->avatar) {
                 if (str_starts_with($user->avatar, 'uploads/')) {
                     if (file_exists(public_path($user->avatar))) {
                         unlink(public_path($user->avatar));
                     }
                 } else {
                     if (file_exists(public_path($user->avatar))) {
                         unlink(public_path($user->avatar));
                     }
                 }
             }
            
            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/avatars'), $filename);
            
            $user->avatar = 'uploads/avatars/' . $filename;
        }

        $user->save();

        // Update Admin Profile fields
        $profileData = $request->only(['first_name', 'last_name']);
        
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
        if ($adminProfile && ($adminProfile->first_name || $adminProfile->last_name)) {
            $name = trim(($adminProfile->first_name ?? '') . ' ' . ($adminProfile->last_name ?? ''));
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
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                function ($attribute, $value, $fail) use ($request) {
                    if (Hash::check($value, $request->user()->password)) {
                        $fail(__('The new password must be different from the old password.'));
                    }
                },
            ],
        ], [
            'password.min' => __('The password is too short. It must be at least 8 characters.'),
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', __('Password updated successfully.'));
    }
}
