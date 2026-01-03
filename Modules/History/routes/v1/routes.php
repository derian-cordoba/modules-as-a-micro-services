<?php

use Modules\History\Http\Controllers\V1\CreateController;

Route::as('histories.')
    ->prefix('histories')
    ->group(static function () {
        Route::post('/', CreateController::class)->name('create');
    });
