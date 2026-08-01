@if($reserva->contratoCheckout)
@php
    $co = $reserva->contratoCheckout;
    $coAssinado = ! empty($co->assinatura) || ! empty($co->data_assinatura) || $co->assinado === 'S';
    $coVeiculo = $co->veiculo?->nome ?? ('ID ' . $co->veiculo_id);
    $coBorder = $coAssinado ? '#10b981' : '#f59e0b';
    $coBgFrom = $coAssinado ? '#ecfdf5' : '#fffbeb';
    $coBgTo = $coAssinado ? '#d1fae5' : '#fef3c7';
    $coBadgeBg = $coAssinado ? 'rgba(16,185,129,.15)' : 'rgba(245,158,11,.18)';
    $coBadgeClr = $coAssinado ? '#047857' : '#b45309';
    $coBadgeTxt = $coAssinado ? 'Contrato Assinado' : 'Aguardando Assinatura';
    $coBadgeIcon = $coAssinado ? 'fa-check-circle' : 'fa-clock';

    $fotosCheckoutPaths = [
        'Direita' => $co->foto_direita_path ?? null,
        'Esquerda' => $co->foto_esquerda_path ?? null,
        'Frente' => $co->foto_frente_path ?? null,
        'Trás' => $co->foto_tras_path ?? null,
        'Velocímetro' => $co->foto_velocimetro_path ?? null,
        'Combustível' => $co->foto_combustivel_path ?? null,
    ];
@endphp

<div class="rs-checkin-layout">
    <div class="rs-ci-card" style="border-left:5px solid {{ $coBorder }};background:linear-gradient(135deg,{{ $coBgFrom }},{{ $coBgTo }})">
        <div class="rs-ci-card__top">
            <div class="rs-ci-card__icon" style="background:{{ $coBorder }}">
                <i class="fas fa-flag-checkered"></i>
            </div>
            <div class="rs-ci-card__info">
                <div class="rs-ci-card__title">Contrato de Check-out</div>
                <span class="rs-ci-card__badge" style="background:{{ $coBadgeBg }};color:{{ $coBadgeClr }}">
                    <i class="fas {{ $coBadgeIcon }} mr-1"></i>{{ $coBadgeTxt }}
                </span>
            </div>
        </div>

        <div class="rs-ci-card__details">
            <div class="rs-ci-detail">
                <span><i class="fas fa-car mr-1"></i>Veículo</span>
                <strong>{{ $coVeiculo }}</strong>
            </div>
            <div class="rs-ci-detail">
                <span><i class="fas fa-tachometer-alt mr-1"></i>Quilometragem</span>
                <strong>{{ number_format((float)($co->quilometragem_devolucao ?? 0), 0, ',', '.') }} km</strong>
            </div>
            <div class="rs-ci-detail">
                <span><i class="fas fa-gas-pump mr-1"></i>Combustível</span>
                <strong>{{ $co->nivel_combustivel_devolucao ?? '—' }}</strong>
            </div>
            <div class="rs-ci-detail">
                <span><i class="fas fa-calendar mr-1"></i>Devolução</span>
                <strong>
                    {{ $co->data_devolucao ? \Carbon\Carbon::parse($co->data_devolucao)->format('d/m/Y') : '—' }}
                    {{ $co->hora_devolucao ? substr($co->hora_devolucao, 0, 5) : '' }}
                </strong>
            </div>
            @if($coAssinado && $co->data_assinatura)
            <div class="rs-ci-detail">
                <span><i class="fas fa-pen-nib mr-1"></i>Assinado em</span>
                <strong>{{ \Carbon\Carbon::parse($co->data_assinatura)->format('d/m/Y H:i') }}</strong>
            </div>
            @endif
            @if(!empty($co->notas_devolucao))
            <div class="rs-ci-detail" style="width:100%">
                <span><i class="fas fa-sticky-note mr-1"></i>Notas de devolução</span>
                <strong>{{ $co->notas_devolucao }}</strong>
            </div>
            @endif
            @if(!empty($co->observacoes_devolucao))
            <div class="rs-ci-detail" style="width:100%">
                <span><i class="fas fa-comment-alt mr-1"></i>Observações</span>
                <strong>{{ $co->observacoes_devolucao }}</strong>
            </div>
            @endif
        </div>

        <div class="rs-ci-card__actions">
            @if($coAssinado)
                <a href="{{ route('reserva.minhas.vercontratocheckout', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id]) }}" target="_blank" class="rs-ci-btn rs-ci-btn--view">
                    <i class="fas fa-eye"></i> Ver contrato
                </a>
            @else
                <a href="{{ route('reserva.minhas.assinar-checkout', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id]) }}" class="rs-ci-btn rs-ci-btn--sign">
                    <i class="fas fa-signature"></i> Assinar Check-out / Devolução
                </a>
            @endif
        </div>
    </div>

    <div class="rs-card">
        <div class="rs-card__header">
            <i class="fas fa-camera rs-card__icon"></i>
            <h2>Fotos do Veículo no Check-out</h2>
        </div>
        <div class="row rs-photo-grid">
            @foreach($fotosCheckoutPaths as $label => $path)
                   @php
        $fotoUrl = $path
            ? asset('storage/' . ltrim($path, '/'))
            : null;
    @endphp
                <div class="col-6 col-md-2 rs-photo-item">
                    @if($fotoUrl)
                        <div class="rs-photo-frame js-lightbox-trigger"
                             data-src="{{ $fotoUrl }}" data-label="{{ $label }} (Check-out)">
                            <img src="{{ $fotoUrl }}" alt="Foto {{ $label }} do check-out" class="rs-photo-thumb">
                            <div class="rs-photo-zoom">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                    @else
                        <div class="rs-photo-placeholder">
                            <i class="fas fa-image"></i>
                            <span>Sem foto</span>
                        </div>
                    @endif
                    <div class="rs-photo-label">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@else
    <div class="rs-empty py-5">
        <i class="fas fa-file-signature" style="font-size:2.5rem;color:#cbd5e1"></i>
        <span>Nenhum contrato de check-out cadastrado para esta reserva.</span>
    </div>
@endif
