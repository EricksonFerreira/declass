<?php

use App\Http\Controllers\FeriadoController;
use Illuminate\Support\Facades\Route;

Route::get('feriado/filtro', [FeriadoController::class, 'filtro'])->name('feriado.filtro');
Route::match(['get', 'post'], 'feriado/filtro/remover', [FeriadoController::class, 'removerFiltro'])->name('feriado.removerFiltro');
Route::resource('feriado', FeriadoController::class)->except('show');
