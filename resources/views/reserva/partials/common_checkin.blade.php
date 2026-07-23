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
            <a href="{{ route('reserva.verContratoCheckin', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id]) }}" target="_blank" class="rs-ci-btn rs-ci-btn--view">
                <i class="fas fa-eye"></i> Ver contrato
            </a>
            @if(!$ciAssinado && !$checkoutJaRegistrado)
                @if($pagamentoEmDiaAssinatura)
                    <a href="{{ route('reserva.assinarContratoCheckin', $reserva->codigoReserva?->codigo ?? $reserva->id) }}" class="rs-ci-btn rs-ci-btn--sign">
                        <i class="fas fa-signature"></i> Assinar
                    </a>
                @else
                    <span class="rs-ci-btn rs-ci-btn--disabled"
                          data-toggle="tooltip"
                          title="Pagamento insuficiente. Restam R$ {{ number_format($saldoPendenteAssinatura, 2, ',', '.') }} para liberar a assinatura.">
                        <i class="fas fa-lock"></i> Assinar
                    </span>
                    <a href="{{ route('reserva.pagamento.efetuar', ['id' => $reserva->codigoReserva?->codigo ?? ('RES-' . $reserva->id)]) }}" class="rs-ci-btn rs-ci-btn--sign">
                        <i class="fas fa-credit-card"></i> Pagar restante
                    </a>
                @endif
                <a href="{{ route('reserva.checkin', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id]) }}" class="rs-ci-btn rs-ci-btn--edit">
                    <i class="fas fa-edit"></i> Editar contrato
                </a>
            @endif
            @if(!$checkoutJaRegistrado)
                <button type="button" class="rs-ci-btn rs-ci-btn--delete js-excluir-contrato-checkin"
                        data-url="{{ route('reserva.checkin.excluirContrato', $reserva->id) }}"
                        data-assinado="{{ $ciAssinado ? '1' : '0' }}">
                    <i class="fas fa-trash"></i>
                    {{ $ciAssinado ? 'Excluir e recriar' : 'Excluir contrato' }}
                </button>
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

    {{-- Calção --}}
    @if($calcao || ($ci->assinado === 'S'))
    <div class="rs-card">
        <div class="rs-card__header">
            <i class="fas fa-shield-alt rs-card__icon" style="color:#6f42c1"></i>
            <h2>Calção no Cartão</h2>
        </div>
        @if($calcao)
            @php
                $calcaoBadge = match($calcao->status) {
                    'autorizado' => '#28a745', 'capturado' => '#dc3545',
                    'parcialmente_capturado' => '#fd7e14', 'cancelado' => '#6c757d',
                    'falhou' => '#dc3545', default => '#17a2b8',
                };
            @endphp
            <div class="rs-info-rows">
                <div class="rs-info-row">
                    <span>Status</span>
                    <strong style="color:{{ $calcaoBadge }}">{{ $calcao->label_status }}</strong>
                </div>
                <div class="rs-info-row">
                    <span>Valor Autorizado</span>
                    <strong>R$ {{ number_format((float)$calcao->valor_autorizado, 2, ',', '.') }}</strong>
                </div>
                @if($calcao->valor_capturado)
                <div class="rs-info-row">
                    <span>Valor Capturado</span>
                    <strong>R$ {{ number_format((float)$calcao->valor_capturado, 2, ',', '.') }}</strong>
                </div>
                @endif
                <div class="rs-info-row">
                    <span>Regra Aplicada</span>
                    <strong>{{ $calcao->tipo_regra_aplicada }} / {{ $calcao->origem_regra_aplicada }}</strong>
                </div>
            </div>
            @if($calcao->podeLiberar())
            @can('calcao.cancelar')
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="liberarCalcao({{ $calcao->id }})">
                    <i class="fas fa-unlock mr-1"></i> Liberar Hold (sem cobrar)
                </button>
            </div>
            @endcan
            @endif
            @if($calcao->podeCapturar())
            @can('calcao.capturar')
            <div class="mt-1">
                <button type="button" class="btn btn-sm btn-outline-danger w-100" data-toggle="modal" data-target="#modalCapturarCalcao">
                    <i class="fas fa-hand-holding-usd mr-1"></i> Capturar Valor
                </button>
            </div>
            @endcan
            @endif
            @if(empty($calcao->payment_intent_id) && ($calcao->status ?? '') !== 'cancelado')
            @can('calcao.cancelar')
            <div class="mt-1">
                <button type="button" class="btn btn-sm btn-outline-success w-100" data-toggle="modal" data-target="#modalDevolverCalcaoPresencial">
                    <i class="fas fa-hand-holding-heart mr-1"></i> Devolver Calção Presencial
                </button>
            </div>
            @endcan
            @endif
        @else
            @can('calcao.autorizar')
            <div class="text-center py-2">
                <a href="{{ route('reserva.calcao', $reserva->codigoReserva->codigo) }}" class="btn btn-sm btn-outline-primary w-100">
                    <i class="fas fa-lock mr-1"></i> Registrar Calcao
                </a>
            </div>
            <div class="text-center pb-2">
                <button type="button" class="btn btn-sm btn-outline-success w-100" data-toggle="modal" data-target="#modalCalcaoPresencial">
                    <i class="fas fa-cash-register mr-1"></i> Marcar Calção como Pago (Presencial)
                </button>
            </div>
            @endcan
        @endif
    </div>
    @endif

</div>
@else
    <div class="rs-empty py-5">
        <i class="fas fa-file-signature" style="font-size:2.5rem;color:#cbd5e1"></i>
        <span>Nenhum contrato de check-in cadastrado para esta reserva.</span>
        @if($pagamentoEfetuado)
            <a href="{{ route('reserva.checkin', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id]) }}" class="rs-btn rs-btn--outline mt-2">
                <i class="fas fa-plus"></i> Criar check-in
            </a>
        @else
            <div class="mt-3 d-flex align-items-center justify-content-center" style="gap:.5rem;background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:.6rem 1rem;font-size:.83rem;color:#92400e;font-weight:600">
                <i class="fas fa-lock"></i> Pagamento necessário para criar o check-in
            </div>
        @endif
    </div>
@endif
