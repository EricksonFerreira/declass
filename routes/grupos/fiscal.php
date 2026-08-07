<?php

use App\Http\Controllers\FiscalController;
use Illuminate\Support\Facades\Route;

Route::prefix('fiscal')->name('fiscal.')->group(function () {
    Route::get('/', [FiscalController::class, 'dashboard'])->name('dashboard');
    
    // Operações
    Route::get('/operacoes', [FiscalController::class, 'operacoes'])->name('operacoes');
    
    // Regras
    Route::get('/regras', [FiscalController::class, 'regras'])->name('regras');
    Route::get('/regras/criar', [FiscalController::class, 'regrasCriar'])->name('regras.criar');
    Route::post('/regras/criar', [FiscalController::class, 'regrasSalvar'])->name('regras.salvar');
    Route::get('/regras/{id}/editar', [FiscalController::class, 'regrasEditar'])->name('regras.editar');
    Route::put('/regras/{id}/editar', [FiscalController::class, 'regrasAtualizar'])->name('regras.atualizar');
    Route::delete('/regras/{id}', [FiscalController::class, 'regrasExcluir'])->name('regras.excluir');
    
    // Créditos
    Route::get('/creditos', [FiscalController::class, 'creditos'])->name('creditos');
    Route::get('/creditos/criar', [FiscalController::class, 'creditosCriar'])->name('creditos.criar');
    Route::post('/creditos/criar', [FiscalController::class, 'creditosSalvar'])->name('creditos.salvar');
    Route::post('/creditos/importar-xml', [FiscalController::class, 'importarXml'])->name('creditos.importar-xml');

    // Classificações Fiscais
    Route::get('/classificacoes', [FiscalController::class, 'classificacoes'])->name('classificacoes');
    Route::get('/classificacoes/criar', [FiscalController::class, 'classificacoesCriar'])->name('classificacoes.criar');
    Route::post('/classificacoes/criar', [FiscalController::class, 'classificacoesSalvar'])->name('classificacoes.salvar');

    // Exportação
    Route::get('/exportar-contabil', [FiscalController::class, 'exportarContabil'])->name('exportar-contabil');

    // Configurações Fiscais da Empresa
    Route::get('/configuracoes', [FiscalController::class, 'configuracoes'])->name('config');
    Route::post('/configuracoes', [FiscalController::class, 'configuracoesSalvar'])->name('config.salvar');
});
