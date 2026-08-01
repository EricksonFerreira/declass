<?php

use App\Http\Controllers\ChamadoCategoriaController;
use App\Http\Controllers\ChamadoStatusController;
use App\Http\Controllers\ChamadoSubcategoriaController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:acessar-superadmin')->prefix('config-chamados')->name('config_chamados.')->group(function () {
    Route::resource('categorias', ChamadoCategoriaController::class)->except(['show']);
    Route::resource('subcategorias', ChamadoSubcategoriaController::class)->except(['show']);
    Route::resource('status', ChamadoStatusController::class)->except(['show']);
});
