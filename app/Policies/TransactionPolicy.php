<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function approve(User $user, Transaction $transaction): bool
    {
        return $user->role === 'admin';
    }
}
