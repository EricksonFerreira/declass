@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Ano letivo '.$anoLetivo->ano" :subtitle="$anoLetivo->escola->nome ?? ''" />
@stop

@section('content')
<style>
    .collapse-toggle { cursor: pointer; }
    .collapse-toggle .collapse-icon { transition: transform .2s ease; display: inline-block; }
    .collapse-toggle[aria-expanded="true"] .collapse-icon { transform: rotate(180deg); }
</style>
@php
    $periodicidadeLabel = \App\Models\AnoLetivo::PERIODICIDADES[$anoLetivo->periodicidade] ?? '—';
    $totalDisciplinas = $turmas->sum(fn ($t) => $t->disciplinas->count());
    // Collapse inteligente: abre so o que precisa de acao no momento.
    $periodosAbertos = $anoLetivo->periodos->isEmpty();
    $turmasAbertas = $turmas->isEmpty();
    $temTurmaSemOrg = $turmas->contains(fn ($t) => $t->disciplinas->isEmpty());
    $temTurmaComOrg = $turmas->contains(fn ($t) => $t->disciplinas->isNotEmpty());
    $replicarAberto = $turmas->count() >= 2 && $temTurmaSemOrg && $temTurmaComOrg;
    $turmasJson = $turmas->map(fn ($t) => [
        'id' => $t->id,
        'nome' => $t->nome,
        'etapa_id' => $t->etapa_id,
        'disciplinas' => $t->disciplinas->map(fn ($d) => [
            'id' => $d->id,
            'nome' => $d->nome,
            'carga' => $d->pivot->carga_horaria_semanal,
        ])->values()->all(),
        'atribuicoes' => $t->atribuicoes->filter->esta_ativa->map(fn ($a) => [
            'professor' => $a->user->nome ?? '—',
            'disciplina' => $a->disciplina->nome ?? 'Turma toda',
            'papel' => \App\Models\Atribuicao::PAPEIS[$a->papel] ?? $a->papel,
        ])->values()->all(),
    ])->values();
