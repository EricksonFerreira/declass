<?php

use App\Http\Controllers\HorarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('horarios')->name('horarios.')->group(function () {
    Route::get('/', [HorarioController::class, 'index'])->name('index');
    Route::get('exportar', [HorarioController::class, 'exportar'])->name('exportar');
});
