<?php

use App\Http\Controllers\EmpresaController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:acessar-superadmin')->group(function () {
    Route::get('rede', [\App\Http\Controllers\RedeController::class, 'index'])->name('rede.index');
});

Route::middleware('can:acessar-superadmin')->prefix('empresa')->name('empresa.')->group(function () {
    Route::post('setperpage', [EmpresaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [EmpresaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [EmpresaController::class, 'filtro'])->name('filtro');

    // Configuracao do site publico da empresa — tela dedicada.
    Route::get('{empresa}/site/preview', [\App\Http\Controllers\EmpresaSiteController::class, 'preview'])->name('site.preview');
    Route::get('{empresa}/site', [\App\Http\Controllers\EmpresaSiteController::class, 'edit'])->name('site.edit');
    Route::put('{empresa}/site', [\App\Http\Controllers\EmpresaSiteController::class, 'update'])->name('site.update');
});

// Rotas para administrador ver/editar o proprio tenant — DEVE vir antes do resource
Route::middleware('auth')->prefix('empresa')->name('empresa.')->group(function () {
    Route::get('minha', [EmpresaController::class, 'minhaEmpresa'])->name('minha');
    Route::get('editar', [EmpresaController::class, 'editarEmpresa'])->name('editar');
    Route::put('atualizar', [EmpresaController::class, 'atualizarEmpresa'])->name('atualizar');
});

Route::middleware('can:acessar-superadmin')->group(function () {
    Route::resource('empresa', EmpresaController::class);
});
