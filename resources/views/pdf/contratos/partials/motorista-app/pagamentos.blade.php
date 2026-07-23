@if(!empty($pagamentos))
    <div class="section-title">Histórico de Pagamentos e Renovações</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th>Data / Hora</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagamentos as $pagamento)
                <tr>
                    <td>{{ $pagamento['data'] ?? '-' }}</td>
                    <td class="money">{{ is_numeric($pagamento['valor'] ?? null) ? ('R$ ' . number_format((float)$pagamento['valor'], 2, ',', '.')) : ($pagamento['valor'] ?? '-') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

