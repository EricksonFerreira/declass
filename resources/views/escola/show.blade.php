@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$escola->nome" subtitle="Detalhes da escola" />
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header font-weight-bold">Dados da Escola</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nome</dt><dd class="col-sm-8">{{ $escola->nome }}</dd>
                    <dt class="col-sm-4">Slug</dt><dd class="col-sm-8">{{ $escola->slug }}</dd>
                    <dt class="col-sm-4">CNPJ</dt><dd class="col-sm-8">{{ $escola->cnpj ?? '—' }}</dd>
                    <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8">{{ $escola->email ?? '—' }}</dd>
                    <dt class="col-sm-4">Telefone</dt><dd class="col-sm-8">{{ $escola->telefone ?? '—' }}</dd>
                    <dt class="col-sm-4">Endereço</dt><dd class="col-sm-8">{{ $escola->endereco ?? '—' }}</dd>
                    <dt class="col-sm-4">Cidade/UF</dt><dd class="col-sm-8">{{ trim(($escola->cidade ?? '').($escola->estado ? '/'.$escola->estado : '')) ?: '—' }}</dd>
                    <dt class="col-sm-4">Capacidade</dt><dd class="col-sm-8">{{ $escola->capacidade_alunos ?? '—' }} alunos</dd>
                    <dt class="col-sm-4">Turno padrão</dt><dd class="col-sm-8">{{ \App\Models\Escola::TURNOS[$escola->turno_padrao] ?? '—' }}</dd>
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @if($escola->esta_ativa)
                            <span class="badge badge-success">Ativa</span>
                        @else
                            <span class="badge badge-secondary">Inativa</span>
                        @endif
                    </dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('escola.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
                @can('escola.edit')
                    <a href="{{ route('escola.edit', $escola->id) }}" class="btn btn-warning btn-sm">Editar</a>
                @endcan
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header font-weight-bold">Identidade Visual</div>
            <div class="card-body">
                <div class="mb-3 text-center">
                    @if($escola->logo_url)
                        <img src="{{ $escola->logo_url }}" alt="Logo da escola" style="max-height:64px;max-width:100%;">
                    @else
                        <span class="text-muted small">Sem logo cadastrada</span>
                    @endif
                </div>
                <div class="d-flex align-items-center mb-2">
                    <span class="mr-2">Cor primária:</span>
                    <span class="badge" style="background:{{ $escola->cor_primaria ?? '#ccc' }};width:24px;height:24px;border-radius:4px;display:inline-block;"></span>
                    <code class="ml-2">{{ $escola->cor_primaria ?? '—' }}</code>
                </div>
                <div class="d-flex align-items-center">
                    <span class="mr-2">Cor secundária:</span>
                    <span class="badge" style="background:{{ $escola->cor_secundaria ?? '#ccc' }};width:24px;height:24px;border-radius:4px;display:inline-block;"></span>
                    <code class="ml-2">{{ $escola->cor_secundaria ?? '—' }}</code>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Matrícula online</div>
            <div class="card-body">
                @if($escola->esta_ativa)
                    <p class="small text-muted mb-2">Link público do formulário de solicitação de matrícula:</p>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="link-matricula" readonly
                               value="{{ url('/matricula/'.$escola->empresa->slug.'/'.$escola->slug) }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" title="Copiar"
                                    onclick="navigator.clipboard.writeText(document.getElementById('link-matricula').value)">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <a href="{{ url('/matricula/'.$escola->empresa->slug.'/'.$escola->slug) }}" target="_blank" class="btn btn-sm btn-primary mt-2">
                        Abrir formulário
                    </a>
                @else
                    <p class="small text-muted mb-0">Escola inativa — o formulário público está desabilitado.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Usuários vinculados ({{ $escola->usuarios->count() }})</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($escola->usuarios as $usuario)
                        <li class="list-group-item py-2">
                            <div class="font-weight-bold">{{ $usuario->nome }}</div>
                            <small class="text-muted">{{ $usuario->email }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-3">Nenhum usuário vinculado.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@stop
