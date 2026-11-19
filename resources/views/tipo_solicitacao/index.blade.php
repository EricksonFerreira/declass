@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Requerimentos que as famílias escolhem no portal — cada tipo pode exigir anexo e campos extras"
        :create-url="auth()->user()->hasPermissao('tipo_solicitacao.create') ? route('tipo_solicitacao.create') : null" :create-can="'tipo_solicitacao.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .tsoli-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
    </style>
@endpush

@section('content')
@php
    $iniciais = function (string $nome): string {
        $partes = preg_split('/\s+/', trim($nome)) ?: [];
        return collect($partes)->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: '?';
    };
@endphp
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        @include('partials.ux-view-toggle', ['viewKey' => 'tipo_solicitacao_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        <div class="ux-cards tsoli-cards">
            @forelse($tipos as $tipo)
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">{{ $iniciais($tipo->nome) }}</div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__id">{{ $tipo->escola->nome ?? 'Rede toda' }}</div>
                            <div class="ux-card__title">{{ $tipo->nome }}</div>
                            <div class="ux-card__sub">{{ $tipo->solicitacoes_count }} solicitação(ões) recebida(s)</div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="ux-card__badge {{ $tipo->ativo ? 'ux-card__badge--green' : 'ux-card__badge--gray' }}">{{ $tipo->ativo ? 'Ativo' : 'Inativo' }}</span>
                            <span class="ux-card__badge {{ $tipo->exige_anexo ? 'ux-card__badge--amber' : 'ux-card__badge--gray' }}">Anexo {{ $tipo->exige_anexo ? 'obrigatório' : 'opcional' }}</span>
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        @if($tipo->descricao)
                            <div class="ux-card__meta-row ux-card__meta-row--full">
                                <span class="ux-card__meta-value">{{ \Illuminate\Support\Str::limit($tipo->descricao, 120) }}</span>
                            </div>
                        @endif
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Campos extras</span>
                            <span class="ux-card__meta-value">{{ count($tipo->campos ?? []) }}</span>
                        </div>
                        @if($tipo->campos)
                            <div class="ux-card__meta-row ux-card__meta-row--full">
                                <span class="ux-card__meta-label">Rótulos</span>
                                <span class="ux-card__meta-value">{{ collect($tipo->campos)->pluck('rotulo')->take(3)->implode(', ') }}{{ count($tipo->campos) > 3 ? '…' : '' }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="ux-card__foot">
                        @can('tipo_solicitacao.edit')
                            <a href="{{ route('tipo_solicitacao.edit', $tipo->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                        @can('tipo_solicitacao.destroy')
                            <x-delete-button :action="route('tipo_solicitacao.destroy', $tipo->id)" titulo="Excluir" mensagem="Excluir o tipo &quot;{{ $tipo->nome }}&quot;? Solicitações já feitas mantêm o nome registrado." />
                        @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-list-ul fa-2x mb-2 d-block"></i>
                    Nenhum tipo cadastrado. Cadastre o primeiro para liberar o formulário do portal.
                </div>
            @endforelse
        </div>

        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nome</th>
                        <th>Escola</th>
                        <th>Anexo</th>
                        <th>Campos extras</th>
                        <th>Solicitações</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tipos as $tipo)
                        <tr>
                            <td>
                                <strong>{{ $tipo->nome }}</strong>
                                @if($tipo->descricao)
                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($tipo->descricao, 90) }}</small>
                                @endif
                            </td>
                            <td>{{ $tipo->escola->nome ?? 'Rede toda' }}</td>
                            <td>
                                @if($tipo->exige_anexo)
                                    <span class="badge badge-warning">Obrigatório</span>
                                @else
                                    <span class="badge badge-secondary">Opcional</span>
                                @endif
                            </td>
                            <td>
                                {{ count($tipo->campos ?? []) }}
                                @if($tipo->campos)
                                    <br><small class="text-muted">{{ collect($tipo->campos)->pluck('rotulo')->take(3)->implode(', ') }}{{ count($tipo->campos) > 3 ? '…' : '' }}</small>
                                @endif
                            </td>
                            <td>{{ $tipo->solicitacoes_count }}</td>
                            <td>
                                <span class="badge badge-{{ $tipo->ativo ? 'success' : 'secondary' }}">{{ $tipo->ativo ? 'Ativo' : 'Inativo' }}</span>
                            </td>
                            <td class="text-right text-nowrap">
                                @can('tipo_solicitacao.edit')
                                    <a href="{{ route('tipo_solicitacao.edit', $tipo->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('tipo_solicitacao.destroy')
                                    <x-delete-button :action="route('tipo_solicitacao.destroy', $tipo->id)" titulo="Excluir" mensagem="Excluir o tipo &quot;{{ $tipo->nome }}&quot;? Solicitações já feitas mantêm o nome registrado." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-list-ul fa-2x mb-2 d-block"></i>
                                Nenhum tipo cadastrado. Cadastre o primeiro para liberar o formulário do portal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($tipos->hasPages())
        <div class="card-footer">
            {{ $tipos->links() }}
        </div>
    @endif
</div>
@stop
