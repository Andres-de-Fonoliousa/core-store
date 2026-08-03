<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $invitationId = $input['invitation'] ?? null;
        $tenant = app(TenantManager::class)->getCurrent();

        if (! $invitationId && ! $tenant) {
            throw ValidationException::withMessages([
                'email' => 'Registration is only available through a store invitation or by creating a store.',
            ]);
        }

        return DB::transaction(function () use ($input, $invitationId, $tenant) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'tenant_id' => $tenant?->id,
            ]);

            if ($invitationId) {
                $this->acceptInvitation($user, $invitationId);
            } elseif ($tenant) {
                $tenant->users()->attach($user->id, [
                    'role' => 'member',
                    'joined_at' => now(),
                ]);
            }

            return $user;
        });
    }

    private function acceptInvitation(User $user, int $invitationId): void
    {
        /** @var TenantUser|null $membership */
        $membership = TenantUser::where('id', $invitationId)
            ->where('user_id', $user->id)
            ->whereNull('joined_at')
            ->whereNotNull('invited_at')
            ->first();

        if (! $membership) {
            return;
        }

        $membership->update([
            'joined_at' => now(),
        ]);

        $user->update(['tenant_id' => $membership->tenant_id]);
    }
}
