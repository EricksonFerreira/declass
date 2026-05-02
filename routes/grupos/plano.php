<?php

use App\Http\Controllers\AssinaturaController;
use App\Http\Controllers\PlanoController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/',                                     [AssinaturaController::class, 'index'])->name('index');
    Route::get('/ajuda',                                [AssinaturaController::class, 'ajuda'])->name('ajuda');
    // Backward-compatible alias (tela de regras virou modulo de ajuda).
    Route::get('/regras',                               [AssinaturaController::class, 'regras'])->name('regras');
    Route::post('/atribuir',                            [AssinaturaController::class, 'atribuir'])->name('atribuir');
    Route::get('/{assinatura}',                         [AssinaturaController::class, 'show'])->name('show');
    Route::post('/{assinatura}/pagamento',              [AssinaturaController::class, 'registrarPagamento'])->name('pagamento.registrar');
    Route::patch('/pagamento/{pagamento}/confirmar',    [AssinaturaController::class, 'confirmarPagamento'])->name('pagamento.confirmar');
    Route::patch('/{assinatura}/status',                [AssinaturaController::class, 'alterarStatus'])->name('status');
});

// ── Área da própria empresa — qualquer usuário autenticado ───────────────────
Route::prefix('meu-plano')->name('assinatura.')->group(function () {
    // Visualização e histórico
    Route::get('/',                 [AssinaturaController::class, 'meuPlano'])->name('meu-plano');
    // Tela de pagamento dedicada para o onboarding
    Route::get('/onboarding-pagamento', [AssinaturaController::class, 'onboardingPagamento'])->name('onboarding-pagamento');
    // Seleção de plano → tela de confirmação de pagamento
    Route::get('/contratar',        [AssinaturaController::class, 'prepararContratacao'])->name('contratar');
    // Processar contratação (pode disparar downgrade)
    Route::post('/contratar',       [AssinaturaController::class, 'contratar'])->name('contratar.confirmar');
    // Tela de seleção de veículos no downgrade
    Route::get('/downgrade',        [AssinaturaController::class, 'downgrade'])->name('downgrade');
    Route::post('/downgrade',       [AssinaturaController::class, 'confirmarDowngrade'])->name('downgrade.confirmar');
    // Renovação do plano atual
    Route::post('/renovar',         [AssinaturaController::class, 'renovar'])->name('renovar');
    Route::post('/cancelar',        [AssinaturaController::class, 'cancelar'])->name('cancelar');
    Route::post('/mudanca/remover', [AssinaturaController::class, 'removerMudancaAgendada'])->name('mudanca.remover');
    // Tela de conclusao apos pagamento confirmado
    Route::get('/concluido',        [AssinaturaController::class, 'concluido'])->name('concluido');
    // Alterar método de pagamento pendente
    Route::patch('/pagamento/{pagamento}/metodo', [AssinaturaController::class, 'alterarMetodoPagamento'])->name('pagamento.alterar-metodo');
    // Gerar QR Code PIX via Stripe
    Route::post('/pagamento/{pagamento}/pix', [AssinaturaController::class, 'gerarPixStripe'])->name('pagamento.pix');
    // Verificar status do pagamento (polling)
    Route::get('/pagamento/{pagamento}/status', [AssinaturaController::class, 'statusPagamento'])->name('pagamento.status');
    // Criar PaymentIntent para cartão de crédito
    Route::post('/pagamento/{pagamento}/cartao-intent', [AssinaturaController::class, 'criarIntentCartao'])->name('pagamento.cartao-intent');
    // Confirmar pagamento cartão após Stripe Elements
    Route::post('/pagamento/{pagamento}/cartao-confirmar', [AssinaturaController::class, 'confirmarCartao'])->name('pagamento.cartao-confirmar');
    // Cancelar pagamento pendente
    Route::post('/pagamento/{pagamento}/cancelar', [AssinaturaController::class, 'cancelarPagamento'])->name('pagamento.cancelar');
});
