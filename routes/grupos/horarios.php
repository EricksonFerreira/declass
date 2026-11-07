<?php

use App\Http\Controllers\HorarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('horarios')->name('horarios.')->group(function () {
    Route::get('/', [HorarioController::class, 'index'])->name('index');
    Route::get('exportar', [HorarioController::class, 'exportar'])->name('exportar');
    Route::put('{turma}/vincular', [HorarioController::class, 'vincular'])->name('vincular');
});
