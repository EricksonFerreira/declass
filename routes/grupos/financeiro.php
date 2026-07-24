<?php

use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\FluxoCaixaController;
use App\Http\Controllers\CupomDescontoController;
use App\Http\Controllers\NfseController;
use Illuminate\Support\Facades\Route;

Route::prefix('financeiro')->name('financeiro.')->group(function () {
    Route::get('/', [FinanceiroController::class, 'portal'])->name('index');
    Route::get('/transacoes', [FinanceiroController::class, 'index'])->name('transacoes');
    Route::get('/reservas', [FinanceiroController::class, 'reservas'])->name('reservas');
    Route::get('/nfse', [NfseController::class, 'index'])->name('nfse.index');
    Route::get('/nfse/{id}/download', [NfseController::class, 'download'])->name('nfse.download');
    Route::post('/nfse/importar-pendentes', [NfseController::class, 'importarPendentes'])->name('nfse.importarPendentes');

    Route::prefix('cupom_desconto')->name('cupom_desconto.')->group(function () {
        Route::post('setperpage', [CupomDescontoController::class, 'setPerPage'])->name('setPerPage');
        Route::get('removerfiltro', [CupomDescontoController::class, 'removerFiltro'])->name('removerFiltro');
        Route::get('filtro', [CupomDescontoController::class, 'filtro'])->name('filtro');
    });
    Route::resource('/cupom_desconto', CupomDescontoController::class)->except(['show'])->names([
        'index' => 'cupom_desconto.index',
        'create' => 'cupom_desconto.create',
        'store' => 'cupom_desconto.store',
        'edit' => 'cupom_desconto.edit',
        'update' => 'cupom_desconto.update',
        'destroy' => 'cupom_desconto.destroy',
    ]);

    // Fluxo de Caixa
    Route::get('/fluxo-caixa', [FluxoCaixaController::class, 'dashboard'])->name('fluxo-caixa.dashboard');
    Route::get('/analise-veiculos', [FluxoCaixaController::class, 'analiseVeiculos'])->name('analise-veiculos');
    Route::get('/fluxo-caixa/categorias', [FluxoCaixaController::class, 'categoriasIndex'])->name('fluxo-caixa.categorias.index');
    Route::get('/fluxo-caixa/categorias/sugerir-codigo', [FluxoCaixaController::class, 'sugerirCodigo'])->name('fluxo-caixa.categorias.sugerir-codigo');
    Route::post('/fluxo-caixa/categorias', [FluxoCaixaController::class, 'categoriasStore'])->name('fluxo-caixa.categorias.store');
    Route::put('/fluxo-caixa/categorias/{categoria}', [FluxoCaixaController::class, 'categoriasUpdate'])->name('fluxo-caixa.categorias.update');
    Route::delete('/fluxo-caixa/categorias/{categoria}', [FluxoCaixaController::class, 'categoriasDestroy'])->name('fluxo-caixa.categorias.destroy');

    Route::resource('/fluxo-caixa/lancamentos', FluxoCaixaController::class)->names([
        'index' => 'fluxo-caixa.index',
        'create' => 'fluxo-caixa.create',
        'store' => 'fluxo-caixa.store',
        'edit' => 'fluxo-caixa.edit',
        'update' => 'fluxo-caixa.update',
        'destroy' => 'fluxo-caixa.destroy',
    ]);
});
