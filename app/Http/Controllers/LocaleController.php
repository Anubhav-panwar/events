<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Switch application locale and persist to cookie and session.
     */
    public function switch(Request $request, ?string $lang = null)
    {
        $locale = $lang ?? $request->input('locale') ?? $request->input('lang');

        if (! in_array($locale, SetLocale::SUPPORTED_LOCALES, true)) {
            $locale = SetLocale::DEFAULT_LOCALE;
        }

        Session::put('locale', $locale);
        App::setLocale($locale);
        \Carbon\Carbon::setLocale($locale);

        $cookie = cookie('locale', $locale, 60 * 24 * 365, '/', null, false, false);

        if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'success' => true,
                'locale' => $locale,
                'message' => 'Language changed successfully to ' . strtoupper($locale),
            ])->withCookie($cookie);
        }

        return redirect()->back()->withCookie($cookie);
    }
}
