@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($organizacao) ? 'Editar Organização' : 'Nova Organização'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($organizacao) ? route('organizacao.update', $organizacao->id) : route('organizacao.store') }}">
            @csrf
            @if(isset($organizacao))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-8">
                    <x-form-input name="nome" label="Nome" :value="old('nome', $organizacao->nome ?? '')" required maxlength="255" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="tipo" label="Tipo" :options="$tipos ?? []" :selected="old('tipo', $organizacao->tipo ?? '')" required />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="cnpj" label="CNPJ" :value="old('cnpj', $organizacao->cnpj ?? '')" maxlength="18" placeholder="00.000.000/0000-00" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="email" type="email" label="E-mail" :value="old('email', $organizacao->email ?? '')" maxlength="255" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="telefone" label="Telefone" :value="old('telefone', $organizacao->telefone ?? '')" maxlength="30" placeholder="(00) 00000-0000" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-10">
                    <x-form-input name="endereco" label="Endereço" :value="old('endereco', $organizacao->endereco ?? '')" maxlength="500" />
                </div>
                <div class="col-md-2">
                    <x-form-select name="ativo" label="Status" :options="['S' => 'Ativa', 'N' => 'Inativa']" :selected="old('ativo', $organizacao->ativo ?? 'S')" />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('organizacao.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
