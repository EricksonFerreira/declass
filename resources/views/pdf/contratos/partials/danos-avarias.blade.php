<div class="section-title">Danos e Avarias</div>
@if(!empty($danos))
    <table class="detail-table">
        <thead>
            <tr>
                <th>Local</th>
                <th>Descrição</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Registrado em</th>
                <th>Imagens</th>
            </tr>
        </thead>
        <tbody>
            @foreach($danos as $dano)
                <tr>
                    <td>
                        <strong>{{ $dano['localizacao'] ?? '-' }}</strong><br>
                        <span class="muted">{{ $dano['categoria'] ?? '-' }}</span>
                    </td>
                    <td>
                        {{ $dano['observacoes'] ?? 'Sem descrição' }}<br>
                        <span class="muted">KM: {{ $dano['quilometragem'] ?? '-' }}</span>
                    </td>
                    <td>
                        {{ $formatarTipoDano($dano['tipo_dano'] ?? null) }}<br>
                        @if(($dano['status'] ?? '') === 'Corrigido')
                            <span class="badge-blue" style="background:#dcfce7;color:#16a34a;">Corrigido</span>
                        @else
                            <span class="badge-blue" style="background:#fef3c7;color:#d97706;">Pendente</span>
                        @endif
                    </td>
                    <td class="money">{{ $dano['custo_estimado'] ?? '-' }}</td>
                    <td>
                        <span class="muted">{{ $dano['registrado_em'] ?? '-' }}</span>
                        @if(!empty($dano['correcao']))
                            <br><span style="font-size:7px;color:#16a34a;">Corrigido: {{ $dano['correcao_data'] }}</span>
                        @endif
                    </td>
                    <td>
                        @forelse(($dano['imagens'] ?? []) as $img)
                            <img class="thumb" src="{{ $img }}" alt="Dano">
                        @empty
                            <span class="muted">Sem foto</span>
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
