@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$turma->nome" subtitle="Disciplinas ofertadas e professores" />
@stop

@section('content')
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
                        @if($turma->estaAtiva())
                            <span class="badge badge-success">Ativa</span>
                        @else
                            <span class="badge badge-secondary">Inativa</span>
                        @endif
                    </dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('turma.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
                @can('turma.edit')
                    <a href="{{ route('turma.edit', $turma->id) }}" class="btn btn-warning btn-sm">Editar</a>
                @endcan
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header font-weight-bold">Disciplinas ofertadas ({{ $turma->disciplinas->count() }})</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Disciplina</th>
                            <th>Carga horária semanal</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($turma->disciplinas as $disciplina)
                            <tr>
                                <td>{{ $disciplina->nome }} @if($disciplina->sigla)<span class="badge badge-info ml-1">{{ $disciplina->sigla }}</span>@endif</td>
                                <td>
                                    <form method="POST" action="{{ route('turma.disciplina.update', [$turma->id, $disciplina->id]) }}" class="d-flex align-items-center" style="gap:.4rem;">
                                        @csrf @method('PUT')
                                        <input type="number" name="carga_horaria_semanal" class="form-control form-control-sm" style="width:90px;" min="1" max="40" value="{{ $disciplina->pivot->carga_horaria_semanal }}">
                                        @can('turma.disciplina.update')
                                            <button class="btn btn-xs btn-outline-primary">OK</button>
                                        @endcan
                                    </form>
                                </td>
                                <td class="text-right">
                                    @can('turma.disciplina.destroy')
                                        <form method="POST" action="{{ route('turma.disciplina.destroy', [$turma->id, $disciplina->id]) }}" onsubmit="return confirm('Remover esta disciplina da turma?');" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-outline-danger">Remover</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Nenhuma disciplina ofertada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @can('turma.disciplina.store')
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
                            <input type="number" name="carga_horaria_semanal" class="form-control form-control-sm" style="width:90px;" min="1" max="40" placeholder="—">
                        </div>
                        <button class="btn btn-sm btn-primary">Ofertar</button>
                    </form>
                </div>
                @endif
            @endcan
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Professores atribuídos ({{ $turma->atribuicoes->count() }})</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
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
                                    @if($atribuicao->estaAtiva())
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
                </table>
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
    </div>
</div>
@stop
