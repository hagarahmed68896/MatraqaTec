<?php

namespace App\Traits;

use App\Models\City;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait HandlesLocation
{
    /**
     * Helper: Reverse Geocode (Lat/Lng -> Address & City Name) using OpenStreetMap (Nominatim)
     */
    protected function getLocationDataFromCoords($lat, $lng)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'MatraqaTec-App/1.0'
            ])->get("https://nominatim.openstreetmap.org/reverse", [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lng,
                'accept-language' => 'ar', // Prefer Arabic
                'addressdetails' => 1
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $address = $data['address'] ?? [];
                
                // Extract city name (prioritize city, then town, then village, then state)
                $cityName = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['state'] ?? null;
                
                // Remove common prefixes if needed
                if ($cityName) {
                    $cityName = str_replace(['محافظة ', 'مدينة '], '', $cityName);
                }

                Log::info("Geocoding result for Lat: {$lat}, Lng: {$lng}", [
                    'display_name' => $data['display_name'] ?? 'N/A',
                    'city_name' => $cityName ?? 'N/A'
                ]);

                return [
                    'display_name' => $data['display_name'] ?? null,
                    'city_name' => $cityName
                ];
            } else {
                Log::error("Geocoding failed for Lat: {$lat}, Lng: {$lng}", ['response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error("Geocoding exception for Lat: {$lat}, Lng: {$lng}", ['message' => $e->getMessage()]);
        }
        return null;
    }

    /**
     * Detect City ID from coordinates
     */
    protected function detectCityFromCoords($lat, $lng)
    {
        $locationData = $this->getLocationDataFromCoords($lat, $lng);
        if ($locationData && !empty($locationData['city_name'])) {
            $cityName = $locationData['city_name'];
            $city = City::where('name_ar', 'LIKE', "%{$cityName}%")
                        ->orWhere('name_en', 'LIKE', "%{$cityName}%")
                        ->first();
            
            if (!$city) {
                Log::warning("City matching failed for name: {$cityName}");
            }

            return $city ? $city->id : null;
        }
        Log::warning("No city name found in coordinates: Lat: {$lat}, Lng: {$lng}");
        return null;
    }
}
