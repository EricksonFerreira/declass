@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Bimestres, trimestres ou semestres de cada ano letivo" :create-url="auth()->user()->hasPermissao('periodo_letivo.create') ? route('periodo_letivo.create') : null" :create-can="'periodo_letivo.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .plt-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px; padding: 8px 6px 12px; }
        .plt-cards .ux-card { border-radius: 12px; box-shadow: 0 1px 4px rgba(11,18,32,.05); cursor: pointer; }
        .plt-cards .ux-card:hover { transform: none; box-shadow: 0 4px 14px rgba(11,18,32,.10); }
        .plt-cards .ux-card__accent { display: none; }
        .plt-cards .ux-card__head { padding: 9px 11px 7px; gap: 8px; }
        .plt-cards .ux-card__avatar { width: 34px; height: 34px; border-radius: 8px; font-size: .72rem; }
        .plt-cards .ux-card__title { font-size: .8rem; white-space: normal; }
        .plt-cards .ux-card__sub { font-size: .68rem; }
        .plt-cards .ux-card__badges { flex-direction: row; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .plt-cards .ux-card__badge { font-size: .6rem; padding: 3px 6px; }
        .plt-cards .ux-card__meta { padding: 7px 11px 5px; gap: 5px 10px; }
        .plt-cards .ux-card__meta-label { font-size: .6rem; }
        .plt-cards .ux-card__meta-value { font-size: .74rem; }
        .plt-cards .ux-card__foot { padding: 7px 11px 9px; }
        .plt-cards .ux-card__foot .btn { padding: .15rem .45rem; font-size: .72rem; }
        .plt-cards .ux-card--muted { opacity: .6; }
        .plt-cards .ux-card--muted:hover { opacity: .9; }
        tr.plt-item { cursor: pointer; }

        .plt-modal { border: none; border-radius: 16px; overflow: hidden; }
        .plt-modal__bar { height: 5px; background: #64748b; }
        .plt-meta { display: flex; gap: 14px; flex-wrap: wrap; font-size: .78rem; color: #64748b; margin-top: 3px; }
        .plt-meta i { color: #94a3b8; margin-right: 3px; }
        .plt-sec { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; margin: 14px 0 5px; }
        .plt-sec i { margin-right: 4px; }
    </style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        @include('partials.ux-view-toggle', ['viewKey' => 'periodo_letivo_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        <div class="ux-cards plt-cards">
            @forelse($periodos as $periodo)
                @php $ativo = $periodo->estaAtivo(); @endphp
                <article class="ux-card plt-item {{ $ativo ? '' : 'ux-card--muted' }}" data-plt
                    data-nome="{{ $periodo->nome }}"
                    data-ordem="{{ $periodo->ordem }}º"
                    data-ano="{{ $periodo->anoLetivo->ano ?? '—' }}"
                    data-escola="{{ $periodo->escola->nome ?? '—' }}"
                    data-periodicidade="{{ \App\Models\AnoLetivo::PERIODICIDADES[$periodo->anoLetivo->periodicidade ?? ''] ?? '—' }}"
                    data-inicio="{{ optional($periodo->data_inicio)->format('d/m/Y') ?? '—' }}"
                    data-fim="{{ optional($periodo->data_fim)->format('d/m/Y') ?? '—' }}"
                    data-status="{{ $ativo ? 'Ativo' : 'Inativo' }}"
                    data-status-key="{{ $ativo ? 'S' : 'N' }}"
                    data-show="{{ route('periodo_letivo.show', $periodo->id) }}"
                    data-edit="{{ route('periodo_letivo.edit', $periodo->id) }}">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">{{ $periodo->ordem }}º</div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">{{ $periodo->nome }}</div>
                            <div class="ux-card__sub">
                                {{ $periodo->anoLetivo->ano ?? '—' }} · {{ $periodo->escola->nome ?? '—' }}
                            </div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="ux-card__badge {{ $ativo ? 'ux-card__badge--green' : 'ux-card__badge--gray' }}">
                                {{ $ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Início</span>
                            <span class="ux-card__meta-value">{{ optional($periodo->data_inicio)->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Fim</span>
                            <span class="ux-card__meta-value">{{ optional($periodo->data_fim)->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row ux-card__meta-row--full">
                            <span class="ux-card__meta-label">Periodicidade</span>
                            <span class="ux-card__meta-value">{{ \App\Models\AnoLetivo::PERIODICIDADES[$periodo->anoLetivo->periodicidade ?? ''] ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        @can('periodo_letivo.edit')
                            <a href="{{ route('periodo_letivo.edit', $periodo->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                        @can('periodo_letivo.destroy')
                            <x-delete-button :action="route('periodo_letivo.destroy', $periodo->id)" titulo="Excluir" mensagem="Excluir o período &quot;{{ $periodo->nome }}&quot;?" />
                        @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-calendar-week fa-2x mb-2 d-block"></i>
                    Nenhum período letivo cadastrado.
                </div>
            @endforelse
        </div>

        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Ordem</th>
                        <th>Período</th>
                        <th>Ano letivo</th>
                        <th>Escola</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodos as $periodo)
                        @php $ativo = $periodo->estaAtivo(); @endphp
                        <tr class="plt-item" data-plt
                            data-nome="{{ $periodo->nome }}"
                            data-ordem="{{ $periodo->ordem }}º"
                            data-ano="{{ $periodo->anoLetivo->ano ?? '—' }}"
                            data-escola="{{ $periodo->escola->nome ?? '—' }}"
                            data-periodicidade="{{ \App\Models\AnoLetivo::PERIODICIDADES[$periodo->anoLetivo->periodicidade ?? ''] ?? '—' }}"
                            data-inicio="{{ optional($periodo->data_inicio)->format('d/m/Y') ?? '—' }}"
                            data-fim="{{ optional($periodo->data_fim)->format('d/m/Y') ?? '—' }}"
                            data-status="{{ $ativo ? 'Ativo' : 'Inativo' }}"
                            data-status-key="{{ $ativo ? 'S' : 'N' }}"
                            data-show="{{ route('periodo_letivo.show', $periodo->id) }}"
                            data-edit="{{ route('periodo_letivo.edit', $periodo->id) }}">
                            <td class="text-center font-weight-bold">{{ $periodo->ordem }}º</td>
                            <td class="font-weight-bold">{{ $periodo->nome }}</td>
                            <td>{{ $periodo->anoLetivo->ano ?? '—' }}</td>
                            <td>{{ $periodo->escola->nome ?? '—' }}</td>
                            <td class="text-nowrap">{{ optional($periodo->data_inicio)->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-nowrap">{{ optional($periodo->data_fim)->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if($ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('periodo_letivo.show')
                                    <a href="{{ route('periodo_letivo.show', $periodo->id) }}" class="btn btn-xs btn-info" title="Detalhes"><i class="fa fa-eye"></i></a>
                                @endcan
                                @can('periodo_letivo.edit')
                                    <a href="{{ route('periodo_letivo.edit', $periodo->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('periodo_letivo.destroy')
                                    <x-delete-button :action="route('periodo_letivo.destroy', $periodo->id)" titulo="Excluir" mensagem="Excluir o período &quot;{{ $periodo->nome }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-week fa-2x mb-2 d-block"></i>
                                Nenhum período letivo cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($periodos->hasPages())
        <div class="card-footer">
            {{ $periodos->links() }}
        </div>
    @endif
</div>

{{-- Modal de detalhes do periodo (compartilhado por cards e tabela) --}}
<div class="modal fade" id="modalPeriodo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content plt-modal">
            <div class="plt-modal__bar" id="pltBar"></div>
            <div class="modal-header border-0 pb-1">
                <div class="pr-2">
                    <div class="d-flex align-items-center flex-wrap" style="gap:.35rem;">
                        <span class="badge" id="pltStatus"></span>
                    </div>
                    <h5 class="modal-title font-weight-bold mt-2 mb-0" id="pltNome"></h5>
                    <div class="plt-meta">
                        <span><i class="fas fa-calendar-alt"></i><span id="pltAno"></span></span>
                        <span><i class="fas fa-school"></i><span id="pltEscola"></span></span>
                    </div>
                </div>
                <button type="button" class="close align-self-start" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body pt-2">
                <div class="plt-sec"><i class="far fa-calendar"></i>Período letivo</div>
                <dl class="row mb-0" style="font-size:.85rem;">
                    <dt class="col-sm-4">Ordem</dt><dd class="col-sm-8" id="pltOrdem"></dd>
                    <dt class="col-sm-4">Início</dt><dd class="col-sm-8" id="pltInicio"></dd>
                    <dt class="col-sm-4">Término</dt><dd class="col-sm-8" id="pltFim"></dd>
                    <dt class="col-sm-4">Periodicidade</dt><dd class="col-sm-8" id="pltPeriodicidade"></dd>
                </dl>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <small class="text-muted">Período letivo</small>
                <div>
                    @can('periodo_letivo.show')
                        <a href="#" id="pltShow" class="btn btn-info btn-sm"><i class="fa fa-eye mr-1"></i>Detalhes</a>
                    @endcan
                    @can('periodo_letivo.edit')
                        <a href="#" id="pltEdit" class="btn btn-warning btn-sm"><i class="fa fa-edit mr-1"></i>Editar</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
$(function () {
    var STATUS_BADGE = {S: 'badge-success', N: 'badge-secondary'};
    var STATUS_COR = {S: '#16a34a', N: '#64748b'};

    $(document).on('click', '.plt-item', function (e) {
        // botoes/links dentro do item nao abrem o modal
        if ($(e.target).closest('a, button, form, input, summary').length) return;

        var d = this.dataset;
        $('#pltBar').css('background', STATUS_COR[d.statusKey] || '#64748b');
        $('#pltStatus').attr('class', 'badge ' + (STATUS_BADGE[d.statusKey] || 'badge-secondary')).text(d.status || '—');
        $('#pltNome').text(d.nome || '—');
        $('#pltAno').text(d.ano ? 'Ano letivo ' + d.ano : '—');
        $('#pltEscola').text(d.escola || '—');
        $('#pltOrdem').text(d.ordem || '—');
        $('#pltInicio').text(d.inicio || '—');
        $('#pltFim').text(d.fim || '—');
        $('#pltPeriodicidade').text(d.periodicidade || '—');
        $('#pltShow').attr('href', d.show || '#');
        $('#pltEdit').attr('href', d.edit || '#');
        $('#modalPeriodo').modal('show');
    });
});
</script>
@endpush
