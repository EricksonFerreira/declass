<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\AvariaCobrancaController;

Route::get('reserva/minhas', [ReservaController::class, 'minhas'])->name('reserva.minhas');

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
    Route::get('{id}/efetuar-pagamento', [ReservaController::class, 'efetuarPagamento'])->name('efetuarPagamento');
    Route::post('{id}/processar-pagamento', [ReservaController::class, 'processarPagamento'])->name('processarPagamento');
    Route::post('{id}/emitir-nfse', [ReservaController::class, 'emitirNfse'])->name('emitirNfse');
    Route::post('{id}/cancelar', [ReservaController::class, 'cancelar'])->name('cancelar');
    Route::get('{id}/checkout', [ReservaController::class, 'checkoutVistoria'])->name('checkout.vistoria');
    Route::post('{id}/checkout/finalizar', [ReservaController::class, 'finalizarCheckoutVistoria'])->name('checkout.finalizar');
    Route::get('{id}/checkin/veiculos-disponiveis', [ReservaController::class, 'veiculosDisponiveisCheckin'])->name('checkin.veiculosDisponiveis');
    Route::get('{id}/checkin/veiculos-modal', [ReservaController::class, 'veiculosModalCheckin'])->name('checkin.veiculosModal');
    Route::get('{id}/checkin/status-upgrade', [ReservaController::class, 'checkinStatusUpgrade'])->name('checkin.statusUpgrade');
    Route::delete('{id}/checkin/contrato', [ReservaController::class, 'excluirContratoCheckin'])->name('checkin.excluirContrato');
    Route::get('{id}/historico', [ReservaController::class, 'historicoReserva'])->name('historico');
    Route::get('{id}/checkin', [ReservaController::class, 'checkin'])->name('checkin');
    // Feature 2: Editar no show
    Route::get('{id}/dados-edicao', [ReservaController::class, 'dadosParaEdicaoNoShow'])->name('dadosEdicaoNoShow');
    Route::post('{id}/atualizar-no-show', [ReservaController::class, 'atualizarNoShow'])->name('atualizarNoShow');
    // Feature 4: Calção
    Route::get('{id}/calcao', [ReservaController::class, 'calcaoCheckin'])->name('calcao');
    Route::post('{id}/calcao/autorizar', [ReservaController::class, 'autorizarCalcao'])->name('calcao.autorizar');
    Route::post('{id}/calcao/capturar', [ReservaController::class, 'capturarCalcao'])->name('calcao.capturar');
    Route::post('{id}/calcao/liberar', [ReservaController::class, 'liberarCalcao'])->name('calcao.liberar');
    Route::post('{id}/calcao/presencial', [ReservaController::class, 'registrarCalcaoPresencial'])->name('calcao.presencial');
    Route::post('{id}/calcao/presencial/devolver', [ReservaController::class, 'devolverCalcaoPresencial'])->name('calcao.presencial.devolver');
    // Feature 5: CobranÃ§a por avarias (pÃ³s-checkout)
    Route::get('{id}/avarias/cobranca', [AvariaCobrancaController::class, 'create'])->name('avarias.cobranca');
    Route::post('{id}/avarias/cobranca', [AvariaCobrancaController::class, 'store'])->name('avarias.cobrar');
    Route::post('{id}/confirmar-pagamento-dinheiro', [ReservaController::class, 'confirmarPagamentoDinheiro'])->name('confirmarPagamentoDinheiro');
    Route::post('{id}/criarContratoCheckin', [ReservaController::class, 'criarContratoCheckin'])->name('criarContratoCheckin');
    Route::post('{id}/adicionarDanoVeiculo', [ReservaController::class, 'adicionarDanoVeiculo'])->name('adicionarDanoVeiculo');
    Route::get('{id}/assinarContratoCheckin', [ReservaController::class, 'assinarContratoCheckin'])->name('assinarContratoCheckin');
    // Route::post('{id}/assinarContratoCheckin', [ReservaController::class, 'salvarAssinaturaContratoCheckin'])->name('salvarAssinaturaContratoCheckin');
    Route::post('{id}/salvar-assinatura-checkin', [ReservaController::class, 'salvarAssinarContratoCheckIn'])->name('salvarAssinarContratoCheckIn');
    Route::get('{id}/verContratoCheckin', [ReservaController::class, 'verContratoCheckin'])->name('verContratoCheckin');
});
Route::resource('reserva', ReservaController::class);
