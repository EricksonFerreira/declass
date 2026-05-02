@extends('adminlte::page')

@section('title', "Editar {$empresa->nome}")

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="col-xl-10 col-lg-11 col-md-12 px-0">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card card-empresa shadow-sm border-0">
        <div class="card-header card-header-empresa border-0">
            <div>
                <h4 class="mb-1">Editar Empresa</h4>
                <p class="mb-0 text-muted">Altere as informacoes principais da sua empresa.</p>
            </div>
        </div>

        <div class="card-body p-4">
            <form method="POST" action="{{ route('empresa.atualizar') }}">
                @csrf
                @method('PUT')

                <div class="empresa-section-title" style="font-size:.9rem;">Dados da Empresa</div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" class="form-control form-control-sm" value="{{ old('nome', $empresa->nome) }}" required>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">Slug</label>
                            <input type="text" class="form-control form-control-sm" value="{{ $empresa->slug }}" readonly style="background:#f1f5f9;">
                            <small class="text-muted">Identificador unico gerado automaticamente.</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">CNPJ</label>
                            <input type="text" name="cnpj" class="form-control form-control-sm" value="{{ old('cnpj', $empresa->cnpj) }}">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">E-mail</label>
                            <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $empresa->email) }}">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">Telefone</label>
                            <input type="text" name="telefone" class="form-control form-control-sm" value="{{ old('telefone', $empresa->telefone) }}">
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <div class="empresa-section-title" style="font-size:.9rem;">Identidade Visual</div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">Cor de fundo do site</label>
                            <div class="d-flex align-items-center" style="gap:10px;">
                                <input type="color" name="cor_primaria" class="form-control p-1" value="{{ old('cor_primaria', $empresa->cor_primaria ?? '#2563eb') }}" style="width:50px;height:38px;cursor:pointer;">
                                <input type="text" class="form-control form-control-sm" value="{{ $empresa->cor_primaria ?? '#2563eb' }}" readonly style="background:#f1f5f9;max-width:120px;">
                                <span class="small text-muted">Usada como fundo do site de apresentacao.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group form-group-empresa">
                            <label class="font-weight-bold" style="font-size:.82rem;">Cor da fonte do site</label>
                            <div class="d-flex align-items-center" style="gap:10px;">
                                <input type="color" name="cor_secundaria" class="form-control p-1" value="{{ old('cor_secundaria', $empresa->cor_secundaria ?? '#4f46e5') }}" style="width:50px;height:38px;cursor:pointer;">
                                <input type="text" class="form-control form-control-sm" value="{{ $empresa->cor_secundaria ?? '#4f46e5' }}" readonly style="background:#f1f5f9;max-width:120px;">
                                <span class="small text-muted">Usada como cor do texto no site.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center" style="gap:10px;flex-wrap:wrap;">
                    {{-- <a href="{{ route('empresa.minha') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </a> --}}
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Salvar alteracoes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
.card-empresa { border-radius:14px; overflow:hidden; }
.card-header-empresa { background:linear-gradient(135deg,#0f172a,#1e293b); color:#fff; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; padding:18px 22px; }
.card-header-empresa h4 { font-weight:700; font-size:1.05rem; }
.card-header-empresa .text-muted { color:rgba(255,255,255,.65) !important; }
.empresa-section-title { font-weight:700; font-size:.85rem; color:#0f172a; margin-bottom:1rem; padding-bottom:.5rem; border-bottom:1px solid #e2e8f0; }
.form-group-empresa { margin-bottom:1rem; }
input[type="color"] { cursor:pointer; border:1px solid #e2e8f0; border-radius:6px; padding:3px; }
input[type="color"]::-webkit-color-swatch-wrapper { padding:0; }
input[type="color"]::-webkit-color-swatch { border:none; border-radius:4px; }
</style>
@endpush
