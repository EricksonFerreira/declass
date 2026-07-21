<div class="section-title">Resumo do Plano Motorista App</div>
<table class="detail-table">
    <tbody>
        <tr>
            <td><strong>Status da Reserva</strong><br>{{ $get('reserva.status', 'Ativa') }}</td>
            <td><strong>Tipo de Origem</strong><br>Motorista de Aplicativo</td>
            <td><strong>Pagamento Confirmado</strong><br>{{ $get('reserva.pagamento_efetuado') ? 'Sim' : 'Pendente' }}</td>
            <td><strong>Data de Criação</strong><br>{{ $get('reserva.criada_em', '-') }}</td>
        </tr>
        <tr>
            <td><strong>Número do Contrato</strong><br>{{ $get('documento.numero', $get('contrato.numero', '-')) }}</td>
            <td><strong>Aceite do Termo</strong><br>{{ $get('contrato.assinatura') ? 'Aceito' : 'Pendente' }}</td>
            <td colspan="2"><strong>Data do Aceite</strong><br>{{ $get('contrato.data_assinatura', '-') }}</td>
        </tr>
    </tbody>
</table>
