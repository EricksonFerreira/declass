@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Ano letivo '.$anoLetivo->ano" :subtitle="$anoLetivo->escola->nome ?? ''" />
@stop

@push('css')
    @include('partials.home-app-styles')
    <style>
        .collapse-toggle { cursor: pointer; }
        .collapse-toggle .collapse-icon { transition: transform .2s ease; display: inline-block; }
        .collapse-toggle[aria-expanded="true"] .collapse-icon { transform: rotate(180deg); }
        .hs-item-acoes { margin-left: auto; display: flex; align-items: center; gap: .35rem; flex-shrink: 0; }
        .hs-mini { display: inline-flex; align-items: center; gap: .3rem; font-size: .66rem; font-weight: 700; color: #6b7280; background: #f3f4f6; border-radius: 999px; padding: .18rem .55rem; white-space: nowrap; }
        .al-aviso { border-radius: 14px; border: 1px dashed #f59e0b; background: #fffbeb; padding: 1rem; text-align: center; }
        .rep-box { border-radius: 12px; border: 1px solid #e5e7eb; background: #f8fafc; padding: .6rem .75rem; max-height: 180px; overflow-y: auto; font-size: .85rem; }
    </style>
@endpush

@section('content')
<div class="hs-widgets mb-3" style="grid-template-columns:repeat(auto-fit,minmax(115px,1fr));">
    <div class="hs-widget">
        <span class="hs-widget-icone"><i class="fas fa-calendar-alt"></i></span>
        <b>{{ $anoLetivo->periodos->count() }}</b>
        <small>Períodos</small>
    </div>
    <div class="hs-widget">
        <span class="hs-widget-icone"><i class="fas fa-chalkboard"></i></span>
        <b>{{ $turmas->count() }}</b>
        <small>Turmas</small>
    </div>
    <div class="hs-widget">
        <span class="hs-widget-icone"><i class="fas fa-book"></i></span>
        <b>{{ $totalDisciplinas }}</b>
        <small>Ofertas</small>
    </div>
    <div class="hs-widget">
        <span class="hs-widget-icone"><i class="fas fa-user-graduate"></i></span>
        <b>{{ $totalMatriculas }}</b>
        <small>Matrículas</small>
    </div>
    <div class="hs-widget">
        <span class="hs-widget-icone"><i class="fas fa-flag"></i></span>
        <b>{{ $anoLetivo->status_label }}</b>
        <small>Status</small>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="hs-sec"><i class="fa fa-info-circle"></i> Dados do ano letivo</div>
        <div class="hs-lista mb-3">
            <div class="hs-item">
                <span class="hs-item-icone" style="--app:#007bff;"><i class="fas fa-calendar-alt"></i></span>
                <div class="hs-item-info"><small>Ano</small><b>{{ $anoLetivo->ano }}</b></div>
            </div>
            <div class="hs-item">
                <span class="hs-item-icone" style="--app:#06b6d4;"><i class="fas fa-school"></i></span>
                <div class="hs-item-info"><small>Escola</small><b>{{ $anoLetivo->escola->nome ?? '—' }}</b></div>
            </div>
            <div class="hs-item">
                <span class="hs-item-icone" style="--app:#10b981;"><i class="fas fa-play"></i></span>
                <div class="hs-item-info"><small>Início</small><b>{{ optional($anoLetivo->data_inicio)->format('d/m/Y') ?? '—' }}</b></div>
            </div>
            <div class="hs-item">
                <span class="hs-item-icone" style="--app:#ef4444;"><i class="fas fa-flag-checkered"></i></span>
                <div class="hs-item-info"><small>Fim</small><b>{{ optional($anoLetivo->data_fim)->format('d/m/Y') ?? '—' }}</b></div>
            </div>
            <div class="hs-item">
                <span class="hs-item-icone" style="--app:#8b5cf6;"><i class="fas fa-layer-group"></i></span>
                <div class="hs-item-info">
                    <small>Periodicidade</small>
                    <b><span class="badge badge-{{ $anoLetivo->periodicidade_badge }}">{{ $anoLetivo->periodicidade_label }}</span></b>
                </div>
            </div>
            <div class="hs-item">
                <span class="hs-item-icone" style="--app:{{ $anoLetivo->status === 'letivo' ? '#22c55e' : '#94a3b8' }};"><i class="fas fa-toggle-on"></i></span>
                <div class="hs-item-info">
                    <small>Status</small>
                    <b><span class="badge badge-{{ $anoLetivo->status_badge }}">{{ $anoLetivo->status_label }}</span></b>
                </div>
            </div>
        </div>

        <div class="d-flex mb-3" style="gap:.5rem;">
            <a href="{{ route('ano_letivo.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left"></i> Voltar</a>
            @can('ano_letivo.edit')
                <a href="{{ route('ano_letivo.edit', $anoLetivo->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> Editar</a>
            @endcan
        </div>

        @if($anoLetivo->periodos->isEmpty() && ($anoLetivo->periodicidade ?? null) !== 'personalizado')
            <div class="al-aviso">
                <i class="fas fa-calendar-times text-warning mb-2 d-block" style="font-size:1.5rem;"></i>
                <p class="mb-2" style="font-size:.85rem;">Este ano ainda não tem períodos letivos.</p>
                @can('ano_letivo.update')
                    <form method="POST" action="{{ route('ano_letivo.gerar_periodos', $anoLetivo->id) }}"
                          onsubmit="return confirm('Gerar os períodos letivos conforme a periodicidade {{ strtolower($anoLetivo->periodicidade_label) }}?');">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fa fa-magic"></i> Gerar períodos ({{ $anoLetivo->periodicidade_label }})
                        </button>
                    </form>
                @endcan
            </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="hs-sec">
            <i class="fas fa-calendar-alt"></i> Períodos letivos
            <span class="badge badge-primary">{{ $anoLetivo->periodos->count() }}</span>
            @if($anoLetivo->periodos->isEmpty() && ($anoLetivo->periodicidade ?? null) !== 'personalizado')
                <span class="badge badge-warning">pendente</span>
            @endif
        </div>
        <div class="hs-lista mb-4">
            @forelse($anoLetivo->periodos->sortBy('ordem') as $periodo)
                <div class="hs-item">
                    <span class="hs-item-icone" style="--app:{{ $periodo->esta_ativo ? '#22c55e' : '#94a3b8' }};"><i class="fas fa-calendar-day"></i></span>
                    <div class="hs-item-info">
                        <b>{{ $periodo->ordem }}º · {{ $periodo->nome }}</b>
                        <small>{{ optional($periodo->data_inicio)->format('d/m/Y') ?? '—' }} — {{ optional($periodo->data_fim)->format('d/m/Y') ?? '—' }}</small>
                    </div>
                    <span class="badge badge-{{ $periodo->esta_ativo ? 'success' : 'secondary' }}">{{ $periodo->esta_ativo ? 'Ativo' : 'Inativo' }}</span>
                    <span class="hs-item-acoes">
                        <a href="{{ route('periodo_letivo.show', $periodo->id) }}" class="btn btn-xs btn-info" title="Detalhes"><i class="fa fa-eye"></i></a>
                        @can('periodo_letivo.destroy')
                            <x-delete-button :action="route('periodo_letivo.destroy', $periodo->id).'?from_ano_letivo_id='.$anoLetivo->id" titulo="Excluir" mensagem="Excluir o período &quot;{{ $periodo->nome }}&quot;? Diários e avaliações vinculados podem impedir a exclusão." />
                        @endcan
                    </span>
                </div>
            @empty
                <div class="hs-item-vazio">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Nenhum período letivo. Ajuste a periodicidade na edição do ano ou gere automaticamente.</span>
                </div>
            @endforelse
        </div>

        <div class="hs-sec">
            <i class="fas fa-chalkboard"></i> Turmas
            <span class="badge badge-primary">{{ $turmas->count() }}</span>
            <span class="ml-auto d-flex" style="gap:.4rem;">
                @if($podeGerar)
                    <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#modal-gerar-lote"><i class="fa fa-layer-group"></i> Geração em massa</button>
                @endif
                @if($podeCriarTurma)
                    <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#modal-nova-turma"><i class="fa fa-plus"></i> Nova turma</button>
                @endif
            </span>
        </div>
        <div class="hs-lista mb-4">
            @forelse($turmas as $turma)
                <div class="hs-item">
                    <span class="hs-item-icone" style="--app:{{ $turma->esta_ativa ? '#007bff' : '#94a3b8' }};"><i class="fas fa-chalkboard"></i></span>
                    <div class="hs-item-info">
                        <b><a href="{{ route('turma.show', $turma->id) }}" class="text-dark">{{ $turma->nome }}</a></b>
                        <small>{{ $turma->etapa->nome ?? '—' }} · {{ $turma->turno_label }}</small>
                    </div>
                    <span class="hs-mini"><i class="fas fa-book"></i> {{ $turma->disciplinas->count() }} disc.</span>
                    <span class="hs-mini"><i class="fas fa-user-graduate"></i> {{ $turma->matriculas_count ?? 0 }}</span>
                    <span class="hs-item-acoes">
                        <a href="{{ route('turma.show', $turma->id) }}" class="btn btn-xs btn-info" title="Detalhes"><i class="fa fa-eye"></i></a>
                        @can('turma.edit')
                            <a href="{{ route('turma.edit', $turma->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                        @can('turma.destroy')
                            <x-delete-button :action="route('turma.destroy', $turma->id).'?from_ano_letivo_id='.$anoLetivo->id" titulo="Excluir" mensagem="Excluir a turma &quot;{{ $turma->nome }}&quot;? Disciplinas e atribuições vinculadas são removidas junto." />
                        @endcan
                    </span>
                </div>
            @empty
                <div class="hs-item-vazio">
                    <i class="fas fa-chalkboard"></i>
                    <span>Nenhuma turma neste ano letivo.@if($podeGerar) Use a <strong>geração em massa</strong> acima para criar todas de uma vez.@endif</span>
                </div>
            @endforelse
        </div>

        @if($podeReplicar && $turmas->count() >= 2)
            <div class="hs-sec collapse-toggle" data-toggle="collapse" data-target="#collapse-replicar" aria-expanded="{{ $replicarAberto ? 'true' : 'false' }}">
                <i class="fa fa-copy"></i> Replicar organização entre turmas
                <i class="fa fa-chevron-down collapse-icon ml-1"></i>
                <span class="hs-sec-link">copie disciplinas e professores da turma modelo para as irmãs</span>
            </div>
            <div id="collapse-replicar" class="collapse {{ $replicarAberto ? 'show' : '' }}">
                <div class="hs-lista mb-4">
                    <div class="p-3">
                        <form method="POST" action="{{ route('turma.replicar_organizacao') }}" id="form-replicar">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Turma de origem <span class="text-danger">*</span></label>
                                    <select name="turma_origem_id" id="rep-origem" class="form-control" required>
                                        <option value="">Selecione a turma modelo…</option>
                                        @foreach($turmas as $turma)
                                            <option value="{{ $turma->id }}">{{ $turma->nome }} ({{ $turma->disciplinas->count() }} disc. / {{ $turma->atribuicoes->filter->esta_ativa->count() }} prof.)</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Disciplinas, cargas e professores desta turma serão copiados.</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Disciplinas da origem</label>
                                    <div id="rep-disciplinas" class="rep-box">
                                        <span class="text-muted">Selecione a turma de origem para ver as disciplinas.</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Professores da origem</label>
                                    <div id="rep-professores" class="rep-box">
                                        <span class="text-muted">Selecione a turma de origem para ver os professores.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-8">
                                    <label class="font-weight-bold">Copiar para as turmas <span class="text-danger">*</span></label>
                                    <div id="rep-destinos" class="rep-box" style="max-height:160px;">
                                        <span class="text-muted">Selecione a turma de origem primeiro — turmas da mesma etapa já vêm marcadas.</span>
                                    </div>
                                    <small class="text-muted">Ofertas e atribuições que o destino já tiver serão atualizadas; nada existente é removido.</small>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="incluir_professores" value="1" id="rep-prof-check" checked>
                                        <label class="form-check-label font-weight-bold" for="rep-prof-check">
                                            Incluir professores atribuídos
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-success" id="rep-btn" disabled>
                                    <i class="fa fa-copy"></i> Replicar organização
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
            (function () {
                var turmas = @json($turmasJson);
                var selectOrigem = document.getElementById('rep-origem');
                var boxDisc = document.getElementById('rep-disciplinas');
                var boxProf = document.getElementById('rep-professores');
                var boxDest = document.getElementById('rep-destinos');
                var btn = document.getElementById('rep-btn');

                function turmaPorId(id) {
                    return turmas.find(function (t) { return t.id === id; });
                }

                function renderDestinos(origem) {
                    boxDest.innerHTML = '';
                    turmas.forEach(function (t) {
                        if (t.id === origem.id) return;
                        var wrap = document.createElement('div');
                        wrap.className = 'form-check form-check-inline';
                        var check = document.createElement('input');
                        check.type = 'checkbox';
                        check.className = 'form-check-input rep-destino';
                        check.name = 'turmas_destino[]';
                        check.value = t.id;
                        check.id = 'rep_dest_' + t.id;
                        check.checked = t.etapa_id === origem.etapa_id;
                        check.addEventListener('change', validar);
                        var label = document.createElement('label');
                        label.className = 'form-check-label';
                        label.setAttribute('for', check.id);
                        label.textContent = t.nome;
                        wrap.appendChild(check);
                        wrap.appendChild(label);
                        boxDest.appendChild(wrap);
                    });
                    if (!boxDest.children.length) {
                        boxDest.innerHTML = '<span class="text-muted">Não há outras turmas para receber a replicação.</span>';
                    }
                }

                function renderDisciplinas(origem) {
                    if (!origem.disciplinas.length) {
                        boxDisc.innerHTML = '<span class="text-danger">Esta turma não tem disciplinas ofertadas — oferte disciplinas na turma antes de replicar.</span>';
                        return;
                    }
                    boxDisc.innerHTML = '<strong>' + origem.disciplinas.length + ' disciplina(s)</strong><ul class="mb-0 mt-1 pl-3">' +
                        origem.disciplinas.map(function (d) {
                            return '<li>' + d.nome + (d.carga ? ' <small class="text-muted">(' + d.carga + 'h/sem)</small>' : '') + '</li>';
                        }).join('') + '</ul>';
                }

                function renderProfessores(origem) {
                    if (!origem.atribuicoes.length) {
                        boxProf.innerHTML = '<span class="text-muted">Nenhum professor atribuído nesta turma.</span>';
                        return;
                    }
                    boxProf.innerHTML = '<strong>' + origem.atribuicoes.length + ' atribuição(ões)</strong><ul class="mb-0 mt-1 pl-3">' +
                        origem.atribuicoes.map(function (a) {
                            return '<li>' + a.professor + ' <small class="text-muted">— ' + a.disciplina + ' (' + a.papel + ')</small></li>';
                        }).join('') + '</ul>';
                }

                function validar() {
                    var algum = document.querySelectorAll('.rep-destino:checked').length > 0;
                    var origem = turmaPorId(parseInt(selectOrigem.value, 10));
                    btn.disabled = !origem || !origem.disciplinas.length || !algum;
                }

                selectOrigem.addEventListener('change', function () {
                    var origem = turmaPorId(parseInt(selectOrigem.value, 10));
                    if (!origem) {
                        boxDisc.innerHTML = '<span class="text-muted">Selecione a turma de origem para ver as disciplinas.</span>';
                        boxProf.innerHTML = '<span class="text-muted">Selecione a turma de origem para ver os professores.</span>';
                        boxDest.innerHTML = '<span class="text-muted">Selecione a turma de origem primeiro — turmas da mesma etapa já vêm marcadas.</span>';
                        btn.disabled = true;
                        return;
                    }
                    renderDisciplinas(origem);
                    renderProfessores(origem);
                    renderDestinos(origem);
                    validar();
                });
            })();
            </script>
        @endif
    </div>
</div>

@include('turma.partials.gerador-lote', [
    'anoLetivo' => $anoLetivo,
    'etapasOpcoes' => $etapasOpcoes,
    'turnosOpcoes' => $turnosOpcoes,
    'nomesExistentes' => $nomesExistentes,
    'podeGerar' => $podeGerar,
])

@include('turma.partials.nova-turma-modal', [
    'anoLetivo' => $anoLetivo,
    'etapasOpcoes' => $etapasOpcoes,
    'turnosOpcoes' => $turnosOpcoes,
    'horariosJson' => $horariosJson,
    'podeCriarTurma' => $podeCriarTurma,
])
@stop
