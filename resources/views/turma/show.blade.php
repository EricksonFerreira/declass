@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$turma->nome" subtitle="Disciplinas ofertadas e professores" />
@stop

@push('css')
<style>
    /* Mobile: acoes principais ocupam a linha inteira */
    @media (max-width: 576px) {
        .turma-acoes .btn,
        .turma-acoes .dropdown { flex: 1 1 100%; }
        .turma-acoes .dropdown .btn { width: 100%; }
    }
</style>
@endpush

@section('content')
@php
    $alunosAtivos = $turma->matriculas->where('status', 'ativa')->count();
    $isProfessor = auth()->user()->isProfessor();
    $totalCarga = $isProfessor
        ? (int) $minhasAulas->sum()
        : $disciplinasVisiveis->sum(fn ($d) => (int) $d->pivot->carga_horaria_semanal);
    $professoresAtivos = $turma->atribuicoes->filter->esta_ativa->pluck('user_id')->unique()->count();
    $larguraStat = $isProfessor ? 4 : 3;
    $ocultarStats = $isProfessor || auth()->user()->isSecretaria();
@endphp

@unless($ocultarStats)
<div class="row mb-3">
    <div class="col-md-{{ $larguraStat }} col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $alunosAtivos }}</h3>
                <p>Alunos ativos</p>
            </div>
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
        </div>
    </div>
    <div class="col-md-{{ $larguraStat }} col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $disciplinasVisiveis->count() }}</h3>
                <p>{{ $isProfessor ? 'Minhas disciplinas' : 'Disciplinas' }}</p>
            </div>
            <div class="icon"><i class="fas fa-book"></i></div>
        </div>
    </div>
    @unless($isProfessor)
    <div class="col-md-{{ $larguraStat }} col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $professoresAtivos }}</h3>
                <p>Professores</p>
            </div>
            <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
        </div>
    </div>
    @endunless
    <div class="col-md-{{ $larguraStat }} col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $totalCarga }}{{ $isProfessor ? '' : 'h' }}</h3>
                <p>{{ $isProfessor ? 'Minhas aulas/semana' : 'Carga semanal' }}</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
</div>
@endunless

<div class="mb-3 d-flex flex-wrap align-items-center turma-acoes" style="gap:.5rem;">
    @can('diario.frequencia')
        <a href="{{ route('turma.chamada.create', $turma->id) }}" class="btn btn-success"><i class="fa fa-clipboard-check"></i> {{ $diarioHoje ? 'Chamada de hoje' : 'Fazer chamada' }}</a>
    @endcan
    @can('avaliacao.create')
        <a href="{{ route('turma.avaliacao.create', $turma->id) }}" class="btn btn-outline-danger btn-sm"><i class="fa fa-star"></i> Nova avaliação</a>
    @endcan
    <div class="dropdown">
        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-ellipsis-h"></i> Mais ações
        </button>
        <div class="dropdown-menu">
            @can('turma.grade')
                <a href="{{ route('turma.grade', $turma->id) }}" class="dropdown-item"><i class="fa fa-calendar-week mr-1"></i> Grade horária</a>
            @endcan
            @can('calendario.index')
                <a href="{{ route('turma.calendario', $turma->id) }}" class="dropdown-item"><i class="fa fa-calendar-check mr-1"></i> Calendário letivo</a>
            @endcan
            @can('turma.grade.pdf')
                <a href="{{ route('turma.grade.pdf', $turma->id) }}" class="dropdown-item"><i class="fa fa-file-pdf mr-1"></i> Quadro em PDF</a>
            @endcan
            @can('matricula.create')
                <a href="{{ route('turma.matricula.create', $turma->id) }}" class="dropdown-item"><i class="fa fa-user-plus mr-1"></i> Nova matrícula</a>
            @endcan
            @can('justificativa.index')
                <a href="{{ route('turma.justificativas', $turma->id) }}" class="dropdown-item"><i class="fa fa-file-medical mr-1"></i> Atestados e abonos</a>
            @endcan
            @can('turma.edit')
                <div class="dropdown-divider"></div>
                <a href="{{ route('turma.edit', $turma->id) }}" class="dropdown-item"><i class="fa fa-edit mr-1"></i> Editar turma</a>
            @endcan
        </div>
    </div>
