@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Períodos letivos por escola" :create-url="auth()->user()->hasPermissao('ano_letivo.create') ? route('ano_letivo.create') : null" :create-can="'ano_letivo.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .alt-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px; padding: 8px 6px 12px; }
        .alt-cards .ux-card { border-radius: 12px; box-shadow: 0 1px 4px rgba(11,18,32,.05); cursor: pointer; }
        .alt-cards .ux-card:hover { transform: none; box-shadow: 0 4px 14px rgba(11,18,32,.10); }
        .alt-cards .ux-card__accent { display: none; }
        .alt-cards .ux-card__head { padding: 9px 11px 7px; gap: 8px; }
        .alt-cards .ux-card__avatar { width: 38px; height: 34px; border-radius: 8px; font-size: .72rem; font-weight: 800; }
        .alt-cards .ux-card__title { font-size: .8rem; white-space: normal; }
        .alt-cards .ux-card__sub { font-size: .68rem; }
        .alt-cards .ux-card__badges { flex-direction: row; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .alt-cards .ux-card__badge { font-size: .6rem; padding: 3px 6px; }
        .alt-cards .ux-card__meta { padding: 7px 11px 5px; gap: 5px 10px; }
        .alt-cards .ux-card__meta-label { font-size: .6rem; }
        .alt-cards .ux-card__meta-value { font-size: .74rem; }
        .alt-cards .ux-card__foot { padding: 7px 11px 9px; }
        .alt-cards .ux-card__foot .btn { padding: .15rem .45rem; font-size: .72rem; }
        .alt-cards .ux-card--muted { opacity: .6; }
        .alt-cards .ux-card--muted:hover { opacity: .9; }
        tr.alt-item { cursor: pointer; }

        .alt-modal { border: none; border-radius: 18px; overflow: hidden; box-shadow: 0 24px 60px rgba(11,18,32,.28); }
        .alt-modal__hero {
            display: flex; align-items: center; gap: 14px;
            padding: 18px 20px 16px; color: #fff;
            background: linear-gradient(135deg, #334155, #0f172a);
        }
        .alt-modal--letivo .alt-modal__hero { background: linear-gradient(135deg, #16a34a, #15803d); }
        .alt-modal--planejamento .alt-modal__hero { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
        .alt-modal--encerrado .alt-modal__hero { background: linear-gradient(135deg, #475569, #1e293b); }
        .alt-modal__medal {
            width: 58px; height: 54px; border-radius: 15px; flex-shrink: 0;
            background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.28);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.05rem; font-weight: 800; letter-spacing: .02em;
        }
        .alt-modal__hero-info { flex: 1; min-width: 0; }
        .alt-modal__title { font-size: 1.06rem; font-weight: 800; margin: 0; color: #fff; line-height: 1.25; }
        .alt-modal__sub { font-size: .76rem; opacity: .82; margin-top: 3px; }
        .alt-modal__status {
            display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0;
            font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
            padding: 5px 11px; border-radius: 999px;
            background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.32);
            white-space: nowrap;
        }
        .alt-modal__status i { font-size: .55rem; }
        .alt-modal__close {
            width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
            border: 1px solid rgba(255,255,255,.35); background: rgba(255,255,255,.12);
            color: #fff; font-size: .8rem; line-height: 1;
            display: flex; align-items: center; justify-content: center;
        }
        .alt-modal__close:hover { background: rgba(255,255,255,.28); color: #fff; }
        .alt-modal__progress { padding: 14px 20px 8px; }
        .alt-progress-label {
            display: flex; justify-content: space-between; align-items: baseline;
            font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #64748b;
        }
        .alt-segs { display: flex; gap: 6px; margin-top: 8px; }
        .alt-seg { flex: 1; height: 7px; border-radius: 99px; background: #e8edf5; transition: background .3s; }
        .alt-modal--letivo .alt-seg--on { background: #22c55e; }
        .alt-modal--planejamento .alt-seg--on { background: #3b82f6; }
        .alt-modal--encerrado .alt-seg--on { background: #94a3b8; }
        .alt-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 18px; padding: 10px 20px 14px; }
        .alt-info__item { min-width: 0; }
        .alt-info__label {
            display: flex; align-items: center; gap: 5px;
            font-size: .63rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; margin-bottom: 3px;
        }
        .alt-info__label i { font-size: .6rem; }
        .alt-info__value { font-size: .85rem; font-weight: 600; color: #0f172a; }
        @media (max-width: 575.98px) {
            .alt-info { grid-template-columns: 1fr; }
            .alt-modal__medal { width: 48px; height: 44px; font-size: .9rem; border-radius: 12px; }
        }
    </style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        @include('partials.ux-view-toggle', ['viewKey' => 'ano_letivo_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        <div class="ux-cards alt-cards">
            @forelse($anosLetivos as $anoLetivo)
                @php
                    $ativo = $anoLetivo->estaAtivo();
                    $statusKey = (string) $anoLetivo->status;
                    $statusLabel = \App\Models\AnoLetivo::STATUS[$statusKey] ?? $statusKey;
                    $statusBadgeVariant = ['letivo' => 'green', 'planejamento' => 'blue', 'encerrado' => 'red'][$statusKey] ?? 'gray';
                @endphp
                <article class="ux-card alt-item {{ $ativo ? '' : 'ux-card--muted' }}" data-alt
                    data-ano="{{ $anoLetivo->ano }}"
                    data-escola="{{ $anoLetivo->escola->nome ?? '—' }}"
                    data-periodicidade-key="{{ $anoLetivo->periodicidade ?? '' }}"
                    data-periodicidade="{{ \App\Models\AnoLetivo::PERIODICIDADES[$anoLetivo->periodicidade] ?? '—' }}"
                    data-inicio="{{ optional($anoLetivo->data_inicio)->format('d/m/Y') ?? '—' }}"
                    data-fim="{{ optional($anoLetivo->data_fim)->format('d/m/Y') ?? '—' }}"
                    data-status="{{ $statusLabel }}"
                    data-status-key="{{ $statusKey }}"
                    data-situacao="{{ $ativo ? 'Ativo' : 'Inativo' }}"
                    data-periodos="{{ $anoLetivo->periodos->count() }}"
                    data-turmas="{{ $anoLetivo->turmas->count() }}"
                    data-show="{{ route('ano_letivo.show', $anoLetivo->id) }}"
                    data-edit="{{ route('ano_letivo.edit', $anoLetivo->id) }}">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">{{ $anoLetivo->ano }}</div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">Ano letivo {{ $anoLetivo->ano }}</div>
                            <div class="ux-card__sub">{{ $anoLetivo->escola->nome ?? '—' }}</div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="ux-card__badge ux-card__badge--{{ $statusBadgeVariant }}">{{ $statusLabel }}</span>
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Início</span>
                            <span class="ux-card__meta-value">{{ optional($anoLetivo->data_inicio)->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Fim</span>
                            <span class="ux-card__meta-value">{{ optional($anoLetivo->data_fim)->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row ux-card__meta-row--full">
                            <span class="ux-card__meta-label">Periodicidade</span>
                            <span class="ux-card__meta-value">{{ \App\Models\AnoLetivo::PERIODICIDADES[$anoLetivo->periodicidade] ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        @can('ano_letivo.edit')
                            <a href="{{ route('ano_letivo.edit', $anoLetivo->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                        @can('ano_letivo.destroy')
                            <x-delete-button :action="route('ano_letivo.destroy', $anoLetivo->id)" titulo="Excluir" mensagem="Excluir o ano letivo &quot;{{ $anoLetivo->ano }}&quot;? Turmas vinculadas impedem a exclusão." />
                        @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-calendar-alt fa-2x mb-2 d-block"></i>
                    Nenhum ano letivo encontrado.
                </div>
            @endforelse
        </div>

        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Ano</th>
                        <th>Escola</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Status</th>
                        <th>Periodicidade</th>
                        <th>Situação</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($anosLetivos as $anoLetivo)
                        @php
                            $ativo = $anoLetivo->estaAtivo();
                            $statusKey = (string) $anoLetivo->status;
                            $statusLabel = \App\Models\AnoLetivo::STATUS[$statusKey] ?? $statusKey;
                        @endphp
                        <tr class="alt-item" data-alt
                            data-ano="{{ $anoLetivo->ano }}"
                            data-escola="{{ $anoLetivo->escola->nome ?? '—' }}"
                            data-periodicidade-key="{{ $anoLetivo->periodicidade ?? '' }}"
                            data-periodicidade="{{ \App\Models\AnoLetivo::PERIODICIDADES[$anoLetivo->periodicidade] ?? '—' }}"
                            data-inicio="{{ optional($anoLetivo->data_inicio)->format('d/m/Y') ?? '—' }}"
                            data-fim="{{ optional($anoLetivo->data_fim)->format('d/m/Y') ?? '—' }}"
                            data-status="{{ $statusLabel }}"
                            data-status-key="{{ $statusKey }}"
                            data-situacao="{{ $ativo ? 'Ativo' : 'Inativo' }}"
                            data-periodos="{{ $anoLetivo->periodos->count() }}"
                            data-turmas="{{ $anoLetivo->turmas->count() }}"
                            data-show="{{ route('ano_letivo.show', $anoLetivo->id) }}"
                            data-edit="{{ route('ano_letivo.edit', $anoLetivo->id) }}">
                            <td class="font-weight-bold">{{ $anoLetivo->ano }}</td>
                            <td>{{ $anoLetivo->escola->nome ?? '—' }}</td>
                            <td class="text-nowrap">{{ optional($anoLetivo->data_inicio)->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-nowrap">{{ optional($anoLetivo->data_fim)->format('d/m/Y') ?? '—' }}</td>
                            <td><span class="badge badge-{{ \App\Models\AnoLetivo::STATUS_BADGES[$statusKey] ?? 'secondary' }}">{{ $statusLabel }}</span></td>
                            <td><span class="badge badge-{{ \App\Models\AnoLetivo::PERIODICIDADE_BADGES[$anoLetivo->periodicidade] ?? 'secondary' }}">{{ \App\Models\AnoLetivo::PERIODICIDADES[$anoLetivo->periodicidade] ?? '—' }}</span></td>
                            <td>
                                @if($ativo)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('ano_letivo.show')
                                    <a href="{{ route('ano_letivo.show', $anoLetivo->id) }}" class="btn btn-xs btn-info" title="Planejamento do ano"><i class="fa fa-eye"></i></a>
                                @endcan
                                @can('ano_letivo.edit')
                                    <a href="{{ route('ano_letivo.edit', $anoLetivo->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('ano_letivo.destroy')
                                    <x-delete-button :action="route('ano_letivo.destroy', $anoLetivo->id)" titulo="Excluir" mensagem="Excluir o ano letivo &quot;{{ $anoLetivo->ano }}&quot;? Turmas vinculadas impedem a exclusão." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-alt fa-2x mb-2 d-block"></i>
                                Nenhum ano letivo encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($anosLetivos->hasPages())
        <div class="card-footer">
            {{ $anosLetivos->links() }}
        </div>
    @endif
</div>

{{-- Modal de detalhes do ano letivo (compartilhado por cards e tabela) --}}
<div class="modal fade" id="modalAnoLetivo" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content alt-modal alt-modal--encerrado" id="altModalCard">
            <div class="alt-modal__hero">
                <div class="alt-modal__medal" id="altAnoMedal"></div>
                <div class="alt-modal__hero-info">
                    <h5 class="alt-modal__title" id="altNome"></h5>
                    <div class="alt-modal__sub" id="altSub"></div>
                </div>
                <span class="alt-modal__status"><i class="fas fa-circle"></i><span id="altStatus"></span></span>
                <button type="button" class="alt-modal__close" data-dismiss="modal" aria-label="Fechar"><i class="fas fa-times"></i></button>
            </div>
            <div class="alt-modal__progress" id="altProgressWrap" style="display:none;">
                <div class="alt-progress-label">
                    <span id="altProgressText"></span>
                    <span id="altProgressPct"></span>
                </div>
                <div class="alt-segs" id="altSegs"></div>
            </div>
            <div class="modal-body p-0">
                <div class="alt-info">
                    <div class="alt-info__item">
                        <div class="alt-info__label"><i class="fas fa-school"></i>Escola</div>
                        <div class="alt-info__value" id="altEscola"></div>
                    </div>
                    <div class="alt-info__item">
                        <div class="alt-info__label"><i class="fas fa-layer-group"></i>Periodicidade</div>
                        <div class="alt-info__value" id="altPeriodicidade"></div>
                    </div>
                    <div class="alt-info__item">
                        <div class="alt-info__label"><i class="far fa-calendar"></i>Início</div>
                        <div class="alt-info__value" id="altInicio"></div>
                    </div>
                    <div class="alt-info__item">
                        <div class="alt-info__label"><i class="far fa-calendar-check"></i>Término</div>
                        <div class="alt-info__value" id="altFim"></div>
                    </div>
                    <div class="alt-info__item">
                        <div class="alt-info__label"><i class="fas fa-toggle-on"></i>Situação</div>
                        <div class="alt-info__value" id="altSituacao"></div>
                    </div>
                    <div class="alt-info__item">
                        <div class="alt-info__label"><i class="fas fa-chalkboard-teacher"></i>Turmas</div>
                        <div class="alt-info__value" id="altTurmas"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-2 pb-3 justify-content-between">
                <small class="text-muted">Ano letivo</small>
                <div>
                    @can('ano_letivo.show')
                        <a href="#" id="altShow" class="btn btn-info btn-sm"><i class="fa fa-eye mr-1"></i>Planejamento</a>
                    @endcan
                    @can('ano_letivo.edit')
                        <a href="#" id="altEdit" class="btn btn-warning btn-sm"><i class="fa fa-edit mr-1"></i>Editar</a>
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
    // total de periodos por periodicidade (espelha PeriodoLetivoService::NOMES_POR_PERIODICIDADE)
    var PER_TOTAL = {bimestral: 4, trimestral: 3, semestral: 2, anual: 1};

    $(document).on('click', '.alt-item', function (e) {
        // botoes/links dentro do item nao abrem o modal
        if ($(e.target).closest('a, button, form, input, summary').length) return;

        var d = this.dataset;
        var statusKey = d.statusKey || '';

        $('#altModalCard')
            .removeClass('alt-modal--letivo alt-modal--planejamento alt-modal--encerrado')
            .addClass('alt-modal--' + (['letivo', 'planejamento', 'encerrado'].indexOf(statusKey) >= 0 ? statusKey : 'encerrado'));

        $('#altAnoMedal').text(d.ano || '—');
        $('#altNome').text(d.ano ? 'Ano letivo ' + d.ano : '—');
        $('#altSub').text(d.escola || '—');
        $('#altStatus').text(d.status || '—');
        $('#altEscola').text(d.escola || '—');
        $('#altPeriodicidade').text(d.periodicidade || '—');
        $('#altInicio').text(d.inicio || '—');
        $('#altFim').text(d.fim || '—');
        $('#altSituacao').text(d.situacao || '—');
        $('#altTurmas').text(d.turmas !== undefined ? d.turmas : '—');
        $('#altShow').attr('href', d.show || '#');
        $('#altEdit').attr('href', d.edit || '#');

        // progresso: periodos cadastrados dentro da periodicidade do ano
        var qtd = parseInt(d.periodos, 10) || 0;
        var total = PER_TOTAL[d.periodicidadeKey] || 0;
        var $wrap = $('#altProgressWrap');
        if (total > 0) {
            $('#altProgressText').text(qtd + ' de ' + total + ' períodos criados');
            $('#altProgressPct').text(Math.round((qtd / total) * 100) + '%');
            var $segs = $('#altSegs').empty();
            for (var i = 1; i <= total; i++) {
                $segs.append($('<div>').addClass('alt-seg' + (i <= qtd ? ' alt-seg--on' : '')));
            }
            $wrap.show();
        } else {
            $wrap.hide();
        }

        $('#modalAnoLetivo').modal('show');
    });
});
</script>
@endpush
