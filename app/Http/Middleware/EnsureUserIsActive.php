<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Contracts\Session\Middleware\AuthenticatesSessions;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public const INACTIVE_MESSAGE = 'Akun Anda tidak aktif. Silakan hubungi administrator.';

    public function __construct(private Router $router) {}

    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('web');
        $user = $guard->user();

        if (! $user instanceof User || $request->routeIs('logout')) {
            return $next($request);
        }

        if ($this->isActiveAccount($user)) {
            return $next($request);
        }

        if (! $user->exists) {
            $user->setRememberToken('');
        }

        $guard->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($this->shouldDenyAccess($request)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => self::INACTIVE_MESSAGE,
                ], Response::HTTP_FORBIDDEN);
            }

            return redirect()->route('login')->withErrors([
                'email' => self::INACTIVE_MESSAGE,
            ]);
        }

        return $next($request);
    }

    private function isActiveAccount(User $user): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if (! $user->exists) {
            return true;
        }

        return (bool) User::query()
            ->whereKey($user->getKey())
            ->value('is_active');
    }

    private function shouldDenyAccess(Request $request): bool
    {
        if ($request->routeIs('test-drive.store', 'service-booking.store')) {
            return true;
        }

        $route = $request->route();
        if (! $route instanceof Route) {
            return false;
        }

        foreach ($this->router->gatherRouteMiddleware($route) as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }

            $class = strstr($middleware, ':', true) ?: $middleware;

            if (
                is_a($class, AuthenticatesRequests::class, true)
                || is_a($class, AuthenticatesSessions::class, true)
                || in_array($class, [
                    AuthenticateWithBasicAuth::class,
                    Authorize::class,
                    CheckRole::class,
                    EnsureEmailIsVerified::class,
                    RequirePassword::class,
                ], true)
            ) {
                return true;
            }
        }

        return false;
    }
}
