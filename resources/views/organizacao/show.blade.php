@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$organizacao->nome" subtitle="Detalhes da organização" />
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header font-weight-bold">Dados da Organização</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nome</dt><dd class="col-sm-8">{{ $organizacao->nome }}</dd>
                    <dt class="col-sm-4">Tipo</dt><dd class="col-sm-8">{{ \App\Models\Organizacao::TIPOS[$organizacao->tipo] ?? $organizacao->tipo }}</dd>
                    <dt class="col-sm-4">CNPJ</dt><dd class="col-sm-8">{{ $organizacao->cnpj ?? '—' }}</dd>
                    <dt class="col-sm-4">E-mail</dt><dd class="col-sm-8">{{ $organizacao->email ?? '—' }}</dd>
                    <dt class="col-sm-4">Telefone</dt><dd class="col-sm-8">{{ $organizacao->telefone ?? '—' }}</dd>
                    <dt class="col-sm-4">Endereço</dt><dd class="col-sm-8">{{ $organizacao->endereco ?? '—' }}</dd>
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @if($organizacao->estaAtiva())
                            <span class="badge badge-success">Ativa</span>
                        @else
                            <span class="badge badge-secondary">Inativa</span>
                        @endif
                    </dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('organizacao.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
                @can('organizacao.edit')
                    <a href="{{ route('organizacao.edit', $organizacao->id) }}" class="btn btn-warning btn-sm">Editar</a>
                @endcan
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header font-weight-bold">Escolas da rede ({{ $organizacao->escolas->count() }})</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($organizacao->escolas as $escola)
                        <li class="list-group-item py-2">
                            <a href="{{ route('escola.show', $escola->id) }}" class="font-weight-bold">{{ $escola->nome }}</a>
                            <div class="text-muted" style="font-size:.75rem;">{{ $escola->cidade ?? '' }}{{ $escola->estado ? '/'.$escola->estado : '' }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center py-3">Nenhuma escola vinculada.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@stop
