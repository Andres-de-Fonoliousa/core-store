<?php

namespace App\Exceptions;

use App\Models\Provider;
use Exception;

class InsufficientProviderBalanceException extends Exception
{
    public function __construct(
        public readonly Provider $provider,
        string $message = null,
    ) {
        parent::__construct($message ?? "Provider '{$provider->name}' has insufficient balance to fulfill the order.");
    }
}
