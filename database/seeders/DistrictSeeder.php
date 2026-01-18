<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districts = [
            ['ar' => 'شمال المدينة', 'en' => 'North of City'],
            ['ar' => 'جنوب المدينة', 'en' => 'South of City'],
            ['ar' => 'شرق المدينة', 'en' => 'East of City'],
            ['ar' => 'غرب المدينة', 'en' => 'West of City'],
            ['ar' => 'وسط المدينة', 'en' => 'City Center'],
        ];

        // Ensure at least one city exists
        $city = \App\Models\City::firstOrCreate(
            ['name_en' => 'Riyadh'],
            ['name_ar' => 'الرياض']
        );

        foreach ($districts as $district) {
            \App\Models\District::updateOrCreate(
                ['name_ar' => $district['ar'], 'city_id' => $city->id],
                ['name_en' => $district['en']]
            );
        }
    }
}
