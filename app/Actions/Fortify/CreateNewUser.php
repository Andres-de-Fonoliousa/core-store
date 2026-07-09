<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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

        return DB::transaction(function () use ($input, $invitationId) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            if ($invitationId) {
                $this->acceptInvitation($user, $invitationId);
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

        if (!$membership) {
            return;
        }

        $membership->update([
            'joined_at' => now(),
        ]);

        $user->update(['tenant_id' => $membership->tenant_id]);
    }
}