@endphp

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header font-weight-bold">Dados do Ano Letivo</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Ano</dt><dd class="col-sm-7 font-weight-bold">{{ $anoLetivo->ano }}</dd>
                    <dt class="col-sm-5">Escola</dt><dd class="col-sm-7">{{ $anoLetivo->escola->nome ?? '—' }}</dd>
                    <dt class="col-sm-5">Início</dt><dd class="col-sm-7">{{ optional($anoLetivo->data_inicio)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-5">Fim</dt><dd class="col-sm-7">{{ optional($anoLetivo->data_fim)->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-sm-5">Periodicidade</dt>
                    <dd class="col-sm-7"><span class="badge badge-{{ \App\Models\AnoLetivo::PERIODICIDADE_BADGES[$anoLetivo->periodicidade] ?? 'secondary' }}">{{ $periodicidadeLabel }}</span></dd>
                    <dt class="col-sm-5">Status</dt>
                    <dd class="col-sm-7">
                        <span class="badge badge-{{ \App\Models\AnoLetivo::STATUS_BADGES[$anoLetivo->status] ?? 'secondary' }}">
                            {{ \App\Models\AnoLetivo::STATUS[$anoLetivo->status] ?? $anoLetivo->status }}
                        </span>
                    </dd>
                    <dt class="col-sm-5">Períodos</dt><dd class="col-sm-7">{{ $anoLetivo->periodos->count() }}</dd>
                    <dt class="col-sm-5">Turmas</dt><dd class="col-sm-7">{{ $turmas->count() }}</dd>
                    <dt class="col-sm-5">Ofertas</dt><dd class="col-sm-7">{{ $totalDisciplinas }} disciplina(s) ofertada(s)</dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('ano_letivo.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
                @can('ano_letivo.edit')
                    <a href="{{ route('ano_letivo.edit', $anoLetivo->id) }}" class="btn btn-warning btn-sm">Editar</a>
                @endcan
            </div>
        </div>

        @if($anoLetivo->periodos->isEmpty() && ($anoLetivo->periodicidade ?? null) !== 'personalizado')
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-times fa-2x text-warning mb-2 d-block"></i>
                    <p class="mb-2">Este ano ainda não tem períodos letivos.</p>
                    @can('ano_letivo.update')
                        <form method="POST" action="{{ route('ano_letivo.gerar_periodos', $anoLetivo->id) }}"
                              onsubmit="return confirm('Gerar os períodos letivos conforme a periodicidade {{ strtolower($periodicidadeLabel) }}?');">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fa fa-magic"></i> Gerar períodos ({{ $periodicidadeLabel }})
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header collapse-toggle d-flex justify-content-between align-items-center"
                 data-toggle="collapse" data-target="#collapse-periodos" aria-expanded="{{ $periodosAbertos ? 'true' : 'false' }}">
                <span class="font-weight-bold">
                    Períodos letivos
                    <span class="badge badge-secondary">{{ $anoLetivo->periodos->count() }}</span>
                    <i class="fa fa-chevron-down collapse-icon ml-1"></i>
                </span>
                @if($anoLetivo->periodos->isEmpty() && ($anoLetivo->periodicidade ?? null) !== 'personalizado')
                    <span class="badge badge-warning">pendente</span>
                @endif
            </div>
            <div id="collapse-periodos" class="collapse {{ $periodosAbertos ? 'show' : '' }}">
            <div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:60px">Ordem</th>
                            <th>Período</th>
                            <th>Início</th>
                            <th>Fim</th>
                            <th>Status</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($anoLetivo->periodos->sortBy('ordem') as $periodo)
                            <tr>
                                <td>{{ $periodo->ordem }}º</td>
                                <td class="font-weight-bold">{{ $periodo->nome }}</td>
                                <td>{{ optional($periodo->data_inicio)->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ optional($periodo->data_fim)->format('d/m/Y') ?? '—' }}</td>
                                <td>
                                    @if($periodo->esta_ativo)
                                        <span class="badge badge-success">Ativo</span>
                                    @else
                                        <span class="badge badge-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('periodo_letivo.show', $periodo->id) }}" class="btn btn-xs btn-info" title="Detalhes"><i class="fa fa-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-calendar-alt fa-2x mb-2 d-block"></i>
                                    Nenhum período letivo. Ajuste a periodicidade na edição do ano ou gere automaticamente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="font-weight-bold collapse-toggle" data-toggle="collapse" data-target="#collapse-turmas" aria-expanded="{{ $turmasAbertas ? 'true' : 'false' }}">
                    Turmas
                    <span class="badge badge-secondary">{{ $turmas->count() }}</span>
                    <i class="fa fa-chevron-down collapse-icon ml-1"></i>
                </span>
                <div>
                    @if($podeGerar)
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modal-gerar-lote"><i class="fa fa-layer-group"></i> Geração em massa</button>
                        <a href="{{ route('turma.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nova turma</a>
                    @endif
                </div>
            </div>
            <div id="collapse-turmas" class="collapse {{ $turmasAbertas ? 'show' : '' }}">
            <div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Turma</th>
                            <th>Etapa</th>
                            <th>Turno</th>
                            <th>Disciplinas</th>
                            <th>Matrículas</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($turmas as $turma)
                            <tr>
                                <td class="font-weight-bold">{{ $turma->nome }}</td>
                                <td>{{ $turma->etapa->nome ?? '—' }}</td>
                                <td>{{ \App\Models\Turma::TURNOS[$turma->turno ?? ''] ?? '—' }}</td>
                                <td>
                                    @php $qtdDisc = $turma->disciplinas->count(); @endphp
                                    @if($qtdDisc > 0)
                                        <span class="badge badge-info">{{ $qtdDisc }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $turma->matriculas_count ?? 0 }}</td>
                                <td class="text-right">
                                    <a href="{{ route('turma.show', $turma->id) }}" class="btn btn-xs btn-info" title="Detalhes"><i class="fa fa-eye"></i></a>
                                    @can('turma.edit')
                                        <a href="{{ route('turma.edit', $turma->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-chalkboard fa-2x mb-2 d-block"></i>
                                    Nenhuma turma neste ano letivo.
                                    @if($podeGerar) Use a <strong>geração em massa</strong> acima para criar todas de uma vez. @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div>
            </div>
        </div>

        @include('turma.partials.gerador-lote', [
            'anoLetivo' => $anoLetivo,
            'etapasOpcoes' => $etapasOpcoes,
            'turnosOpcoes' => $turnosOpcoes,
            'nomesExistentes' => $nomesExistentes,
            'podeGerar' => $podeGerar,
        ])

        @if($podeReplicar && $turmas->count() >= 2)
            <div class="card">
                <div class="card-header collapse-toggle font-weight-bold"
                     data-toggle="collapse" data-target="#collapse-replicar" aria-expanded="{{ $replicarAberto ? 'true' : 'false' }}">
                    <i class="fa fa-copy"></i> Replicar organização entre turmas
                    <i class="fa fa-chevron-down collapse-icon ml-1"></i>
                    <small class="text-muted font-weight-normal">— copie disciplinas e professores da turma modelo para as irmãs (A, B, C...)</small>
                </div>
                <div id="collapse-replicar" class="collapse {{ $replicarAberto ? 'show' : '' }}">
                <div class="card-body">
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
                                <div id="rep-disciplinas" class="border rounded p-2 bg-light" style="max-height: 180px; overflow-y: auto; font-size: .9rem;">
                                    <span class="text-muted">Selecione a turma de origem para ver as disciplinas.</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold">Professores da origem</label>
                                <div id="rep-professores" class="border rounded p-2 bg-light" style="max-height: 180px; overflow-y: auto; font-size: .9rem;">
                                    <span class="text-muted">Selecione a turma de origem para ver os professores.</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-8">
                                <label class="font-weight-bold">Copiar para as turmas <span class="text-danger">*</span></label>
                                <div id="rep-destinos" class="border rounded p-2" style="max-height: 160px; overflow-y: auto;">
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
@stop
