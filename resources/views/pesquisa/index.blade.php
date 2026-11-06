@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Pesquisas de satisfação e avaliações com as famílias" :create-url="auth()->user()->hasPermissao('pesquisa.create') ? route('pesquisa.create') : null" :create-can="'pesquisa.create'" />
@stop

@section('content')
@php
    $statusCores = ['rascunho' => 'secondary', 'aberta' => 'success', 'encerrada' => 'dark'];
@endphp
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Título</th>
                        <th>Escola</th>
                        <th>Período</th>
                        <th>Status</th>
                        <th>Perguntas</th>
                        <th>Respostas</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pesquisas as $pesquisa)
                        <tr>
                            <td>
                                <strong>{{ $pesquisa->titulo }}</strong>
                                @if($pesquisa->descricao)
                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($pesquisa->descricao, 90) }}</small>
                                @endif
                            </td>
                            <td>{{ $pesquisa->escola->nome ?? 'Rede toda' }}</td>
                            <td class="text-nowrap">
                                <small>{{ $pesquisa->data_inicio->format('d/m/Y') }} a {{ $pesquisa->data_fim->format('d/m/Y') }}</small>
                                @if($pesquisa->estaAberta())
                                    <br><span class="badge badge-success">Aberta agora</span>
                                @endif
                            </td>
                            <td><span class="badge badge-{{ $statusCores[$pesquisa->status] ?? 'secondary' }}">{{ $pesquisa->statusLabel() }}</span></td>
                            <td>{{ $pesquisa->perguntas->count() }}</td>
                            <td>{{ $pesquisa->respostas->count() }}</td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('pesquisa.resultados', $pesquisa->id) }}" class="btn btn-xs btn-info" title="Resultados"><i class="fa fa-chart-bar"></i></a>
                                @can('pesquisa.edit')
                                    <a href="{{ route('pesquisa.edit', $pesquisa->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('pesquisa.destroy')
                                    <x-delete-button :action="route('pesquisa.destroy', $pesquisa->id)" titulo="Excluir" mensagem="Excluir a pesquisa &quot;{{ $pesquisa->titulo }}&quot; e todas as respostas?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-question fa-2x mb-2 d-block"></i>
                                Nenhuma pesquisa encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($pesquisas->hasPages())
        <div class="card-footer">
            {{ $pesquisas->links() }}
        </div>
    @endif
</div>
@stop
