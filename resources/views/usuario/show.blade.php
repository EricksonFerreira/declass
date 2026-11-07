@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$usuario->nome ?? 'Usuário'" subtitle="Conta de acesso do tenant" />
@stop

@section('content')
@php($tipo = $usuario->tipoConta())
<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header font-weight-bold">Dados da Conta</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nome</dt><dd class="col-sm-8">{{ $usuario->nome ?? '—' }}</dd>
                    <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8">{{ $usuario->email ?? '—' }}</dd>
                    <dt class="col-sm-4">Login alternativo</dt><dd class="col-sm-8">{{ $usuario->usuario ?? '—' }}</dd>
                    <dt class="col-sm-4">CPF / documento</dt><dd class="col-sm-8">{{ $usuario->numero_identificacao ?? '—' }}</dd>
                    <dt class="col-sm-4">Nascimento</dt><dd class="col-sm-8">{{ optional($usuario->data_nascimento)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-4">Telefone</dt><dd class="col-sm-8">{{ $usuario->telefone ?? '—' }}</dd>
                    <dt class="col-sm-4">Celular</dt><dd class="col-sm-8">{{ $usuario->celular ?? '—' }}</dd>
                    <dt class="col-sm-4">Cidade</dt><dd class="col-sm-8">{{ $usuario->cidade ?? '—' }}</dd>
                    <dt class="col-sm-4">Cargo / função</dt><dd class="col-sm-8">{{ $usuario->cargo ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Acesso e vínculos</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Tipo de conta</dt>
                    <dd class="col-sm-8">
                        @if($tipo === 'aluno')
                            <span class="badge badge-primary">Aluno</span>
                        @elseif($tipo === 'responsavel')
                            <span class="badge badge-info">Responsável</span>
                        @else
                            <span class="badge badge-secondary">Colaborador</span>
                        @endif
                    </dd>
                    <dt class="col-sm-4">Perfil de acesso</dt><dd class="col-sm-8">{{ $usuario->regra->name ?? '—' }}</dd>
                    @if($usuario->aluno)
                        <dt class="col-sm-4">Aluno vinculado</dt>
                        <dd class="col-sm-8"><a href="{{ route('aluno.show', $usuario->aluno->id) }}">{{ $usuario->aluno->nome }}</a></dd>
                    @elseif($usuario->responsavel)
                        <dt class="col-sm-4">Responsável vinculado</dt>
                        <dd class="col-sm-8"><a href="{{ route('responsavel.show', $usuario->responsavel->id) }}">{{ $usuario->responsavel->nome }}</a></dd>
                    @endif
                    <dt class="col-sm-4">Escolas / unidades</dt>
                    <dd class="col-sm-8">{{ $usuario->escolas->pluck('nome')->join(', ') ?: '—' }}</dd>
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @if($usuario->ativo)
                            <span class="badge badge-success">Ativo</span>
                        @else
                            <span class="badge badge-secondary">Inativo</span>
                        @endif
                    </dd>
                    <dt class="col-sm-4">Troca de senha</dt>
                    <dd class="col-sm-8">{{ $usuario->forcar_troca_senha ? 'Obrigatória no próximo login' : 'Não pendente' }}</dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('usuario.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
                @can('usuario.edit')
                    <a href="{{ route('usuario.edit', $usuario->id) }}" class="btn btn-warning btn-sm">Editar</a>
                @endcan
                @can('usuario.alterarSenha')
                    <a href="{{ route('usuario.alterarSenha', $usuario->id) }}" class="btn btn-secondary btn-sm">Senha</a>
                @endcan
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header font-weight-bold">Foto de perfil</div>
            <div class="card-body text-center">
                <img src="{{ $usuario->foto_perfil_url }}" alt="{{ $usuario->nome }}" class="img-circle" style="width:128px;height:128px;object-fit:cover;">
            </div>
        </div>
    </div>
</div>
@stop
