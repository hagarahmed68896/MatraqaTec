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
        $locale = null;

        $hasSession = $request->hasSession();

        // 1. Check Query Parameter (e.g., ?lang=ar)
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            if (in_array($locale, ['ar', 'en']) && $hasSession) {
                $request->session()->put('locale', $locale);
            }
        } 
        // 2. Check Session (for web)
        elseif ($hasSession && $request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        } 
        // 3. Check Accept-Language header (for API/Mobile)
        elseif ($request->is('api/*') && $request->header('Accept-Language')) {
            $locale = substr($request->header('Accept-Language'), 0, 2);
        }

        // Validate locale (ensure it's one of supported languages) or fallback to system default
        if (!in_array($locale, ['ar', 'en'])) {
            $locale = \App\Models\Setting::getByKey('default_language', config('app.locale', 'ar'));
        }

        app()->setLocale($locale);
        
        return $next($request);
    }
}
