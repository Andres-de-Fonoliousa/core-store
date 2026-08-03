<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GrantPlatformAdmin extends Command
{
    protected $signature = 'platform:grant-admin {email} {--revoke : Revoke platform admin access}';

    protected $description = 'Grant or revoke is_platform_admin for a user';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("No user found with email {$this->argument('email')}");

            return Command::FAILURE;
        }

        if ($this->option('revoke')) {
            $user->update(['is_platform_admin' => false]);
            $this->info("Revoked platform admin from {$user->email}");

            return Command::SUCCESS;
        }

        $user->update(['is_platform_admin' => true]);
        $this->info("Granted platform admin to {$user->email}");

        return Command::SUCCESS;
    }
}
