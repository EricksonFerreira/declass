<div class="section-title">Dados da Locação</div>
<table class="info-table">
    <tr>
        <td style="width:50%">
            <div class="field-label">Local Retirada</div>
            <div class="field-value">{{ $get('reserva.alugado_em.local', '-') }}</div>
        </td>
        <td style="width:25%">
            <div class="field-label">Data</div>
            <div class="field-value">{{ $get('reserva.alugado_em.data', '-') }}</div>
        </td>
        <td style="width:25%">
            <div class="field-label">Hora</div>
            <div class="field-value">{{ $get('reserva.alugado_em.hora', '-') }}</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="field-label">Local Devolução</div>
            <div class="field-value">{{ $get('reserva.devolver_em.local', '-') }}</div>
        </td>
        <td>
            <div class="field-label">Data</div>
            <div class="field-value">{{ $get('reserva.devolver_em.data', '-') }}</div>
        </td>
        <td>
            <div class="field-label">Hora</div>
            <div class="field-value">{{ $get('reserva.devolver_em.hora', '-') }}</div>
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
            <div class="field-label">Placa / ID</div>
            <div class="field-value">{{ $get('veiculo.matricula', '-') }}</div>
        </td>
        <td style="width:30%">
            <div class="field-label">Grupo</div>
            <div class="field-value">{{ $get('veiculo.grupo', '-') }}</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="field-label">Quilometragem</div>
            <div class="field-value">{{ $get('veiculo.quilometros', '0') }} km</div>
        </td>
        <td>
            <div class="field-label">Combustível</div>
            <div class="field-value">{{ $get('veiculo.combustivel', '-') }}</div>
        </td>
        <td>
            <div class="field-label">Km Permitidos</div>
            <div class="field-value">{{ $get('veiculo.kms_permitidos', '-') }}</div>
        </td>
    </tr>
</table>
