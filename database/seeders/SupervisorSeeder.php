<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SupervisorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supervisors = [
            [
                'name' => 'مدير العملاء',
                'email' => 'client.manager@matraqa.com',
                'role' => 'Client Manager',
            ],
            [
                'name' => 'مدير فني',
                'email' => 'tech.manager@matraqa.com',
                'role' => 'Technical Manager',
            ],
            [
                'name' => 'مدقق مالي',
                'email' => 'auditor@matraqa.com',
                'role' => 'Financial Auditor',
            ],
            [
                'name' => 'مدير المحتوى',
                'email' => 'content.manager@matraqa.com',
                'role' => 'Content Manager',
            ],
            [
                'name' => 'موظف دعم',
                'email' => 'support@matraqa.com',
                'role' => 'Support Agent',
            ],
        ];

        foreach ($supervisors as $data) {
            $user = User::firstOrCreate([
                'email' => $data['email'],
            ], [
                'name' => $data['name'],
                'password' => Hash::make('password'),
                'type' => 'supervisor',
                'status' => 'active',
            ]);

            $role = Role::where('name', $data['role'])->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([
                    $role->id => ['model_type' => User::class]
                ]);
            }
        }
    }
}
