<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('locale')) {
            app()->setLocale(session()->get('locale'));
        } elseif ($request->hasHeader('Accept-Language')) {
            $locale = $request->header('Accept-Language');
            // Support simple locale strings like 'ar' or 'en'
            if (in_array($locale, ['ar', 'en'])) {
                app()->setLocale($locale);
            } else {
                // Handle complex strings like 'ar-SA,ar;q=0.9,en-US;q=0.8,en;q=0.7'
                $languages = explode(',', $locale);
                foreach ($languages as $lang) {
                    $langCode = substr(trim($lang), 0, 2);
                    if (in_array($langCode, ['ar', 'en'])) {
                        app()->setLocale($langCode);
                        break;
                    }
                }
            }
        } else {
            $locale = \App\Models\Setting::getByKey('default_language', config('app.locale'));
            app()->setLocale($locale);
        }
        
        return $next($request);
    }
}
