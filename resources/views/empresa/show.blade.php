@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$empresa->nome" subtitle="Tenant da plataforma" />
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header font-weight-bold">Dados do Tenant</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nome</dt><dd class="col-sm-8">{{ $empresa->nome }}</dd>
                    <dt class="col-sm-4">Slug</dt><dd class="col-sm-8"><code>{{ $empresa->slug }}</code></dd>
                    <dt class="col-sm-4">Domínio</dt><dd class="col-sm-8">{{ $empresa->dominio ?? '—' }}</dd>
                    <dt class="col-sm-4">CNPJ</dt><dd class="col-sm-8">{{ $empresa->cnpj ?? '—' }}</dd>
                    <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8">{{ $empresa->email ?? '—' }}</dd>
                    <dt class="col-sm-4">Telefone</dt><dd class="col-sm-8">{{ $empresa->telefone ?? '—' }}</dd>
                    <dt class="col-sm-4">Endereço</dt><dd class="col-sm-8">{{ $empresa->endereco ?? '—' }}</dd>
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @if($empresa->estaAtiva())
                            <span class="badge badge-success">Ativo</span>
                        @else
                            <span class="badge badge-secondary">Inativo</span>
                        @endif
                    </dd>
                    <dt class="col-sm-4">Criado em</dt><dd class="col-sm-8">{{ $empresa->created_at?->format('d/m/Y H:i') }}</dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('empresa.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
                <a href="{{ route('empresa.edit', $empresa->id) }}" class="btn btn-warning btn-sm">Editar</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header font-weight-bold">Escolas ({{ $empresa->escolas->count() }})</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($empresa->escolas as $escola)
                        <li class="list-group-item py-2">
                            <a href="{{ route('escola.show', $escola->id) }}" class="font-weight-bold">{{ $escola->nome }}</a>
                            <div class="text-muted" style="font-size:.75rem;">{{ $escola->cidade ?? '' }}{{ $escola->estado ? '/'.$escola->estado : '' }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-3">Nenhuma escola.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@stop
