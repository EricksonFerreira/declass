@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Fila de análise de pedidos de matrícula" :create-url="auth()->user()->hasPermissao('solicitacao_matricula.create') ? route('solicitacao_matricula.create') : null" :create-can="'solicitacao_matricula.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .sol-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .sol-cards .ux-card__codigo { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 800; letter-spacing: .08em; }
    </style>
@endpush

@section('content')
@php
    $statusCores = ['pendente' => 'warning', 'em_analise' => 'info', 'aprovada' => 'success', 'recusada' => 'danger', 'cancelada' => 'secondary'];
    $statusBadge = ['pendente' => 'ux-card__badge--amber', 'em_analise' => 'ux-card__badge--blue', 'aprovada' => 'ux-card__badge--green', 'recusada' => 'ux-card__badge--red', 'cancelada' => 'ux-card__badge--gray'];
    $iniciais = function (string $nome): string {
        $partes = preg_split('/\s+/', trim($nome)) ?: [];
        return collect($partes)->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: '?';
    };
@endphp
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        @include('partials.ux-view-toggle', ['viewKey' => 'solicitacao_matricula_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        <div class="ux-cards sol-cards">
            @forelse($solicitacoes as $s)
                <article class="ux-card">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">{{ $iniciais($s->nome_aluno) }}</div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__id ux-card__codigo">#{{ $s->codigo }}</div>
                            <div class="ux-card__title">{{ $s->nome_aluno }}</div>
                            <div class="ux-card__sub">
                                {{ $s->escola->nome ?? '—' }}
                                @if($s->turma) · {{ $s->turma->nome }}
                                @elseif($s->etapa) · {{ $s->etapa->nome }}@endif
                            </div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="ux-card__badge {{ $statusBadge[$s->status] ?? 'ux-card__badge--gray' }}">{{ $s->status_label }}</span>
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Responsável</span>
                            <span class="ux-card__meta-value">{{ $s->nome_responsavel }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Origem</span>
                            <span class="ux-card__meta-value">{{ \App\Models\SolicitacaoMatricula::ORIGENS[$s->origem] ?? $s->origem }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Recebida em</span>
                            <span class="ux-card__meta-value">{{ $s->recebida_em }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Nascimento</span>
                            <span class="ux-card__meta-value">{{ $s->nascimento_aluno_br ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        <a href="{{ route('solicitacao_matricula.show', $s->id) }}" class="btn btn-xs btn-info" title="Detalhes"><i class="fa fa-eye"></i></a>
                        @if($s->esta_pendente)
                            @can('solicitacao_matricula.edit')
                                <a href="{{ route('solicitacao_matricula.edit', $s->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                            @endcan
                            @can('solicitacao_matricula.destroy')
                                <x-delete-button :action="route('solicitacao_matricula.destroy', $s->id)" titulo="Excluir" mensagem="Excluir a solicitação de &quot;{{ $s->nome_aluno }}&quot;?" />
                            @endcan
                        @endif
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-user-plus fa-2x mb-2 d-block"></i>
                    Nenhuma solicitação encontrada.
                </div>
            @endforelse
        </div>

        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Código</th>
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
                            <td class="text-nowrap"><code class="text-dark font-weight-bold">#{{ $s->codigo }}</code></td>
                            <td>
                                <strong>{{ $s->nome_aluno }}</strong>
                                @if($s->data_nascimento_aluno)
                                    <br><small class="text-muted">nasc. {{ $s->nascimento_aluno_br }}</small>
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
                            <td><span class="badge badge-{{ $statusCores[$s->status] ?? 'secondary' }}">{{ $s->status_label }}</span></td>
                            <td class="text-nowrap"><small>{{ $s->recebida_em }}</small></td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('solicitacao_matricula.show', $s->id) }}" class="btn btn-xs btn-info" title="Detalhes"><i class="fa fa-eye"></i></a>
                                @if($s->esta_pendente)
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
                            <td colspan="8" class="text-center text-muted py-4">
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
