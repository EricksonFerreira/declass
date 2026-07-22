<div class="mt-4 pt-3 border-top">
    <div class="d-flex align-items-center mb-3">
        <i class="fas fa-car-crash text-warning mr-2" style="font-size: 1.1rem;"></i>
        <h6 class="font-weight-bold mb-0" style="color: var(--ma-ink);">Avarias informadas pelo cliente</h6>
    </div>

    @if($avariasCliente->isNotEmpty())
        <div class="ma-list">
            @foreach($avariasCliente as $avaria)
                <div class="ma-list__item">
                    <div class="d-flex justify-content-between flex-wrap">
                        <div>
                            <div class="ma-list__label">{{ strtoupper((string) ($avaria->local_dano ?? 'Avaria')) }}</div>
                            <div class="ma-list__value">
                                {{ $avaria->dano?->nome ?? $avaria->categoria?->nome ?? 'Avaria registrada' }}
                                @if($avaria->tipo_dano)
                                    <span class="text-muted">- {{ ucfirst(str_replace('_', ' ', (string) $avaria->tipo_dano)) }}</span>
                                @endif
                            </div>
                            @if($avaria->observacoes)
                                <div class="text-muted small mt-1">{{ $avaria->observacoes }}</div>
                            @endif
                            @if($avaria->quilometragem_veiculo)
                                <div class="text-muted small mt-1">KM: {{ number_format((float) $avaria->quilometragem_veiculo, 0, ',', '.') }}</div>
                            @endif
                        </div>
                        <div class="text-muted small">
                            {{ $avaria->created_at ? \Carbon\Carbon::parse($avaria->created_at)->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>

                    @if($avaria->imagens->isNotEmpty())
                        <div class="d-flex flex-wrap mt-2" style="gap: 8px;">
                            @foreach($avaria->imagens as $imagem)
                                @php
                                    $pathImagem = $imagem->caminho_imagem ?: $imagem->nome_imagem;
                                    $urlImagem = $pathImagem ? asset('storage/' . ltrim((string) $pathImagem, '/')) : null;
                                @endphp
                                @if($urlImagem)
                                    <img src="{{ $urlImagem }}" alt="Foto da avaria" onclick="openPhotoModal('{{ $urlImagem }}', 'Avaria - {{ $avaria->local_dano ?? 'Veiculo' }}')" style="width: 64px; height: 64px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; cursor: pointer;">
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-muted small">Nenhuma avaria informada pelo cliente nesta reserva.</div>
    @endif
</div>
