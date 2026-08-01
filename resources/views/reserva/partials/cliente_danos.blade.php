<div class="rs-card">
    <div class="rs-card__head">
        <div class="rs-card__icon"><i class="fas fa-car-crash"></i></div>
        <div>
            <div class="rs-card__title">Danos do veículo</div>
            <div class="rs-card__sub">Histórico de danos registrados para este carro</div>
        </div>
        <span class="rs-status is-warning ml-auto">{{ $danosVeiculo->count() }} {{ $danosVeiculo->count() === 1 ? 'dano' : 'danos' }}</span>
    </div>
    <div class="rs-card__body">
        @if($danosVeiculo->isEmpty())
            <div class="rs-note">Nenhum dano registrado para este veículo.</div>
        @else
            <div style="max-height: 500px; overflow-y: auto; padding-right: 8px;">
                <div class="rs-list" style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px;">
                    @foreach($danosVeiculo as $dano)
                        @php
                            $statusDano = strtolower((string) ($dano->status ?? 'pendente'));
                            $statusLabel = match ($statusDano) {
                                'corrigida' => 'Corrigido',
                                'parcialmente_corrigida' => 'Parcial',
                                'descartada' => 'Descartado',
                                'em_correcao' => 'Em correção',
                                default => 'Pendente',
                            };
                            $badgeClass = match ($statusDano) {
                                'corrigida', 'parcialmente_corrigida' => 'success',
                                'descartada' => 'secondary',
                                'em_correcao' => 'warning',
                                default => 'danger',
                            };
                            $imagensDano = $dano->imagens
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

                            // Definir a origem do dano
                            $origemDano = 'Outra reserva';
                            $origemBadgeClass = 'badge-secondary';
                            if ((int)$dano->reserva_id === (int)$reserva->id) {
                                if ($dano->contrato_id) {
                                    $origemDano = 'Check-in';
                                    $origemBadgeClass = 'badge-info';
                                } else {
                                    $origemDano = 'Check-out';
                                    $origemBadgeClass = 'badge-primary';
                                }
                            }
                        @endphp
                        <div class="rs-list__item" style="margin: 0; display: flex; flex-direction: column; justify-content: space-between; height: 100%; gap: 8px;">
                            <div>
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="rs-list__label">{{ $dano->dano?->categoria?->nome ?? 'Dano' }}</div>
                                        <div class="rs-list__value" style="font-size: 0.85rem;">{{ $dano->observacoes ?: $dano->local_dano ?: $dano->localizacao_problema ?: 'Sem descrição' }}</div>
                                    </div>
                                    <span class="badge badge-{{ $badgeClass }}">{{ $statusLabel }}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="badge {{ $origemBadgeClass }}">{{ $origemDano }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.74rem;">
                                    {{ $dano->created_at ? \Carbon\Carbon::parse($dano->created_at)->format('d/m/Y H:i') : '-' }}
                                    @if($dano->quilometragem_veiculo)
                                        · {{ number_format((float) $dano->quilometragem_veiculo, 0, ',', '.') }} km
                                    @endif
                                </div>
                                @if($dano->custo_estimado > 0)
                                    <div class="mt-1 font-weight-bold" style="font-size: 0.82rem; color: #b91c1c;">
                                        Valor Estimado: R$ {{ number_format((float)$dano->custo_estimado, 2, ',', '.') }}
                                    </div>
                                @endif
                                @if($imagensDano->isNotEmpty())
                                    <div class="rs-damage-thumbs mt-2 animate__animated animate__fadeIn" style="display: flex; gap: 6px; flex-wrap: wrap;">
                                        @foreach($imagensDano as $img)
                                            <a href="{{ $img['url'] }}" target="_blank" rel="noopener">
                                                <img src="{{ $img['url'] }}" alt="Foto do dano" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1.5px solid #e2e8f0;transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
