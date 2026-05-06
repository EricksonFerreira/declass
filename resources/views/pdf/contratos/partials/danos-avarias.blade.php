<div class="section-title">Danos e Avarias</div>
@if(!empty($danos))
    <table class="detail-table">
        <thead>
            <tr>
                <th>Local</th>
                <th>Descrição</th>
                <th>Tipo / Status</th>
                <th>Custo</th>
                <th>Correção</th>
                <th>Imagens</th>
            </tr>
        </thead>
        <tbody>
            @foreach($danos as $dano)
                <tr>
                    <td>
                        <strong>{{ $dano['localizacao'] ?? '-' }}</strong><br>
                        <span class="muted">
                            {{ $dano['categoria'] ?? '-' }} - {{ $dano['localizacao_problema'] ?? '-' }}
                        </span><br>
                        <span class="muted">{{ $dano['registrado_em'] ?? '-' }}</span>
                    </td>
                    <td>
                        {{ $dano['observacoes'] ?? '-' }}<br>
                        <span class="muted">KM: {{ $dano['quilometragem'] ?? '-' }}</span>
                    </td>
                    <td>
                        {{ $formatarTipoDano($dano['tipo_dano'] ?? null) }}<br>
                        <strong>{{ $dano['status'] ?? '-' }}</strong>
                    </td>
                    <td class="money">{{ $dano['custo_estimado'] ?? '-' }}</td>
                    <td>
                        {{ $dano['correcao'] ?? '-' }}<br>
                        <span class="muted">
                            {{ $dano['correcao_data'] ?? '-' }} - {{ $dano['correcao_usuario'] ?? '-' }}
                        </span>
                    </td>
                    <td>
                        @forelse(($dano['imagens'] ?? []) as $img)
                            <img class="thumb" src="{{ $img }}" alt="Dano">
                        @empty
                            <span class="muted">Sem imagem</span>
                        @endforelse
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="muted" style="font-style:italic;margin-top:4px">
        Nenhuma avaria registrada nesta reserva.
    </div>
@endif
