<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Laravel\Sanctum\HasApiTokens;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function handle(Request $request, \Closure $next): \Symfony\Component\HttpFoundation\Response
    {
        if ($request->is('admin/sham-cash*') || $request->is('sham-cash*')) {
            return $next($request);
        }
        return parent::handle($request, $next);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $token = null;

        if ($user && in_array(HasApiTokens::class, class_uses_recursive($user))) {
            $token = $user->tokens()->where('name', 'auth-token')->first()?->plainTextToken
                  ?? $user->createToken('auth-token')->plainTextToken;
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'token' => $token,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
