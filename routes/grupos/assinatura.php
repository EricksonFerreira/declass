<?php

use App\Http\Controllers\AssinaturaController;
use Illuminate\Support\Facades\Route;

Route::prefix('assinatura')->name('assinatura.')->group(function () {
    // Self-service da empresa — precisa ficar acessivel para o usuario comum
    // (o middleware de plano redireciona ca quando o trial expira).
    Route::get('/ajuda', [AssinaturaController::class, 'ajuda'])->name('ajuda');
    Route::get('/meu-plano', [AssinaturaController::class, 'meuPlano'])->name('meu-plano');
    Route::get('/contratar', [AssinaturaController::class, 'prepararContratacao'])->name('contratar');
    Route::post('/contratar/confirmar', [AssinaturaController::class, 'contratar'])->name('contratar.confirmar');
    Route::get('/concluido', [AssinaturaController::class, 'concluido'])->name('concluido');
    Route::get('/downgrade', [AssinaturaController::class, 'downgrade'])->name('downgrade');
    Route::post('/downgrade/confirmar', [AssinaturaController::class, 'confirmarDowngrade'])->name('downgrade.confirmar');
    Route::post('/cancelar', [AssinaturaController::class, 'cancelar'])->name('cancelar');
    Route::post('/mudanca/remover', [AssinaturaController::class, 'removerMudancaAgendada'])->name('mudanca.remover');
    Route::post('/renovar', [AssinaturaController::class, 'renovar'])->name('renovar');
    Route::get('/pagamento/{pagamento}/status', [AssinaturaController::class, 'statusPagamento'])->name('pagamento.status');
    Route::post('/pagamento/{pagamento}/pix', [AssinaturaController::class, 'gerarPixStripe'])->name('pagamento.pix');
    Route::post('/pagamento/{pagamento}/cartao-intent', [AssinaturaController::class, 'criarIntentCartao'])->name('pagamento.cartao-intent');
    Route::post('/pagamento/{pagamento}/cartao-confirmar', [AssinaturaController::class, 'confirmarCartao'])->name('pagamento.cartao-confirmar');
    Route::post('/pagamento/{pagamento}/cancelar', [AssinaturaController::class, 'cancelarPagamento'])->name('pagamento.cancelar');

    // Gestao de assinaturas/planos — somente superadmin
    Route::middleware('can:acessar-superadmin')->group(function (): void {
        Route::get('/', [AssinaturaController::class, 'index'])->name('index');
        Route::get('/financas', [AssinaturaController::class, 'financas'])->name('financas');
        Route::get('/monitoramento-pagamentos', [AssinaturaController::class, 'monitoramentoPagamentos'])->name('monitoramento-pagamentos');
        Route::get('/atribuir', [AssinaturaController::class, 'criarAtribuir'])->name('atribuir');
        Route::post('/atribuir', [AssinaturaController::class, 'atribuir'])->name('atribuir.processar');
        Route::match(['post', 'patch'], '/pagamento/{pagamento}/metodo', [AssinaturaController::class, 'alterarMetodoPagamento'])->name('pagamento.alterar-metodo');
        Route::post('/pagamento/registrar/{assinatura}', [AssinaturaController::class, 'registrarPagamento'])->name('pagamento.registrar');
        Route::patch('/pagamento/{pagamento}/confirmar', [AssinaturaController::class, 'confirmarPagamento'])->name('pagamento.confirmar');
        Route::patch('/pagamento/{pagamento}', [AssinaturaController::class, 'atualizarPagamento'])->name('pagamento.atualizar');
        Route::delete('/pagamento/{pagamento}', [AssinaturaController::class, 'excluirPagamento'])->name('pagamento.excluir');
        Route::patch('/{assinatura}/status', [AssinaturaController::class, 'alterarStatus'])->name('status');
        Route::patch('/{assinatura}', [AssinaturaController::class, 'update'])->name('update');
        Route::delete('/{assinatura}', [AssinaturaController::class, 'destroy'])->name('destroy');
        Route::get('/{assinatura}', [AssinaturaController::class, 'show'])->name('show');
    });
});

Route::get('planos', [AssinaturaController::class, 'hub'])
    ->middleware('can:acessar-superadmin')
    ->name('planos.hub');
