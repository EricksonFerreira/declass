@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@push('css')
<link href="{{ asset('css/select2.min.css') }}" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .sm-divider-or { display:flex; align-items:center; gap:.6rem; color:#9ca3af; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; margin:.9rem 0; }
    .sm-divider-or::before, .sm-divider-or::after { content:''; flex:1; border-top:1px solid #eef1f6; }
    .sm-status-lg { font-size:.95rem; font-weight:800; padding:.4rem .85rem; border-radius:8px; letter-spacing:.02em; }
    .sm-perfil { display:flex; align-items:center; gap:1rem; flex-wrap:wrap; }
    .sm-avatar { width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.35rem; font-weight:700; color:#fff; background:linear-gradient(135deg,#2563eb,#7c3aed); flex-shrink:0; }
    .sm-nome { font-size:1.15rem; font-weight:700; margin-bottom:2px; }
    .sm-meta { font-size:.78rem; color:#6b7280; }
    .sm-acoes { margin-left:auto; display:flex; gap:.35rem; flex-wrap:wrap; }
    .sm-sec { font-size:.76rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; margin:1rem 0 .5rem; }
    .sm-sec:first-child { margin-top:0; }
    .sm-info { display:grid; grid-template-columns:1fr; gap:.35rem; }
    .sm-item { display:flex; gap:.55rem; align-items:flex-start; padding:.42rem .6rem; border:1px solid #eef1f6; border-radius:8px; background:#fff; }
    .sm-item i { color:#2563eb; width:14px; margin-top:3px; text-align:center; }
    .sm-item label { display:block; font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; margin:0; }
    .sm-item span { font-size:.82rem; color:#1f2937; font-weight:600; }
</style>
@endpush

@section('content')
@php
    $statusCores = ['pendente' => 'warning', 'em_analise' => 'info', 'aprovada' => 'success', 'recusada' => 'danger', 'cancelada' => 'secondary'];
    $statusIcones = ['pendente' => 'fa-clock', 'em_analise' => 'fa-search', 'aprovada' => 'fa-check-circle', 'recusada' => 'fa-times-circle', 'cancelada' => 'fa-ban'];
    $statusCor = $statusCores[$solicitacao->status] ?? 'secondary';
    $iniciais = collect(explode(' ', trim($solicitacao->nome_aluno ?? '?')))->filter()->map(fn ($s) => mb_strtoupper(mb_substr($s, 0, 1)))->take(2)->implode('');
    $info = function (string $icon, string $label, $valor) {
        return '<div class="sm-item"><i class="fa '.$icon.'"></i><div><label>'.$label.'</label><span>'.$valor.'</span></div></div>';
    };
@endphp

{{-- Header --}}
<div class="card">
    <div class="card-body py-3 sm-perfil">
        <div class="sm-avatar">{{ $iniciais }}</div>
        <div>
            <div class="sm-nome">{{ $solicitacao->nome_aluno }}</div>
            <div class="sm-meta">
                <code class="text-dark font-weight-bold">#{{ $solicitacao->codigo }}</code>
                · {{ $solicitacao->escola->nome ?? '—' }}
                <span class="badge badge-{{ $statusCor }} sm-status-lg ml-2"><i class="fa {{ $statusIcones[$solicitacao->status] ?? 'fa-circle' }}"></i> {{ $solicitacao->status_label }}</span>
            </div>
        </div>
        <div class="sm-acoes">
            @if($solicitacao->esta_pendente)
                @can('solicitacao_matricula.edit')
                    <a href="{{ route('solicitacao_matricula.edit', $solicitacao->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> Editar</a>
                @endcan
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fa fa-file-signature mr-1"></i> Dados da solicitação</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="sm-sec"><i class="fa fa-child mr-1"></i> Aluno pretendido</div>
                        <div class="sm-info">
                            {!! $info('fa-user', 'Nome', e($solicitacao->nome_aluno)) !!}
                            {!! $info('fa-birthday-cake', 'Nascimento', e($solicitacao->nascimento_aluno_br ?? '—')) !!}
                            {!! $info('fa-venus-mars', 'Sexo', e(ucfirst($solicitacao->sexo_aluno ?? '—'))) !!}
                        </div>
                        <div class="sm-sec"><i class="fa fa-graduation-cap mr-1"></i> Vaga pretendida</div>
                        <div class="sm-info">
                            {!! $info('fa-school', 'Escola', e($solicitacao->escola->nome ?? '—')) !!}
                            {!! $info('fa-calendar-alt', 'Ano letivo', e($solicitacao->anoLetivo->ano ?? '—')) !!}
                            {!! $info('fa-layer-group', 'Etapa', e($solicitacao->etapa->nome ?? '—')) !!}
                            {!! $info('fa-users', 'Turma de preferência', e($solicitacao->turma->nome ?? '—').($solicitacao->turma && $solicitacao->turma->finalizada ? ' <span class="badge badge-danger ml-1">inativa</span>' : '')) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sm-sec"><i class="fa fa-user-friends mr-1"></i> Responsável</div>
                        <div class="sm-info">
                            {!! $info('fa-user', 'Nome', e($solicitacao->nome_responsavel)) !!}
                            {!! $info('fa-link', 'Parentesco', e($parentescoLabel ?? '—')) !!}
                            {!! $info('fa-phone', 'Telefone', e($solicitacao->telefone_responsavel ?? '—')) !!}
                            {!! $info('fa-envelope', 'E-mail', e($solicitacao->email_responsavel ?? '—')) !!}
                        </div>
                        <div class="sm-sec"><i class="fa fa-inbox mr-1"></i> Registro</div>
                        <div class="sm-info">
                            {!! $info('fa-door-open', 'Origem', e($origemLabel)) !!}
                            {!! $info('fa-clock', 'Recebida em', e($solicitacao->recebida_em)) !!}
                        </div>
                    </div>
                </div>
                @if($solicitacao->observacoes)
                    <div class="sm-sec mt-3"><i class="fa fa-sticky-note mr-1"></i> Observações do responsável</div>
                    <div class="sm-item"><i class="fa fa-comment-dots"></i><div><span>{{ $solicitacao->observacoes }}</span></div></div>
                @endif
            </div>
        </div>

        @if($solicitacao->status === 'aprovada')
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fa fa-check-circle text-success mr-1"></i> Resultado da aprovação</h3></div>
                <div class="card-body">
                    <div class="sm-info">
                        {!! $info('fa-user-check', 'Analisado por', e(($solicitacao->analista->name ?? '—').' em '.($solicitacao->analisada_em ?? '—'))) !!}
                        @if($solicitacao->aluno)
                            {!! $info('fa-user-graduate', 'Aluno criado', '<a href="'.route('aluno.show', $solicitacao->aluno_id).'">'.e($solicitacao->aluno->nome).'</a> <small class="text-muted">RA '.e($solicitacao->aluno->registro_academico ?? '—').'</small>') !!}
                        @endif
                        @if($solicitacao->matricula)
                            {!! $info('fa-id-card', 'Matrícula', e($solicitacao->matricula->turma->nome ?? '—').' <span class="badge badge-success">'.e(ucfirst($solicitacao->matricula->status)).'</span> <small class="text-muted">· chamada '.e($solicitacao->matricula->numero_chamada).'</small>') !!}
                        @endif
                        {!! $info('fa-user-friends', 'Responsável', e($solicitacao->responsavel->pessoa->nome ?? '—')) !!}
                    </div>
                </div>
            </div>
        @endif

        @if($solicitacao->status === 'recusada')
            <div class="card card-outline card-danger">
                <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fa fa-times-circle text-danger mr-1"></i> Motivo da recusa</h3></div>
                <div class="card-body">
                    <p class="mb-1">{{ $solicitacao->motivo_recusa }}</p>
                    <small class="text-muted">Por {{ $solicitacao->analista->name ?? '—' }} em {{ $solicitacao->analisada_em }}</small>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-5">
        @if($solicitacao->esta_pendente)
            @canany(['solicitacao_matricula.aprovar', 'solicitacao_matricula.recusar', 'solicitacao_matricula.cancelar'])
                <div class="card card-outline card-success">
                    <div class="card-header bg-success"><h3 class="card-title font-weight-bold text-white"><i class="fa fa-check-circle mr-1"></i> Aprovar solicitação</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('solicitacao_matricula.aprovar', $solicitacao->id) }}">
                            @csrf
                            <div class="form-group">
                                <label>
                                    Turma para matrícula <span class="text-danger">*</span>
                                    <span class="badge badge-success ml-1">somente ativas</span>
                                </label>
                                <select name="turma_id" id="turmaAprovacao" class="form-control" required>
                                    <option value=""></option>
                                    @foreach($turmasPorEtapa as $etapaNome => $turmasEtapa)
                                        <optgroup label="{{ $etapaNome }}">
                                            @foreach($turmasEtapa as $t)
                                                <option value="{{ $t->id }}" {{ $solicitacao->turma_id == $t->id ? 'selected' : '' }}>
                                                    {{ $t->nome }}@if($t->anoLetivo) · {{ $t->anoLetivo->ano }} @endif
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <small class="text-muted">A lista exibe só turmas ativas de ano letivo vigente na escola da solicitação. A matrícula nasce ativa com o próximo número de chamada.</small>
                                @if($solicitacao->turma && $solicitacao->turma->finalizada)
                                    <div class="alert alert-warning py-2 mt-2 mb-0 small">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        A turma de preferência <strong>{{ $solicitacao->turma->nome }}</strong> está inativa ou de ano letivo encerrado — escolha outra turma.
                                    </div>
                                @elseif($semTurmasAtivas)
                                    <div class="alert alert-warning py-2 mt-2 mb-0 small">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        Nenhuma turma ativa nesta escola no momento.
                                    </div>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Aprovar e criar aluno + matrícula?')">
                                <i class="fa fa-check"></i> Aprovar e matricular
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card card-outline card-danger">
                    <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fa fa-times-circle text-danger mr-1"></i> Recusar pré-matrícula</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('solicitacao_matricula.recusar', $solicitacao->id) }}">
                            @csrf
                            <div class="form-group">
                                <label>Motivo da recusa <span class="text-danger">*</span></label>
                                <textarea name="motivo_recusa" class="form-control" rows="2" maxlength="2000" required placeholder="Ex.: Turma lotada, documentos pendentes..."></textarea>
                                <small class="text-muted">A escola nega a vaga e o responsável recebe o motivo por e-mail.</small>
                            </div>
                            <button type="submit" class="btn btn-outline-danger btn-block" onclick="return confirm('Recusar esta solicitação?')"><i class="fa fa-times"></i> Recusar</button>
                        </form>

                        <div class="sm-divider-or">ou</div>

                        <form method="POST" action="{{ route('solicitacao_matricula.cancelar', $solicitacao->id) }}" class="text-center">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm btn-block" title="Anula o pedido sem decisão — ex.: duplicado ou registrado por engano" onclick="return confirm('Cancelar anula o pedido sem decisão (duplicado, registro por engano). Continuar?')"><i class="fa fa-ban"></i> Cancelar solicitação (anula sem decisão)</button>
                        </form>
                    </div>
                </div>
            @endcan
        @endif

    </div>
</div>
@stop

@push('js')
<script src="{{ asset('js/select2.min.js') }}"></script>
<script>
    $(function () {
        $('#turmaAprovacao').select2({
            theme: 'bootstrap4',
            placeholder: 'Busque a turma por nome, etapa ou ano...',
            allowClear: false,
            width: '100%',
            language: { noResults: function () { return 'Nenhuma turma ativa encontrada'; } }
        });
    });
</script>
@endpush
