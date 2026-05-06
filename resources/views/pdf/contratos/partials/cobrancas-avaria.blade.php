@if(!empty($cobrancasAvaria))
    <div class="section-title">Cobranças de Avarias</div>
    <table class="detail-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Status</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cobrancasAvaria as $cobranca)
                <tr>
                    <td>{{ $cobranca['data'] ?? '-' }}</td>
                    <td>{{ $cobranca['descricao'] ?? '-' }}</td>
                    <td>{{ $cobranca['status'] ?? '-' }}</td>
                    <td class="money">{{ $cobranca['valor'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
