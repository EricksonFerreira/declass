@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="\App\Models\SolicitacaoMatricula::STATUS[$solicitacao->status] ?? $solicitacao->statusLabel()" subtitle="Solicitação #{{ $solicitacao->codigo }} — {{ $solicitacao->nome_aluno }}" />
@stop

@section('content')
@php
    $statusCores = ['pendente' => 'warning', 'em_analise' => 'info', 'aprovada' => 'success', 'recusada' => 'danger', 'cancelada' => 'secondary'];
@endphp

<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Dados da solicitação</h3>
                <div class="card-tools">
                    <span class="badge badge-{{ $statusCores[$solicitacao->status] ?? 'secondary' }}">{{ $solicitacao->statusLabel() }}</span>
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Código</dt>
                    <dd class="col-sm-8"><code class="text-dark font-weight-bold">#{{ $solicitacao->codigo }}</code></dd>

                    <dt class="col-sm-4">Aluno pretendido</dt>
                    <dd class="col-sm-8">{{ $solicitacao->nome_aluno }}
                        @if($solicitacao->data_nascimento_aluno)
                            <br><small class="text-muted">nasc. {{ $solicitacao->data_nascimento_aluno->format('d/m/Y') }}</small>
                        @endif
                        @if($solicitacao->sexo_aluno)<br><small class="text-muted">{{ ucfirst($solicitacao->sexo_aluno) }}</small>@endif
                    </dd>

                    <dt class="col-sm-4">Escola</dt><dd class="col-sm-8">{{ $solicitacao->escola->nome ?? '—' }}</dd>
                    <dt class="col-sm-4">Ano letivo</dt><dd class="col-sm-8">{{ $solicitacao->anoLetivo->ano ?? '—' }}</dd>
                    <dt class="col-sm-4">Etapa pretendida</dt><dd class="col-sm-8">{{ $solicitacao->etapa->nome ?? '—' }}</dd>
                    <dt class="col-sm-4">Turma de preferência</dt><dd class="col-sm-8">{{ $solicitacao->turma->nome ?? '—' }}</dd>

                    <dt class="col-sm-4">Responsável</dt>
                    <dd class="col-sm-8">{{ $solicitacao->nome_responsavel }}
                        @if($solicitacao->parentesco)<br><small class="text-muted">{{ \App\Models\SolicitacaoMatricula::PARENTESCOS[$solicitacao->parentesco] ?? $solicitacao->parentesco }}</small>@endif
                        @if($solicitacao->telefone_responsavel)<br><small class="text-muted">{{ $solicitacao->telefone_responsavel }}</small>@endif
                        @if($solicitacao->email_responsavel)<br><small class="text-muted">{{ $solicitacao->email_responsavel }}</small>@endif
                    </dd>

                    <dt class="col-sm-4">Origem</dt><dd class="col-sm-8">{{ \App\Models\SolicitacaoMatricula::ORIGENS[$solicitacao->origem] ?? $solicitacao->origem }}</dd>
                    <dt class="col-sm-4">Recebida em</dt><dd class="col-sm-8">{{ $solicitacao->created_at->format('d/m/Y H:i') }}</dd>
                    @if($solicitacao->observacoes)
                        <dt class="col-sm-4">Observações</dt><dd class="col-sm-8">{{ $solicitacao->observacoes }}</dd>
                    @endif
                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('solicitacao_matricula.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
                @if($solicitacao->estaPendente())
                    @can('solicitacao_matricula.edit')
                        <a href="{{ route('solicitacao_matricula.edit', $solicitacao->id) }}" class="btn btn-warning btn-sm">Editar</a>
                    @endcan
                @endif
            </div>
        </div>

        @if($solicitacao->status === 'aprovada')
            <div class="card">
                <div class="card-header"><h3 class="card-title font-weight-bold">Resultado da aprovação</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Analisado por</dt><dd class="col-sm-8">{{ $solicitacao->analista->name ?? '—' }} em {{ $solicitacao->data_analise?->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-4">Aluno criado</dt>
                        <dd class="col-sm-8">
                            @if($solicitacao->aluno)
                                <a href="{{ route('aluno.show', $solicitacao->aluno_id) }}">{{ $solicitacao->aluno->nome }}</a>
                                <br><small class="text-muted">RA: {{ $solicitacao->aluno->registro_academico ?? '—' }}</small>
                            @else — @endif
                        </dd>
                        <dt class="col-sm-4">Matrícula</dt>
                        <dd class="col-sm-8">
                            @if($solicitacao->matricula)
                                {{ $solicitacao->matricula->turma->nome ?? '—' }}
                                <span class="badge badge-success">{{ ucfirst($solicitacao->matricula->status) }}</span>
                                · chamada {{ $solicitacao->matricula->numero_chamada }}
                            @else — @endif
                        </dd>
                        <dt class="col-sm-4">Responsável</dt>
                        <dd class="col-sm-8">{{ $solicitacao->responsavel->pessoa->nome ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        @endif

        @if($solicitacao->status === 'recusada')
            <div class="card border-danger">
                <div class="card-header bg-danger text-white"><h3 class="card-title font-weight-bold">Motivo da recusa</h3></div>
                <div class="card-body">
                    <p class="mb-1">{{ $solicitacao->motivo_recusa }}</p>
                    <small class="text-muted">Por {{ $solicitacao->analista->name ?? '—' }} em {{ $solicitacao->data_analise?->format('d/m/Y H:i') }}</small>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-5">
        @if($solicitacao->estaPendente())
            @canany(['solicitacao_matricula.aprovar', 'solicitacao_matricula.recusar', 'solicitacao_matricula.cancelar'])
                <div class="card border-success">
                    <div class="card-header bg-success text-white"><h3 class="card-title font-weight-bold">Aprovar solicitação</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('solicitacao_matricula.aprovar', $solicitacao->id) }}">
                            @csrf
                            <div class="form-group">
                                <label>Turma para matrícula <span class="text-danger">*</span></label>
                                <select name="turma_id" class="form-control" required>
                                    <option value="">Selecione a turma...</option>
                                    @foreach($turmasAprovacao as $t)
                                        <option value="{{ $t->id }}" {{ $solicitacao->turma_id == $t->id ? 'selected' : '' }}>
                                            {{ $t->nome }}@if($t->etapa) — {{ $t->etapa->nome }} @endif @if($t->anoLetivo) · {{ $t->anoLetivo->ano }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Só aparecem turmas da escola da solicitação. A matrícula nasce ativa com o próximo número de chamada.</small>
                            </div>
                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Aprovar e criar aluno + matrícula?')">
                                <i class="fa fa-check"></i> Aprovar e matricular
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card border-danger">
                    <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Recusar / cancelar</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('solicitacao_matricula.recusar', $solicitacao->id) }}" class="mb-3">
                            @csrf
                            <div class="form-group">
                                <label>Motivo da recusa <span class="text-danger">*</span></label>
                                <textarea name="motivo_recusa" class="form-control" rows="2" maxlength="2000" required placeholder="Ex.: Turma lotada, documentos pendentes..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-outline-danger btn-block" onclick="return confirm('Recusar esta solicitação?')">Recusar</button>
                        </form>

                        <form method="POST" action="{{ route('solicitacao_matricula.cancelar', $solicitacao->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-block btn-sm" onclick="return confirm('Cancelar esta solicitação?')">Cancelar solicitação</button>
                        </form>
                    </div>
                </div>
            @endcan
        @endif
    </div>
</div>
@stop
