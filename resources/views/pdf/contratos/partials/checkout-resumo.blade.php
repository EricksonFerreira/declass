<div class="section-title">Resumo da Devolução</div>
<table class="info-table">
    <tr>
        <td>
            <div class="field-label">Veículo devolvido</div>
            <div class="field-value">{{ $get('veiculo.nome', '-') }} - {{ $get('veiculo.matricula', '-') }}</div>
        </td>
        <td>
            <div class="field-label">Quilometragem na devolução</div>
            <div class="field-value">{{ $get('veiculo.quilometros', '-') }}</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="field-label">Combustível na devolução</div>
            <div class="field-value">{{ $get('veiculo.combustivel', '-') }}</div>
        </td>
        <td>
            <div class="field-label">Funcionário responsável</div>
            <div class="field-value">{{ $get('funcionario.nome', '-') }}</div>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <div class="field-label">Observações do check-out</div>
            <div class="field-value">{{ $get('reserva.observacoes', 'Sem observações registradas.') }}</div>
        </td>
    </tr>
</table>
