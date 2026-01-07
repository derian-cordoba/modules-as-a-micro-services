<?php

use Modules\History\Http\Controllers\V1\CreateController;
use Modules\History\Http\Controllers\V1\DeleteController;

Route::as('histories.')
    ->prefix('histories')
    ->group(static function () {
        Route::post('/', CreateController::class)->name('create');
        Route::delete('/', DeleteController::class)->name('delete');
    });
