<?php

use App\Http\Controllers\SinistroController;
use Illuminate\Support\Facades\Route;

Route::prefix('sinistro')->name('sinistro.')->group(function () {
    Route::get('export', [SinistroController::class, 'export'])->name('export');
});

Route::resource('sinistro', SinistroController::class);
