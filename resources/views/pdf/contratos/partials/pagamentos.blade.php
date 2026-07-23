@if(!empty($pagamentos))
    <div class="section-title">Pagamentos e Transações</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagamentos as $pagamento)
                <tr>
                    <td>{{ $pagamento['data'] ?? '-' }}</td>
                    <td class="money">{{ $pagamento['valor'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

