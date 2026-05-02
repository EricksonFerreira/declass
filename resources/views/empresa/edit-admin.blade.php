@extends('adminlte::page')

@section('title', "Editar {$empresa->nome}")

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="card" style="border-radius:14px;overflow:hidden;box-shadow:0 2px 12px rgba(15,23,42,.05);">
            <div class="card-header py-3" style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                <h5 class="mb-0 font-weight-bold" style="font-size:.95rem;">
                    <i class="fas fa-building mr-2 text-primary"></i> {{ $empresa->nome }}
                </h5>
            </div>

            <form method="POST" action="{{ route('empresa.atualizar') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:.82rem;">Nome da empresa <span class="text-danger">*</span></label>
                                <input type="text" name="nome" class="form-control" value="{{ old('nome', $empresa->nome) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:.82rem;">Slug</label>
                                <input type="text" class="form-control" value="{{ $empresa->slug }}" readonly style="background:#f8fafc;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:.82rem;">CNPJ</label>
                                <input type="text" name="cnpj" class="form-control" value="{{ old('cnpj', $empresa->cnpj) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:.82rem;">E-mail</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $empresa->email) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:.82rem;">Telefone</label>
                                <input type="text" name="telefone" class="form-control" value="{{ old('telefone', $empresa->telefone) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:.82rem;">Dominio</label>
                                <input type="text" name="dominio" class="form-control" value="{{ old('dominio', $empresa->dominio) }}" placeholder="seudominio.com.br">
                                <small class="text-muted">Dominio personalizado para acesso ao sistema (opcional).</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="font-weight-bold mb-3" style="font-size:.85rem;"><i class="fas fa-palette mr-1"></i> Cores da Marca</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:.82rem;">Cor primaria</label>
                                <div class="input-group">
                                    <input type="color" name="cor_primaria" class="form-control form-control-color p-1" value="{{ old('cor_primaria', $empresa->cor_primaria ?? '#2563eb') }}" style="max-width:60px;height:38px;">
                                    <input type="text" class="form-control" value="{{ $empresa->cor_primaria ?? '#2563eb' }}" readonly style="background:#f8fafc;">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold" style="font-size:.82rem;">Cor secundaria</label>
                                <div class="input-group">
                                    <input type="color" name="cor_secundaria" class="form-control form-control-color p-1" value="{{ old('cor_secundaria', $empresa->cor_secundaria ?? '#4f46e5') }}" style="max-width:60px;height:38px;">
                                    <input type="text" class="form-control" value="{{ $empresa->cor_secundaria ?? '#4f46e5' }}" readonly style="background:#f8fafc;">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer text-right py-3" style="background:#f8fafc;border-top:1px solid #e2e8f0;">
                    <a href="{{ route('empresa.minha') }}" class="btn btn-secondary mr-2">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
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
.form-control-color { cursor:pointer; }
.form-control-color::-webkit-color-swatch-wrapper { padding:0; }
.form-control-color::-webkit-color-swatch { border:1px solid #e2e8f0; border-radius:6px; }
</style>
@endpush