</div>

<ul class="nav nav-tabs nav-tabs-app" id="turmaTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="tab-visao-link" data-toggle="tab" href="#tab-visao" role="tab"><i class="fas fa-info-circle mr-1"></i> Visão geral</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-alunos-link" data-toggle="tab" href="#tab-alunos" role="tab"><i class="fas fa-user-graduate mr-1"></i> Alunos <span class="badge badge-light border ml-1">{{ $alunosAtivos }}</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-registros-link" data-toggle="tab" href="#tab-registros" role="tab"><i class="fas fa-book-open mr-1"></i> Diário e avaliações <span class="badge badge-light border ml-1">{{ $registros->count() }}</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-tarefas-link" data-toggle="tab" href="#tab-tarefas" role="tab"><i class="fas fa-tasks mr-1"></i> Atividades <span class="badge badge-{{ $tarefasCorrecao->sum('pendentes_count') > 0 ? 'danger' : 'light border' }} ml-1">{{ $tarefasCorrecao->sum('pendentes_count') > 0 ? $tarefasCorrecao->sum('pendentes_count').' pendentes' : $tarefasCorrecao->count() }}</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-materiais-link" data-toggle="tab" href="#tab-materiais" role="tab"><i class="fas fa-folder-open mr-1"></i> Materiais <span class="badge badge-light border ml-1">{{ $materiais->count() }}</span></a>
    </li>
</ul>

