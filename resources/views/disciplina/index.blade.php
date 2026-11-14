@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Componentes curriculares do tenant" :create-url="auth()->user()->hasPermissao('disciplina.create') ? route('disciplina.create') : null" :create-can="'disciplina.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .dis-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px; padding: 8px 6px 12px; }
        .dis-cards .ux-card { border-radius: 12px; box-shadow: 0 1px 4px rgba(11,18,32,.05); cursor: pointer; }
        .dis-cards .ux-card:hover { transform: none; box-shadow: 0 4px 14px rgba(11,18,32,.10); }
        .dis-cards .ux-card__accent { display: none; }
        .dis-cards .ux-card__head { padding: 9px 11px 7px; gap: 8px; }
        .dis-cards .ux-card__avatar { width: 34px; height: 34px; border-radius: 8px; font-size: .72rem; }
        .dis-cards .ux-card__title { font-size: .8rem; white-space: normal; }
        .dis-cards .ux-card__sub { font-size: .68rem; }
        .dis-cards .ux-card__badges { flex-direction: row; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .dis-cards .ux-card__badge { font-size: .6rem; padding: 3px 6px; }
        .dis-cards .ux-card__meta { padding: 7px 11px 5px; gap: 5px 10px; }
        .dis-cards .ux-card__meta-label { font-size: .6rem; }
        .dis-cards .ux-card__meta-value { font-size: .74rem; }
        .dis-cards .ux-card__foot { padding: 7px 11px 9px; }
        .dis-cards .ux-card__foot .btn { padding: .15rem .45rem; font-size: .72rem; }
        .dis-cards .ux-card--muted { opacity: .6; }
        .dis-cards .ux-card--muted:hover { opacity: .9; }
        tr.dis-item { cursor: pointer; }

        .dis-modal { border: none; border-radius: 18px; overflow: hidden; box-shadow: 0 24px 60px rgba(11,18,32,.28); }
        .dis-modal__hero {
            display: flex; align-items: center; gap: 14px;
            padding: 18px 20px 16px; color: #fff;
            background: linear-gradient(135deg, #334155, #0f172a);
        }
        .dis-modal--ativa .dis-modal__hero { background: linear-gradient(135deg, #16a34a, #15803d); }
        .dis-modal__medal {
            width: 54px; height: 54px; border-radius: 15px; flex-shrink: 0;
            background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.28);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.05rem; font-weight: 800; letter-spacing: .03em;
        }
        .dis-modal__hero-info { flex: 1; min-width: 0; }
        .dis-modal__title { font-size: 1.06rem; font-weight: 800; margin: 0; color: #fff; line-height: 1.25; }
        .dis-modal__sub { font-size: .76rem; opacity: .82; margin-top: 3px; }
        .dis-modal__status {
            display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0;
            font-size: .66rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em;
            padding: 5px 11px; border-radius: 999px;
            background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.32);
            white-space: nowrap;
        }
        .dis-modal__status i { font-size: .55rem; }
        .dis-modal__close {
            width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
            border: 1px solid rgba(255,255,255,.35); background: rgba(255,255,255,.12);
            color: #fff; font-size: .8rem; line-height: 1;
            display: flex; align-items: center; justify-content: center;
        }
        .dis-modal__close:hover { background: rgba(255,255,255,.28); color: #fff; }
        .dis-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 18px; padding: 14px 20px 6px; }
        .dis-info__item { min-width: 0; }
        .dis-info__label {
            display: flex; align-items: center; gap: 5px;
            font-size: .63rem; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; margin-bottom: 3px;
        }
        .dis-info__label i { font-size: .6rem; }
        .dis-info__value { font-size: .85rem; font-weight: 600; color: #0f172a; }
        .dis-sec {
            font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em;
            color: #94a3b8; margin: 14px 20px 5px;
        }
        .dis-sec i { margin-right: 4px; }
        .dis-desc {
            background: #f8fafc; border-left: 3px solid #cbd5e1; border-radius: 0 8px 8px 0;
            padding: 10px 12px; font-size: .85rem; line-height: 1.55; white-space: pre-line;
            margin: 0 20px 14px;
        }
        @media (max-width: 575.98px) {
            .dis-info { grid-template-columns: 1fr; }
            .dis-modal__medal { width: 44px; height: 44px; font-size: .9rem; border-radius: 12px; }
        }
    </style>
@endpush

@section('content')
@php
    $avatarDisc = function ($disciplina) {
        if ($disciplina->sigla) {
            return mb_strtoupper(\Illuminate\Support\Str::limit($disciplina->sigla, 6, ''));
        }
        $partes = preg_split('/\s+/', trim((string) $disciplina->nome)) ?: [];
        return collect($partes)->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'D';
    };
@endphp
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        @include('partials.ux-view-toggle', ['viewKey' => 'disciplina_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        <div class="ux-cards dis-cards">
            @forelse($disciplinas as $disciplina)
                @php $ativa = $disciplina->esta_ativa; @endphp
                <article class="ux-card dis-item {{ $ativa ? '' : 'ux-card--muted' }}" data-dis
                    data-nome="{{ $disciplina->nome }}"
                    data-avatar="{{ $avatarDisc($disciplina) }}"
                    data-sigla="{{ $disciplina->sigla ?? '—' }}"
                    data-turmas="{{ $disciplina->turmas_count ?? 0 }}"
                    data-descricao="{{ $disciplina->descricao ?? '' }}"
                    data-status="{{ $ativa ? 'Ativa' : 'Inativa' }}"
                    data-status-key="{{ $ativa ? 'S' : 'N' }}"
                    data-edit="{{ route('disciplina.edit', $disciplina->id) }}">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">{{ $avatarDisc($disciplina) }}</div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">{{ $disciplina->nome }}</div>
                            <div class="ux-card__sub">
                                {{ $disciplina->sigla ? 'Sigla '.$disciplina->sigla.' · ' : '' }}{{ $disciplina->turmas_count ?? 0 }} {{ \Illuminate\Support\Str::plural('turma', $disciplina->turmas_count ?? 0) }}
                            </div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="ux-card__badge {{ $ativa ? 'ux-card__badge--green' : 'ux-card__badge--gray' }}">
                                {{ $ativa ? 'Ativa' : 'Inativa' }}
                            </span>
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row ux-card__meta-row--full">
                            <span class="ux-card__meta-label">Descrição</span>
                            <span class="ux-card__meta-value" style="white-space:normal;">{{ \Illuminate\Support\Str::limit($disciplina->descricao, 90) ?: '—' }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        @can('disciplina.edit')
                            <a href="{{ route('disciplina.edit', $disciplina->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                        @can('disciplina.destroy')
                            <x-delete-button :action="route('disciplina.destroy', $disciplina->id)" titulo="Excluir" mensagem="Excluir a disciplina &quot;{{ $disciplina->nome }}&quot;? Ofertas em turmas são removidas junto." />
                        @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-book fa-2x mb-2 d-block"></i>
                    Nenhuma disciplina encontrada.
                </div>
            @endforelse
        </div>

        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Sigla</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disciplinas as $disciplina)
                        @php $ativa = $disciplina->esta_ativa; @endphp
                        <tr class="dis-item" data-dis
                            data-nome="{{ $disciplina->nome }}"
                            data-avatar="{{ $avatarDisc($disciplina) }}"
                            data-sigla="{{ $disciplina->sigla ?? '—' }}"
                            data-turmas="{{ $disciplina->turmas_count ?? 0 }}"
                            data-descricao="{{ $disciplina->descricao ?? '' }}"
                            data-status="{{ $ativa ? 'Ativa' : 'Inativa' }}"
                            data-status-key="{{ $ativa ? 'S' : 'N' }}"
                            data-edit="{{ route('disciplina.edit', $disciplina->id) }}">
                            <td><span class="badge badge-info">{{ $disciplina->sigla ?? '—' }}</span></td>
                            <td class="font-weight-bold">{{ $disciplina->nome }}</td>
                            <td class="text-muted" style="font-size:.8rem; max-width:280px;">{{ \Illuminate\Support\Str::limit($disciplina->descricao, 80) ?: '—' }}</td>
                            <td>
                                @if($ativa)
                                    <span class="badge badge-success">Ativa</span>
                                @else
                                    <span class="badge badge-secondary">Inativa</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('disciplina.edit')
                                    <a href="{{ route('disciplina.edit', $disciplina->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('disciplina.destroy')
                                    <x-delete-button :action="route('disciplina.destroy', $disciplina->id)" titulo="Excluir" mensagem="Excluir a disciplina &quot;{{ $disciplina->nome }}&quot;? Ofertas em turmas são removidas junto." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-book fa-2x mb-2 d-block"></i>
                                Nenhuma disciplina encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($disciplinas->hasPages())
        <div class="card-footer">
            {{ $disciplinas->links() }}
        </div>
    @endif
</div>

{{-- Modal de detalhes da disciplina (compartilhado por cards e tabela) --}}
<div class="modal fade" id="modalDisciplina" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content dis-modal dis-modal--inativa" id="disModalCard">
            <div class="dis-modal__hero">
                <div class="dis-modal__medal" id="disAvatar"></div>
                <div class="dis-modal__hero-info">
                    <h5 class="dis-modal__title" id="disNome"></h5>
                    <div class="dis-modal__sub">Disciplina</div>
                </div>
                <span class="dis-modal__status"><i class="fas fa-circle"></i><span id="disStatus"></span></span>
                <button type="button" class="dis-modal__close" data-dismiss="modal" aria-label="Fechar"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body p-0">
                <div class="dis-info">
                    <div class="dis-info__item">
                        <div class="dis-info__label"><i class="fas fa-tag"></i>Sigla</div>
                        <div class="dis-info__value" id="disSigla"></div>
                    </div>
                    <div class="dis-info__item">
                        <div class="dis-info__label"><i class="fas fa-chalkboard"></i>Turmas</div>
                        <div class="dis-info__value" id="disTurmas"></div>
                    </div>
                </div>
                <div class="dis-sec"><i class="fas fa-align-left"></i>Descrição</div>
                <div class="dis-desc" id="disDescricao"></div>
            </div>
            <div class="modal-footer border-0 pt-2 pb-3 justify-content-between">
                <small class="text-muted">Componente curricular</small>
                <div>
                    @can('disciplina.edit')
                        <a href="#" id="disEdit" class="btn btn-warning btn-sm"><i class="fa fa-edit mr-1"></i>Editar</a>
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
    $(document).on('click', '.dis-item', function (e) {
        // botoes/links dentro do item nao abrem o modal
        if ($(e.target).closest('a, button, form, input, summary').length) return;

        var d = this.dataset;
        var ativa = d.statusKey === 'S';

        $('#disModalCard')
            .toggleClass('dis-modal--ativa', ativa)
            .toggleClass('dis-modal--inativa', !ativa);

        $('#disAvatar').text(d.avatar || '—');
        $('#disNome').text(d.nome || '—');
        $('#disStatus').text(d.status || '—');
        $('#disSigla').text(d.sigla || '—');
        $('#disTurmas').text(d.turmas || '0');
        $('#disDescricao').text(d.descricao || '—');
        $('#disEdit').attr('href', d.edit || '#');

        $('#modalDisciplina').modal('show');
    });
});
</script>
@endpush
