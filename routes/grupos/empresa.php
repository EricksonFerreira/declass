<?php

use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\AssinaturaController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:acessar-superadmin')->prefix('empresa')->name('empresa.')->group(function () {
    Route::post('setperpage', [EmpresaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [EmpresaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [EmpresaController::class, 'filtro'])->name('filtro');
    Route::get('{empresa}/armazenamento', [EmpresaController::class, 'armazenamento'])->name('armazenamento');
    Route::get('{empresa}/atribuir-assinatura', [AssinaturaController::class, 'criarAtribuir'])->name('atribuir-assinatura');
    Route::get('{empresa}/site-apresentacao', [EmpresaController::class, 'editSiteApresentacao'])->name('site-apresentacao.edit');
    Route::put('{empresa}/site-apresentacao', [EmpresaController::class, 'updateSiteApresentacao'])->name('site-apresentacao.update');
    Route::get('{empresa}/site-editor', [EmpresaController::class, 'editSiteEditor'])->name('site-editor.edit');
    Route::post('{empresa}/site-editor', [EmpresaController::class, 'updateSiteEditor'])->name('site-editor.update');
    Route::get('{empresa}/site-editor-preview', [EmpresaController::class, 'previewSiteEditor'])->name('site-editor.preview');
});

// Rotas para administrador ver/editar a propria empresa — DEVE vir antes do resource
Route::middleware('auth')->prefix('empresa')->name('empresa.')->group(function () {
    Route::get('minha', [EmpresaController::class, 'minhaEmpresa'])->name('minha');
    Route::get('editar', [EmpresaController::class, 'editarEmpresa'])->name('editar');
    Route::put('atualizar', [EmpresaController::class, 'atualizarEmpresa'])->name('atualizar');
});

Route::middleware('can:acessar-superadmin')->group(function () {
    Route::resource('empresa', EmpresaController::class);
});
