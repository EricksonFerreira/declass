@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $codigoReserva = $registro->codigoReserva?->codigo ?? ('MAP-' . str_pad((string) $registro->id, 6, '0', STR_PAD_LEFT));
    $clienteNome = $registro->user?->nome ?? $registro->user?->name ?? 'Cliente';
    $veiculo = $registro->veiculo;
    $plano = $registro->planoMotoristaApp;
    $inicio = $registro->data_inicio ? \Carbon\Carbon::parse($registro->data_inicio)->format('d/m/Y H:i') : '-';
    $fim = $registro->data_fim ? \Carbon\Carbon::parse($registro->data_fim)->format('d/m/Y H:i') : '-';
    $checkoutFeito = !empty($registro->checkout_realizado_em);
    $niveis = ['Reserva','1/8','1/4','3/8','1/2','5/8','3/4','7/8','Cheio'];
    $checkoutDocs = data_get($registro->documentos_json ?? [], 'checkout', []);
@endphp

<style>
    .ci-card { background:#fff; border:1px solid #dbe4ee; border-radius:22px; overflow:hidden; box-shadow:0 16px 42px rgba(15,23,42,.08); }
    .ci-card-header { background:linear-gradient(135deg,#0f172a,#173d63 55%,#2563eb); color:#fff; padding:24px 26px; display:flex; justify-content:space-between; gap:18px; flex-wrap:wrap; }
    .ci-header-left { display:flex; align-items:center; gap:16px; }
    .ci-header-icon { width:56px; height:56px; border-radius:18px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,.13); font-size:1.45rem; }
    .ci-header-eyebrow { font-size:.72rem; letter-spacing:.18em; text-transform:uppercase; font-weight:900; opacity:.78; }
    .ci-header-title { margin:2px 0 4px; font-size:1.45rem; font-weight:900; }
    .ci-header-sub { margin:0; color:rgba(255,255,255,.82); }
    .ci-header-right { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .ci-date-badge, .ci-btn-back { border-radius:999px; padding:9px 13px; display:inline-flex; align-items:center; gap:8px; font-weight:800; background:rgba(255,255,255,.12); color:#fff; border:1px solid rgba(255,255,255,.18); text-decoration:none; }
    .ci-body { padding:22px; }
    .step-indicator { display:flex; align-items:center; gap:10px; margin-bottom:20px; overflow:auto; }
    .step-btn { border:0; border-radius:999px; background:#f1f5f9; color:#64748b; padding:9px 13px; display:inline-flex; align-items:center; gap:8px; font-weight:900; white-space:nowrap; }
    .step-btn.active { background:#2563eb; color:#fff; }
    .step-btn.done { background:#ecfdf5; color:#047857; }
    .step-num { width:24px; height:24px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,.22); }
    .step-divider { width:34px; height:2px; background:#dbe4ee; flex:0 0 auto; }
    .rw-step-content-header { border:1px solid #dbeafe; border-radius:18px; padding:16px; background:linear-gradient(135deg,#f8fbff,#eff6ff); display:flex; gap:12px; align-items:flex-start; margin-bottom:16px; }
    .rw-step-content-icon { width:44px; height:44px; border-radius:14px; display:flex; align-items:center; justify-content:center; background:#dbeafe; color:#1d4ed8; font-size:1.1rem; }
    .rw-step-content-header h5 { margin:0; font-weight:900; color:#0f172a; }
    .rw-step-content-header p { margin:3px 0 0; color:#64748b; }
    .ci-block { border:1px solid #e2e8f0; border-radius:18px; padding:16px; margin-bottom:16px; background:#fff; }
    .ci-block-label { display:flex; align-items:flex-start; gap:10px; margin-bottom:14px; color:#0f172a; }
    .ci-block-label i { width:38px; height:38px; border-radius:12px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; }
    .ci-block-label strong { display:block; font-size:1rem; }
    .ci-block-label span { display:block; color:#64748b; font-size:.84rem; }
    .ci-info-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .ci-info { background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:12px; }
    .ci-info__label { color:#64748b; font-size:.68rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
    .ci-info__value { color:#0f172a; font-weight:900; margin-top:3px; }
    .ci-btn-create { border:0; border-radius:999px; padding:11px 18px; display:inline-flex; gap:8px; align-items:center; font-weight:900; background:#f59e0b; color:#fff; }
    .ci-btn-secondary { border:1px solid #dbe4ee; border-radius:999px; padding:11px 18px; display:inline-flex; gap:8px; align-items:center; font-weight:900; background:#fff; color:#0f172a; text-decoration:none; }
    .ci-photo-card { border:1px solid #e2e8f0; border-radius:16px; padding:12px; background:#f8fafc; margin-bottom:12px; }
    .ci-photo-card label { font-weight:900; color:#0f172a; font-size:.86rem; }
    .ci-photo-preview { margin-top:10px; min-height:130px; border:1px dashed #cbd5e1; border-radius:12px; background:#fff; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .ci-photo-preview img { width:100%; max-height:170px; object-fit:cover; }
    .ci-photo-preview__placeholder { color:#94a3b8; font-weight:800; }
    .ci-hint { color:#64748b; font-size:.76rem; }
    .req { color:#dc2626; }
    @media (max-width: 992px) { .ci-info-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width: 768px) { .ci-card-header, .ci-header-left, .ci-header-right { align-items:flex-start; flex-direction:column; } .ci-info-grid { grid-template-columns:1fr; } }
</style>

<div class="col-12 px-0">
    <div class="ci-card">
        <div class="ci-card-header">
            <div class="ci-header-left">
                <div class="ci-header-icon"><i class="fas fa-undo-alt"></i></div>
                <div>
                    <div class="ci-header-eyebrow">Check-out / Devolucao</div>
                    <h4 class="ci-header-title">Reserva {{ $codigoReserva }}</h4>
                    <p class="ci-header-sub">
                        <i class="fas fa-user mr-1"></i>{{ $clienteNome }}
                        &nbsp;&middot;&nbsp;
                        <i class="fas fa-car mr-1"></i>{{ $veiculo?->nome ?? 'Veiculo vinculado' }}
                    </p>
                </div>
            </div>
            <div class="ci-header-right">
                <div class="ci-date-badge"><i class="fas fa-calendar-alt"></i><span>{{ $inicio }} &rarr; {{ $fim }}</span></div>
                <a href="{{ route('reserva.show', $codigoReserva) }}" class="ci-btn-back"><i class="fas fa-arrow-left"></i><span>Voltar</span></a>
            </div>
        </div>

        <div class="ci-body">
            <div class="step-indicator" role="tablist">
                <button type="button" class="step-btn done"><span class="step-num">1</span><span><i class="fas fa-clipboard-check"></i> Check-in</span></button>
                <div class="step-divider"></div>
                <button type="button" class="step-btn active"><span class="step-num">2</span><span><i class="fas fa-car-side"></i> Dados da Devolucao</span></button>
                <div class="step-divider"></div>
                <button type="button" class="step-btn {{ $checkoutFeito ? 'done' : 'active' }}"><span class="step-num">3</span><span><i class="fas fa-flag-checkered"></i> Finalizacao</span></button>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <section class="wizard-step active">
                <div class="rw-step-content-header">
                    <div class="rw-step-content-icon"><i class="fas fa-car-side"></i></div>
                    <div>
                        <h5>Dados da Devolucao</h5>
                        <p>Confira o veiculo em uso e registre quilometragem e combustivel finais.</p>
                    </div>
                </div>

                <div class="ci-block">
                    <div class="ci-block-label">
                        <i class="fas fa-car"></i>
                        <div>
                            <strong>Veiculo vinculado</strong>
                            <span>Referencia do check-in desta reserva motorista APP.</span>
                        </div>
                    </div>
                    <div class="ci-info-grid">
                        <div class="ci-info"><div class="ci-info__label">Veiculo</div><div class="ci-info__value">{{ $veiculo?->nome ?? '-' }}</div></div>
                        <div class="ci-info"><div class="ci-info__label">Placa</div><div class="ci-info__value">{{ $veiculo?->placa_licenca ?? '-' }}</div></div>
                        <div class="ci-info"><div class="ci-info__label">Km saida</div><div class="ci-info__value">{{ number_format((int) ($registro->quilometragem_inicial ?? 0), 0, ',', '.') }} km</div></div>
                        <div class="ci-info"><div class="ci-info__label">Combustivel saida</div><div class="ci-info__value">{{ $registro->nivel_combustivel_inicial ?? '-' }}</div></div>
                        <div class="ci-info"><div class="ci-info__label">Check-in em</div><div class="ci-info__value">{{ $registro->checkin_realizado_em ? \Carbon\Carbon::parse($registro->checkin_realizado_em)->format('d/m/Y H:i') : '-' }}</div></div>
                        <div class="ci-info"><div class="ci-info__label">Plano</div><div class="ci-info__value">{{ $plano?->nome ?? '-' }}</div></div>
                        <div class="ci-info"><div class="ci-info__label">Inicio</div><div class="ci-info__value">{{ $inicio }}</div></div>
                        <div class="ci-info"><div class="ci-info__label">Fim</div><div class="ci-info__value">{{ $fim }}</div></div>
                    </div>
                </div>

                <div class="ci-block">
                    <div class="ci-block-label">
                        <i class="fas fa-tachometer-alt"></i>
                        <div>
                            <strong>Condicao na devolucao</strong>
                            <span>Informe os dados finais para encerrar a locacao.</span>
                        </div>
                    </div>

                    @if($checkoutFeito)
                        <div class="alert alert-success mb-3">
                            <i class="fas fa-check-circle mr-1"></i>
                            Check-out registrado em {{ \Carbon\Carbon::parse($registro->checkout_realizado_em)->format('d/m/Y H:i') }}.
                        </div>
                        <div class="ci-info-grid">
                            <div class="ci-info"><div class="ci-info__label">Km final</div><div class="ci-info__value">{{ number_format((int) ($registro->quilometragem_final ?? 0), 0, ',', '.') }} km</div></div>
                            <div class="ci-info"><div class="ci-info__label">Combustivel final</div><div class="ci-info__value">{{ $registro->nivel_combustivel_final ?? '-' }}</div></div>
                        </div>
                    @else
                        <form action="{{ route('reserva.checkout.vistoria', $codigoReserva) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="font-weight-bold">Quilometragem final <span class="req">*</span></label>
                                    <input type="number" name="quilometragem_final" class="form-control" min="{{ (int) ($registro->quilometragem_inicial ?? 0) }}" step="1" value="{{ old('quilometragem_final', $registro->quilometragem_final ?? '') }}" required>
                                    <small class="text-muted">Minimo: {{ number_format((int) ($registro->quilometragem_inicial ?? 0), 0, ',', '.') }} km</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="font-weight-bold">Nivel de combustivel final <span class="req">*</span></label>
                                    <select name="nivel_combustivel_final" class="form-control" required>
                                        <option value="">Selecione</option>
                                        @foreach($niveis as $nivel)
                                            <option value="{{ $nivel }}" @selected(old('nivel_combustivel_final', $registro->nivel_combustivel_final ?? '') === $nivel)>{{ $nivel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="ci-block">
                                <div class="ci-block-label">
                                    <i class="fas fa-images"></i>
                                    <div>
                                        <strong>Fotos da Devolucao</strong>
                                        <span>Direita, esquerda, frente, tras, velocimetro e combustivel, igual ao check-out da reserva.</span>
                                    </div>
                                </div>
                                @php
                                    $fotosCheckout = [
                                        ['id' => 'foto_direita_co', 'label' => 'Direita'],
                                        ['id' => 'foto_esquerda_co', 'label' => 'Esquerda'],
                                        ['id' => 'foto_frente_co', 'label' => 'Frente'],
                                        ['id' => 'foto_tras_co', 'label' => 'Tras'],
                                        ['id' => 'foto_velocimetro_co', 'label' => 'Velocimetro'],
                                        ['id' => 'foto_combustivel_co', 'label' => 'Medidor de Combustivel'],
                                    ];
                                @endphp
                                <div class="row">
                                    @foreach($fotosCheckout as $foto)
                                        <div class="col-md-4">
                                            <div class="ci-photo-card">
                                                <label for="{{ $foto['id'] }}">{{ $foto['label'] }}</label>
                                                <input type="file" class="form-control form-control-sm js-photo-input" id="{{ $foto['id'] }}" name="{{ $foto['id'] }}" accept="image/*">
                                                <div class="ci-photo-preview" data-preview-for="{{ $foto['id'] }}">
                                                    <span class="ci-photo-preview__placeholder"><i class="far fa-image mr-1"></i>Sem imagem</span>
                                                </div>
                                                <small class="ci-hint">Opcional. Max 8MB. Formatos: JPG/PNG/WEBP.</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="ci-block">
                                <div class="ci-block-label">
                                    <i class="fas fa-pen-alt"></i>
                                    <div>
                                        <strong>Notas e Observacoes na Devolucao</strong>
                                        <span>Informacoes registradas junto do check-out.</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="notas_devolucao">Notas na Devolucao</label>
                                        <textarea class="form-control form-control-sm" id="notas_devolucao" name="notas_devolucao" rows="5">{{ old('notas_devolucao', data_get($checkoutDocs, 'notas_devolucao', '')) }}</textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="observacoes_devolucao">Observacoes na Devolucao</label>
                                        <textarea class="form-control form-control-sm" id="observacoes_devolucao" name="observacoes_devolucao" rows="5">{{ old('observacoes_devolucao', data_get($checkoutDocs, 'observacoes_devolucao', '')) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="ci-btn-create">
                                <i class="fas fa-check"></i>
                                Registrar check-out
                            </button>
                        </form>
                    @endif
                </div>

                <div class="ci-block mb-0">
                    <div class="ci-block-label">
                        <i class="fas fa-file-contract"></i>
                        <div>
                            <strong>Contrato</strong>
                            <span>Consulte o contrato assinado antes de encerrar a locacao se necessario.</span>
                        </div>
                    </div>
                    <a href="{{ route('reserva.minhas.motorista-app.ver-contrato', ['id' => $registro->id]) }}" target="_blank" class="ci-btn-secondary">
                        <i class="fas fa-eye"></i> Ver contrato assinado
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>
<script>
document.querySelectorAll('.js-photo-input').forEach(function (input) {
    input.addEventListener('change', function () {
        var preview = document.querySelector('[data-preview-for="' + input.id + '"]');
        var file = input.files && input.files[0];
        if (!preview || !file) return;
        var reader = new FileReader();
        reader.onload = function (event) {
            preview.innerHTML = '<img src="' + event.target.result + '" alt="preview">';
        };
        reader.readAsDataURL(file);
    });
});
</script>
@stop
