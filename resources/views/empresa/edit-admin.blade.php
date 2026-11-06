@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="Editar: {$empresa->nome}" subtitle="Dados da minha organização" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('empresa.atualizar') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8">
                    <x-form-input name="nome" label="Nome" :value="old('nome', $empresa->nome)" required maxlength="200" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="cnpj" label="CNPJ" :value="old('cnpj', $empresa->cnpj)" maxlength="20" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="email" type="email" label="E-mail" :value="old('email', $empresa->email)" maxlength="200" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="telefone" label="Telefone" :value="old('telefone', $empresa->telefone)" maxlength="30" />
                </div>
                <div class="col-md-4">
                    <label class="text-xs">Logo</label>
                    <input type="file" name="logo" class="form-control form-control-sm" accept="image/png,image/jpeg,image/jpg,image/webp">
                    @if($empresa->logo_url)
                        <img src="{{ $empresa->logo_url }}" alt="Logo" style="max-height:40px;margin-top:.4rem;">
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <x-form-input name="endereco" label="Endereço" :value="old('endereco', $empresa->endereco)" maxlength="500" />
                </div>
            </div>

            <hr>
            <h6 class="font-weight-bold">Cores da marca</h6>
            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="cor_primaria" type="color" label="Cor primária" :value="old('cor_primaria', $empresa->cor_primaria ?? '#1e3a8a')" />
                </div>
                <div class="col-md-6">
                    <x-form-input name="cor_secundaria" type="color" label="Cor secundária" :value="old('cor_secundaria', $empresa->cor_secundaria ?? '#f59e0b')" />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('empresa.minha') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
