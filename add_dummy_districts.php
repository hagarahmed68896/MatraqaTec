<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\District;
use App\Models\City;
use App\Models\MaintenanceCompany;

echo "--- Adding Dummy Districts ---\n";

$city = City::first();
if (!$city) {
    $city = City::create(['name_ar' => 'الرياض', 'name_en' => 'Riyadh']);
}

$districts = [
    ['name_ar' => 'شمال المدينه', 'name_en' => 'North City'],
    ['name_ar' => 'جنوب المدينه', 'name_en' => 'South City'],
];

$addedDistricts = [];
foreach ($districts as $d) {
    $district = District::firstOrCreate(
        ['name_ar' => $d['name_ar'], 'city_id' => $city->id],
        ['name_en' => $d['name_en']]
    );
    $addedDistricts[] = $district;
    echo "District ID {$district->id}: {$district->name_ar} added/found.\n";
}

echo "--- Assigning Districts to Companies ---\n";
$companies = MaintenanceCompany::all();
foreach ($companies as $company) {
    foreach ($addedDistricts as $district) {
        // Assuming syncWithoutDetaching or manually check pivot
        if (!$company->districts()->where('district_id', $district->id)->exists()) {
            $company->districts()->attach($district->id);
            echo "Assigned {$district->name_ar} to company: {$company->company_name_ar}\n";
        }
    }
}

echo "--- Done ---\n";
