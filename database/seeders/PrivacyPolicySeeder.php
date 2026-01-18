<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PrivacyPolicy;

class PrivacyPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PrivacyPolicy::create([
            'title_ar' => 'سياسة الخصوصية',
            'title_en' => 'Privacy Policy',
            'content_ar' => 'هذا هو نص سياسة الخصوصية الافتراضي. يرجى تحديثه من لوحة التحكم.',
            'content_en' => 'This is the default privacy policy text. Please update it from the admin panel.',
            'target_group' => 'all',
            'status' => 'active',
        ]);
    }
}
