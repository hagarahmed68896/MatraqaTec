
// 1. Setup Data
$email = 'fav_test@example.com';
\App\Models\User::where('email', $email)->delete();

$user = \App\Models\User::create([
    'name' => 'Fav Test',
    'email' => $email,
    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
    'type' => 'individual',
    'phone' => '522222222'
]);

\Illuminate\Support\Facades\Auth::login($user);

// Create Dummy Cities
$city1 = \App\Models\City::firstOrCreate(['name_en' => 'TestCity1'], ['name_ar' => 'مدينة1']);
$city2 = \App\Models\City::firstOrCreate(['name_en' => 'TestCity2'], ['name_ar' => 'مدينة2']);

// Create Dummy Categories (Services with null parent)
$cat1 = \App\Models\Service::create(['name_en' => 'Cat1', 'name_ar' => 'تصنيف1', 'price' => 0, 'city_id' => $city1->id, 'parent_id' => null]);
$cat2 = \App\Models\Service::create(['name_en' => 'Cat2', 'name_ar' => 'تصنيف2', 'price' => 0, 'city_id' => $city1->id, 'parent_id' => null]);

// Create Services
$srv1 = \App\Models\Service::create(['name_en' => 'Srv1', 'name_ar' => 'S1', 'price' => 100, 'city_id' => $city1->id, 'parent_id' => $cat1->id]); // City1, Cat1
$srv2 = \App\Models\Service::create(['name_en' => 'Srv2', 'name_ar' => 'S2', 'price' => 100, 'city_id' => $city1->id, 'parent_id' => $cat2->id]); // City1, Cat2
$srv3 = \App\Models\Service::create(['name_en' => 'Srv3', 'name_ar' => 'S3', 'price' => 100, 'city_id' => $city2->id, 'parent_id' => $cat1->id]); // City2, Cat1
$srv4 = \App\Models\Service::create(['name_en' => 'Srv4', 'name_ar' => 'S4', 'price' => 100, 'city_id' => $city1->id, 'parent_id' => $cat1->id]); // Not Favorite

// Add to Favorites
\App\Models\Favorite::create(['user_id' => $user->id, 'service_id' => $srv1->id]);
\App\Models\Favorite::create(['user_id' => $user->id, 'service_id' => $srv2->id]);
\App\Models\Favorite::create(['user_id' => $user->id, 'service_id' => $srv3->id]);

$controller = new \App\Http\Controllers\Api\ServiceController();

function testFav($desc, $params, $expectedCount, $controller, $user) {
    echo "TEST: $desc ... ";
    $req = \Illuminate\Http\Request::create('/api/services/favorites', 'GET', $params);
    $req->setUserResolver(function () use ($user) { return $user; });
    
    $res = $controller->favorites($req);
    $data = $res->getData(true)['data'];
    $count = count($data);
    
    if ($count === $expectedCount) {
        echo "PASS (Got $count)\n";
    } else {
        echo "FAIL (Expected $expectedCount, Got $count)\n";
        print_r(array_column($data, 'name_en'));
    }
}

// Tests
testFav("All Favorites", [], 3, $controller, $user); // Srv1, Srv2, Srv3
testFav("Filter City1", ['city_id' => $city1->id], 2, $controller, $user); // Srv1, Srv2
testFav("Filter Cat1", ['category_ids' => [$cat1->id]], 2, $controller, $user); // Srv1, Srv3
testFav("Filter City1 AND Cat1", ['city_id' => $city1->id, 'category_ids' => [$cat1->id]], 1, $controller, $user); // Srv1
testFav("Filter City2", ['city_id' => $city2->id], 1, $controller, $user); // Srv3

// Clean up
$user->delete();
// Optional: delete created services/cities if needed, but standard rollback usually handles or test db logic
\App\Models\Service::whereIn('id', [$cat1->id, $cat2->id, $srv1->id, $srv2->id, $srv3->id, $srv4->id])->delete();
\App\Models\City::whereIn('id', [$city1->id, $city2->id])->delete();
