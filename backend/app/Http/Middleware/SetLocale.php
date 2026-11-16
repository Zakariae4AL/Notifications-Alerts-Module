<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Picks the response language, in order of priority:
 *   1. an explicit ?lang= query parameter,
 *   2. the authenticated user's saved locale,
 *   3. the Accept-Language header,
 *   4. the application default.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang')
            ?? $request->user('sanctum')?->preferredLocale()
            ?? $request->getPreferredLanguage(User::SUPPORTED_LOCALES);

        if (in_array($locale, User::SUPPORTED_LOCALES, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
