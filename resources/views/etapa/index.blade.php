@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Etapas de ensino do tenant" :create-url="auth()->user()->hasPermissao('etapa.create') ? route('etapa.create') : null" :create-can="'etapa.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .eta-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px; padding: 8px 6px 12px; }
        .eta-cards .ux-card { border-radius: 12px; box-shadow: 0 1px 4px rgba(11,18,32,.05); cursor: pointer; }
        .eta-cards .ux-card:hover { transform: none; box-shadow: 0 4px 14px rgba(11,18,32,.10); }
        .eta-cards .ux-card__accent { display: none; }
        .eta-cards .ux-card__head { padding: 9px 11px 7px; gap: 8px; }
        .eta-cards .ux-card__avatar { width: 34px; height: 34px; border-radius: 8px; font-size: .72rem; }
        .eta-cards .ux-card__title { font-size: .8rem; white-space: normal; }
        .eta-cards .ux-card__sub { font-size: .68rem; }
        .eta-cards .ux-card__badges { flex-direction: row; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .eta-cards .ux-card__badge { font-size: .6rem; padding: 3px 6px; }
        .eta-cards .ux-card__meta { padding: 7px 11px 5px; gap: 5px 10px; }
        .eta-cards .ux-card__meta-label { font-size: .6rem; }
        .eta-cards .ux-card__meta-value { font-size: .74rem; }
        .eta-cards .ux-card__foot { padding: 7px 11px 9px; }
        .eta-cards .ux-card__foot .btn { padding: .15rem .45rem; font-size: .72rem; }
        .eta-cards .ux-card--muted { opacity: .6; }
        .eta-cards .ux-card--muted:hover { opacity: .9; }
        tr.eta-item { cursor: pointer; }

        .eta-modal { border: none; border-radius: 18px; overflow: hidden; box-shadow: 0 24px 60px rgba(11,18,32,.28); }
        .eta-modal__hero {
            display: flex; align-items: center; gap: 14px;
            padding: 18px 20px 16px; color: #fff;
            background: linear-gradient(135deg, #334155, #0f172a);
        }
        .eta-modal--ativa .eta-modal__hero { background: linear-gradient(135deg, #16a34a, #15803d); }
        .eta-modal__medal {
            width: 54px; height: 54px; border-radius: 15px; flex-shrink: 0;
            background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.28);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; font-weight: 800; letter-spacing: .02em;
        }
        .eta-modal__hero-info { flex: 1; min-width: 0; }
        .eta-modal__title { font-size: 1.06rem; font-weight: 800; margin: 0; color: #fff; line-height: 1.25; }
        .eta-modal__sub { font-size: .76rem; opacity: .82; margin-top: 3px; }
        .eta-modal__status {
            display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0;
            font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
            padding: 5px 11px; border-radius: 999px;
            background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.32);
            white-space: nowrap;
        }
        .eta-modal__status i { font-size: .55rem; }
        .eta-modal__close {
            width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
            border: 1px solid rgba(255,255,255,.35); background: rgba(255,255,255,.12);
            color: #fff; font-size: .8rem; line-height: 1;
            display: flex; align-items: center; justify-content: center;
        }
        .eta-modal__close:hover { background: rgba(255,255,255,.28); color: #fff; }
        .eta-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 18px; padding: 14px 20px 6px; }
        .eta-info__item { min-width: 0; }
        .eta-info__label {
            display: flex; align-items: center; gap: 5px;
            font-size: .63rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; margin-bottom: 3px;
        }
        .eta-info__label i { font-size: .6rem; }
        .eta-info__value { font-size: .85rem; font-weight: 600; color: #0f172a; }
        .eta-sec {
            font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em;
            color: #94a3b8; margin: 14px 20px 5px;
        }
        .eta-sec i { margin-right: 4px; }
        .eta-desc {
            background: #f8fafc; border-left: 3px solid #cbd5e1; border-radius: 0 8px 8px 0;
            padding: 10px 12px; font-size: .85rem; line-height: 1.55; white-space: pre-line;
            margin: 0 20px 14px;
        }
        @media (max-width: 575.98px) {
            .eta-info { grid-template-columns: 1fr; }
            .eta-modal__medal { width: 44px; height: 44px; font-size: 1rem; border-radius: 12px; }
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
        @include('partials.ux-view-toggle', ['viewKey' => 'etapa_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        <div class="ux-cards eta-cards">
            @forelse($etapas as $etapa)
                @php $ativa = $etapa->estaAtiva(); @endphp
                <article class="ux-card eta-item {{ $ativa ? '' : 'ux-card--muted' }}" data-eta
                    data-nome="{{ $etapa->nome }}"
                    data-ordem="{{ $etapa->ordem }}"
                    data-segmento="{{ \App\Models\Etapa::SEGMENTOS[$etapa->segmento] ?? $etapa->segmento }}"
                    data-certificado="{{ $etapa->emite_certificado ? 'Sim' : 'Não' }}"
                    data-turmas="{{ $etapa->turmas_count ?? 0 }}"
                    data-descricao="{{ $etapa->descricao ?? '' }}"
                    data-status="{{ $ativa ? 'Ativa' : 'Inativa' }}"
                    data-status-key="{{ $ativa ? 'S' : 'N' }}"
                    data-edit="{{ route('etapa.edit', $etapa->id) }}">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">{{ $etapa->ordem }}</div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">{{ $etapa->nome }}</div>
                            <div class="ux-card__sub">{{ \App\Models\Etapa::SEGMENTOS[$etapa->segmento] ?? $etapa->segmento }}</div>
                        </div>
                        <div class="ux-card__badges">
                            @if($etapa->emite_certificado)
                                <span class="ux-card__badge ux-card__badge--blue" title="Emite certificado de conclusão">Certificado</span>
                            @endif
                            <span class="ux-card__badge {{ $ativa ? 'ux-card__badge--green' : 'ux-card__badge--gray' }}">
                                {{ $ativa ? 'Ativa' : 'Inativa' }}
                            </span>
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Turmas</span>
                            <span class="ux-card__meta-value">{{ $etapa->turmas_count ?? 0 }}</span>
                        </div>
                        <div class="ux-card__meta-row">
                            <span class="ux-card__meta-label">Ordem</span>
                            <span class="ux-card__meta-value">{{ $etapa->ordem }}</span>
                        </div>
                        <div class="ux-card__meta-row ux-card__meta-row--full">
                            <span class="ux-card__meta-label">Descrição</span>
                            <span class="ux-card__meta-value" style="white-space:normal;">{{ \Illuminate\Support\Str::limit($etapa->descricao, 90) ?: '—' }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        @can('etapa.edit')
                            <a href="{{ route('etapa.edit', $etapa->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                        @can('etapa.destroy')
                            <x-delete-button :action="route('etapa.destroy', $etapa->id)" titulo="Excluir" mensagem="Excluir a etapa &quot;{{ $etapa->nome }}&quot;? Turmas vinculadas impedem a exclusão." />
                        @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-layer-group fa-2x mb-2 d-block"></i>
                    Nenhuma etapa encontrada.
                </div>
            @endforelse
        </div>

        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Ordem</th>
                        <th>Nome</th>
                        <th>Segmento</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($etapas as $etapa)
                        @php $ativa = $etapa->estaAtiva(); @endphp
                        <tr class="eta-item" data-eta
                            data-nome="{{ $etapa->nome }}"
                            data-ordem="{{ $etapa->ordem }}"
                            data-segmento="{{ \App\Models\Etapa::SEGMENTOS[$etapa->segmento] ?? $etapa->segmento }}"
                            data-certificado="{{ $etapa->emite_certificado ? 'Sim' : 'Não' }}"
                            data-turmas="{{ $etapa->turmas_count ?? 0 }}"
                            data-descricao="{{ $etapa->descricao ?? '' }}"
                            data-status="{{ $ativa ? 'Ativa' : 'Inativa' }}"
                            data-status-key="{{ $ativa ? 'S' : 'N' }}"
                            data-edit="{{ route('etapa.edit', $etapa->id) }}">
                            <td>{{ $etapa->ordem }}</td>
                            <td class="font-weight-bold">{{ $etapa->nome }}</td>
                            <td>{{ \App\Models\Etapa::SEGMENTOS[$etapa->segmento] ?? $etapa->segmento }}</td>
                            <td class="text-muted" style="font-size:.8rem; max-width:280px;">{{ \Illuminate\Support\Str::limit($etapa->descricao, 80) ?: '—' }}</td>
                            <td>
                                @if($ativa)
                                    <span class="badge badge-success">Ativa</span>
                                @else
                                    <span class="badge badge-secondary">Inativa</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('etapa.edit')
                                    <a href="{{ route('etapa.edit', $etapa->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('etapa.destroy')
                                    <x-delete-button :action="route('etapa.destroy', $etapa->id)" titulo="Excluir" mensagem="Excluir a etapa &quot;{{ $etapa->nome }}&quot;? Turmas vinculadas impedem a exclusão." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-layer-group fa-2x mb-2 d-block"></i>
                                Nenhuma etapa encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($etapas->hasPages())
        <div class="card-footer">
            {{ $etapas->links() }}
        </div>
    @endif
</div>

{{-- Modal de detalhes da etapa (compartilhado por cards e tabela) --}}
<div class="modal fade" id="modalEtapa" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content eta-modal eta-modal--inativa" id="etaModalCard">
            <div class="eta-modal__hero">
                <div class="eta-modal__medal" id="etaOrdemMedal"></div>
                <div class="eta-modal__hero-info">
                    <h5 class="eta-modal__title" id="etaNome"></h5>
                    <div class="eta-modal__sub" id="etaSub"></div>
                </div>
                <span class="eta-modal__status"><i class="fas fa-circle"></i><span id="etaStatus"></span></span>
                <button type="button" class="eta-modal__close" data-dismiss="modal" aria-label="Fechar"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body p-0">
                <div class="eta-info">
                    <div class="eta-info__item">
                        <div class="eta-info__label"><i class="fas fa-layer-group"></i>Segmento</div>
                        <div class="eta-info__value" id="etaSegmento"></div>
                    </div>
                    <div class="eta-info__item">
                        <div class="eta-info__label"><i class="fas fa-sort-numeric-down"></i>Ordem</div>
                        <div class="eta-info__value" id="etaOrdem"></div>
                    </div>
                    <div class="eta-info__item">
                        <div class="eta-info__label"><i class="fas fa-certificate"></i>Certificado</div>
                        <div class="eta-info__value" id="etaCertificado"></div>
                    </div>
                    <div class="eta-info__item">
                        <div class="eta-info__label"><i class="fas fa-chalkboard"></i>Turmas</div>
                        <div class="eta-info__value" id="etaTurmas"></div>
                    </div>
                </div>
                <div class="eta-sec"><i class="fas fa-align-left"></i>Descrição</div>
                <div class="eta-desc" id="etaDescricao"></div>
            </div>
            <div class="modal-footer border-0 pt-2 pb-3 justify-content-between">
                <small class="text-muted">Etapa de ensino</small>
                <div>
                    @can('etapa.edit')
                        <a href="#" id="etaEdit" class="btn btn-warning btn-sm"><i class="fa fa-edit mr-1"></i>Editar</a>
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
    $(document).on('click', '.eta-item', function (e) {
        // botoes/links dentro do item nao abrem o modal
        if ($(e.target).closest('a, button, form, input, summary').length) return;

        var d = this.dataset;
        var ativa = d.statusKey === 'S';

        $('#etaModalCard')
            .toggleClass('eta-modal--ativa', ativa)
            .toggleClass('eta-modal--inativa', !ativa);

        $('#etaOrdemMedal').text(d.ordem || '—');
        $('#etaNome').text(d.nome || '—');
        $('#etaSub').text(d.segmento || '—');
        $('#etaStatus').text(d.status || '—');
        $('#etaSegmento').text(d.segmento || '—');
        $('#etaOrdem').text(d.ordem || '—');
        $('#etaCertificado').text(d.certificado || '—');
        $('#etaTurmas').text(d.turmas || '0');
        $('#etaDescricao').text(d.descricao || '—');
        $('#etaEdit').attr('href', d.edit || '#');

        $('#modalEtapa').modal('show');
    });
});
</script>
@endpush
