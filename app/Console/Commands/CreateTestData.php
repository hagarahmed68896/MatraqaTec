<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = \App\Models\User::where('type', 'individual')->first() ?: \App\Models\User::factory()->create(['type' => 'individual', 'name' => 'Test Customer']);
        $techUser = \App\Models\User::where('type', 'technician')->first() ?: \App\Models\User::factory()->create(['type' => 'technician', 'name' => 'Test Tech']);
        $tech = \App\Models\Technician::where('user_id', $techUser->id)->first() ?: \App\Models\Technician::create(['user_id' => $techUser->id]);
        $service = \App\Models\Service::find(1) ?: \App\Models\Service::create(['id' => 1, 'name_ar' => 'خدمة تجريبية (الكهرباء)', 'name_en' => 'Test Service (Electricity)', 'price' => null]);
        $city = \App\Models\City::first() ?: \App\Models\City::create(['name_ar' => 'الرياض', 'name_en' => 'Riyadh']);
        
        $service->update(['is_featured' => true, 'description_ar' => 'وصف تجريبي للخدمة الرئيسية', 'parent_id' => null]);

        // Create Sub-services under ID 1
        \App\Models\Service::updateOrCreate(
            ['name_ar' => 'إصلاح أسلاك', 'parent_id' => $service->id],
            ['name_en' => 'Wiring Repair', 'price' => 50, 'is_featured' => true]
        );
        \App\Models\Service::updateOrCreate(
            ['name_ar' => 'تركيب مفاتيح', 'parent_id' => $service->id],
            ['name_en' => 'Switches Installation', 'price' => 75, 'is_featured' => false]
        );

        $order = \App\Models\Order::create([
            'order_number' => 'TEST-' . rand(1000, 9999),
            'user_id' => $user->id,
            'technician_id' => $tech->id,
            'service_id' => $service->id,
            'city_id' => $city->id,
            'status' => 'in_progress',
            'total_price' => 150,
            'payment_method' => 'cash',
            'address' => 'Test Address',
            'scheduled_at' => now(),
        ]);

        $this->info("Test Order Created! ID: {$order->id}");
        
        // Create a test location for the technician
        \App\Models\TechnicianLocation::updateOrCreate(
            ['technician_id' => $tech->id],
            [
                'latitude' => 24.7136, // Riyadh
                'longitude' => 46.6753,
            ]
        );
        $this->info("Test Location Created for Technician ID: {$tech->id}");

        $this->info("Customer ID: {$user->id}");
        $this->info("Technician ID: {$tech->id}");
        $this->info("Service ID: {$service->id}");
    }
}
