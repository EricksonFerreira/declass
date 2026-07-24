<div class="rs-checkin-layout">

    {{-- Link para vistoria --}}
    <div class="rs-card">
        <div class="rs-card__header">
            <i class="fas fa-search rs-card__icon"></i>
            <h2>Vistoria de Check-out</h2>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap" style="gap:12px">
            @if($pagamentoEfetuado)
                <a href="{{ route('reserva.checkout.vistoria', $reserva->codigoReserva->codigo) }}" class="rs-btn rs-btn--outline">
                    <i class="fas fa-search"></i> Abrir vistoria / danos
                </a>
            @else
                <span class="rs-btn rs-btn--outline" style="opacity:.5;cursor:not-allowed;pointer-events:none" title="Pagamento necessário para acessar o checkout">
                    <i class="fas fa-lock mr-1"></i> Abrir vistoria / danos
                </span>
                <small style="color:#92400e;font-weight:600;background:#fef3c7;border:1px solid #f59e0b;border-radius:6px;padding:3px 10px;font-size:.78rem">
                    <i class="fas fa-lock mr-1"></i>Pagamento necessário
                </small>
            @endif
            @can('avaria.cobrar')
                <a href="{{ route('reserva.avarias.cobranca', $reserva->codigoReserva->codigo) }}" class="rs-btn rs-btn--outline">
                    <i class="fas fa-hand-holding-usd"></i> Debitar avarias
                </a>
            @endcan
        </div>
    </div>

    @if($reserva->contratoCheckout)
    @php
        $co = $reserva->contratoCheckout;
        $fotosCheckoutPaths = [
            'Direita' => $co->foto_direita_path ?? null,
            'Esquerda' => $co->foto_esquerda_path ?? null,
            'Frente' => $co->foto_frente_path ?? null,
            'Trás' => $co->foto_tras_path ?? null,
            'Velocímetro' => $co->foto_velocimetro_path ?? null,
            'Combustível' => $co->foto_combustivel_path ?? null,
        ];
    @endphp
    <div class="rs-card">
        <div class="rs-card__header">
            <i class="fas fa-camera rs-card__icon"></i>
            <h2>Fotos do Veículo no Check-out</h2>
        </div>
        <div class="row rs-photo-grid">
            @foreach($fotosCheckoutPaths as $label => $path)
                @php
                    $fotoUrl = null;
                    if (!empty($path)) {
                        $cleanPath = ltrim((string) $path, '/');
                        if (str_contains($cleanPath, 'http://') || str_contains($cleanPath, 'https://')) {
                            $fotoUrl = $cleanPath;
                        } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                            $fotoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cleanPath);
                        }
                    }
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

    {{-- Dados de checkout e assinatura digital --}}
    <div class="rs-card">
        <div class="rs-card__header">
            <i class="fas fa-signature rs-card__icon"></i>
            <h2>Dados da Devolução & Assinatura</h2>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="rs-info-rows">
                    <div class="rs-info-row">
                        <span>Quilometragem na Devolução</span>
                        <strong>{{ number_format((float)($co->quilometragem_devolucao ?? 0), 0, ',', '.') }} km</strong>
                    </div>
                    <div class="rs-info-row">
                        <span>Nível de Combustível</span>
                        <strong>{{ $co->nivel_combustivel_devolucao ?? '—' }}</strong>
                    </div>
                    <div class="rs-info-row">
                        <span>Data/Hora da Devolução</span>
                        <strong>
                            {{ $co->data_devolucao ? \Carbon\Carbon::parse($co->data_devolucao)->format('d/m/Y') : '—' }}
                            {{ $co->hora_devolucao ? substr($co->hora_devolucao, 0, 5) : '' }}
                        </strong>
                    </div>
                    @if(!empty($co->notas_devolucao))
                        <div class="rs-info-row" style="width:100%">
                            <span>Notas de Devolução</span>
                            <strong>{{ $co->notas_devolucao }}</strong>
                        </div>
                    @endif
                    @if(!empty($co->observacoes_devolucao))
                        <div class="rs-info-row" style="width:100%">
                            <span>Observações</span>
                            <strong>{{ $co->observacoes_devolucao }}</strong>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-6 d-flex flex-column align-items-center justify-content-center border-left">
                @if($co->assinatura)
                    @if($co->data_assinatura)
                        <div class="text-success font-weight-bold mb-2">
                            <i class="fas fa-check-circle mr-1"></i> Termo Assinado Digitalmente
                        </div>
                        <small class="text-muted mb-3">Registrado em {{ \Carbon\Carbon::parse($co->data_assinatura)->format('d/m/Y H:i') }}</small>
                    @endif
                    <a href="{{ route('reserva.verContratoCheckout', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-pdf mr-1"></i> Ver Contrato de Devolução
                    </a>
                @else
                    <div class="text-muted text-center py-4">
                        <i class="fas fa-signature fa-2x mb-2" style="color:#cbd5e1"></i>
                        <div class="mb-3">Sem assinatura registrada</div>
                        <a href="{{ route('reserva.assinarContratoCheckout', ['codigo' => $reserva->codigoReserva?->codigo ?? $reserva->id]) }}" class="btn btn-sm btn-success text-white font-weight-bold">
                            <i class="fas fa-signature mr-1"></i> Assinar Check-out / Devolução
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if(!$reserva->contratoCheckout)
        <div class="rs-card p-5 text-center text-muted" style="border: 1px dashed #cbd5e1; background: #fafbff; border-radius: 16px; margin-top: 16px;">
            <i class="fas fa-flag-checkered fa-3x mb-3" style="color:#cbd5e1"></i>
            <h5 class="font-weight-bold text-dark">Check-out não realizado</h5>
            <p class="mb-3">Os dados e fotos da devolução do veículo ficarão disponíveis aqui após a conclusão do check-out operacional pelo operador.</p>
            @if($reserva->contratoCheckin && $reserva->contratoCheckin->assinado === 'S')
                <div class="mt-3">
                    <a href="{{ route('reserva.checkout.vistoria', $reserva->codigoReserva->codigo) }}" class="btn btn-warning font-weight-bold text-white px-4 py-2" style="border-radius: 999px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);">
                        <i class="fas fa-flag-checkered mr-2"></i> Iniciar Processo de Check-out
                    </a>
                </div>
            @endif
        </div>
    @endif

</div>
