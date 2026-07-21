@if(!empty($pagamentos))
    <div class="section-title">Histórico de Pagamentos e Renovações</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th>Data / Hora</th>
                <th>Valor</th>
                <th>Método</th>
                <th>Referência</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagamentos as $pagamento)
                <tr>
                    <td>{{ $pagamento['data'] ?? '-' }}</td>
                    <td class="money">{{ is_numeric($pagamento['valor'] ?? null) ? ('R$ ' . number_format((float)$pagamento['valor'], 2, ',', '.')) : ($pagamento['valor'] ?? '-') }}</td>
                    <td>{{ $pagamento['metodo'] ?? '-' }}</td>
                    <td style="font-family: monospace; font-size: 0.85em;">{{ $pagamento['referencia'] ?? '-' }}</td>
                    <td>
                        @php
                            $st = strtolower((string) ($pagamento['status'] ?? 'pendente'));
                            $labelStatus = match($st) {
                                'pago', 'paid', 'approved', 'succeeded', 'confirmado' => 'Pago',
                                'pendente', 'pending' => 'Pendente',
                                'cancelado', 'canceled' => 'Cancelado',
                                default => ucfirst($st),
                            };
                        @endphp
                        {{ $labelStatus }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
