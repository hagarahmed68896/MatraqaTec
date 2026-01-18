<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = \App\Models\MaintenanceCompany::latest()->first();

        if (!$company) {
            $this->command->error('No Maintenance Company found!');
            return;
        }

        $this->command->info("Seeding data for Company: " . ($company->company_name_en ?? 'Unknown'));

        // Create 5 Technicians
        for ($i = 1; $i <= 5; $i++) {
            // Create user for technician
            $user = \App\Models\User::create([
                'name' => "Technician $i",
                'email' => "tech{$i}_{$company->id}@example.com", // Unique email
                'password' => bcrypt('password'),
                'type' => 'technician',
                'phone' => '5' . rand(10000000, 99999999),
            ]);

            $tech = \App\Models\Technician::create([
                'user_id' => $user->id,
                'maintenance_company_id' => $company->id,
                'name_en' => "Technician $i",
                'name_ar' => "فني $i",
                'years_experience' => rand(1, 10),
                'availability_status' => 'available',
            ]);

            // Create Orders for this Technician
            for ($j = 1; $j <= 3; $j++) {
                $status = $j == 1 ? 'completed' : 'in_progress';
                \App\Models\Order::create([
                    'user_id' => $company->user_id, // Assigned by company owner for simplicity/test
                    'technician_id' => $tech->id,
                    'maintenance_company_id' => $company->id,
                    'service_id' => 1, // Assuming service ID 1 exists
                    'status' => $status,
                    'total_amount' => rand(100, 500),
                    'address_id' => 1, // Dummy
                    'booking_date' => now()->subDays(rand(1, 10)),
                    'booking_time' => '10:00:00',
                ]);
            }
        }
    }
}
