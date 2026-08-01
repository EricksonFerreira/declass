@if($reserva->contratoCheckin)
@php
    $ci          = $reserva->contratoCheckin;
    $ciAssinado  = $ci->assinado === 'S';
    $checkoutJaRegistrado = $reserva->contratoCheckout()->exists();
    $ciVeiculo   = $ci->veiculo?->nome ?? ('ID ' . $ci->veiculo_id);
    $ciBorder    = $ciAssinado ? '#10b981' : '#f59e0b';
    $ciBgFrom    = $ciAssinado ? '#ecfdf5' : '#fffbeb';
    $ciBgTo      = $ciAssinado ? '#d1fae5' : '#fef3c7';
    $ciBadgeBg   = $ciAssinado ? 'rgba(16,185,129,.15)' : 'rgba(245,158,11,.18)';
    $ciBadgeClr  = $ciAssinado ? '#047857' : '#b45309';
    $ciBadgeTxt  = $ciAssinado ? 'Contrato Assinado' : 'Aguardando Assinatura';
    $ciBadgeIcon = $ciAssinado ? 'fa-check-circle' : 'fa-clock';

    $fotosPaths = [
        'Direita'      => $ci->foto_direita_path ?? null,
        'Esquerda'     => $ci->foto_esquerda_path ?? null,
        'Frente'       => $ci->foto_frente_path ?? null,
        'Trás'         => $ci->foto_tras_path ?? null,
        'Velocímetro'  => $ci->foto_velocimetro_path ?? null,
        'Combustível'  => $ci->foto_combustivel_path ?? null,
    ];
@endphp

<div class="rs-checkin-layout">

    {{-- Contrato status card --}}
    <div class="rs-ci-card" style="border-left:5px solid {{ $ciBorder }};background:linear-gradient(135deg,{{ $ciBgFrom }},{{ $ciBgTo }})">
        <div class="rs-ci-card__top">
            <div class="rs-ci-card__icon" style="background:{{ $ciBorder }}">
                <i class="fas fa-file-signature"></i>
            </div>
            <div class="rs-ci-card__info">
                <div class="rs-ci-card__title">Contrato de Check-in</div>
                <span class="rs-ci-card__badge" style="background:{{ $ciBadgeBg }};color:{{ $ciBadgeClr }}">
                    <i class="fas {{ $ciBadgeIcon }} mr-1"></i>{{ $ciBadgeTxt }}
                </span>
            </div>
        </div>

        <div class="rs-ci-card__details">
            <div class="rs-ci-detail">
                <span><i class="fas fa-car mr-1"></i>Veículo</span>
                <strong>{{ $ciVeiculo }}</strong>
            </div>
            <div class="rs-ci-detail">
                <span><i class="fas fa-tachometer-alt mr-1"></i>Quilometragem</span>
                <strong>{{ number_format((float)($ci->quilometragem_atual ?? 0), 0, ',', '.') }} km</strong>
            </div>
            <div class="rs-ci-detail">
                <span><i class="fas fa-gas-pump mr-1"></i>Combustível</span>
                <strong>{{ $ci->nivel_combustivel ?? '—' }}</strong>
            </div>
            <div class="rs-ci-detail">
                <span><i class="fas fa-calendar mr-1"></i>Saída</span>
                <strong>
                    {{ $ci->data_saida ? \Carbon\Carbon::parse($ci->data_saida)->format('d/m/Y') : '—' }}
                    {{ $ci->hora_saida ? substr($ci->hora_saida, 0, 5) : '' }}
                </strong>
            </div>
            @if($ciAssinado && $ci->data_assinatura)
            <div class="rs-ci-detail">
                <span><i class="fas fa-pen-nib mr-1"></i>Assinado em</span>
                <strong>{{ \Carbon\Carbon::parse($ci->data_assinatura)->format('d/m/Y H:i') }}</strong>
            </div>
            @endif
            @if(!empty($ci->notas_saida))
            <div class="rs-ci-detail" style="width:100%">
                <span><i class="fas fa-sticky-note mr-1"></i>Notas de saída</span>
                <strong>{{ $ci->notas_saida }}</strong>
            </div>
            @endif
            @if(!empty($ci->observacoes_saida))
            <div class="rs-ci-detail" style="width:100%">
                <span><i class="fas fa-comment-alt mr-1"></i>Observações</span>
                <strong>{{ $ci->observacoes_saida }}</strong>
            </div>
            @endif
        </div>

        <div class="rs-ci-card__actions">
            @if($ciAssinado)
                <a href="{{ route('reserva.minhas.vercontratocheckin', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id]) }}" target="_blank" class="rs-ci-btn rs-ci-btn--view">
                    <i class="fas fa-eye"></i> Ver contrato
                </a>
            @else
                @if(!$checkoutJaRegistrado)
                    <a href="{{ route('reserva.minhas.assinar-checkin', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id]) }}" class="rs-ci-btn rs-ci-btn--sign">
                        <i class="fas fa-signature"></i> Assinar
                    </a>
                @endif
            @endif
        </div>
    </div>

    {{-- Upgrade de Categoria --}}
    @if($ci->houve_upgrade_categoria)
    <div class="rs-card" style="border-left:4px solid #fd7e14">
        <div class="rs-card__header">
            <i class="fas fa-arrow-circle-up rs-card__icon" style="color:#fd7e14"></i>
            <h2>Upgrade de Categoria</h2>
        </div>
        <div class="rs-info-rows">
            <div class="rs-info-row">
                <span>Categoria Reservada</span>
                <strong>{{ $ci->grupoFrotaOriginal?->nome ?? 'Grupo #' . $ci->grupo_frota_original_id }}</strong>
            </div>
            <div class="rs-info-row">
                <span>Veiculo Entregue</span>
                <strong>{{ $ci->veiculo?->nome ?? '—' }}</strong>
            </div>
            <div class="rs-info-row">
                <span>Motivo</span>
                <strong>{{ $ci->motivo_upgrade ?? '—' }}</strong>
            </div>
            <div class="rs-info-row">
                <span>Operador</span>
                <strong>{{ $ci->usuarioUpgrade?->nome ?? '—' }}</strong>
            </div>
            <div class="rs-info-row">
                <span>Data/Hora</span>
                <strong>{{ $ci->data_hora_upgrade?->format('d/m/Y H:i') ?? '—' }}</strong>
            </div>
        </div>
    </div>
    @endif

    {{-- Fotos do veículo no check-in --}}
    <div class="rs-card">
        <div class="rs-card__header">
            <i class="fas fa-camera rs-card__icon"></i>
            <h2>Fotos do Veículo no Check-in</h2>
        </div>
        <div class="row rs-photo-grid">
            @foreach($fotosPaths as $label => $path)
                @php $fotoUrl = $path ? asset('storage/' . $path) : null; @endphp
                <div class="col-6 col-md-2 rs-photo-item">
                    @if($fotoUrl)
                        <div class="rs-photo-frame js-lightbox-trigger"
                             data-src="{{ $fotoUrl }}" data-label="{{ $label }}">
                            <img src="{{ $fotoUrl }}" alt="Foto {{ $label }}" class="rs-photo-thumb">
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
        <span>Nenhum contrato de check-in cadastrado para esta reserva.</span>
    </div>
@endif
