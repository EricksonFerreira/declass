@if(!empty($pagamentos))
    <div class="section-title">Pagamentos e Transações</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Confirmado em</th>
                <th>Pago por</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagamentos as $pagamento)
                <tr>
                    <td>{{ $pagamento['data'] ?? '-' }}</td>
                    <td class="money">{{ $pagamento['valor'] ?? '-' }}</td>
                    <td>
                        @php
                            $status = $pagamento['status'] ?? '-';
                            $statusTraduzido = match(strtolower($status)) {
                                'succeeded', 'approved', 'paid', 'confirmed', 'concluido' => 'Sucedido',
                                'pending', 'pending_confirmation', 'awaiting', 'pendente', 'processando' => 'Pendente',
                                'failed', 'refused', 'canceled', 'cancelled', 'recusado', 'cancelado' => 'Cancelado',
                                'refunded', 'reembolsado', 'estornado' => 'Reembolsado',
                                'authorized', 'autorizado' => 'Autorizado',
                                default => $status,
                            };
                        @endphp
                        {{ $statusTraduzido }}
                    </td>
                    <td>{{ $pagamento['confirmado_em'] ?? '-' }}</td>
                    <td>{{ $pagamento['pago_por'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
