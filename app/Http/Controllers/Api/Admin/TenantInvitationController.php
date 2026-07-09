<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantUser;
use App\Models\User;
use App\Notifications\MemberInvitation;
use App\Services\Tenant\PlanFeatures;
use App\Services\Tenant\TenantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TenantInvitationController extends Controller
{
    public function __construct(
        private TenantManager $manager,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = $this->manager->getCurrent();

        if (!$tenant) {
            return response()->json(['message' => 'No tenant context'], 404);
        }

        $invitations = TenantUser::where('tenant_id', $tenant->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return response()->json($invitations);
    }

    public function invite(Request $request): JsonResponse
    {
        $tenant = $this->manager->getCurrent();

        if (!$tenant) {
            return response()->json(['message' => 'No tenant context'], 404);
        }

        if (!PlanFeatures::canInviteUser($tenant)) {
            return response()->json(['message' => 'User limit reached for your plan. Upgrade to invite more users.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->input('email');

        $existingUser = User::withoutTenant()->where('email', $email)->first();

        if ($existingUser) {
            $alreadyMember = TenantUser::where('tenant_id', $tenant->id)
                ->where('user_id', $existingUser->id)
                ->exists();

            if ($alreadyMember) {
                return response()->json(['message' => 'User is already a member of this tenant'], 409);
            }
        }

        $membership = DB::transaction(function () use ($tenant, $email, $existingUser) {
            if (!$existingUser) {
                $existingUser = User::withoutTenant()->create([
                    'name' => explode('@', $email)[0],
                    'email' => $email,
                    'password' => bcrypt( Str::random(32)),
                    'tenant_id' => $tenant->id,
                ]);
            }

            $membership = TenantUser::create([
                'tenant_id' => $tenant->id,
                'user_id' => $existingUser->id,
                'role' => 'member',
                'invited_at' => now(),
            ]);

            $existingUser->notify(new MemberInvitation($tenant, $membership));

            return $membership;
        });

        return response()->json([
            'message' => 'Invitation sent successfully',
            'invitation' => $membership->load('user'),
        ], 201);
    }

    public function resend(Request $request, TenantUser $invitation): JsonResponse
    {
        $tenant = $this->manager->getCurrent();

        if (!$tenant || $invitation->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (!$invitation->isPending()) {
            return response()->json(['message' => 'Invitation is no longer pending'], 400);
        }

        $invitation->update(['invited_at' => now()]);
        $invitation->user->notify(new MemberInvitation($tenant, $invitation));

        return response()->json(['message' => 'Invitation resent']);
    }

    public function revoke(Request $request, TenantUser $invitation): JsonResponse
    {
        $tenant = $this->manager->getCurrent();

        if (!$tenant || $invitation->tenant_id !== $tenant->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $invitation->delete();

        return response()->json(['message' => 'Invitation revoked']);
    }
}