<div class="tab-content pt-3">
<div class="tab-pane fade show active" id="tab-visao" role="tabpanel">
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header font-weight-bold">Dados da Turma</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Nome</dt><dd class="col-sm-7">{{ $turma->nome }}</dd>
                    <dt class="col-sm-5">Escola</dt><dd class="col-sm-7">{{ $turma->escola->nome ?? '—' }}</dd>
                    <dt class="col-sm-5">Ano letivo</dt><dd class="col-sm-7">{{ $turma->anoLetivo->ano ?? '—' }}</dd>
                    <dt class="col-sm-5">Etapa</dt><dd class="col-sm-7">{{ $turma->etapa->nome ?? '—' }}</dd>
                    <dt class="col-sm-5">Turno</dt><dd class="col-sm-7">{{ $turnos[$turma->turno ?? ''] ?? '—' }}</dd>
                    <dt class="col-sm-5">Sala</dt><dd class="col-sm-7">{{ $turma->sala ?? '—' }}</dd>
                    <dt class="col-sm-5">Capacidade</dt><dd class="col-sm-7">{{ $turma->capacidade_maxima ?? '—' }}</dd>
                    <dt class="col-sm-5">Status</dt>
                    <dd class="col-sm-7">
                        @if($turma->esta_ativa)
                            <span class="badge badge-success">Ativa</span>
                        @else
                            <span class="badge badge-secondary">Inativa</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header font-weight-bold">{{ $isProfessor ? 'Disciplinas que você leciona' : 'Disciplinas ofertadas' }} ({{ $disciplinasVisiveis->count() }})</div>
            <div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Disciplina</th>
                            <th>{{ $isProfessor ? 'Minhas aulas/semana' : 'Carga horária · Média aprovação' }}</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($disciplinasVisiveis as $disciplina)
                            <tr>
                                <td><a href="{{ route('turma.disciplina.show', [$turma->id, $disciplina->id]) }}">{{ $disciplina->nome }}</a> @if($disciplina->sigla)<span class="badge badge-info ml-1">{{ $disciplina->sigla }}</span>@endif</td>
                                <td>
                                    @if($isProfessor)
                                        <span class="font-weight-bold">{{ (int) ($minhasAulas[$disciplina->id] ?? 0) }}</span>
                                        <span class="text-muted small">aula(s)</span>
                                    @else
                                        <form method="POST" action="{{ route('turma.disciplina.update', [$turma->id, $disciplina->id]) }}" class="d-flex align-items-center" style="gap:.4rem;">
                                            @csrf @method('PUT')
                                            <input type="number" name="carga_horaria_semanal" class="form-control form-control-sm" style="width:80px;" min="1" max="40" value="{{ $disciplina->pivot->carga_horaria_semanal }}" title="Carga horária semanal" @cannot('turma.disciplina.update') disabled @endcannot>
                                            <input type="number" name="media_aprovacao" class="form-control form-control-sm" style="width:80px;" min="0" max="10" step="0.1" value="{{ $disciplina->pivot->media_aprovacao }}" placeholder="{{ $disciplina::MEDIA_APROVACAO_PADRAO }}" title="Média de aprovação (vazio = {{ $disciplina::MEDIA_APROVACAO_PADRAO }})" @cannot('turma.disciplina.update') disabled @endcannot>
                                            @can('turma.disciplina.update')
                                                <button class="btn btn-xs btn-outline-primary">OK</button>
                                            @endcan
                                        </form>
                                    @endif
                                </td>
                                <td class="text-right text-nowrap">
                                    @if($isProfessor)
                                        @can('diario.frequencia')
                                            <a href="{{ route('diario.chamada', ['turma_id' => $turma->id, 'disciplina_id' => $disciplina->id, 'data' => now()->toDateString()]) }}" class="btn btn-xs btn-outline-success" title="Fazer chamada desta disciplina"><i class="fa fa-clipboard-check"></i></a>
                                        @endcan
                                        @can('avaliacao.create')
                                            <a href="{{ route('turma.avaliacao.create', $turma->id) }}?disciplina_id={{ $disciplina->id }}" class="btn btn-xs btn-outline-danger" title="Nova avaliação nesta disciplina"><i class="fa fa-star"></i></a>
                                        @endcan
                                    @else
                                        @can('turma.disciplina.destroy')
                                            <form method="POST" action="{{ route('turma.disciplina.destroy', [$turma->id, $disciplina->id]) }}" onsubmit="return confirm('Remover esta disciplina da turma?');" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-xs btn-outline-danger">Remover</button>
                                            </form>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">{{ $isProfessor ? 'Você não leciona nenhuma disciplina nesta turma.' : 'Nenhuma disciplina ofertada.' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div>
            @can('turma.disciplina.store')
                @unless($isProfessor)
                @if(count($disciplinasOpcoes) > 0)
                <div class="card-footer">
                    <form method="POST" action="{{ route('turma.disciplina.store', $turma->id) }}" class="d-flex align-items-end flex-wrap" style="gap:.5rem;">
                        @csrf
                        <div class="flex-grow-1" style="min-width:200px;">
                            <label class="form-label mb-0">Ofertar disciplina</label>
                            <select name="disciplina_id" class="form-control form-control-sm" required>
                                <option value="">Selecionar disciplina...</option>
                                @foreach($disciplinasOpcoes as $did => $dnome)
                                    <option value="{{ $did }}">{{ $dnome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label mb-0">C.H. semanal</label>
                            <input type="number" name="carga_horaria_semanal" class="form-control form-control-sm" style="width:80px;" min="1" max="40" placeholder="—">
                        </div>
                        <div>
                            <label class="form-label mb-0">Média aprovação</label>
                            <input type="number" name="media_aprovacao" class="form-control form-control-sm" style="width:80px;" min="0" max="10" step="0.1" placeholder="{{ \App\Models\Disciplina::MEDIA_APROVACAO_PADRAO }}">
                        </div>
                        <button class="btn btn-sm btn-primary">Ofertar</button>
                    </form>
                </div>
                @endif
                @endunless
            @endcan
        </div>

        @can('turma.atribuicao.store')
        <div class="card">
            <div class="card-header font-weight-bold">Professores atribuídos ({{ $turma->atribuicoes->count() }})</div>
            <div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Professor</th>
                            <th>Disciplina</th>
                            <th>Papel</th>
                            <th>Status</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($turma->atribuicoes as $atribuicao)
                            <tr>
                                <td class="font-weight-bold">{{ $atribuicao->user->nome ?? '—' }}</td>
                                <td>{{ $atribuicao->disciplina->nome ?? 'Toda a turma' }}</td>
                                <td><span class="badge badge-secondary">{{ $papeis[$atribuicao->papel] ?? $atribuicao->papel }}</span></td>
                                <td>
                                    @if($atribuicao->esta_ativa)
                                        <span class="badge badge-success">Ativa</span>
                                    @else
                                        <span class="badge badge-secondary">Inativa</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @can('turma.atribuicao.destroy')
                                        <form method="POST" action="{{ route('turma.atribuicao.destroy', [$turma->id, $atribuicao->id]) }}" onsubmit="return confirm('Remover esta atribuição?');" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-outline-danger">Remover</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Nenhum professor atribuído.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div>
            @can('turma.atribuicao.store')
            <div class="card-footer">
                <form method="POST" action="{{ route('turma.atribuicao.store', $turma->id) }}" class="d-flex align-items-end flex-wrap" style="gap:.5rem;">
                    @csrf
                    <div class="flex-grow-1" style="min-width:180px;">
                        <label class="form-label mb-0">Professor</label>
                        <select name="user_id" class="form-control form-control-sm" required>
                            <option value="">Selecionar usuário...</option>
                            @foreach($professoresOpcoes as $uid => $unome)
                                <option value="{{ $uid }}">{{ $unome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-grow-1" style="min-width:160px;">
                        <label class="form-label mb-0">Disciplina (opcional)</label>
                        <select name="disciplina_id" class="form-control form-control-sm">
                            <option value="">Toda a turma</option>
                            @foreach($disciplinasOfertadas as $did => $dnome)
                                <option value="{{ $did }}">{{ $dnome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label mb-0">Papel</label>
                        <select name="papel" class="form-control form-control-sm" required>
                            @foreach($papeis as $pk => $pl)
                                <option value="{{ $pk }}">{{ $pl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-sm btn-primary">Atribuir</button>
                </form>
            </div>
            @endcan
        </div>
        @endcan
    </div>
</div>
</div>

<div class="tab-pane fade" id="tab-registros" role="tabpanel">
    <form method="GET" action="{{ route('turma.show', $turma->id) }}#tab-registros" class="card card-outline-secondary mb-3">
        <div class="card-body py-2">
            <div class="form-row align-items-end">
                <div class="col-md-2 col-6">
                    <label class="form-label mb-1 small">Disciplina</label>
                    <select name="f_disciplina" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        @foreach($disciplinasVisiveis as $disc)
                            <option value="{{ $disc->id }}" {{ (string) ($filtrosRegistros['disciplina_id'] ?? '') === (string) $disc->id ? 'selected' : '' }}>{{ $disc->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label mb-1 small">De</label>
                    <input type="date" name="f_ini" class="form-control form-control-sm" value="{{ $filtrosRegistros['data_inicio'] ?? '' }}">
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label mb-1 small">Até</label>
                    <input type="date" name="f_fim" class="form-control form-control-sm" value="{{ $filtrosRegistros['data_fim'] ?? '' }}">
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label mb-1 small">Tipo <small class="text-muted">(só avaliações)</small></label>
                    <select name="f_tipo" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach(\App\Models\Avaliacao::TIPOS as $tk => $tl)
                            <option value="{{ $tk }}" {{ ($filtrosRegistros['tipo'] ?? '') === $tk ? 'selected' : '' }}>{{ $tl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label mb-1 small">Período <small class="text-muted">(só avaliações)</small></label>
                    <select name="f_periodo" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($periodosLetivos as $p)
                            <option value="{{ $p->id }}" {{ (string) ($filtrosRegistros['periodo_letivo_id'] ?? '') === (string) $p->id ? 'selected' : '' }}>{{ $p->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-12 text-right">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
                    <a href="{{ route('turma.show', $turma->id) }}#tab-registros" class="btn btn-sm btn-outline-secondary">Limpar</a>
                </div>
            </div>
        </div>
    </form>
    <div class="card">
        <div class="card-header font-weight-bold">Diário e avaliações ({{ $registros->count() }})</div>
        <div class="card-body p-0">
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:115px">Registro</th>
                        <th style="width:95px">Data</th>
                        <th>Item</th>
                        <th>Disciplina</th>
                        <th>Detalhe</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $r)
                        <tr>
                            <td>
                                @if($r['tipo'] === 'diario')
                                    <span class="badge badge-info"><i class="fa fa-book"></i> Diário</span>
                                @else
                                    <span class="badge badge-warning"><i class="fa fa-star"></i> Avaliação</span>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $r['data']?->format('d/m/Y') ?? '—' }}</td>
                            <td class="{{ $r['tipo'] === 'avaliacao' ? 'font-weight-bold' : '' }}">
                                {{ $r['tipo'] === 'diario' ? \Illuminate\Support\Str::limit($r['item'], 60) : $r['item'] }}
                            </td>
                            <td>{{ $r['disciplina'] ?? '—' }}</td>
                            <td class="text-muted small">{{ $r['detalhe'] ?? '—' }}</td>
                            <td class="text-right text-nowrap">
                                @if($r['tipo'] === 'diario')
                                    @php $d = $r['model']; @endphp
                                    @can('diario.frequencia')
                                        <a href="{{ route('diario.chamada', ['turma_id' => $d->turma_id, 'disciplina_id' => $d->disciplina_id, 'data' => $d->data?->format('Y-m-d')]) }}" class="btn btn-xs btn-success" title="Chamada"><i class="fa fa-clipboard-check"></i></a>
                                    @endcan
                                    @can('diario.edit')
                                        <a href="{{ route('diario.edit', $d->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                    @endcan
                                @else
                                    @php $a = $r['model']; @endphp
                                    @can('avaliacao.notas')
                                        <a href="{{ route('avaliacao.notas', $a->id) }}" class="btn btn-xs btn-success" title="Lançar notas"><i class="fa fa-pencil-alt"></i></a>
                                    @endcan
                                    @can('avaliacao.edit')
                                        <a href="{{ route('avaliacao.edit', $a->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Nenhum registro nesta turma.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table></div>
        </div>
        @can('avaliacao.create')
            <div class="card-footer">
                <a href="{{ route('turma.avaliacao.create', $turma->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-plus"></i> Nova avaliação</a>
            </div>
        @endcan
    </div>
</div>


<div class="tab-pane fade" id="tab-alunos" role="tabpanel">
        @php $disciplinaFreq = $disciplinasVisiveis->firstWhere('id', (int) request('al_disciplina')); @endphp
        <form method="GET" action="{{ route('turma.show', $turma->id) }}#tab-alunos" class="card card-outline-secondary mb-3">
            <div class="card-body py-2">
                <div class="form-row align-items-end">
                    <div class="col-md-4 col-8">
                        <label class="form-label mb-1 small">Frequência por disciplina</label>
                        <select name="al_disciplina" class="form-control form-control-sm">
                            <option value="">Todas as disciplinas (consolidado)</option>
                            @foreach($disciplinasVisiveis as $disc)
                                <option value="{{ $disc->id }}" {{ (string) request('al_disciplina') === (string) $disc->id ? 'selected' : '' }}>{{ $disc->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-4 text-right">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
                        <a href="{{ route('turma.show', $turma->id) }}#tab-alunos" class="btn btn-sm btn-outline-secondary">Limpar</a>
                    </div>
                </div>
            </div>
        </form>
        <div class="card">
            <div class="card-header font-weight-bold">
                Lista de presença — Alunos matriculados ({{ $turma->matriculas->where('status', 'ativa')->count() }})
                @if($disciplinaFreq)
                    <span class="badge badge-info ml-1">{{ $disciplinaFreq->nome }}</span>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nº</th>
                            <th>Aluno</th>
                            <th class="text-center">Presenças</th>
                            <th class="text-center">Faltas</th>
                            <th class="text-center">Justif./Abon.</th>
                            <th class="text-center">Freq. %</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($turma->matriculas->where('status', 'ativa')->sortBy('numero_chamada') as $matricula)
                            @php
                                $freq = $presencas[$matricula->id] ?? null;
                                $pct = $freq && (int) $freq->total > 0
                                    ? round(((int) $freq->total - (int) $freq->falta) * 100 / (int) $freq->total)
                                    : null;
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $matricula->numero_chamada ?? '—' }}</td>
                                <td>
                                    @can('aluno.show')
                                        <a href="{{ route('aluno.show', $matricula->aluno_id) }}" class="font-weight-bold">{{ $matricula->aluno->pessoa->nome ?? '—' }}</a>
                                    @else
                                        {{ $matricula->aluno->pessoa->nome ?? '—' }}
                                    @endcan
                                </td>
                                <td class="text-center">{{ $freq?->presente ?? '—' }}</td>
                                <td class="text-center">{{ $freq?->falta ?? '—' }}</td>
                                <td class="text-center" title="Justificadas / Abonadas">
                                    {{ $freq?->justificada ?? '—' }}@if($freq && (int) $freq->abonada > 0) <span class="text-muted">/ {{ $freq->abonada }}</span>@endif
                                </td>
                                <td class="text-center">
                                    @if($pct === null)
                                        <span class="text-muted">—</span>
                                    @else
                                        <span class="badge badge-{{ $pct >= 75 ? 'success' : 'danger' }}">{{ $pct }}%</span>
                                    @endif
                                </td>
                                <td>
                                    @php $cor = ['ativa' => 'success', 'trancada' => 'warning', 'cancelada' => 'danger', 'concluida' => 'info'][$matricula->status] ?? 'secondary'; @endphp
                                    <span class="badge badge-{{ $cor }}">{{ \App\Models\Matricula::STATUS[$matricula->status] ?? $matricula->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">Nenhum aluno matriculado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div>
            @can('matricula.create')
                <div class="card-footer">
                    <a href="{{ route('turma.matricula.create', $turma->id) }}" class="btn btn-sm btn-primary">Nova matrícula nesta turma</a>
                </div>
            @endcan
        </div>
</div>

<div class="tab-pane fade" id="tab-tarefas" role="tabpanel">
    <div class="card">
        <div class="card-header font-weight-bold d-flex justify-content-between align-items-center flex-wrap">
            <span><i class="fas fa-tasks mr-1"></i> Atividades e provas da turma</span>
            @can('tarefa.create')
                <a href="{{ route('turma.tarefa.create', $turma->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa fa-plus mr-1"></i> Nova tarefa</a>
            @endcan
        </div>
        <div class="list-group list-group-flush">
            @forelse($tarefasCorrecao as $tarefa)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
                        <div class="min-w-0">
                            <strong>{{ $tarefa->titulo }}</strong>
                            @if($tarefa->tipo !== 'atividade')
                                <span class="badge badge-{{ $tarefa->tipo === 'prova' ? 'danger' : 'warning' }}">{{ $tarefa->tipo_label }}</span>
                            @endif
                            <small class="text-muted d-block">
                                {{ $tarefa->disciplina->nome ?? 'Geral' }} · entrega até {{ $tarefa->data_entrega->format('d/m/Y') }}
                                · {{ $tarefa->total_count }} entrega(s)
                            </small>
                        </div>
                        <div class="d-flex align-items-center text-nowrap" style="gap:.5rem;">
                            @if($tarefa->pendentes_count > 0)
                                <span class="badge badge-danger">{{ $tarefa->pendentes_count }} aguardando</span>
                            @else
                                <span class="badge badge-success">em dia</span>
                            @endif
                            @can('tarefa.entregas')
                                <a href="{{ route('tarefa.entregas', $tarefa->id) }}" class="btn btn-sm btn-{{ $tarefa->pendentes_count > 0 ? 'danger' : 'outline-secondary' }}"><i class="fa fa-check-double mr-1"></i> Corrigir</a>
                            @endcan
                            @can('tarefa.edit')
                                <a href="{{ route('tarefa.edit', $tarefa->id) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fa fa-edit"></i></a>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="list-group-item text-muted text-center py-4">Nenhuma atividade publicada nesta turma.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-materiais" role="tabpanel">
    <div class="row">
        <div class="col-lg-5">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title font-weight-bold">Publicar material</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('turma.material.store', $turma->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Título</label>
                            <input type="text" name="titulo" class="form-control" maxlength="200" required placeholder="Ex.: Lista de exercícios — cap. 3">
                        </div>
                        <div class="form-group">
                            <label>Disciplina</label>
                            <select name="disciplina_id" class="form-control">
                                <option value="">Geral (todas)</option>
                                @foreach($disciplinasVisiveis as $d)
                                    <option value="{{ $d->id }}">{{ $d->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Descrição <small class="text-muted">(opcional)</small></label>
                            <textarea name="descricao" class="form-control" rows="2" maxlength="2000"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Arquivo</label>
                            <input type="file" name="arquivo" class="form-control-file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.txt">
                            <small class="text-muted">PDF, Office, imagem ou ZIP — até 15MB. Ou informe um link:</small>
                        </div>
                        <div class="form-group">
                            <input type="url" name="url" class="form-control" maxlength="500" placeholder="https://…">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-upload mr-1"></i> Publicar material</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title font-weight-bold">Materiais publicados</h3></div>
                <div class="list-group list-group-flush">
                    @forelse($materiais as $material)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                <div>
                                    <strong>{{ $material->titulo }}</strong>
                                    <small class="text-muted d-block">{{ $material->disciplina->nome ?? 'Geral' }} · {{ $material->autor->nome ?? '' }} · {{ $material->created_at?->format('d/m/Y') }}</small>
                                    @if($material->descricao)<small>{{ $material->descricao }}</small>@endif
                                </div>
                                <div class="d-flex align-items-center" style="gap:.4rem;">
                                    @if($material->arquivo_path)
                                        <a href="{{ asset('storage/'.$material->arquivo_path) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fa fa-download"></i></a>
                                    @elseif($material->url)
                                        <a href="{{ $material->url }}" target="_blank" rel="noopener" class="btn btn-xs btn-outline-primary"><i class="fa fa-external-link-alt"></i></a>
                                    @endif
                                    <form method="POST" action="{{ route('turma.material.destroy', [$turma->id, $material->id]) }}" onsubmit="return confirm('Remover este material?');" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-muted text-center py-4">Nenhum material publicado ainda.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
</div>
@stop

@section('js')
<script>
    $(function () {
        var hash = window.location.hash;
        if (hash && $('#turmaTabs a[href="' + hash + '"]').length) {
            $('#turmaTabs a[href="' + hash + '"]').tab('show');
        }
        $('#turmaTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            history.replaceState(null, '', e.target.hash);
        });
    });
</script>
@stop
