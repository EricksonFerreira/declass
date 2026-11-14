@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$usuario->nome ?? 'Usuário'" subtitle="Conta de acesso do tenant" />
@stop

@section('content')
@php $tipo = $usuario->tipo_conta; @endphp
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

@if($permissoesGrupos)
<div class="card card-outline card-warning">
    <div class="card-header">
        <span class="font-weight-bold"><i class="fa fa-key mr-1"></i> Permissões de acesso</span>
        <span class="badge badge-danger ml-2">somente superadmin</span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('usuario.permissoes', $usuario->id) }}">
            @csrf
            @method('PUT')
            <div class="alert alert-light border small mb-3">
                <i class="fa fa-info-circle mr-1"></i>
                <strong>Herdar do perfil</strong> mantém o padrão do perfil <em>{{ $usuario->regra->nome ?? '—' }}</em>;
                <strong class="text-success">Permitir</strong> concede a tela/ação mesmo fora do perfil;
                <strong class="text-danger">Negar</strong> remove mesmo que o perfil conceda.
            </div>
            <div class="row">
                @foreach($permissoesGrupos as $modulo => $nomes)
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-2 h-100">
                            <div class="font-weight-bold small text-uppercase text-muted mb-1">
                                {{ str_replace('_', ' ', $modulo) }}
                            </div>
                            @foreach($nomes as $nome)
                                @php
                                    $acao = explode('.', $nome, 2)[1] ?? $nome;
                                    $estado = in_array($nome, $permEstado['permitir']) ? 'permitir'
                                        : (in_array($nome, $permEstado['negar']) ? 'negar' : 'herdar');
                                    $noPerfil = $permPerfil->contains($nome);
                                @endphp
                                <div class="d-flex align-items-center mb-1" style="gap:.4rem;">
                                    <span class="small flex-grow-1 {{ $noPerfil ? '' : 'text-muted' }}" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $nome }}">
                                        {{ $acao }}
                                        @if($noPerfil)
                                            <i class="fa fa-user-check text-muted" title="vem do perfil" style="font-size:.65rem;"></i>
                                        @endif
                                    </span>
                                    <select name="perm[{{ $nome }}]" class="form-control form-control-sm" style="width:6.2rem;font-size:.72rem;">
                                        <option value="herdar" {{ $estado === 'herdar' ? 'selected' : '' }}>Herdar</option>
                                        <option value="permitir" {{ $estado === 'permitir' ? 'selected' : '' }}>Permitir</option>
                                        <option value="negar" {{ $estado === 'negar' ? 'selected' : '' }}>Negar</option>
                                    </select>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="submit" class="btn btn-warning btn-sm"><i class="fa fa-save mr-1"></i> Salvar permissões diretas</button>
        </form>
    </div>
</div>
@endif
@stop
