<?php

use App\Http\Controllers\AssinaturaController;
use Illuminate\Support\Facades\Route;

Route::prefix('assinatura')->name('assinatura.')->group(function () {
    Route::get('/', [AssinaturaController::class, 'index'])->name('index');
    Route::get('/ajuda', [AssinaturaController::class, 'ajuda'])->name('ajuda');
    Route::get('/meu-plano', [AssinaturaController::class, 'meuPlano'])->name('meu-plano');
    Route::get('/financas', [AssinaturaController::class, 'financas'])->name('financas');
    Route::get('/contratar', [AssinaturaController::class, 'prepararContratacao'])->name('contratar');
    Route::post('/contratar/confirmar', [AssinaturaController::class, 'contratar'])->name('contratar.confirmar');
    Route::get('/concluido', [AssinaturaController::class, 'concluido'])->name('concluido');
    Route::get('/downgrade', [AssinaturaController::class, 'downgrade'])->name('downgrade');
    Route::post('/downgrade/confirmar', [AssinaturaController::class, 'confirmarDowngrade'])->name('downgrade.confirmar');
    Route::post('/cancelar', [AssinaturaController::class, 'cancelar'])->name('cancelar');
    Route::post('/mudanca/remover', [AssinaturaController::class, 'removerMudancaAgendada'])->name('mudanca.remover');
    Route::get('/pagamento/{pagamento}/status', [AssinaturaController::class, 'statusPagamento'])->name('pagamento.status');
    Route::post('/pagamento/{pagamento}/pix', [AssinaturaController::class, 'gerarPixStripe'])->name('pagamento.pix');
    Route::get('/atribuir', [AssinaturaController::class, 'criarAtribuir'])->name('atribuir');
    Route::post('/atribuir', [AssinaturaController::class, 'atribuir'])->name('atribuir.processar');
    Route::delete('/{assinatura}', [AssinaturaController::class, 'destroy'])->name('destroy');
    Route::get('/{assinatura}', [AssinaturaController::class, 'show'])->name('show');
});
