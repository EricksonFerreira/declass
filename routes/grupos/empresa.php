<?php

use App\Http\Controllers\EmpresaController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:acessar-superadmin')->prefix('empresa')->name('empresa.')->group(function () {
    Route::post('setperpage', [EmpresaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [EmpresaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [EmpresaController::class, 'filtro'])->name('filtro');
    Route::get('{empresa}/site-apresentacao', [EmpresaController::class, 'editSiteApresentacao'])->name('site-apresentacao.edit');
    Route::put('{empresa}/site-apresentacao', [EmpresaController::class, 'updateSiteApresentacao'])->name('site-apresentacao.update');
});

// Rota para ver a propria empresa (qualquer usuario vinculado) — DEVE vir antes do resource
Route::middleware('auth')->prefix('empresa')->name('empresa.')->group(function () {
    Route::get('minha', [EmpresaController::class, 'minhaEmpresa'])->name('minha');
});

Route::middleware('can:acessar-superadmin')->group(function () {
    Route::resource('empresa', EmpresaController::class);
});
