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
        $admin = User::firstOrCreate([
            'email' => 'admin@matraqa.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'type' => 'admin',
            'phone' => '0000000000',
            'status' => 'active',
        ]);

        $role = \App\Models\Role::where('name', 'Super Admin')->first();
        if ($role) {
            $admin->roles()->syncWithoutDetaching([
                $role->id => ['model_type' => User::class]
            ]);
        }
    }
}
