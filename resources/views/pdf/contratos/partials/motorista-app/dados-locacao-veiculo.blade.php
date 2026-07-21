<div class="section-title">Dados da Locação - Motorista App</div>
<table class="info-table">
    <tr>
        <td style="width:50%">
            <div class="field-label">Estação de Retirada</div>
            <div class="field-value">{{ $get('reserva.estacao_retirada', $get('reserva.alugado_em.local', '-')) }}</div>
        </td>
        <td style="width:25%">
            <div class="field-label">Início da Locação</div>
            <div class="field-value">{{ $get('reserva.data_inicio', '-') }}</div>
        </td>
        <td style="width:25%">
            <div class="field-label">Fim do Ciclo Atual</div>
            <div class="field-value">{{ $get('reserva.data_fim', '-') }}</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="field-label">Plano Contratado</div>
            <div class="field-value">{{ $get('reserva.plano_nome', $get('grupo_label', 'Plano Motorista App')) }}</div>
        </td>
        <td>
            <div class="field-label">Periodicidade</div>
            <div class="field-value">{{ $get('contrato.periodicidade_label', $get('contrato.periodicidade', 'Semanal')) }}</div>
        </td>
        <td>
            <div class="field-label">Duração Prevista</div>
            <div class="field-value">{{ $get('reserva.quantidade_dias', '-') }} dias</div>
        </td>
    </tr>
</table>

<div class="section-title" style="margin-top:8px">Veículo</div>
<table class="info-table">
    <tr>
        <td style="width:40%">
            <div class="field-label">Marca / Modelo</div>
            <div class="field-value">
                {{ $get('veiculo.marca', '-') }} / {{ $get('veiculo.modelo', '-') }}
            </div>
        </td>
        <td style="width:30%">
            <div class="field-label">Placa / Identificador</div>
            <div class="field-value">{{ $get('veiculo.matricula', '-') }}</div>
        </td>
        <td style="width:30%">
            <div class="field-label">Nome Veículo</div>
            <div class="field-value">{{ $get('veiculo.nome', '-') }}</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="field-label">Quilometragem Inicial</div>
            <div class="field-value">{{ $get('veiculo.quilometros', '0') }} km</div>
        </td>
        <td>
            <div class="field-label">Nível Combustível</div>
            <div class="field-value">{{ $get('veiculo.combustivel', '-') }}</div>
        </td>
        <td>
            <div class="field-label">Franquia de Kms</div>
            <div class="field-value">{{ $get('veiculo.kms_permitidos', 'Livre / Conforme Plano') }}</div>
        </td>
    </tr>
</table>
