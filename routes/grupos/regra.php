<?php

use App\Http\Controllers\RegraController;
use Illuminate\Support\Facades\Route;

// Perfis de acesso (roles) — escopo de empresa via RegraService; a permissao
// de tela e cobrada pelo middleware CheckPermissaoPorRota (rotas regra.*).
Route::prefix('regra')->name('regra.')->group(function () {
    Route::post('setperpage', [RegraController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [RegraController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [RegraController::class, 'filtro'])->name('filtro');
});
Route::resource('regra', RegraController::class);
