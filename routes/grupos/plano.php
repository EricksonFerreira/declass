<?php

use App\Http\Controllers\AssinaturaController;
use App\Http\Controllers\PlanoController;
use App\Http\Controllers\PlanoMotoristaAppController;
use Illuminate\Support\Facades\Route;

Route::prefix('plano_motorista_app')->name('plano_motorista_app.')->group(function () {
    Route::post('setperpage', [PlanoMotoristaAppController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [PlanoMotoristaAppController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [PlanoMotoristaAppController::class, 'filtro'])->name('filtro');
});
Route::resource('plano_motorista_app', PlanoMotoristaAppController::class);

// ── Gestão de Planos (catálogo) — superadmin / gerenciar-planos ─────────────
Route::middleware('can:acessar-superadmin')->group(function () {
    Route::get('planos', [PlanoController::class, 'hub'])->name('planos.hub');
    Route::get('plano/{plano}/acessos', [PlanoController::class, 'acessos'])->name('plano.acessos');
    Route::put('plano/{plano}/acessos', [PlanoController::class, 'salvarAcessos'])->name('plano.acessos.update');
    Route::resource('plano', PlanoController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});

// ── Gestão de Assinaturas (visão global) — superadmin / gerenciar-planos ────
Route::middleware('can:acessar-superadmin')->prefix('assinatura')->name('assinatura.')->group(function () {
    Route::get('/', [AssinaturaController::class, 'index'])->name('index');
    Route::get('/financas', [AssinaturaController::class, 'financas'])->name('financas');
    Route::get('/monitoramento-pagamentos', [AssinaturaController::class, 'monitoramentoPagamentos'])->name('monitoramento-pagamentos');
    Route::get('/ajuda', [AssinaturaController::class, 'ajuda'])->name('ajuda');
    Route::get('/regras', [AssinaturaController::class, 'regras'])->name('regras');
    Route::get('/atribuir', [AssinaturaController::class, 'criarAtribuir'])->name('atribuir');
    Route::post('/atribuir', [AssinaturaController::class, 'atribuir'])->name('atribuir.processar');
    Route::get('/{assinatura}', [AssinaturaController::class, 'show'])->name('show');
    Route::patch('/{assinatura}', [AssinaturaController::class, 'update'])->name('update');
    Route::delete('/{assinatura}', [AssinaturaController::class, 'destroy'])->name('destroy');
    Route::post('/{assinatura}/pagamento', [AssinaturaController::class, 'registrarPagamento'])->name('pagamento.registrar');
    Route::patch('/pagamento/{pagamento}', [AssinaturaController::class, 'atualizarPagamento'])->name('pagamento.atualizar');
    Route::delete('/pagamento/{pagamento}', [AssinaturaController::class, 'excluirPagamento'])->name('pagamento.excluir');
    Route::patch('/pagamento/{pagamento}/confirmar', [AssinaturaController::class, 'confirmarPagamento'])->name('pagamento.confirmar');
    Route::patch('/{assinatura}/status', [AssinaturaController::class, 'alterarStatus'])->name('status');
});

// ── Área da própria empresa — qualquer usuário autenticado ──────────────────
Route::middleware(['auth', 'can:gerenciar-empresa'])->prefix('meu-plano')->name('assinatura.')->group(function () {
    Route::get('/', [AssinaturaController::class, 'meuPlano'])->name('meu-plano');
    Route::get('/onboarding-pagamento', [AssinaturaController::class, 'onboardingPagamento'])->name('onboarding-pagamento');
    Route::get('/contratar', [AssinaturaController::class, 'prepararContratacao'])->name('contratar');
    Route::post('/contratar', [AssinaturaController::class, 'contratar'])->name('contratar.confirmar');
    Route::get('/downgrade', [AssinaturaController::class, 'downgrade'])->name('downgrade');
    Route::post('/downgrade', [AssinaturaController::class, 'confirmarDowngrade'])->name('downgrade.confirmar');
    Route::post('/renovar', [AssinaturaController::class, 'renovar'])->name('renovar');
    Route::post('/cancelar', [AssinaturaController::class, 'cancelar'])->name('cancelar');
    Route::post('/mudanca/remover', [AssinaturaController::class, 'removerMudancaAgendada'])->name('mudanca.remover');
    Route::get('/concluido', [AssinaturaController::class, 'concluido'])->name('concluido');
    Route::patch('/pagamento/{pagamento}/metodo', [AssinaturaController::class, 'alterarMetodoPagamento'])->name('pagamento.alterar-metodo');
    Route::post('/pagamento/{pagamento}/pix', [AssinaturaController::class, 'gerarPixStripe'])->name('pagamento.pix');
    Route::get('/pagamento/{pagamento}/status', [AssinaturaController::class, 'statusPagamento'])->name('pagamento.status');
    Route::post('/pagamento/{pagamento}/cartao-intent', [AssinaturaController::class, 'criarIntentCartao'])->name('pagamento.cartao-intent');
    Route::post('/pagamento/{pagamento}/cartao-confirmar', [AssinaturaController::class, 'confirmarCartao'])->name('pagamento.cartao-confirmar');
    Route::post('/pagamento/{pagamento}/cancelar', [AssinaturaController::class, 'cancelarPagamento'])->name('pagamento.cancelar');
});
