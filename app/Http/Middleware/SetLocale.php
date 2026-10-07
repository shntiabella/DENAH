<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menerapkan bahasa aktif (locale) ke aplikasi berdasarkan Session/Cookie.
 *
 * Prioritas: Session > Cookie > config('app.locale').
 * Hanya locale yang didukung ('id', 'en') yang diterapkan.
 */
class SetLocale
{
    public const SUPPORTED = ['id', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale')
            ?? $request->cookie('locale')
            ?? config('app.locale', 'id');

        if (in_array($locale, self::SUPPORTED, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
