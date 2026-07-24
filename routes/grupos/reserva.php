<?php

use App\Http\Controllers\AvariaCobrancaController;
use App\Http\Controllers\Reserva\ReservaCheckinController;
use App\Http\Controllers\Reserva\ReservaCheckoutController;
use App\Http\Controllers\Reserva\ReservaController;
use App\Http\Controllers\Reserva\ReservaMotoristaAppController;
use App\Http\Controllers\Reserva\ReservaPagamentoController;
use Illuminate\Support\Facades\Route;

Route::get('minhas-reservas', [ReservaController::class, 'minhas'])->name('reserva.minhas');

// ── Motorista App (controller separado) ──
Route::get('minhas-reservas/motorista-app/{id}', [ReservaController::class, 'showMinhasMotoristaApp'])->name('reserva.minhas.motorista-app.show');
Route::get('reserva-motorista-app/create', [ReservaMotoristaAppController::class, 'create'])->name('reserva-motorista-app.create');
Route::post('reserva-motorista-app', [ReservaMotoristaAppController::class, 'store'])->name('reserva-motorista-app.store');
Route::get('reserva-motorista-app/{id}/edit', [ReservaMotoristaAppController::class, 'edit'])->name('reserva-motorista-app.edit');
Route::put('reserva-motorista-app/{id}', [ReservaMotoristaAppController::class, 'update'])->name('reserva-motorista-app.update');
Route::post('reserva-motorista-app/criar-motorista', [ReservaMotoristaAppController::class, 'criarMotoristaAjax'])->name('reserva-motorista-app.criar-motorista');
Route::get('reserva-motorista-app/solicitacao-status/{userId}', [ReservaMotoristaAppController::class, 'solicitacaoStatusAjax'])->name('reserva-motorista-app.solicitacao-status');
Route::post('reserva-motorista-app/salvar-documentos', [ReservaMotoristaAppController::class, 'salvarDocumentosAjax'])->name('reserva-motorista-app.salvar-documentos');
Route::get('reserva-motorista-app/veiculos-disponiveis', [ReservaMotoristaAppController::class, 'veiculosDisponiveisAjax'])->name('reserva-motorista-app.veiculos-disponiveis');
Route::get('minhas-reservas/{id}/efetuar-pagamento', [ReservaPagamentoController::class, 'efetuarPagamentoMinhas'])->name('reserva.minhas.pagamento.efetuar');
Route::post('minhas-reservas/{codigo}/confirmar-pagamento', [ReservaPagamentoController::class, 'confirmarPagamentoMinhas'])->name('reserva.minhas.pagamento.confirmar');
Route::get('minhas-reservas/{id}', [ReservaController::class, 'showMinhas'])->name('reserva.minhas.show');
Route::post('reserva/minhas/motorista-app/{id}/efetivar', [ReservaMotoristaAppController::class, 'efetivarMotoristaApp'])->name('reserva.minhas.motorista-app.efetivar');
Route::post('reserva/minhas/motorista-app/{id}/aceitar-termo', [ReservaMotoristaAppController::class, 'aceitarTermoConfirmacaoMotoristaApp'])->name('reserva.minhas.motorista-app.aceitar-termo');
Route::post('reserva/minhas/motorista-app/{id}/assinar-contrato', [ReservaMotoristaAppController::class, 'assinarContratoMotoristaApp'])->name('reserva.minhas.motorista-app.assinar-contrato');
Route::post('reserva/minhas/motorista-app/{id}/assinar-devolucao', [ReservaMotoristaAppController::class, 'assinarDevolucaoMotoristaApp'])->name('reserva.minhas.motorista-app.assinar-devolucao');
Route::get('reserva/minhas/motorista-app/{id}/contrato', [ReservaMotoristaAppController::class, 'verContratoMotoristaApp'])->name('reserva.minhas.motorista-app.ver-contrato');
// Route::get('reserva/minhas/motorista-app/{id}/checkout', [ReservaMotoristaAppController::class, 'checkoutMotoristaApp'])->name('reserva.minhas.motorista-app.checkout.form');
// Route::post('reserva/minhas/motorista-app/{id}/checkout', [ReservaMotoristaAppController::class, 'registrarCheckoutMotoristaApp'])->name('reserva.minhas.motorista-app.checkout');
Route::post('reserva/minhas/{id}/motorista-app/renovar', [ReservaMotoristaAppController::class, 'renovarMotoristaApp'])->name('reserva.minhas.motorista-app.renovar');
Route::post('reserva/minhas/{id}/motorista-app/cancelar', [ReservaMotoristaAppController::class, 'cancelarMotoristaApp'])->name('reserva.minhas.motorista-app.cancelar');
// Route::get('reserva/minhas/pagamentos/{pagamento}/efetuar', [ReservaMotoristaAppController::class, 'efetuarPagamentoMotoristaApp'])->name('reserva.minhas.motorista-app.efetuar-pagamento.legado');
Route::get('reserva/minhas/multa/{id}', [ReservaController::class, 'showMinhaMulta'])->name('reserva.minhas.multa.show');
Route::get('minha-reserva', fn () => redirect()->route('reserva.minhas'));
Route::get('minha-reserva/motorista-app/{id}', fn ($id) => redirect()->route('reserva.minhas.motorista-app.show', ['id' => $id]));
Route::get('minha-reserva/{id}', fn ($id) => redirect()->route('reserva.minhas.show', ['id' => $id]));
Route::get('reserva/minhas', fn () => redirect()->route('reserva.minhas'));
Route::get('reserva/minhas/motorista-app/{id}', fn ($id) => redirect()->route('reserva.minhas.motorista-app.show', ['id' => $id]));
Route::get('reserva/minhas/{id}', fn ($id) => redirect()->route('reserva.minhas.show', ['id' => $id]));

