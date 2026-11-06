<?php

use App\Http\Controllers\HorarioMatrizController;
use Illuminate\Support\Facades\Route;

Route::prefix('horario-matriz')->name('horario_matriz.')->group(function () {
    Route::get('/', [HorarioMatrizController::class, 'index'])->name('index');
    Route::get('{escola}/{turno}/editar', [HorarioMatrizController::class, 'edit'])->name('edit');
    Route::put('{escola}/{turno}', [HorarioMatrizController::class, 'update'])->name('update');
});
