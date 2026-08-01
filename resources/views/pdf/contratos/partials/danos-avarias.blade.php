<div class="section-title">Danos e Avarias</div>
@php
    $formatarTipoDano = $formatarTipoDano ?? function ($tipo) {
        $map = [
            'amolgadela_grave' => 'Amassado grave',
            'amolgadela_leve' => 'Amassado leve',
            'risco_leve' => 'Risco leve',
            'risco_profundo' => 'Risco profundo',
            'pneu_danificado' => 'Pneu danificado',
            'vidro_trincado' => 'Vidro trincado',
            'para_choque_danificado' => 'Para-choque danificado',
        ];
        if (blank($tipo)) {
            return '-';
        }
        return $map[$tipo] ?? ucfirst(str_replace('_', ' ', (string) $tipo));
    };
    // Flag to hide value and image columns for checkin contracts
    $hideValor = $hideValor ?? false;
@endphp
@if(!empty($danos))
    <table class="detail-table">
        <thead>
            <tr>
                <th>Local</th>
                <th>Descrição</th>
                <th>Tipo</th>
                @if(!$hideValor)
                    <th>Valor</th>
                @endif
                <th>Registrado em</th>
                @if(!$hideValor)
                    <th>Imagens</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($danos as $dano)
                <tr>
                    <td>
                        <strong>{{ $dano['localizacao'] ?? '-' }}</strong><br>
                        <span class="muted">
                            {{ $dano['nome_dano'] ?? $dano['categoria'] ?? 'Dano não informado' }}
                        </span>
                    </td>
                    <td>
                        {{ $dano['observacoes'] ?? 'Sem descrição' }}<br>
                        <span class="muted">KM: {{ $dano['quilometragem'] ?? '-' }}</span>
                    </td>
                    <td>
                        {{ $formatarTipoDano($dano['tipo_dano'] ?? null) }}
                    </td>
                    @if(!$hideValor)
                        <td class="money">{{ $dano['custo_estimado'] ?? '-' }}</td>
                    @endif
                    <td>
                        <span class="muted">{{ $dano['registrado_em'] ?? '-' }}</span>
                    </td>
                    @if(!$hideValor)
                        <td>
                            @forelse(($dano['imagens'] ?? []) as $img)
                                <img class="thumb" src="{{ $img }}" alt="Dano">
                            @empty
                                <span class="muted">Sem foto</span>
                            @endforelse
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="muted" style="font-style:italic;margin-top:4px">
        Nenhuma avaria registrada nesta reserva.
    </div>
@endif
