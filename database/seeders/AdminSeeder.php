<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@matraqa.com',
            'password' => Hash::make('password'), // Change this in production
            'type' => 'admin',
            'phone' => '0000000000',
            'status' => 'active',
        ]);
    }
}
