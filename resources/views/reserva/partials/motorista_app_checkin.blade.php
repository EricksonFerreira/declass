{{-- CARD DO TERMO DE CONFIRMAÇÃO DA RESERVA --}}
<div class="ma-card mb-4">
    <div class="ma-card__head">
        <div class="ma-card__icon"><i class="fas fa-file-signature"></i></div>
        <div>
            <div class="ma-card__title">Termo de Confirmação da Reserva</div>
            <div class="ma-card__sub">Termo de aceite prévio assinado digitalmente</div>
        </div>
        <div class="ml-auto">
            @if($termoConfirmacaoAceito)
                <span class="ma-pill ma-pill--success"><i class="fas fa-check-circle"></i> Aceito / Concluído</span>
            @else
                <span class="ma-pill ma-pill--warning"><i class="fas fa-clock"></i> Pendente</span>
            @endif
        </div>
    </div>
    <div class="ma-card__body">
        @if($termoConfirmacaoAceito)
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="ma-grid flex-grow-1 mr-3" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div class="ma-info">
                        <div class="ma-info__label">Status do Termo</div>
                        <div class="ma-info__value text-success font-weight-bold">
                            <i class="fas fa-check-circle mr-1"></i> Aceito / Assinado
                        </div>
                    </div>
                    <div class="ma-info">
                        <div class="ma-info__label">Data e Hora do Aceite</div>
                        <div class="ma-info__value font-weight-bold">
                            {{ \Carbon\Carbon::parse($termoConfirmacaoAceitoEm)->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary font-weight-bold" data-toggle="modal" data-target="#modalVerTermoAssinado">
                        <i class="fas fa-file-alt mr-1"></i> Ver Termo Assinado
                    </button>
                </div>
            </div>
        @else
            <div class="alert alert-warning mb-0 d-flex align-items-center justify-content-between">
                <div>
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Termo de confirmação pendente.</strong> O cliente ainda não realizou o aceite do termo de confirmação da reserva.
                </div>
                @if($podeAceitarTermo)
                    <button type="button" class="btn btn-sm btn-success ml-3" data-toggle="modal" data-target="#modalTermoConfirmacaoMotoristaApp">
                        <i class="fas fa-file-signature mr-1"></i> Aceitar termo agora
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- CARD DO CHECK-IN OPERACIONAL --}}
@if($temContratoCheckin)
    @php
        $fotosCheckin = [
            'Frente'       => $contratoCheckinOperacional->foto_frente_path ?? null,
            'Trás'         => $contratoCheckinOperacional->foto_tras_path ?? null,
            'Direita'      => $contratoCheckinOperacional->foto_direita_path ?? null,
            'Esquerda'     => $contratoCheckinOperacional->foto_esquerda_path ?? null,
            'Velocímetro'  => $contratoCheckinOperacional->foto_velocimetro_path ?? null,
            'Combustível'  => $contratoCheckinOperacional->foto_combustivel_path ?? null,
        ];
        $temFotosCheckin = collect($fotosCheckin)->filter()->isNotEmpty();
    @endphp

    <div class="ma-card mb-4">
        <div class="ma-card__head">
            <div class="ma-card__icon"><i class="fas fa-clipboard-check"></i></div>
            <div>
                <div class="ma-card__title">Check-in operacional</div>
                <div class="ma-card__sub">Dados registrados na retirada do veículo</div>
            </div>
            <div class="ml-auto">
                @if($checkinContratoAssinado)
                    <span class="ma-pill ma-pill--success"><i class="fas fa-check-circle"></i> Contrato Assinado</span>
                @else
                    <span class="ma-pill ma-pill--warning"><i class="fas fa-clock"></i> Aguardando Assinatura</span>
                @endif
            </div>
        </div>
        <div class="ma-card__body">
            <div class="ma-grid">
                <div class="ma-info">
                    <div class="ma-info__label">Veículo</div>
                    <div class="ma-info__value font-weight-bold">
                        {{ $contratoCheckinOperacional->veiculo?->nome ?? ('#' . $contratoCheckinOperacional->veiculo_id) }}
                        @if($contratoCheckinOperacional->veiculo?->placa_licenca)
                            <span class="text-muted">({{ $contratoCheckinOperacional->veiculo->placa_licenca }})</span>
                        @endif
                    </div>
                </div>
                <div class="ma-info">
                    <div class="ma-info__label">Modelo</div>
                    <div class="ma-info__value">{{ $contratoCheckinOperacional->modelo?->nome ?? $contratoCheckinOperacional->veiculo?->modelo?->nome ?? '-' }}</div>
                </div>
                <div class="ma-info">
                    <div class="ma-info__label">Marca</div>
                    <div class="ma-info__value">{{ $contratoCheckinOperacional->marca?->nome ?? $contratoCheckinOperacional->veiculo?->marca?->nome ?? '-' }}</div>
                </div>
                <div class="ma-info">
                    <div class="ma-info__label">KM inicial</div>
                    <div class="ma-info__value font-weight-bold">{{ number_format((float) $contratoCheckinOperacional->quilometragem_atual, 0, ',', '.') }} km</div>
                </div>
                <div class="ma-info">
                    <div class="ma-info__label">Combustível</div>
                    <div class="ma-info__value font-weight-bold">{{ $contratoCheckinOperacional->nivel_combustivel ?? '-' }}</div>
                </div>
                <div class="ma-info">
                    <div class="ma-info__label">Data/Hora da Saída</div>
                    <div class="ma-info__value font-weight-bold">
                        {{ $contratoCheckinOperacional->data_saida ? \Carbon\Carbon::parse($contratoCheckinOperacional->data_saida)->format('d/m/Y') : '-' }}
                        {{ $contratoCheckinOperacional->hora_saida ? \Carbon\Carbon::parse($contratoCheckinOperacional->hora_saida)->format('H:i') : '' }}
                    </div>
                </div>
                <div class="ma-info">
                    <div class="ma-info__label">Status Assinatura</div>
                    <div class="ma-info__value font-weight-bold {{ $checkinContratoAssinado ? 'text-success' : 'text-warning' }}">
                        {{ $checkinContratoAssinado ? 'Assinado' : 'Pendente' }}
                        @if($checkinContratoAssinado && $contratoCheckinOperacional->data_assinatura)
                            <small class="d-block text-muted">{{ \Carbon\Carbon::parse($contratoCheckinOperacional->data_assinatura)->format('d/m/Y H:i') }}</small>
                        @endif
                    </div>
                </div>
                @if($codigoOperacionalCheckin)
                    <div class="ma-info">
                        <div class="ma-info__label">Código Operacional</div>
                        <div class="ma-info__value"><code>{{ $codigoOperacionalCheckin }}</code></div>
                    </div>
                @endif
            </div>

            @if($contratoCheckinOperacional->notes_saida || $contratoCheckinOperacional->observacoes_saida)
                <div class="mt-3 pt-3 border-top">
                    @if($contratoCheckinOperacional->notes_saida)
                        <div class="ma-info mb-2">
                            <div class="ma-info__label"><i class="fas fa-sticky-note mr-1"></i> Notas de saída</div>
                            <div class="ma-info__value">{{ $contratoCheckinOperacional->notes_saida }}</div>
                        </div>
                    @endif
                    @if($contratoCheckinOperacional->observacoes_saida)
                        <div class="ma-info">
                            <div class="ma-info__label"><i class="fas fa-comment-alt mr-1"></i> Observações</div>
                            <div class="ma-info__value">{{ $contratoCheckinOperacional->observacoes_saida }}</div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- FOTOS REGISTRADAS NO CHECK-IN --}}
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-camera text-primary mr-2" style="font-size: 1.1rem;"></i>
                    <h6 class="font-weight-bold mb-0" style="color: var(--ma-ink);">Fotos do Veículo no Check-in</h6>
                </div>

                <div class="row">
                    @foreach($fotosCheckin as $label => $path)
                        @php $fotoUrl = $path ? asset('storage/' . $path) : null; @endphp
                        <div class="col-6 col-md-4 col-lg-2 mb-3">
                            <div class="card h-100 shadow-sm border-light">
                                <div class="card-body p-2 text-center d-flex flex-column justify-content-between">
                                    @if($fotoUrl)
                                        <div class="position-relative overflow-hidden rounded mb-2" style="height: 110px; background: #f8fafc; cursor: pointer;"
                                             onclick="openPhotoModal('{{ $fotoUrl }}', 'Foto do Check-in - {{ $label }}')">
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

            @include('reserva.components.avarias_cliente_motorista_app', ['avariasCliente' => $avariasCliente])
        </div>
    </div>
@else
    <div class="ma-card">
        <div class="ma-card__body">
            <div class="alert alert-info mb-0 text-center py-4">
                <i class="fas fa-info-circle fa-2x mb-2 d-block text-info"></i>
                <h5>Check-in ainda não realizado</h5>
                <p class="mb-0 text-muted">O check-in operacional do veículo será liberado após a confirmação do pagamento e o aceite do termo de confirmação.</p>
            </div>
        </div>
    </div>
@endif
