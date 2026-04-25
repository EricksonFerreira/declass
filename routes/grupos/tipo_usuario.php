<?php

use App\Http\Controllers\TipoUsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:acessar-superadmin')->prefix('tipo_usuario')->name('tipo_usuario.')->group(function () {
    Route::post('setperpage', [TipoUsuarioController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [TipoUsuarioController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [TipoUsuarioController::class, 'filtro'])->name('filtro');
});

Route::middleware('can:acessar-superadmin')->group(function () {
    Route::resource('tipo_usuario', TipoUsuarioController::class);
});
