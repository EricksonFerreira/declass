@php
    $etapaAtiva = 'onboarding.cores';
    $etapasConc = ['onboarding.empresa', 'onboarding.plano'];
@endphp

@extends('onboarding._layout')
@section('page_title', 'Identidade Visual')

@push('styles')
<style>
.cores-card { max-width:680px; }
.cor-preview-box {
    width:100%; height:80px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:.85rem;
    transition:background .2s,color .2s;
    border:1px solid #e2e8f0;
}
input[type="color"] {
    width:48px; height:38px; cursor:pointer;
    border:1px solid #e2e8f0; border-radius:8px; padding:2px;
    background:#fff;
}
input[type="color"]::-webkit-color-swatch-wrapper { padding:0; }
input[type="color"]::-webkit-color-swatch { border:1px solid #e2e8f0; border-radius:6px; }
</style>
@endpush

@section('content')
<div class="onb-card cores-card">
    <h1 class="onb-card-title">Identidade Visual</h1>
    <p class="onb-card-sub">Defina as cores da sua empresa para personalizar o sistema e o site de apresentacao.</p>

    @if (session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('onboarding.cores.salvar') }}" enctype="multipart/form-data">
        @csrf

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:1.5rem;margin-bottom:1.5rem;">
            <h6 class="font-weight-bold mb-3" style="font-size:.88rem;color:#0f172a;"><i class="fas fa-palette mr-1"></i> Cores da Marca</h6>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold mb-2" style="font-size:.82rem;">Cor de fundo <span class="text-muted font-weight-normal">(padrao: #000000)</span></label>
                    <div class="d-flex align-items-center" style="gap:12px;">
                        <input type="color" name="cor_primaria" id="corPrimaria" value="{{ $empresa->cor_primaria ?? '#000000' }}">
                        <input type="text" class="form-control form-control-sm" id="corPrimariaText" value="{{ $empresa->cor_primaria ?? '#000000' }}" readonly style="max-width:120px;background:#f1f5f9;font-family:monospace;">
                    </div>
                    <small class="text-muted">Usada como fundo do site.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="font-weight-bold mb-2" style="font-size:.82rem;">Cor da fonte <span class="text-muted font-weight-normal">(padrao: #ffffff)</span></label>
                    <div class="d-flex align-items-center" style="gap:12px;">
                        <input type="color" name="cor_secundaria" id="corSecundaria" value="{{ $empresa->cor_secundaria ?? '#ffffff' }}">
                        <input type="text" class="form-control form-control-sm" id="corSecundariaText" value="{{ $empresa->cor_secundaria ?? '#ffffff' }}" readonly style="max-width:120px;background:#f1f5f9;font-family:monospace;">
                    </div>
                    <small class="text-muted">Usada como cor do texto no site.</small>
                </div>
            </div>

            <div class="mt-3">
                <label class="font-weight-bold mb-2" style="font-size:.82rem;">Pre-visualizacao</label>
                <div class="cor-preview-box" id="previewBox" style="background:{{ $empresa->cor_primaria ?? '#000000' }};color:{{ $empresa->cor_secundaria ?? '#ffffff' }};">
                    DECLOCK — Sistema de Gestao
                </div>
            </div>
        </div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:1.5rem;margin-bottom:1.5rem;">
            <h6 class="font-weight-bold mb-3" style="font-size:.88rem;color:#0f172a;"><i class="fas fa-image mr-1"></i> Logo da Empresa</h6>
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/png,image/jpeg,image/webp">
                        <label class="custom-file-label" for="logo">Escolher arquivo...</label>
                    </div>
                    <small class="text-muted">PNG, JPG ou WEBP. Max 2MB.</small>
                </div>
                <div class="col-md-5 text-center mt-3 mt-md-0">
                    @if($empresa->logo_url)
                        <img src="{{ $empresa->logo_url }}" alt="Logo" style="max-height:50px;border-radius:6px;">
                        <p class="small text-muted mt-1 mb-0">Logo atual</p>
                    @else
                        <div style="padding:10px;">
                            <i class="fas fa-image fa-2x text-muted" style="opacity:.3;"></i>
                            <p class="small text-muted mb-0">Nenhuma logo</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;">
            <a href="{{ route('onboarding.orientacoes') }}" style="font-size:.875rem;color:#64748b;">
                <i class="fas fa-arrow-left mr-1"></i> Pular etapa
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-check mr-1"></i> Salvar e continuar
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function updatePreview() {
    var bg = document.getElementById('corPrimaria').value || '#000000';
    var text = document.getElementById('corSecundaria').value || '#ffffff';
    document.getElementById('previewBox').style.background = bg;
    document.getElementById('previewBox').style.color = text;
    document.getElementById('corPrimariaText').value = bg;
    document.getElementById('corSecundariaText').value = text;
}

document.getElementById('corPrimaria').addEventListener('input', updatePreview);
document.getElementById('corSecundaria').addEventListener('input', updatePreview);

document.getElementById('logo')?.addEventListener('change', function (e) {
    var label = this.nextElementSibling;
    if (label) label.textContent = e.target.files[0]?.name || 'Escolher arquivo...';
});
</script>
@endpush
