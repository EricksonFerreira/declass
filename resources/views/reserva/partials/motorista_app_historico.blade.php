<div class="ma-card mb-4">
    <div class="ma-card__head">
        <div class="ma-card__icon"><i class="fas fa-file-alt"></i></div>
        <div>
            <div class="ma-card__title">Dados enviados</div>
            <div class="ma-card__sub">Resumo do que foi informado na solicitacao</div>
        </div>
    </div>
    <div class="ma-card__body">
        @if(empty($dadosJson))
            <div class="ma-history-empty">Nenhum dado adicional informado.</div>
        @else
            <div class="ma-list">
                @foreach($dadosJson as $key => $value)
                    @php
                        $campoKey = (string) $key;
                        $campoCfg = $camposPersonalizados->get($campoKey);
                        $campoLabel = (string) ($rotulosPadraoDados[$campoKey] ?? $campoCfg['label'] ?? str_replace(['_', '-'], ' ', ucfirst($campoKey)));
                        $campoTipo = (string) ($campoCfg['type'] ?? '');
                        if ($campoKey === 'opcionais_payload') {
                            $valorExibicao = collect($opcionaisSelecionados)
                                ->map(function ($item) use ($opcionaisMap) {
                                    $opcional = $opcionaisMap->get((int) ($item['id'] ?? 0));
                                    $nome = $opcional?->nome ?? ('Opcional #' . (int) ($item['id'] ?? 0));
                                    $quantidade = (int) ($item['quantidade'] ?? 0);
                                    return $quantidade > 1 ? $nome . ' x' . $quantidade : $nome;
                                })
                                ->filter()
                                ->values()
                                ->implode(', ');
                            $valorExibicao = $valorExibicao !== '' ? $valorExibicao : 'Nenhum opcional selecionado';
                        } elseif ($campoKey === 'plano_id') {
                            $valorExibicao = $plano?->nome ?? ('Plano #' . (int) $value);
                        } elseif ($campoKey === 'estacao_id') {
                            $valorExibicao = $registro->estacaoRetirada?->nome ?? ('Estação #' . (int) $value);
                        } elseif ($campoKey === 'data_inicio') {
                            $valorExibicao = $formatarDataValor($value);
                        } elseif ($campoTipo === 'data') {
                            $valorExibicao = $formatarDataValor($value);
                        } else {
                            $valorExibicao = $formatarValor($value);
                        }
                    @endphp
                    <div class="ma-list__item">
                        <div class="ma-list__label">{{ $campoLabel }}</div>
                        <div class="ma-list__value">{{ $valorExibicao }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@if(!empty($registro->observacoes))
    <div class="ma-card">
        <div class="ma-card__head">
            <div class="ma-card__icon"><i class="fas fa-sticky-note"></i></div>
            <div>
                <div class="ma-card__title">Observacoes</div>
                <div class="ma-card__sub">Notas internas ou do cliente</div>
            </div>
        </div>
        <div class="ma-card__body">
            <div class="ma-note">{{ $registro->observacoes }}</div>
        </div>
    </div>
@endif
