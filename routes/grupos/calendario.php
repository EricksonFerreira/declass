<?php

use App\Http\Controllers\CalendarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('calendario')->name('calendario.')->group(function () {
    Route::get('/', [CalendarioController::class, 'index'])->name('index');
});