Route::prefix('reserva')->name('reserva.')->group(function () {
    Route::get('obterOpcionais', [ReservaController::class, 'obterOpcionais'])->name('obterOpcionais');
    Route::get('veiculosDisponiveis', [ReservaController::class, 'veiculosDisponiveis'])->name('veiculosDisponiveis');
    Route::get('estacoes-disponiveis', [ReservaController::class, 'estacoesDisponiveisNoPeriodo'])->name('estacoesDisponiveisNoPeriodo');
    Route::get('estacoes-disponiveis-dia', [ReservaController::class, 'estacoesDisponiveisNaDataHora'])->name('estacoesDisponiveisNaDataHora');
    Route::get('datas-retirada-disponiveis', [ReservaController::class, 'datasRetiradaDisponiveisPorEstacao'])->name('datasRetiradaDisponiveisPorEstacao');
    Route::get('dias-sem-disponibilidade', [ReservaController::class, 'diasSemDisponibilidade'])->name('diasSemDisponibilidade');
    Route::get('pesquisar-grupos', [ReservaController::class, 'pesquisarGruposVeiculos'])->name('pesquisarGruposVeiculos');
    Route::get('agenda-disponivel', [ReservaController::class, 'agendaDisponivelPorEstacao'])->name('agendaDisponivelPorEstacao');
    Route::get('validar-funcionamento', [ReservaController::class, 'validarFuncionamentoEstacao'])->name('validarFuncionamentoEstacao');
    Route::post('setperpage', [ReservaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [ReservaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [ReservaController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [ReservaController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [ReservaController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
    Route::get('{id}/efetuar-pagamento', [ReservaPagamentoController::class, 'efetuarPagamento'])->name('pagamento.efetuar');
    Route::post('{codigo}/processar-pagamento', [ReservaPagamentoController::class, 'processarPagamento'])->name('processarPagamento');
    Route::post('{codigo}/emitir-nfse', [ReservaController::class, 'emitirNfse'])->name('emitirNfse');
    Route::post('{codigo}/cancelar', [ReservaController::class, 'cancelar'])->name('cancelar');

    // ── Checkout ──
    Route::get('{codigo}/checkout', [ReservaCheckoutController::class, 'checkoutVistoria'])->name('checkout.vistoria');
    Route::post('{codigo}/checkout/finalizar', [ReservaCheckoutController::class, 'finalizarCheckoutVistoria'])->name('checkout.finalizar');

    // ── Checkin ──
    Route::get('{codigo}/checkin/veiculos-disponiveis', [ReservaCheckinController::class, 'veiculosDisponiveisCheckin'])->name('checkin.veiculosDisponiveis');
    Route::get('{codigo}/checkin/veiculos-modal', [ReservaCheckinController::class, 'veiculosModalCheckin'])->name('checkin.veiculosModal');
    Route::get('{codigo}/checkin/diagnostico', [ReservaCheckinController::class, 'diagnosticoDisponibilidadeCheckin'])->name('checkin.diagnostico');
    Route::get('{codigo}/checkin/status-upgrade', [ReservaCheckinController::class, 'checkinStatusUpgrade'])->name('checkin.statusUpgrade');
    Route::delete('{codigo}/checkin/contrato', [ReservaCheckinController::class, 'excluirContratoCheckin'])->name('checkin.excluirContrato');

    Route::get('{id}/historico', [ReservaController::class, 'historicoReserva'])->name('historico');
    Route::get('{codigo}/checkin', [ReservaCheckinController::class, 'checkin'])->name('checkin');
    Route::get('{id}/dados-edicao', [ReservaController::class, 'dadosParaEdicaoNoShow'])->name('dadosEdicaoNoShow');
    Route::post('{id}/atualizar-no-show', [ReservaController::class, 'atualizarNoShow'])->name('atualizarNoShow');
    Route::post('{codigo}/criarContratoCheckin', [ReservaCheckinController::class, 'criarContratoCheckIn'])->name('criarContratoCheckin');
    Route::post('{id}/adicionarDanoVeiculo', [ReservaCheckoutController::class, 'adicionarDanoVeiculo'])->name('adicionarDanoVeiculo');
    Route::post('{codigo}/satisfacao', [ReservaController::class, 'salvarSatisfacao'])->name('satisfacao.store');
    // ── Calção e Pagamento presencial ──
    Route::get('{codigo}/calcao', [ReservaPagamentoController::class, 'calcaoCheckin'])->name('calcao');
    Route::post('{codigo}/calcao/autorizar', [ReservaPagamentoController::class, 'autorizarCalcao'])->name('calcao.autorizar');
    Route::post('{codigo}/calcao/salvar-cartao', [ReservaPagamentoController::class, 'salvarCartaoCalcao'])->name('calcao.salvar-cartao');
    Route::post('{codigo}/calcao/capturar', [ReservaPagamentoController::class, 'capturarCalcao'])->name('calcao.capturar');
    Route::post('{codigo}/calcao/liberar', [ReservaPagamentoController::class, 'liberarCalcao'])->name('calcao.liberar');
    Route::post('{codigo}/calcao/presencial', [ReservaPagamentoController::class, 'registrarCalcaoPresencial'])->name('calcao.presencial');
    Route::post('{codigo}/calcao/presencial/devolver', [ReservaPagamentoController::class, 'devolverCalcaoPresencial'])->name('calcao.presencial.devolver');
    Route::post('{codigo}/confirmar-pagamento', [ReservaPagamentoController::class, 'confirmarPagamento'])->name('pagamento.confirmar');
    Route::post('{codigo}/confirmar-pagamento-dinheiro', [ReservaPagamentoController::class, 'confirmarPagamentoDinheiro'])->name('confirmarPagamentoDinheiro');
    // ── Cobrança de Avarias ──
    Route::get('{id}/avarias/cobranca', [AvariaCobrancaController::class, 'create'])->name('avarias.cobranca');
    Route::post('{id}/avarias/cobrar', [AvariaCobrancaController::class, 'store'])->name('avarias.cobrar');
    // ── Contrato Checkin──
    Route::get('{codigo}/assinarContratoCheckin', [ReservaCheckinController::class, 'assinarContratoCheckin'])->name('assinarContratoCheckin');
    Route::post('{codigo}/salvar-assinatura-checkin', [ReservaCheckinController::class, 'salvarAssinarContratoCheckIn'])->name('salvarAssinarContratoCheckIn');
    Route::get('{codigo}/verContratoCheckin', [ReservaCheckinController::class, 'verContratoCheckin'])->name('verContratoCheckin');
    // ── Contrato Checkout──
    Route::get('{codigo}/assinarContratoCheckout', [ReservaCheckoutController::class, 'assinarContratoCheckout'])->name('assinarContratoCheckout');
    Route::post('{codigo}/salvar-assinatura-checkout', [ReservaCheckoutController::class, 'salvarAssinarContratoCheckout'])->name('salvarAssinaturaContratoCheckout');
    Route::get('{codigo}/verContratoCheckout', [ReservaCheckoutController::class, 'verContratoCheckout'])->name('verContratoCheckout');
    // ── Renovacao ──
    Route::get('planos/{id}/opcionais', [\App\Http\Controllers\Reserva\PlanoController::class, 'opcionais'])->name('planos.opcionais');
    Route::get('{codigo}/renovacao', [ReservaController::class, 'renovacao'])->name('renovacao');
    Route::post('{id}/renovar-comum', [ReservaController::class, 'renovarComum'])->name('renovarComum');
});
Route::resource('reserva', ReservaController::class);
