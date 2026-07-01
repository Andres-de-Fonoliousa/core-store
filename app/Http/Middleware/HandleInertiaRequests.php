<?php

namespace App\Http\Middleware;

use App\Services\Tenant\TenantManager;
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
            $token = $request->session()->get('auth-token-plaintext');

            if (!$token) {
                $user->tokens()->where('name', 'auth-token')->delete();
                $token = $user->createToken('auth-token')->plainTextToken;
                $request->session()->put('auth-token-plaintext', $token);
            }
        }

        $manager = app(TenantManager::class);
        $tenant = $manager->getCurrent();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'token' => $token,
            ],
            'shop' => $tenant ? [
                'id' => $tenant->uuid,
                'name' => $tenant->name,
                'logo' => $tenant->logo_url,
                'favicon' => $tenant->favicon_url,
                'color' => $tenant->brand_color,
                'colorDark' => $tenant->brand_color_dark,
                'locale' => $tenant->locale,
                'currency' => $tenant->currency,
                'plan' => $tenant->plan,
                'status' => $tenant->status,
            ] : null,
            'platform' => $manager->isPlatformRequest(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
