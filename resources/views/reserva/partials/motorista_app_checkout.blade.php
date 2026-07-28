@if($estaCheckout)
    @php
        $checkoutDocs = is_array($registro->documentos_json ?? null)
            ? data_get($registro->documentos_json, 'checkout', [])
            : [];
        $assinaturaCo = data_get($checkoutDocs, 'assinatura');
        $dataAssinaturaCo = data_get($checkoutDocs, 'data_assinatura');
        $fotosCheckoutMap = [
            'Direita'      => data_get($checkoutDocs, 'fotos.foto_direita_co') ?? data_get($checkoutDocs, 'fotos.foto_direita_path') ?? data_get($checkoutDocs, 'foto_direita_co') ?? data_get($checkoutDocs, 'foto_direita_path') ?? ($contratoCheckoutOperacional->foto_direita_path ?? null),
            'Esquerda'     => data_get($checkoutDocs, 'fotos.foto_esquerda_co') ?? data_get($checkoutDocs, 'fotos.foto_esquerda_path') ?? data_get($checkoutDocs, 'foto_esquerda_co') ?? data_get($checkoutDocs, 'foto_esquerda_path') ?? ($contratoCheckoutOperacional->foto_esquerda_path ?? null),
            'Frente'       => data_get($checkoutDocs, 'fotos.foto_frente_co') ?? data_get($checkoutDocs, 'fotos.foto_frente_path') ?? data_get($checkoutDocs, 'foto_frente_co') ?? data_get($checkoutDocs, 'foto_frente_path') ?? ($contratoCheckoutOperacional->foto_frente_path ?? null),
            'Trás'         => data_get($checkoutDocs, 'fotos.foto_tras_co') ?? data_get($checkoutDocs, 'fotos.foto_tras_path') ?? ($contratoCheckoutOperacional->foto_tras_path ?? null),
            'Velocímetro'  => data_get($checkoutDocs, 'fotos.foto_velocimetro_co') ?? data_get($checkoutDocs, 'fotos.foto_velocimetro_path') ?? ($contratoCheckoutOperacional->foto_velocimetro_path ?? null),
            'Combustível'  => data_get($checkoutDocs, 'fotos.foto_combustivel_co') ?? data_get($checkoutDocs, 'fotos.foto_combustivel_path') ?? ($contratoCheckoutOperacional->foto_combustivel_path ?? null),
        ];
        $temFotosCheckout = collect($fotosCheckoutMap)->filter()->isNotEmpty();
    @endphp

    <div class="ma-card mb-4">
        <div class="ma-card__head" style="border-left-color: #e11d48;">
            <div class="ma-card__icon"><i class="fas fa-flag-checkered text-danger"></i></div>
            <div>
                <div class="ma-card__title">Check-out operacional</div>
                <div class="ma-card__sub">Dados registrados no encerramento do contrato</div>
            </div>
            <div class="ml-auto">
                @if($assinaturaCo)
                    <span class="ma-pill ma-pill--success"><i class="fas fa-check-circle"></i> Termo Assinado</span>
                @else
                    <span class="ma-pill ma-pill--warning"><i class="fas fa-signature"></i> Assinatura Pendente</span>
                @endif
            </div>
        </div>
        <div class="ma-card__body">
            <div class="ma-grid">
                <div class="ma-info">
                    <div class="ma-info__label">KM final</div>
                    <div class="ma-info__value font-weight-bold">
                        {{ $registro->quilometragem_final ? number_format((float) $registro->quilometragem_final, 0, ',', '.') : '-' }} km
                    </div>
                </div>
                <div class="ma-info">
                    <div class="ma-info__label">Combustível final</div>
                    <div class="ma-info__value font-weight-bold">{{ $registro->nivel_combustivel_final ?? '-' }}</div>
                </div>
                <div class="ma-info">
                    <div class="ma-info__label">Data/Hora de Devolução</div>
                    <div class="ma-info__value font-weight-bold">
                        {{ $registro->checkout_realizado_em ? \Carbon\Carbon::parse($registro->checkout_realizado_em)->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>
            </div>

            @if(data_get($checkoutDocs, 'notas_devolucao') || data_get($checkoutDocs, 'observacoes_devolucao'))
                <div class="mt-3 pt-3 border-top">
                    @if(data_get($checkoutDocs, 'notas_devolucao'))
                        <div class="ma-info mb-2">
                            <div class="ma-info__label"><i class="fas fa-sticky-note mr-1"></i> Notas de devolução</div>
                            <div class="ma-info__value">{{ data_get($checkoutDocs, 'notas_devolucao') }}</div>
                        </div>
                    @endif
                    @if(data_get($checkoutDocs, 'observacoes_devolucao'))
                        <div class="ma-info">
                            <div class="ma-info__label"><i class="fas fa-comment-alt mr-1"></i> Observações</div>
                            <div class="ma-info__value">{{ data_get($checkoutDocs, 'observacoes_devolucao') }}</div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- FOTOS REGISTRADAS NO CHECK-OUT --}}
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-camera text-danger mr-2" style="font-size: 1.1rem;"></i>
                    <h6 class="font-weight-bold mb-0" style="color: var(--ma-ink);">Fotos do Veículo no Check-out</h6>
                </div>

                <div class="row">
                    @foreach($fotosCheckoutMap as $label => $path)
                        @php
                            $fotoUrl = null;
                            if ($path) {
                                $pathLimpo = ltrim((string) $path, '/');
                                $fotoUrl = str_starts_with($pathLimpo, 'http://') || str_starts_with($pathLimpo, 'https://')
                                    ? $pathLimpo
                                    : asset(str_starts_with($pathLimpo, 'storage/') ? $pathLimpo : ('storage/' . $pathLimpo));
                            }
                        @endphp
                        <div class="col-6 col-md-4 col-lg-2 mb-3">
                            <div class="card h-100 shadow-sm border-light">
                                <div class="card-body p-2 text-center d-flex flex-column justify-content-between">
                                    @if($fotoUrl)
                                        <div class="position-relative overflow-hidden rounded mb-2" style="height: 110px; background: #f8fafc; cursor: pointer;"
                                             onclick="openPhotoModal('{{ $fotoUrl }}', 'Foto do Check-out - {{ $label }}')">
                                            <img src="{{ $fotoUrl }}" alt="Foto {{ $label }}" class="w-100 h-100" style="object-fit: cover; transition: transform 0.2s ease;">
                                            <div class="position-absolute inset-0 d-flex align-items-center justify-content-center text-white" style="background: rgba(0,0,0,0.35); opacity: 0; transition: opacity 0.2s;" onmouseenter="this.style.opacity=1" onmouseleave="this.style.opacity=0">
                                                <i class="fas fa-search-plus fa-lg"></i>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex flex-column align-items-center justify-content-center rounded mb-2" style="height: 110px; background: #f1f5f9; color: #94a3b8;">
                                            <i class="fas fa-image fa-2x mb-1"></i>
                                            <small style="font-size: 0.72rem;">Sem foto</small>
                                        </div>
                                    @endif
                                    <span class="font-weight-bold text-secondary" style="font-size: 0.78rem;">{{ $label }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ASSINATURA DO TERMO DE DEVOLUÇÃO --}}
            @include('reserva.components.avarias_cliente_motorista_app', ['avariasCliente' => $avariasCliente])

            <div class="mt-4 pt-3 border-top">
                @if($assinaturaCo)
                    <div class="d-flex flex-column align-items-center justify-content-center py-3">
                        <div class="text-success font-weight-bold mb-2">
                            <i class="fas fa-check-circle mr-1"></i> Termo Assinado Digitalmente
                        </div>
                        @if($dataAssinaturaCo)
                            <small class="text-muted mb-3">Registrado em {{ \Carbon\Carbon::parse($dataAssinaturaCo)->format('d/m/Y H:i') }}</small>
                        @endif
                        <a href="{{ route('reserva.verContratoCheckout', ['codigo' => $codigoReserva]) }}" target="_blank" class="btn btn-sm btn-outline-primary px-4 py-2 font-weight-bold">
                            <i class="fas fa-file-pdf mr-2"></i> Ver Contrato de Devolução
                        </a>
                    </div>
                @else
                    <div class="row justify-content-center">
                        <div class="col-md-8 col-12 text-center py-4">
                            <i class="fas fa-signature fa-3x mb-3" style="color: #cbd5e1;"></i>
                            <h5 class="font-weight-bold" style="color: var(--ma-ink);">Assinatura Digital Pendente</h5>
                            <p class="text-muted mb-3">O motorista deve assinar o termo de devolução para registrar a devolução oficial do veículo.</p>
                            <a href="{{ route('reserva.assinarContratoCheckout', ['codigo' => $codigoReserva]) }}" class="btn btn-success font-weight-bold px-4 py-2 text-white">
                                <i class="fas fa-signature mr-2"></i> Assinar Termo de Devolução
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="ma-card p-5 text-center text-muted">
        <i class="fas fa-flag-checkered fa-3x mb-3" style="color:#cbd5e1"></i>
        <h5>Check-out não realizado</h5>
        <p class="mb-0">Os dados e fotos da devolução do veículo ficarão disponíveis aqui após a conclusão do check-out operacional pelo operador.</p>
       @php $calcaoAtual = $reserva->calcao ?? null; @endphp
        @php( $podeFazerCalcao = !$calcaoAtual || $calcaoAtual->status === 'falhou')
        @if($estaCheckin && $checkinContratoAssinado && !$podeFazerCalcao)
            <div class="mt-4">
                <a href="{{ route('reserva.checkout.vistoria', ['codigo' => $codigoReserva]) }}" class="btn btn-warning font-weight-bold text-white px-4 py-2" style="border-radius: 999px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);">
                    <i class="fas fa-flag-checkered mr-2"></i> Iniciar Processo de Check-out
                </a>
            </div>
        @endif
    </div>
@endif
