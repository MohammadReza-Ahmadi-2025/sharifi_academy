<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\ModulesServiceProvider;

return [
    AppServiceProvider::class,
    ModulesServiceProvider::class,
    FortifyServiceProvider::class,
];
