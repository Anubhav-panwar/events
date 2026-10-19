<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserNotBlocked
{
    /**
     * Handle an incoming request.
     * Terminate session immediately if the user is blocked.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user && $user->isBlocked()) {
                $reason = $user->blocked_reason ?: __('Your account has been suspended by an administrator.');

                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'message' => __('Account blocked. Reason: :reason', ['reason' => $reason]),
                        'blocked' => true,
                    ], 403);
                }

                return redirect()->route('login')->withErrors([
                    'email' => __('Your account has been blocked. Reason: :reason', ['reason' => $reason]),
                ]);
            }
        }

        return $next($request);
    }
}
