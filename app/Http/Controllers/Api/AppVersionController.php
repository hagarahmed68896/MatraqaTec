<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppVersionController extends Controller
{
    /**
     * Check application version for Client, Company, or Technician apps against latest and minimum versions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkVersion(Request $request): JsonResponse
    {
        try {
            // 1. Determine App Type (client, company, technician)
            $rawApp = $request->input('app', $request->input('app_type', $request->input('type', $request->header('X-App-Type', 'client'))));
            $app = strtolower(trim((string) $rawApp));
            
            if (in_array($app, ['customer', 'user', 'individual'], true)) {
                $app = 'client';
            } elseif (in_array($app, ['corporate', 'maintenance_company'], true)) {
                $app = 'company';
            } elseif (in_array($app, ['tech', 'worker'], true)) {
                $app = 'technician';
            }

            if (!in_array($app, ['client', 'company', 'technician'], true)) {
                $app = 'client';
            }

            // 2. Determine Platform (android or ios)
            $rawPlatform = $request->input('platform', $request->header('X-App-Platform', $request->header('X-Platform', '')));
            $platform = strtolower(trim((string) $rawPlatform));

            if (!in_array($platform, ['android', 'ios'], true)) {
                $userAgent = strtolower($request->header('User-Agent', ''));
                if (
                    str_contains($userAgent, 'iphone') ||
                    str_contains($userAgent, 'ipad') ||
                    str_contains($userAgent, 'ios') ||
                    str_contains($userAgent, 'cfnetwork') ||
                    str_contains($userAgent, 'darwin')
                ) {
                    $platform = 'ios';
                } else {
                    $platform = 'android';
                }
            }

            // 3. Determine Current Version
            $rawVersion = $request->input('current_version', $request->input('version', $request->header('X-App-Version', $request->header('App-Version', '1.0.0'))));
            $currentVersion = trim((string) $rawVersion);
            if ($currentVersion === '') {
                $currentVersion = '1.0.0';
            }

            // 4. Default Store URLs per App & Platform
            $defaultStoreUrls = [
                'client' => [
                    'android' => 'https://play.google.com/store/apps/details?id=com.isky.matraqa&pcampaignid=web_share',
                    'ios'     => 'https://apps.apple.com/app/matraqa-tech/id6766811584',
                ],
                'company' => [
                    'android' => 'https://play.google.com/store/apps/details?id=com.isky.matraqa.company&pcampaignid=web_share',
                    'ios'     => 'https://apps.apple.com/app/matraqa-company/id6766896302',
                ],
                'technician' => [
                    'android' => 'https://play.google.com/store/apps/details?id=com.isky.matraqa.technician&pcampaignid=web_share',
                    'ios'     => 'https://apps.apple.com/app/matraqa-technician/id6766959106',
                ],
            ];

            // 5. Fetch Settings from Database for the Target App & Platform
            $prefix = "{$app}_{$platform}";
            $legacyPrefix = $platform; // fallback for client

            $latestVersion = Setting::getByKey("{$prefix}_latest_version", 
                Setting::getByKey("{$legacyPrefix}_latest_version", '1.0.0')
            );

            $minVersion = Setting::getByKey("{$prefix}_minimum_version", 
                Setting::getByKey("{$legacyPrefix}_minimum_version", $latestVersion ?: '1.0.0')
            );

            $storeUrl = Setting::getByKey("{$prefix}_store_url", 
                Setting::getByKey("{$legacyPrefix}_store_url", $defaultStoreUrls[$app][$platform] ?? '')
            );

            // Fallback ensure values are non-empty strings
            $latestVersion = (string) ($latestVersion ?: '1.0.0');
            $minVersion = (string) ($minVersion ?: $latestVersion);
            $storeUrl = (string) ($storeUrl ?: ($defaultStoreUrls[$app][$platform] ?? ''));

            // 6. Compare Version (Semantic Version comparison)
            // If current_version is strictly less than minimum_supported_version, force update is required
            $updateRequired = version_compare($currentVersion, $minVersion, '<');

            // 7. Build Message
            $message = null;
            if ($updateRequired) {
                // Determine language preference
                $acceptLanguage = $request->header('Accept-Language', '');
                $isArabic = str_starts_with(strtolower($acceptLanguage), 'ar') || app()->getLocale() === 'ar';
                
                $customMsgKey = $isArabic ? 'app_update_message_ar' : 'app_update_message_en';
                $defaultMsg = $isArabic
                    ? 'توجد نسخة جديدة من التطبيق. يرجى التحديث للمتابعة.'
                    : 'A new version of the app is available. Please update to continue.';

                $message = Setting::getByKey($customMsgKey, $defaultMsg) ?: $defaultMsg;
            }

            // 8. Return response matching exact required schema
            return response()->json([
                'success' => true,
                'data' => [
                    'app' => $app,
                    'platform' => $platform,
                    'current_version' => $currentVersion,
                    'latest_version' => $latestVersion,
                    'minimum_supported_version' => $minVersion,
                    'update_required' => (bool) $updateRequired,
                    'store_url' => $storeUrl,
                    'message' => $message,
                ],
            ], 200);

        } catch (\Throwable $e) {
            Log::error('AppVersionController checkVersion error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to check app version at this time.',
            ], 500);
        }
    }
}
