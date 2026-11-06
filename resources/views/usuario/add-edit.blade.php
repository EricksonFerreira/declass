@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($usuario) ? 'Editar Usuário' : 'Novo Usuário'" subtitle="Conta de acesso do tenant" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ $formRota ?? $rota ?? (isset($usuario) ? route('usuario.update', $usuario->id) : route('usuario.store')) }}">
            @csrf
            @if(($metodo ?? 'POST') !== 'POST')
                @method($metodo)
            @endif

            <h6 class="font-weight-bold">Dados pessoais</h6>
            <div class="row">
                <div class="col-md-6">
                    <x-form-input name="nome" label="Nome" :value="old('nome', $usuario->nome ?? '')" required maxlength="255" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="email" type="email" label="E-mail" :value="old('email', $usuario->email ?? '')" required maxlength="150" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="usuario" label="Login alternativo" :value="old('usuario', $usuario->usuario ?? '')" maxlength="60" placeholder="ex.: joao.silva" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <x-form-input name="numero_identificacao" label="CPF / documento" :value="old('numero_identificacao', $usuario->numero_identificacao ?? '')" maxlength="30" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="data_nascimento" type="date" label="Data de nascimento" :value="old('data_nascimento', optional($usuario->data_nascimento)->format('Y-m-d') ?? '')" />
                </div>
                <div class="col-md-3">
                    <x-form-select name="sexo" label="Sexo" :options="['M' => 'Masculino', 'F' => 'Feminino', 'NI' => 'Não informado']" :selected="old('sexo', $usuario->sexo ?? '')" placeholder="Selecionar" />
                </div>
                <div class="col-md-3">
                    <x-form-input name="cargo" label="Cargo / função" :value="old('cargo', $usuario->cargo ?? '')" maxlength="120" placeholder="ex.: Professor de Matemática" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="telefone" label="Telefone" :value="old('telefone', $usuario->telefone ?? '')" maxlength="20" placeholder="(00) 0000-0000" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="celular" label="Celular" :value="old('celular', $usuario->celular ?? '')" maxlength="20" placeholder="(00) 00000-0000" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="cidade" label="Cidade" :value="old('cidade', $usuario->cidade ?? '')" maxlength="100" />
                </div>
            </div>

            <hr>
            <h6 class="font-weight-bold">Acesso e vínculos</h6>
            <div class="row">
                <div class="col-md-4">
                    <x-form-select name="role_id" label="Perfil de acesso" :options="$tiposUsuario ?? []" :selected="old('role_id', $usuario->role_id ?? '')" required />
                </div>
                <div class="col-md-4">
                    <x-form-select name="ativo" label="Status" :options="['1' => 'Ativo', '0' => 'Inativo']" :selected="old('ativo', isset($usuario) ? ($usuario->ativo ? '1' : '0') : '1')" />
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-form-select name="escolas[]" label="Escolas / unidades" :options="$escolasOpcoes ?? []" :selected="old('escolas', $escolasSelecionadas ?? [])" multiple />
                </div>
                <div class="col-md-6">
                    <x-form-select name="organizacoes[]" label="Organizações / redes" :options="$organizacoesOpcoes ?? []" :selected="old('organizacoes', $organizacoesSelecionadas ?? [])" multiple />
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('usuario.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
@stop
