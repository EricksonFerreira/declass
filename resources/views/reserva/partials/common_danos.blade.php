<div class="rs-checkin-layout">
    {{-- Avarias registradas --}}
    <div class="rs-card">
        <div class="rs-card__header rs-card__header--between">
            <div class="rs-card__header-left">
                <i class="fas fa-car-crash rs-card__icon" style="color:#f59e0b"></i>
                <h2>Avarias Registradas</h2>
            </div>
            @if(($danosReserva->count() ?? 0) > 0)
                <span class="rs-status-badge rs-status-badge--sm is-warning">
                    {{ $danosPendentes->count() }} pendente(s)
                </span>
            @endif
        </div>

        @if(($danosReserva->count() ?? 0) > 0)
            <div class="rs-info-rows mb-3">
                <div class="rs-info-row">
                    <span><i class="fas fa-list"></i>Total registradas</span>
                    <strong>{{ $danosReserva->count() }}</strong>
                </div>
                <div class="rs-info-row">
                    <span><i class="fas fa-receipt"></i>Cobranças</span>
                    <strong>{{ $totalCobrancasAvaria }}</strong>
                </div>
            </div>

            <div class="rs-damage-block rs-damage-block--amber">
                <div class="rs-damage-block__label">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>Danos Nesta Reserva</strong>
                        <span>Avarias registradas no check-in ou nesta devolução</span>
                    </div>
                </div>

                <div class="rs-damage-table-wrap">
                    <table class="rs-damage-table" id="danos-detectados-table">
                        <thead>
                            <tr>
                                <th>Localização</th>
                                <th>Descrição</th>
                                <th>Tipo</th>
                                <th>Custo Est.</th>
                                <th>Imagem</th>
                            </tr>
                        </thead>
                        <tbody id="danos-detectados-container">
                            <tr id="danos-detectados-loading">
                                <td colspan="5" class="damage-empty">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Carregando danos...
                                </td>
                            </tr>
                @foreach($danosReserva as $d)
                    @php
                        $imagensDano = $d->imagens
                            ->map(function ($img) {
                                $path = $img->caminho_imagem ?: $img->nome_imagem;
                                return [
                                    'url' => $path
                                        ? asset('storage/' . ltrim(str_replace('\\', '/', $path), '/'))
                                        : null,
                                ];
                            })
                            ->filter(function ($img) {
                                return !empty($img['url']);
                            })
                            ->values();
                        $danoPayload = [
                            'id' => $d->id,
                            'localizacao' => strtoupper((string) ($d->local_dano ?? $d->localizacao_problema ?? '-')),
                            'descricao' => $d->observacoes,
                            'tipo_dano' => $d->tipo_dano,
                            'categoria' => $d->dano?->categoria?->nome,
                            'local_dano' => $d->local_dano,
                            'localizacao_problema' => $d->localizacao_problema,
                            'custo_estimado' => $d->custo_estimado,
                            'quilometragem_veiculo' => $d->quilometragem_veiculo,
                            'created_at' => $d->created_at ? \Carbon\Carbon::parse($d->created_at)->format('d/m/Y H:i') : null,
                            'correcao_descricao' => $d->correcao_descricao,
                            'correcao_data' => $d->correcao_data ? \Carbon\Carbon::parse($d->correcao_data)->format('d/m/Y H:i') : null,
                            'correcao_usuario' => $d->usuarioCorrecao?->nome,
                            'correcao_realizada' => (bool) $d->correcao_data,
                            'imagens' => $imagensDano->all(),
                        ];
                    @endphp
                    <tr class="js-dano-detalhe-trigger rs-damage-table__row" data-dano='@json($danoPayload)'>
                        <td>
                            <strong>{{ strtoupper($d->local_dano ?? $d->localizacao_problema ?? '-') }}</strong><br>
                            <small class="text-muted">{{ $d->dano?->categoria?->nome ?? '—' }} · {{ $d->localizacao_problema ?? '—' }}</small>
                        </td>
                        <td>
                            {{ $d->observacoes ?: '—' }}
                            @if($d->quilometragem_veiculo)
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-tachometer-alt mr-1"></i>KM {{ number_format((float) $d->quilometragem_veiculo, 0, ',', '.') }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <div>
                                <span class="badge badge-light text-dark border">{{ $d->tipo_dano ?: '—' }}</span>
                                @if($d->tipo_dano)
                                    <small class="d-block text-muted mt-1">{{ $d->tipo_dano }}</small>
                                @endif
                            </div>
                            <div class="mt-1">
                                    @if($d->correcao_data)
                                        <span class="badge badge-success">Corrigido</span>
                                    @else
                                        <span class="badge badge-warning">Pendente</span>
                                    @endif
                            </div>
                        </td>
                        <td>
                            <strong>R$ {{ number_format((float) ($d->custo_estimado ?? 0), 2, ',', '.') }}</strong>
                        </td>
                        <td>
                            <div class="rs-damage-thumbs">
                                @forelse($imagensDano as $img)
                                    <a href="{{ $img['url'] }}" target="_blank" rel="noopener" onclick="event.stopPropagation()">
                                        <img src="{{ $img['url'] }}" alt="Foto do dano" style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1.5px solid #e2e8f0;margin-right:4px">
                                    </a>
                                @empty
                                    <span class="rs-damage-empty-inline">
                                        <i class="far fa-image"></i> Sem imagem
                                    </span>
                                @endforelse
                            </div>
                        </td>
                    </tr>
                @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rs-empty">
                <i class="fas fa-check-circle" style="color:#10b981"></i>
                <span>Nenhuma avaria registrada nesta reserva.</span>
            </div>
        @endif
    </div>
</div>
