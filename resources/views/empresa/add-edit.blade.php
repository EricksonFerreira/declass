@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($empresa) ? 'Editar Tenant' : 'Novo Tenant'" subtitle="Cliente independente da plataforma" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $rota ?? (isset($empresa) ? route('empresa.update', $empresa->id) : route('empresa.store')) }}" enctype="multipart/form-data">
            @csrf
            @if(($metodo ?? 'POST') !== 'POST')
                @method($metodo)
            @endif

            <div class="row">
                <div class="col-md-8">
                    <x-form-input name="nome" label="Nome" :value="old('nome', $empresa->nome ?? '')" required maxlength="255" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="slug" label="Slug" :value="old('slug', $empresa->slug ?? '')" required maxlength="255" placeholder="minha-escola" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="cnpj" label="CNPJ" :value="old('cnpj', $empresa->cnpj ?? '')" maxlength="18" placeholder="00.000.000/0000-00" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="email" type="email" label="E-mail" :value="old('email', $empresa->email ?? '')" maxlength="255" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="telefone" label="Telefone" :value="old('telefone', $empresa->telefone ?? '')" maxlength="30" placeholder="(00) 00000-0000" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <x-form-input name="endereco" label="Endereço" :value="old('endereco', $empresa->endereco ?? '')" maxlength="500" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="dominio" label="Domínio personalizado" :value="old('dominio', $empresa->dominio ?? '')" maxlength="255" placeholder="escola.exemplo.com" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="ativo" label="Status" :options="$status ?? ['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $empresa->ativo ?? 'S')" />
                </div>
                <div class="col-md-4">
                    <label class="text-xs">Logo</label>
                    <input type="file" name="logo" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp">
                    @if(!empty($empresa->logo_url))
                        <img src="{{ $empresa->logo_url }}" alt="Logo" style="max-height:40px;margin-top:.4rem;">
                    @endif
                </div>
            </div>

            <hr>
            <h6 class="font-weight-bold">Identidade visual</h6>
            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="cor_primaria" type="color" label="Cor primária" :value="old('cor_primaria', $empresa->cor_primaria ?? '#1e3a8a')" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="cor_secundaria" type="color" label="Cor secundária" :value="old('cor_secundaria', $empresa->cor_secundaria ?? '#f59e0b')" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="cor_terciaria" type="color" label="Cor terciária" :value="old('cor_terciaria', $empresa->cor_terciaria ?? '#64748b')" />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('empresa.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
