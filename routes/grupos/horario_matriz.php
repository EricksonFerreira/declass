<?php

use App\Http\Controllers\HorarioMatrizController;
use Illuminate\Support\Facades\Route;

Route::prefix('horario-matriz')->name('horario_matriz.')->group(function () {
    Route::get('/', [HorarioMatrizController::class, 'index'])->name('index');
    Route::get('{escola}/novo', [HorarioMatrizController::class, 'create'])->name('create');
    Route::post('{escola}', [HorarioMatrizController::class, 'store'])->name('store');
    Route::get('{horario}/editar', [HorarioMatrizController::class, 'edit'])->name('edit');
    Route::put('{horario}', [HorarioMatrizController::class, 'update'])->name('update');
    Route::delete('{horario}', [HorarioMatrizController::class, 'destroy'])->name('destroy');
});
