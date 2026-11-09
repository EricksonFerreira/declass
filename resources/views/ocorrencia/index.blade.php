@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Registros disciplinares e pedagógicos dos alunos" :create-url="auth()->user()->hasPermissao('ocorrencia.create') ? route('ocorrencia.create') : null" :create-can="'ocorrencia.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .oco-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 10px; padding: 8px 6px 12px; }
        .oco-cards .ux-card { border-radius: 12px; box-shadow: 0 1px 4px rgba(11,18,32,.05); cursor: pointer; }
        .oco-cards .ux-card:hover { transform: none; box-shadow: 0 4px 14px rgba(11,18,32,.10); }
        .oco-cards .ux-card__accent { display: none; }
        .oco-cards .ux-card__head { padding: 9px 11px 7px; gap: 8px; }
        .oco-cards .ux-card__avatar { width: 32px; height: 32px; border-radius: 8px; font-size: .68rem; }
        .oco-cards .ux-card__title { font-size: .8rem; white-space: normal; }
        .oco-cards .ux-card__sub { font-size: .68rem; }
        .oco-cards .ux-card__badges { flex-direction: row; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .oco-cards .ux-card__badge { font-size: .6rem; padding: 3px 6px; }
        .oco-cards .ux-card__meta { padding: 7px 11px 5px; gap: 5px 10px; }
        .oco-cards .ux-card__meta-label { font-size: .6rem; }
        .oco-cards .ux-card__meta-value { font-size: .74rem; }
        .oco-cards .ux-card__foot { padding: 7px 11px 9px; }
        .oco-cards .ux-card__foot .btn { padding: .15rem .45rem; font-size: .72rem; }
        tr.oco-item { cursor: pointer; }

        .oco-modal { border: none; border-radius: 16px; overflow: hidden; }
        .oco-modal__bar { height: 5px; background: #64748b; }
        .oco-meta { display: flex; gap: 14px; flex-wrap: wrap; font-size: .78rem; color: #64748b; margin-top: 3px; }
        .oco-meta i { color: #94a3b8; margin-right: 3px; }
        .oco-sec { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; margin: 14px 0 5px; }
        .oco-sec i { margin-right: 4px; }
        .oco-desc { background: #f8fafc; border-left: 3px solid #cbd5e1; border-radius: 0 8px 8px 0; padding: 10px 12px; font-size: .88rem; line-height: 1.6; white-space: pre-line; }
        .oco-desc--medida { border-left-color: #93c5fd; background: #eff6ff; }
    </style>
@endpush

@section('content')
@php
    $tipoBadgeCard = [
        'elogio' => 'ux-card__badge--green',
        'advertencia' => 'ux-card__badge--amber',
        'ocorrencia' => 'ux-card__badge--blue',
        'suspensao' => 'ux-card__badge--red',
        'outro' => 'ux-card__badge--gray',
    ];
    $gravBadgeCard = [
        'leve' => 'ux-card__badge--gray',
        'media' => 'ux-card__badge--amber',
        'grave' => 'ux-card__badge--red',
    ];
@endphp
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
        @include('partials.ux-view-toggle', ['viewKey' => 'ocorrencia_index_view'])
    </div>

    <div class="card-body p-0 ux-index-body" data-view="cards">
        <div class="ux-cards oco-cards">
            @forelse($ocorrencias as $ocorrencia)
                @php
                    $aluno = $ocorrencia->aluno;
                    $nome = $aluno->pessoa->nome ?? '?';
                    $iniciais = collect(explode(' ', trim($nome)))->filter()->take(2)
                        ->map(fn ($s) => mb_strtoupper(mb_substr($s, 0, 1)))->implode('') ?: 'A';
                @endphp
                <article class="ux-card oco-item" data-oco
                    data-tipo="{{ $ocorrencia->tipo }}"
                    data-tipo-label="{{ $ocorrencia->tipoLabel() }}"
                    data-gravidade="{{ $ocorrencia->gravidade }}"
                    data-gravidade-label="{{ $ocorrencia->gravidadeLabel() }}"
                    data-data="{{ $ocorrencia->data->format('d/m/Y') }}"
                    data-aluno="{{ $nome }}"
                    data-ra="{{ $aluno->registro_academico ?? '' }}"
                    data-escola="{{ $ocorrencia->escola->nome ?? '' }}"
                    data-local="{{ $ocorrencia->local ?? '' }}"
                    data-descricao="{{ $ocorrencia->descricao ?? '' }}"
                    data-medida="{{ $ocorrencia->medida_adotada ?? '' }}"
                    data-responsavel="{{ $ocorrencia->responsavel->name ?? '' }}"
                    data-notifica="{{ $ocorrencia->notifica_responsavel ? '1' : '0' }}"
                    data-edit="{{ route('ocorrencia.edit', $ocorrencia->id) }}">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar">{{ $iniciais }}</div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">{{ $nome }}</div>
                            <div class="ux-card__sub">
                                {{ $ocorrencia->escola->nome ?? '—' }} · {{ $ocorrencia->data->format('d/m/Y') }}
                                @if($ocorrencia->notifica_responsavel)<i class="fas fa-bell text-info ml-1" title="Responsável notificado"></i>@endif
                            </div>
                        </div>
                        <div class="ux-card__badges">
                            <span class="ux-card__badge {{ $tipoBadgeCard[$ocorrencia->tipo] ?? 'ux-card__badge--gray' }}">{{ $ocorrencia->tipoLabel() }}</span>
                            @if($ocorrencia->tipo !== 'elogio')
                                <span class="ux-card__badge {{ $gravBadgeCard[$ocorrencia->gravidade] ?? 'ux-card__badge--gray' }}">{{ $ocorrencia->gravidadeLabel() }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row ux-card__meta-row--full">
                            <span class="ux-card__meta-label">Descrição</span>
                            <span class="ux-card__meta-value" style="white-space:normal;">{{ \Illuminate\Support\Str::limit($ocorrencia->descricao, 90) ?: '—' }}</span>
                        </div>
                        @if($ocorrencia->local)
                            <div class="ux-card__meta-row ux-card__meta-row--full">
                                <span class="ux-card__meta-label">Local</span>
                                <span class="ux-card__meta-value">{{ $ocorrencia->local }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="ux-card__foot">
                        <small class="text-muted mr-auto align-self-center">{{ $ocorrencia->responsavel->name ?? '—' }}</small>
                        @can('ocorrencia.edit')
                            <a href="{{ route('ocorrencia.edit', $ocorrencia->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                        @can('ocorrencia.destroy')
                            <x-delete-button :action="route('ocorrencia.destroy', $ocorrencia->id)" titulo="Excluir" mensagem="Excluir a ocorrência de {{ $nome }}?" />
                        @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                    Nenhuma ocorrência registrada.
                </div>
            @endforelse
        </div>

        <div class="ux-table table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Data</th>
                        <th>Aluno</th>
                        <th>Escola</th>
                        <th>Tipo</th>
                        <th>Gravidade</th>
                        <th>Descrição</th>
                        <th>Registrado por</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ocorrencias as $ocorrencia)
                        <tr class="oco-item" data-oco
                            data-tipo="{{ $ocorrencia->tipo }}"
                            data-tipo-label="{{ $ocorrencia->tipoLabel() }}"
                            data-gravidade="{{ $ocorrencia->gravidade }}"
                            data-gravidade-label="{{ $ocorrencia->gravidadeLabel() }}"
                            data-data="{{ $ocorrencia->data->format('d/m/Y') }}"
                            data-aluno="{{ $ocorrencia->aluno->pessoa->nome ?? '—' }}"
                            data-ra="{{ $ocorrencia->aluno->registro_academico ?? '' }}"
                            data-escola="{{ $ocorrencia->escola->nome ?? '' }}"
                            data-local="{{ $ocorrencia->local ?? '' }}"
                            data-descricao="{{ $ocorrencia->descricao ?? '' }}"
                            data-medida="{{ $ocorrencia->medida_adotada ?? '' }}"
                            data-responsavel="{{ $ocorrencia->responsavel->name ?? '' }}"
                            data-notifica="{{ $ocorrencia->notifica_responsavel ? '1' : '0' }}"
                            data-edit="{{ route('ocorrencia.edit', $ocorrencia->id) }}">
                            <td class="text-nowrap">{{ $ocorrencia->data->format('d/m/Y') }}</td>
                            <td class="font-weight-bold">{{ $ocorrencia->aluno->pessoa->nome ?? '—' }}</td>
                            <td>{{ $ocorrencia->escola->nome ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $ocorrencia->tipo === 'elogio' ? 'success' : ($ocorrencia->tipo === 'suspensao' ? 'danger' : 'warning') }}">
                                    {{ $ocorrencia->tipoLabel() }}
                                </span>
                            </td>
                            <td>
                                @if($ocorrencia->tipo === 'elogio')
                                    <span class="text-muted">—</span>
                                @else
                                    @php($grav = ['leve' => 'secondary', 'media' => 'warning', 'grave' => 'danger'][$ocorrencia->gravidade] ?? 'secondary')
                                    <span class="badge badge-{{ $grav }}">{{ $ocorrencia->gravidadeLabel() }}</span>
                                @endif
                            </td>
                            <td style="max-width:280px;">
                                <small>{{ \Illuminate\Support\Str::limit($ocorrencia->descricao, 100) }}</small>
                                @if($ocorrencia->notifica_responsavel)
                                    <br><small class="text-info"><i class="fas fa-bell"></i> responsável notificado</small>
                                @endif
                            </td>
                            <td><small>{{ $ocorrencia->responsavel->name ?? '—' }}</small></td>
                            <td class="text-right">
                                @can('ocorrencia.edit')
                                    <a href="{{ route('ocorrencia.edit', $ocorrencia->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('ocorrencia.destroy')
                                    <x-delete-button :action="route('ocorrencia.destroy', $ocorrencia->id)" titulo="Excluir" mensagem="Excluir a ocorrência de {{ $ocorrencia->aluno->pessoa->nome ?? 'aluno' }}?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                                Nenhuma ocorrência registrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($ocorrencias->hasPages())
        <div class="card-footer">
            {{ $ocorrencias->links() }}
        </div>
    @endif
</div>

{{-- Modal de detalhes da ocorrencia (compartilhado por cards e tabela) --}}
<div class="modal fade" id="modalOcorrencia" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content oco-modal">
            <div class="oco-modal__bar" id="ocoBar"></div>
            <div class="modal-header border-0 pb-1">
                <div class="pr-2">
                    <div class="d-flex align-items-center flex-wrap" style="gap:.35rem;">
                        <span class="badge" id="ocoTipo"></span>
                        <span class="badge" id="ocoGravidade"></span>
                        <span class="badge badge-info d-none" id="ocoNotifica"><i class="fas fa-bell mr-1"></i>responsável notificado</span>
                    </div>
                    <h5 class="modal-title font-weight-bold mt-2 mb-0" id="ocoAluno"></h5>
                    <div class="oco-meta">
                        <span><i class="fas fa-school"></i><span id="ocoEscola"></span></span>
                        <span><i class="far fa-calendar"></i><span id="ocoData"></span></span>
                        <span id="ocoLocalWrap"><i class="fas fa-map-marker-alt"></i><span id="ocoLocal"></span></span>
                        <span id="ocoRaWrap"><i class="fas fa-id-card"></i>RA <span id="ocoRa"></span></span>
                    </div>
                </div>
                <button type="button" class="close align-self-start" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body pt-2">
                <div class="oco-sec"><i class="fas fa-align-left"></i>Descrição</div>
                <div class="oco-desc" id="ocoDescricao"></div>
                <div id="ocoMedidaWrap">
                    <div class="oco-sec"><i class="fas fa-gavel"></i>Medida adotada</div>
                    <div class="oco-desc oco-desc--medida" id="ocoMedida"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <small class="text-muted">Registrado por <strong id="ocoResponsavel"></strong></small>
                @can('ocorrencia.edit')
                    <a href="#" id="ocoEdit" class="btn btn-warning btn-sm"><i class="fa fa-edit mr-1"></i>Editar</a>
                @endcan
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
$(function () {
    var TIPO_BADGE = {elogio: 'badge-success', advertencia: 'badge-warning', ocorrencia: 'badge-warning', suspensao: 'badge-danger', outro: 'badge-secondary'};
    var GRAV_BADGE = {leve: 'badge-secondary', media: 'badge-warning', grave: 'badge-danger'};
    var TIPO_COR = {elogio: '#16a34a', advertencia: '#d97706', ocorrencia: '#b45309', suspensao: '#dc2626', outro: '#64748b'};

    $(document).on('click', '.oco-item', function (e) {
        // botoes/links dentro do item nao abrem o modal
        if ($(e.target).closest('a, button, form, input, summary').length) return;

        var d = this.dataset;
        $('#ocoBar').css('background', TIPO_COR[d.tipo] || '#64748b');
        $('#ocoTipo').attr('class', 'badge ' + (TIPO_BADGE[d.tipo] || 'badge-secondary')).text(d.tipoLabel || '—');
        $('#ocoGravidade').attr('class', 'badge ' + (GRAV_BADGE[d.gravidade] || 'badge-secondary')).text(d.gravidadeLabel || '—').toggle(d.tipo !== 'elogio');
        $('#ocoNotifica').toggleClass('d-none', d.notifica !== '1');
        $('#ocoAluno').text(d.aluno || '—');
        $('#ocoEscola').text(d.escola || '—');
        $('#ocoData').text(d.data || '—');
        $('#ocoLocalWrap').toggle(!!d.local);
        $('#ocoLocal').text(d.local || '');
        $('#ocoRaWrap').toggle(!!d.ra);
        $('#ocoRa').text(d.ra || '');
        $('#ocoDescricao').text(d.descricao || '—');
        $('#ocoMedidaWrap').toggle(!!d.medida);
        $('#ocoMedida').text(d.medida || '');
        $('#ocoResponsavel').text(d.responsavel || '—');
        $('#ocoEdit').attr('href', d.edit || '#');
        $('#modalOcorrencia').modal('show');
    });
});
</script>
@endpush
