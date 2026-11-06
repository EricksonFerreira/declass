@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Fila de análise de pedidos de matrícula" :create-url="auth()->user()->hasPermissao('solicitacao_matricula.create') ? route('solicitacao_matricula.create') : null" :create-can="'solicitacao_matricula.create'" />
@stop

@section('content')
@php
    $statusCores = ['pendente' => 'warning', 'em_analise' => 'info', 'aprovada' => 'success', 'recusada' => 'danger', 'cancelada' => 'secondary'];
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
                        <th>Aluno</th>
                        <th>Escola / Turma</th>
                        <th>Responsável</th>
                        <th>Origem</th>
                        <th>Status</th>
                        <th>Recebida em</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solicitacoes as $s)
                        <tr>
                            <td>
                                <strong>{{ $s->nome_aluno }}</strong>
                                @if($s->data_nascimento_aluno)
                                    <br><small class="text-muted">nasc. {{ $s->data_nascimento_aluno->format('d/m/Y') }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $s->escola->nome ?? '—' }}
                                @if($s->turma)
                                    <br><small class="text-muted">{{ $s->turma->nome }}</small>
                                @elseif($s->etapa)
                                    <br><small class="text-muted">{{ $s->etapa->nome }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $s->nome_responsavel }}
                                @if($s->parentesco)
                                    <br><small class="text-muted">{{ \App\Models\SolicitacaoMatricula::PARENTESCOS[$s->parentesco] ?? $s->parentesco }}</small>
                                @endif
                            </td>
                            <td><small>{{ \App\Models\SolicitacaoMatricula::ORIGENS[$s->origem] ?? $s->origem }}</small></td>
                            <td><span class="badge badge-{{ $statusCores[$s->status] ?? 'secondary' }}">{{ $s->statusLabel() }}</span></td>
                            <td class="text-nowrap"><small>{{ $s->created_at->format('d/m/Y H:i') }}</small></td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('solicitacao_matricula.show', $s->id) }}" class="btn btn-xs btn-info" title="Detalhes"><i class="fa fa-eye"></i></a>
                                @if($s->estaPendente())
                                    @can('solicitacao_matricula.edit')
                                        <a href="{{ route('solicitacao_matricula.edit', $s->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                    @endcan
                                    @can('solicitacao_matricula.destroy')
                                        <x-delete-button :action="route('solicitacao_matricula.destroy', $s->id)" titulo="Excluir" mensagem="Excluir a solicitação de &quot;{{ $s->nome_aluno }}&quot;?" />
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-user-plus fa-2x mb-2 d-block"></i>
                                Nenhuma solicitação encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($solicitacoes->hasPages())
        <div class="card-footer">
            {{ $solicitacoes->links() }}
        </div>
    @endif
</div>
@stop
