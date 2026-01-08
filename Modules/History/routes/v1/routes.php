<?php

use Modules\History\Http\Controllers\V1\CreateController;
use Modules\History\Http\Controllers\V1\DeleteController;
use Modules\History\Http\Controllers\V1\FetchController;

Route::as('histories.')
    ->prefix('histories')
    ->group(static function () {
        Route::get('/', FetchController::class)->name('fetch');
        Route::post('/', CreateController::class)->name('create');
        Route::delete('/', DeleteController::class)->name('delete');
    });
