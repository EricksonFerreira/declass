@php
    $etapaAtiva = 'onboarding.empresa';
    $etapasConc = [];
@endphp

@extends('onboarding._layout')
@section('page_title', 'Dados da Empresa')

@section('content')
<div class="onb-card">
    <h1 class="onb-card-title">Bem-vindo ao DECLOCK! 👋</h1>
    <p class="onb-card-sub">
        Vamos configurar sua conta em poucos passos. Comece informando os dados básicos da sua empresa.
    </p>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong><i class="fas fa-exclamation-circle mr-1"></i> Corrija os erros abaixo:</strong>
            <ul style="margin:.5rem 0 0 1.2rem;">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('onboarding.empresa.salvar') }}" method="POST">
        @csrf

        <div class="form-row" style="margin-bottom:1.25rem;">
            <div>
                <label class="form-label">Nome da Empresa <span style="color:#ef4444;">*</span></label>
                <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                       value="{{ old('nome', auth()->user()->empresa?->nome ?? '') }}"
                       placeholder="Ex.: Agência Horizonte" required maxlength="200" autofocus>
                @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">CNPJ</label>
                <input type="text" name="cnpj" class="form-control @error('cnpj') is-invalid @enderror"
                       value="{{ old('cnpj', auth()->user()->empresa?->cnpj ?? '') }}"
                       placeholder="00.000.000/0000-00" maxlength="20" id="cnpjInput">
                @error('cnpj')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-row" style="margin-bottom:2rem;">
            <div>
                <label class="form-label">E-mail da Empresa</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', auth()->user()->empresa?->email ?? '') }}"
                       placeholder="contato@minhaempresa.com.br" maxlength="200">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control @error('telefone') is-invalid @enderror"
                       value="{{ old('telefone', auth()->user()->empresa?->telefone ?? '') }}"
                       placeholder="(00) 00000-0000" maxlength="30" id="telefoneInput">
                @error('telefone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="alert alert-info" style="font-size:.83rem;">
            <i class="fas fa-info-circle mr-1"></i>
            Você poderá completar endereço, logo e outras configurações depois, diretamente no painel.
        </div>

        <div style="display:flex; justify-content:flex-end; margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary btn-lg">
                Próximo: Ativar teste grátis <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Máscara CNPJ
document.getElementById('cnpjInput')?.addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').slice(0, 14);
    if (v.length > 12) v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/, '$1.$2.$3/$4-$5');
    else if (v.length > 8) v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d+)/, '$1.$2.$3/$4');
    else if (v.length > 5) v = v.replace(/^(\d{2})(\d{3})(\d+)/, '$1.$2.$3');
    else if (v.length > 2) v = v.replace(/^(\d{2})(\d+)/, '$1.$2');
    this.value = v;
});
// Máscara telefone
document.getElementById('telefoneInput')?.addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 10) v = v.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4})(\d+)/, '($1) $2-$3');
    else if (v.length > 2) v = v.replace(/^(\d{2})(\d+)/, '($1) $2');
    this.value = v;
});
</script>
@endpush
