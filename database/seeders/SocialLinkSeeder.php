<?php

namespace Database\Seeders;

use App\Models\SocialLink;
use Illuminate\Database\Seeder;

class SocialLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mobile = '+966 500000000';
        $email = 'info@example.com';

        // Default Social Links
        $links = [
            ['name' => 'Facebook', 'url' => 'https://facebook.com', 'icon' => 'facebook'],
            ['name' => 'Instagram', 'url' => 'https://instagram.com', 'icon' => 'instagram'],
            ['name' => 'Twitter', 'url' => 'https://twitter.com', 'icon' => 'twitter'],
            ['name' => 'LinkedIn', 'url' => 'https://linkedin.com', 'icon' => 'linkedin'],
            ['name' => 'TikTok', 'url' => 'https://tiktok.com', 'icon' => 'tiktok'],
        ];

        foreach ($links as $link) {
            SocialLink::create(array_merge($link, [
                'mobile' => $mobile,
                'email' => $email,
            ]));
        }
    }
}
