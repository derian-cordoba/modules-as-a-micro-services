<?php

use Illuminate\Support\Facades\Route;

Route::as('v1.')
    ->prefix('v1')
    ->group(module_path('History', 'routes/v1/routes.php'));
