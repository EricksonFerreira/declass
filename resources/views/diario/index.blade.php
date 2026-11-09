@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Registro de aulas e chamada" :create-url="auth()->user()->hasPermissao('diario.create') ? route('diario.create') : null" :create-can="'diario.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .dia-cards .ux-card__avatar { background: linear-gradient(135deg,#2563eb,#0ea5e9); }
        .dia-item { cursor: pointer; }
        .dia-item:hover { background: #f0f6ff; }

        .dia-modal .modal-content { border-radius: 14px; overflow: hidden; border: none; }
        .dia-modal .modal-header { background: linear-gradient(135deg,#1e3a8a,#2563eb); color: #fff; padding: .65rem 1rem; border-bottom: none; }
        .dia-modal .modal-title { font-size: .95rem; font-weight: 700; }
        .dia-modal .close { color: #fff; opacity: .85; text-shadow: none; }
        .dia-badges { display: flex; gap: 6px; flex-wrap: wrap; }
        .dia-badges .badge { font-size: .68rem; padding: 4px 8px; }
        .dia-meta { display: flex; flex-wrap: wrap; gap: 4px 18px; margin: 10px 0 12px; }
        .dia-meta-item { font-size: .78rem; color: #475569; }
        .dia-meta-item i { color: #2563eb; margin-right: 5px; width: 13px; text-align: center; }
        .dia-lbl { font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 3px; }
        .dia-bloco { background: #f8fafc; border: 1px solid #e5e9f0; border-radius: 8px; padding: 10px 12px; font-size: .83rem; margin-bottom: 10px; }
        .dia-bloco--obs { background: #fffbeb; border-color: #fde68a; }
    </style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        @include('partials.ux-view-toggle', ['viewKey' => 'diario_index_view'])
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        {{-- Visao cards --}}
        <div class="ux-cards dia-cards">
            @forelse($diarios as $diario)
                <article class="ux-card dia-item"
                    data-id="{{ $diario->id }}"
                    data-data="{{ $diario->data?->format('d/m/Y') }}"
                    data-dia-semana="{{ $diario->data?->translatedFormat('l') }}"
                    data-turma="{{ $diario->turma->nome ?? '—' }}"
                    data-escola="{{ $diario->turma->escola->nome ?? '' }}"
                    data-disciplina="{{ $diario->disciplina->nome ?? '—' }}"
                    data-professor="{{ $diario->professor->nome ?? '—' }}"
                    data-freq="{{ $diario->frequencias_count ?? 0 }}"
                    data-conteudo="{{ $diario->conteudo }}"
                    data-obs="{{ $diario->observacoes ?? '' }}"
                    data-freq-url="{{ auth()->user()->hasPermissao('diario.frequencia') ? route('diario.frequencia', $diario->id) : '' }}"
                    data-edit-url="{{ auth()->user()->hasPermissao('diario.edit') ? route('diario.edit', $diario->id) : '' }}">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">{{ $diario->data?->format('d/m') }}</div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">{{ $diario->disciplina->nome ?? '—' }}</div>
                            <div class="ux-card__sub">{{ $diario->turma->nome ?? '—' }}</div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="ux-card__badge ux-card__badge--blue">{{ $diario->data?->format('d/m/Y') }}</span>
                            @if(($diario->frequencias_count ?? 0) > 0)
                                <span class="ux-card__badge ux-card__badge--green"><i class="fa fa-clipboard-check"></i> {{ $diario->frequencias_count }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row ux-card__meta-row--full">
                            <span class="ux-card__meta-label">Professor</span>
                            <span class="ux-card__meta-value">{{ $diario->professor->nome ?? '—' }}</span>
                        </div>
                        <div class="ux-card__meta-row ux-card__meta-row--full">
                            <span class="ux-card__meta-label">Conteúdo</span>
                            <span class="ux-card__meta-value">{{ \Illuminate\Support\Str::limit($diario->conteudo, 90) }}</span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        @can('diario.frequencia')
                            <a href="{{ route('diario.frequencia', $diario->id) }}" class="btn btn-xs btn-success" title="Chamada"><i class="fa fa-clipboard-check"></i></a>
                        @endcan
                        @can('diario.edit')
                            <a href="{{ route('diario.edit', $diario->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                        @can('diario.destroy')
                            <x-delete-button :action="route('diario.destroy', $diario->id)" titulo="Excluir" mensagem="Excluir este registro de aula? A chamada vinculada também será removida." />
                        @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-chalkboard fa-2x mb-2 d-block"></i>
                    Nenhuma aula registrada.
                </div>
            @endforelse
        </div>

        {{-- Visao tabela --}}
        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Data</th>
                        <th>Turma</th>
                        <th>Disciplina</th>
                        <th>Professor</th>
                        <th>Conteúdo</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($diarios as $diario)
                        <tr class="dia-item"
                            data-id="{{ $diario->id }}"
                            data-data="{{ $diario->data?->format('d/m/Y') }}"
                            data-dia-semana="{{ $diario->data?->translatedFormat('l') }}"
                            data-turma="{{ $diario->turma->nome ?? '—' }}"
                            data-escola="{{ $diario->turma->escola->nome ?? '' }}"
                            data-disciplina="{{ $diario->disciplina->nome ?? '—' }}"
                            data-professor="{{ $diario->professor->nome ?? '—' }}"
                            data-freq="{{ $diario->frequencias_count ?? 0 }}"
                            data-conteudo="{{ $diario->conteudo }}"
                            data-obs="{{ $diario->observacoes ?? '' }}"
                            data-freq-url="{{ auth()->user()->hasPermissao('diario.frequencia') ? route('diario.frequencia', $diario->id) : '' }}"
                            data-edit-url="{{ auth()->user()->hasPermissao('diario.edit') ? route('diario.edit', $diario->id) : '' }}">
                            <td class="text-nowrap">{{ $diario->data?->format('d/m/Y') }}</td>
                            <td>{{ $diario->turma->nome ?? '—' }}</td>
                            <td>{{ $diario->disciplina->nome ?? '—' }}</td>
                            <td>{{ $diario->professor->nome ?? '—' }}</td>
                            <td class="text-truncate" style="max-width:280px;" title="{{ $diario->conteudo }}">{{ \Illuminate\Support\Str::limit($diario->conteudo, 80) }}</td>
                            <td class="text-right text-nowrap">
                                @can('diario.frequencia')
                                    <a href="{{ route('diario.frequencia', $diario->id) }}" class="btn btn-xs btn-success" title="Chamada"><i class="fa fa-clipboard-check"></i></a>
                                @endcan
                                @can('diario.edit')
                                    <a href="{{ route('diario.edit', $diario->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('diario.destroy')
                                    <x-delete-button :action="route('diario.destroy', $diario->id)" titulo="Excluir" mensagem="Excluir este registro de aula? A chamada vinculada também será removida." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-chalkboard fa-2x mb-2 d-block"></i>
                                Nenhuma aula registrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($diarios->hasPages())
        <div class="card-footer">
            {{ $diarios->links() }}
        </div>
    @endif
</div>

{{-- Modal de detalhes da aula --}}
<div class="modal fade dia-modal" id="modalDiario" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-book-open mr-1"></i> Registro de aula</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="dia-badges">
                    <span class="badge badge-primary" id="diaMDisciplina"></span>
                    <span class="badge badge-info" id="diaMTurma"></span>
                    <span class="badge badge-success" id="diaMFreq" style="display:none;"></span>
                </div>
                <div class="dia-meta">
                    <span class="dia-meta-item"><i class="far fa-calendar-alt"></i><strong id="diaMData"></strong> <span id="diaMDiaSemana" class="text-muted"></span></span>
                    <span class="dia-meta-item" id="diaMRowEscola"><i class="fas fa-school"></i><span id="diaMEscola"></span></span>
                    <span class="dia-meta-item"><i class="fas fa-chalkboard-teacher"></i><span id="diaMProfessor"></span></span>
                </div>
                <div>
                    <div class="dia-lbl">Conteúdo ministrado</div>
                    <div class="dia-bloco" id="diaMConteudo"></div>
                </div>
                <div id="diaMWrapObs" style="display:none;">
                    <div class="dia-lbl">Observações</div>
                    <div class="dia-bloco dia-bloco--obs" id="diaMObs"></div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-sm btn-success mr-auto" id="diaMBtnFreq" style="display:none;"><i class="fa fa-clipboard-check mr-1"></i> Chamada</a>
                <a href="#" class="btn btn-sm btn-warning" id="diaMBtnEdit" style="display:none;"><i class="fa fa-edit mr-1"></i> Editar</a>
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
$(function () {
    var $modal = $('#modalDiario');

    $(document).on('click', '.dia-item', function (e) {
        // acoes internas (editar, excluir, chamada) nao abrem o modal
        if ($(e.target).closest('a, button, form').length) return;

        var d = this.dataset;
        $('#diaMDisciplina').text(d.disciplina || '—');
        $('#diaMTurma').text(d.turma || '—');
        $('#diaMData').text(d.data || '');
        $('#diaMDiaSemana').text(d.diaSemana || '');
        $('#diaMEscola').text(d.escola || '');
        $('#diaMRowEscola').toggle(!!d.escola);
        $('#diaMProfessor').text(d.professor || '—');
        $('#diaMConteudo').text(d.conteudo || '—');
        $('#diaMObs').text(d.obs || '');
        $('#diaMWrapObs').toggle(!!d.obs);
        $('#diaMFreq').toggle(+d.freq > 0).text(d.freq + ' na chamada');

        var btnFreq = $('#diaMBtnFreq');
        btnFreq.toggle(!!d.freqUrl).attr('href', d.freqUrl || '#');
        var btnEdit = $('#diaMBtnEdit');
        btnEdit.toggle(!!d.editUrl).attr('href', d.editUrl || '#');

        $modal.modal('show');
    });
});
</script>
@endpush
