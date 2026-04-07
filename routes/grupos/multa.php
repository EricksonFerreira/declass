<?php

use App\Http\Controllers\MultaController;
use Illuminate\Support\Facades\Route;

Route::prefix('multa')->name('multa.')->group(function () {
    Route::get('export', [MultaController::class, 'export'])->name('export');
});

Route::resource('multa', MultaController::class);
