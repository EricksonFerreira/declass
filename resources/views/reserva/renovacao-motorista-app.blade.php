@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Renovação de Locação</h1>
        <a href="{{ route('reserva.show', $codigo) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar para a reserva
        </a>
    </div>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card card-outline card-success shadow-lg" style="border-radius: 16px; overflow: hidden; border-top-width: 4px;">
            <div class="card-header bg-dark text-white py-3">
                <h3 class="card-title font-weight-bold mb-0" style="font-size: 1.15rem;">
                    <i class="fas fa-arrows-rotate mr-2 text-success"></i>
                    @if($tipo === 'MAP')
                        Renovar Ciclo do Plano de Motorista
                    @else
                        Aditivo de Renovação de Reserva Comum
                    @endif
                </h3>
            </div>

            @if($tipo === 'MAP')
                {{-- Form para Motorista de App --}}
                <form action="{{ route('reserva.minhas.motorista-app.renovar', ['id' => $reserva->id]) }}" method="POST">
                    @csrf
                    <div class="card-body p-4">
                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                            </div>
                        @endif

                        <div class="alert alert-info mb-4" style="background-color: #f0fdf4; border-color: #bbf7d0; color: #166534;">
                            <i class="fas fa-info-circle mr-2"></i>
                            A renovação do motorista de app respeita a periodicidade fechada do plano. Defina a quantidade de ciclos adicionais.
                        </div>

                        <div class="row bg-light p-3 rounded mb-4 border" style="margin: 0;">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <span class="d-block text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Plano Contratado</span>
                                <strong style="font-size: 1.05rem; color: #0f172a;">{{ $plano?->nome ?? 'Plano Motorista App' }}</strong>
                            </div>
                            <div class="col-md-6">
                                <span class="d-block text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Vigência Atual</span>
                                <strong style="font-size: 1.05rem; color: #0f172a;">{{ $inicioVigenciaFormatado }} até {{ $fimVigenciaFormatado }}</strong>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-dark">Valor por Ciclo</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text bg-white">R$</span></div>
                                    <input type="text" class="form-control bg-white font-weight-bold" id="valorCiclo" value="{{ number_format($valorPeriodoContratado, 2, ',', '.') }}" readonly style="background-color: #f8fafc !important;">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-dark">Quantidade de Ciclos</label>
                                <input type="number" name="periodos" id="quantidadeCiclos" class="form-control font-weight-bold" min="1" step="1" value="1" required>
                                <small class="text-muted">Informe quantos novos ciclos deseja adicionar.</small>
                            </div>
                        </div>

                        {{-- Card de Resumo do Custo --}}
                        <div class="card border shadow-sm my-4 bg-light" style="border-radius: 12px;">
                            <div class="card-body p-3">
                                <h5 class="font-weight-bold text-dark mb-2" style="font-size: 0.95rem;">Resumo da Renovação</h5>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Valor por ciclo</span>
                                    <span class="font-weight-bold">R$ {{ number_format($valorPeriodoContratado, 2, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Ciclos adicionais</span>
                                    <span class="font-weight-bold" id="resumoCiclos">1x</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold text-dark">Custo Total Adicional</span>
                                    <strong class="text-success" style="font-size: 1.25rem;" id="custoTotalMap">R$ {{ number_format($valorPeriodoContratado, 2, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark">Observações</label>
                            <textarea name="observacoes" class="form-control" rows="3" placeholder="Opcional - insira notas sobre a renovação se necessário"></textarea>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top d-flex justify-content-end gap-2 p-3">
                        <a href="{{ route('reserva.show', $codigo) }}" class="btn btn-light mr-2">Cancelar</a>
                        <button type="submit" class="btn btn-success font-weight-bold px-4">
                            <i class="fas fa-check mr-1"></i> Confirmar Renovação
                        </button>
                    </div>
                </form>
            @else
                {{-- Form para Reserva Normal --}}
                <form action="{{ route('reserva.renovarComum', ['id' => $reserva->id]) }}" method="POST">
                    @csrf
                    <div class="card-body p-4">
                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                            </div>
                        @endif

                        <div class="alert alert-info mb-4" style="background-color: #f0fdf4; border-color: #bbf7d0; color: #166534;">
                            <i class="fas fa-info-circle mr-2"></i>
                            Defina a nova data e horário de devolução e a estação onde o veículo será retornado. O custo extra será calculado por diária.
                        </div>

                        <div class="row bg-light p-3 rounded mb-4 border" style="margin: 0;">
                            <div class="col-md-6 mb-2 mb-md-0 border-right">
                                <span class="d-block text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Devolução Atual</span>
                                <strong style="font-size: 1.05rem; color: #0f172a;" id="dataFimAtual" data-raw="{{ $reserva->data_fim }}">
                                    {{ \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}
                                </strong>
                            </div>
                            <div class="col-md-6">
                                <span class="d-block text-muted text-uppercase font-weight-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Veículo da Locação</span>
                                <strong style="font-size: 1.05rem; color: #0f172a;">
                                    {{ $reserva->veiculo->marca->nome ?? '-' }} {{ $reserva->veiculo->modelo->nome ?? '-' }} 
                                    <span class="badge badge-secondary ml-1">{{ $reserva->veiculo->placa_licenca ?? '' }}</span>
                                </strong>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-dark">Nova Data de Devolução</label>
                                <input type="date" name="data_fim_nova" id="dataFimNova" class="form-control" required min="{{ \Carbon\Carbon::parse($reserva->data_fim)->format('Y-m-d') }}" value="{{ \Carbon\Carbon::parse($reserva->data_fim)->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-dark">Novo Horário de Devolução</label>
                                <input type="time" name="hora_fim_nova" id="horaFimNova" class="form-control" required value="{{ \Carbon\Carbon::parse($reserva->data_fim)->format('H:i') }}">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark">Local de Devolução</label>
                            <select name="estacao_devolucao_id" class="form-control" required>
                                @foreach($estacoes as $estacao)
                                    <option value="{{ $estacao->id }}" {{ (int) $estacao->id === (int) $reserva->estacao_devolucao_id ? 'selected' : '' }}>
                                        {{ $estacao->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark">Observações</label>
                            <textarea name="observacoes" class="form-control" rows="2" placeholder="Notas sobre a prorrogação do contrato..."></textarea>
                        </div>

                        {{-- Card de Resumo Financeiro da Reserva Comum --}}
                        <div class="card border shadow-sm bg-light" style="border-radius: 12px; display: none;" id="cardResumoComum">
                            <div class="card-body p-3">
                                <h5 class="font-weight-bold text-dark mb-2" style="font-size: 0.95rem;">Simulação do Aditivo</h5>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Valor da diária</span>
                                    <span class="font-weight-bold" id="diariaValor" data-raw="{{ $reserva->tarifa_base }}">R$ {{ number_format($reserva->tarifa_base, 2, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Diárias adicionais</span>
                                    <span class="font-weight-bold text-info" id="diariasAdicionais">0 dia(s)</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold text-dark">Custo Total Adicional</span>
                                    <strong class="text-success" style="font-size: 1.25rem;" id="custoTotalComum">R$ 0,00</strong>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mb-0 d-none" id="alertaDataInvalida">
                            <i class="fas fa-exclamation-circle mr-2"></i> A nova data e hora deve ser superior à devolução atual.
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top d-flex justify-content-end p-3">
                        <a href="{{ route('reserva.show', $codigo) }}" class="btn btn-light mr-2">Cancelar</a>
                        <button type="submit" class="btn btn-success font-weight-bold px-4" id="btnSubmitComum">
                            <i class="fas fa-check mr-1"></i> Confirmar Aditivo
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    const tipo = @json($tipo);

    if (tipo === 'MAP') {
        const valorCiclo = parseFloat(@json($valorPeriodoContratado));
        const $quantidade = $('#quantidadeCiclos');
        const $resumoCiclos = $('#resumoCiclos');
        const $total = $('#custoTotalMap');

        function calcularMap() {
            let qtd = parseInt($quantidade.val()) || 1;
            if (qtd < 1) {
                qtd = 1;
                $quantidade.val(1);
            }
            $resumoCiclos.text(qtd + 'x');
            const total = valorCiclo * qtd;
            $total.text('R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }

        $quantidade.on('input change', calcularMap);
    } else {
        const diaria = parseFloat($('#diariaValor').data('raw')) || 0;
        const dataFimAtualStr = $('#dataFimAtual').data('raw');
        const dataFimAtual = new Date(dataFimAtualStr);

        const $dataNova = $('#dataFimNova');
        const $horaNova = $('#horaFimNova');
        const $diariasAdicionais = $('#diariasAdicionais');
        const $custoTotalComum = $('#custoTotalComum');
        const $cardResumo = $('#cardResumoComum');
        const $alerta = $('#alertaDataInvalida');
        const $btnSubmit = $('#btnSubmitComum');

        function calcularComum() {
            const dataStr = $dataNova.val();
            const horaStr = $horaNova.val();

            if (!dataStr || !horaStr) {
                $cardResumo.hide();
                return;
            }

            const dataNovaObj = new Date(dataStr + 'T' + horaStr + ':00');

            if (isNaN(dataNovaObj.getTime()) || dataNovaObj <= dataFimAtual) {
                $cardResumo.hide();
                $alerta.removeClass('d-none');
                $btnSubmit.prop('disabled', true);
                return;
            }

            $alerta.addClass('d-none');
            $btnSubmit.prop('disabled', false);

            // Calcular diferença em dias
            const diffMs = dataNovaObj - dataFimAtual;
            const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));

            $diariasAdicionais.text('+' + diffDays + ' diária(s)');
            const total = diaria * diffDays;
            $custoTotalComum.text('R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $cardResumo.show();
        }

        $dataNova.on('input change', calcularComum);
        $horaNova.on('input change', calcularComum);
        
        // Disparar inicialmente
        calcularComum();
    }
});
</script>
@stop
