<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Level 1: Category (Electricity)
        $electricity = Service::updateOrCreate(
            ['code' => 'ELEC-MAIN'],
            [
                'name_ar' => 'خدمات الكهرباء',
                'name_en' => 'Electricity Services',
                'description_ar' => 'جميع خدمات الكهرباء المنزلية والصناعية',
                'description_en' => 'All residential and industrial electricity services',
                'price' => 50,
                'is_featured' => true,
                'city_id' => 1,
            ]
        );

        // 2. Level 2: Sub-categories
        $wires = Service::updateOrCreate(
            ['code' => 'ELEC-WIRES'],
            [
                'name_ar' => 'أسلاك',
                'name_en' => 'Wires',
                'parent_id' => $electricity->id,
                'description_ar' => 'تمديد وإصلاح الأسلاك والتماسات الكهربائية',
                'description_en' => 'Extension and repair of wires and electrical contacts',
                'price' => 50,
                'city_id' => 1,
            ]
        );

        $switches = Service::updateOrCreate(
            ['code' => 'ELEC-SWITCH'],
            [
                'name_ar' => 'أفياش ومفاتيح',
                'name_en' => 'Switches & Plugs',
                'parent_id' => $electricity->id,
                'description_ar' => 'تركيب وتصليح الأفياش والمفاتيح الكهربائية',
                'description_en' => 'Installation and repair of electrical switches and plugs',
                'price' => 45,
                'city_id' => 1,
            ]
        );

        $fans = Service::updateOrCreate(
            ['code' => 'ELEC-FANS'],
            [
                'name_ar' => 'مراوح وتكييفات',
                'name_en' => 'Fans & Air Conditioning',
                'parent_id' => $electricity->id,
                'description_ar' => 'صيانة المراوح وأجهزة التكييف المختلفة',
                'description_en' => 'Maintenance of various fans and AC units',
                'price' => 60,
                'city_id' => 1,
            ]
        );

        // 3. Level 3: Specific Tasks (Children of "Wires")
        $wireTasks = [
            ['name_ar' => 'تمديد أسلاك جديد', 'name_en' => 'New Wire Extension', 'price' => 100, 'code' => 'TASK-W-1'],
            ['name_ar' => 'إصلاح تماسات كهربائية', 'name_en' => 'Electrical contact repair', 'price' => 80, 'code' => 'TASK-W-2'],
            ['name_ar' => 'فحص جودة التمديدات', 'name_en' => 'Wiring Quality Inspection', 'price' => 50, 'code' => 'TASK-W-3'],
            ['name_ar' => 'تجديد تمديدات قديمة', 'name_en' => 'Old wiring renewal', 'price' => 150, 'code' => 'TASK-W-4'],
            ['name_ar' => 'إصلاح قواطع رئيسية', 'name_en' => 'Main breaker repair', 'price' => 120, 'code' => 'TASK-W-5'],
        ];

        foreach ($wireTasks as $task) {
            Service::updateOrCreate(
                ['code' => $task['code']],
                [
                    'name_ar' => $task['name_ar'],
                    'name_en' => $task['name_en'],
                    'parent_id' => $wires->id,
                    'price' => $task['price'],
                    'city_id' => 1,
                ]
            );
        }

        // 4. Level 3: Specific Tasks (Children of "Switches")
        $switchTasks = [
            ['name_ar' => 'تبديل فيش تالف', 'name_en' => 'Replace damaged plug', 'price' => 30, 'code' => 'TASK-S-1'],
            ['name_ar' => 'تركيب فيش مودرن', 'name_en' => 'Install modern plug', 'price' => 45, 'code' => 'TASK-S-2'],
        ];

        foreach ($switchTasks as $task) {
            Service::updateOrCreate(
                ['code' => $task['code']],
                [
                    'name_ar' => $task['name_ar'],
                    'name_en' => $task['name_en'],
                    'parent_id' => $switches->id,
                    'price' => $task['price'],
                    'city_id' => 1,
                ]
            );
        }
    }
}
