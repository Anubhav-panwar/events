<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED_LOCALES = ['en', 'de', 'it'];
    public const DEFAULT_LOCALE = 'en';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Route or Query parameter override if explicitly provided
        $locale = $request->route('lang') ?? $request->query('lang') ?? $request->query('locale');
        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = null;
        }

        // 2. Cookie preference
        if (! $locale || ! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $rawCookie = $request->cookie('locale');
            if ($rawCookie && in_array($rawCookie, self::SUPPORTED_LOCALES, true)) {
                $locale = $rawCookie;
            } elseif ($rawCookie) {
                try {
                    $decrypted = \Illuminate\Support\Facades\Crypt::decrypt($rawCookie, false);
                    if (is_string($decrypted) && in_array($decrypted, self::SUPPORTED_LOCALES, true)) {
                        $locale = $decrypted;
                    }
                } catch (\Throwable $e) {
                    // Ignore decryption failures and let fallbacks handle it
                }
            }
        }

        // 3. Session preference
        if (! $locale || ! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = Session::get('locale');
        }

        // 4. Default fallback
        if (! $locale || ! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = self::DEFAULT_LOCALE;
        }

        App::setLocale($locale);
        \Carbon\Carbon::setLocale($locale);

        if (Session::get('locale') !== $locale) {
            Session::put('locale', $locale);
        }

        $response = $next($request);

        // Attach unencrypted persistent cookie reflecting final active locale
        $finalLocale = App::getLocale();
        if (in_array($finalLocale, self::SUPPORTED_LOCALES, true)) {
            if ($request->cookie('locale') !== $finalLocale) {
                $cookie = cookie('locale', $finalLocale, 60 * 24 * 365, '/', null, false, false);
                if (method_exists($response, 'withCookie')) {
                    $response->withCookie($cookie);
                } else {
                    $response->headers->setCookie($cookie);
                }
            }
        }

        return $response;
    }
}
