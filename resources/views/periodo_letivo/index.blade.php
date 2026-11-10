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

        .plt-modal { border: none; border-radius: 18px; overflow: hidden; box-shadow: 0 24px 60px rgba(11,18,32,.28); }
        .plt-modal__hero {
            display: flex; align-items: center; gap: 14px;
            padding: 18px 20px 16px; color: #fff;
            background: linear-gradient(135deg, #334155, #0f172a);
        }
        .plt-modal--ativo .plt-modal__hero { background: linear-gradient(135deg, #16a34a, #15803d); }
        .plt-modal__medal {
            width: 54px; height: 54px; border-radius: 15px; flex-shrink: 0;
            background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.28);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; font-weight: 800; letter-spacing: .02em;
        }
        .plt-modal__hero-info { flex: 1; min-width: 0; }
        .plt-modal__title { font-size: 1.06rem; font-weight: 800; margin: 0; color: #fff; line-height: 1.25; }
        .plt-modal__sub { font-size: .76rem; opacity: .82; margin-top: 3px; }
        .plt-modal__status {
            display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0;
            font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
            padding: 5px 11px; border-radius: 999px;
            background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.32);
            white-space: nowrap;
        }
        .plt-modal__status i { font-size: .55rem; }
        .plt-modal__close {
            width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
            border: 1px solid rgba(255,255,255,.35); background: rgba(255,255,255,.12);
            color: #fff; font-size: .8rem; line-height: 1;
            display: flex; align-items: center; justify-content: center;
        }
        .plt-modal__close:hover { background: rgba(255,255,255,.28); color: #fff; }
        .plt-modal__progress { padding: 14px 20px 8px; }
        .plt-progress-label {
            display: flex; justify-content: space-between; align-items: baseline;
            font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #64748b;
        }
        .plt-segs { display: flex; gap: 6px; margin-top: 8px; }
        .plt-seg { flex: 1; height: 7px; border-radius: 99px; background: #e8edf5; transition: background .3s; }
        .plt-modal--ativo .plt-seg--on { background: #22c55e; }
        .plt-modal--inativo .plt-seg--on { background: #94a3b8; }
        .plt-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 18px; padding: 10px 20px 14px; }
        .plt-info__item { min-width: 0; }
        .plt-info__label {
            display: flex; align-items: center; gap: 5px;
            font-size: .63rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; margin-bottom: 3px;
        }
        .plt-info__label i { font-size: .6rem; }
        .plt-info__value { font-size: .85rem; font-weight: 600; color: #0f172a; }
        @media (max-width: 575.98px) {
            .plt-info { grid-template-columns: 1fr; }
            .plt-modal__medal { width: 44px; height: 44px; font-size: 1rem; border-radius: 12px; }
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
        @include('partials.ux-view-toggle', ['viewKey' => 'periodo_letivo_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        <div class="ux-cards plt-cards">
            @forelse($periodos as $periodo)
                @php $ativo = $periodo->estaAtivo(); @endphp
                <article class="ux-card plt-item {{ $ativo ? '' : 'ux-card--muted' }}" data-plt
                    data-nome="{{ $periodo->nome }}"
                    data-ordem="{{ $periodo->ordem }}º"
                    data-ordem-num="{{ $periodo->ordem }}"
                    data-periodicidade-key="{{ $periodo->anoLetivo->periodicidade ?? '' }}"
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
                            data-ordem-num="{{ $periodo->ordem }}"
                            data-periodicidade-key="{{ $periodo->anoLetivo->periodicidade ?? '' }}"
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
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content plt-modal plt-modal--inativo" id="pltModalCard">
            <div class="plt-modal__hero">
                <div class="plt-modal__medal" id="pltOrdemMedal"></div>
                <div class="plt-modal__hero-info">
                    <h5 class="plt-modal__title" id="pltNome"></h5>
                    <div class="plt-modal__sub" id="pltSub"></div>
                </div>
                <span class="plt-modal__status"><i class="fas fa-circle"></i><span id="pltStatus"></span></span>
                <button type="button" class="plt-modal__close" data-dismiss="modal" aria-label="Fechar"><i class="fas fa-times"></i></button>
            </div>
            <div class="plt-modal__progress" id="pltProgressWrap" style="display:none;">
                <div class="plt-progress-label">
                    <span id="pltProgressText"></span>
                    <span id="pltProgressPct"></span>
                </div>
                <div class="plt-segs" id="pltSegs"></div>
            </div>
            <div class="modal-body pt-3 pb-2">
                <div class="plt-info">
                    <div class="plt-info__item">
                        <div class="plt-info__label"><i class="fas fa-calendar-alt"></i>Ano letivo</div>
                        <div class="plt-info__value" id="pltAno"></div>
                    </div>
                    <div class="plt-info__item">
                        <div class="plt-info__label"><i class="fas fa-school"></i>Escola</div>
                        <div class="plt-info__value" id="pltEscola"></div>
                    </div>
                    <div class="plt-info__item">
                        <div class="plt-info__label"><i class="fas fa-sort-numeric-down"></i>Ordem</div>
                        <div class="plt-info__value" id="pltOrdem"></div>
                    </div>
                    <div class="plt-info__item">
                        <div class="plt-info__label"><i class="far fa-calendar"></i>Início</div>
                        <div class="plt-info__value" id="pltInicio"></div>
                    </div>
                    <div class="plt-info__item">
                        <div class="plt-info__label"><i class="far fa-calendar-check"></i>Término</div>
                        <div class="plt-info__value" id="pltFim"></div>
                    </div>
                    <div class="plt-info__item">
                        <div class="plt-info__label"><i class="fas fa-layer-group"></i>Periodicidade</div>
                        <div class="plt-info__value" id="pltPeriodicidade"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-2 pb-3 justify-content-between">
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
    // total de periodos por periodicidade (espelha PeriodoLetivoService::NOMES_POR_PERIODICIDADE)
    var PER_TOTAL = {bimestral: 4, trimestral: 3, semestral: 2, anual: 1};

    $(document).on('click', '.plt-item', function (e) {
        // botoes/links dentro do item nao abrem o modal
        if ($(e.target).closest('a, button, form, input, summary').length) return;

        var d = this.dataset;
        var ativo = d.statusKey === 'S';

        $('#pltModalCard')
            .toggleClass('plt-modal--ativo', ativo)
            .toggleClass('plt-modal--inativo', !ativo);

        $('#pltOrdemMedal').text(d.ordem || '—');
        $('#pltNome').text(d.nome || '—');
        $('#pltSub').text([d.ano ? 'Ano letivo ' + d.ano : null, d.escola].filter(Boolean).join(' · '));
        $('#pltStatus').text(d.status || '—');
        $('#pltAno').text(d.ano || '—');
        $('#pltEscola').text(d.escola || '—');
        $('#pltOrdem').text(d.ordem || '—');
        $('#pltInicio').text(d.inicio || '—');
        $('#pltFim').text(d.fim || '—');
        $('#pltPeriodicidade').text(d.periodicidade || '—');
        $('#pltShow').attr('href', d.show || '#');
        $('#pltEdit').attr('href', d.edit || '#');

        // progresso: posicao do periodo dentro do ano letivo
        var ordemNum = parseInt(d.ordemNum, 10) || 0;
        var total = PER_TOTAL[d.periodicidadeKey] || 0;
        var $wrap = $('#pltProgressWrap');
        if (total > 0 && ordemNum > 0 && ordemNum <= total) {
            $('#pltProgressText').text('Período ' + ordemNum + ' de ' + total);
            $('#pltProgressPct').text(Math.round((ordemNum / total) * 100) + '% do ano letivo');
            var $segs = $('#pltSegs').empty();
            for (var i = 1; i <= total; i++) {
                $segs.append($('<div>').addClass('plt-seg' + (i <= ordemNum ? ' plt-seg--on' : '')));
            }
            $wrap.show();
        } else {
            $wrap.hide();
        }

        $('#modalPeriodo').modal('show');
    });
});
</script>
@endpush
