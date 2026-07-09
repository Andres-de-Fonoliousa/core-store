<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\WelcomeOnboarding;
use App\Services\Tenant\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function __construct(
        private TenantManager $manager,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator->errors())->withInput();
        }

        $slug = Str::slug($request->input('name'));

        $result = DB::transaction(function () use ($request, $slug) {
            $originalSlug = $slug;
            $counter = 1;
            while (Tenant::withoutTenant()->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }

            $tenant = Tenant::withoutTenant()->create([
                'uuid' => (string) Str::uuid(),
                'name' => $request->input('name'),
                'slug' => $slug,
                'subdomain' => $slug,
                'status' => 'trial',
                'plan' => 'free',
                'trial_ends_at' => now()->addDays(14),
                'locale' => 'en',
                'currency' => 'USD',
            ]);

            $user = User::withoutTenant()->create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'tenant_id' => $tenant->id,
                'role' => 'admin',
            ]);

            $tenant->users()->attach($user->id, [
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            return ['tenant' => $tenant, 'user' => $user];
        });

        $this->manager->setCurrent($result['tenant']);

        auth()->login($result['user']);

        $result['user']->notify(new WelcomeOnboarding($result['tenant']));

        return redirect()->to('/admin/onboarding');
    }
}
