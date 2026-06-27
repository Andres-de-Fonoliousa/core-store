<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use Laravel\Pulse\PulseServiceProvider;
use Livewire\LivewireServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    PulseServiceProvider::class,
    LivewireServiceProvider::class,
];
