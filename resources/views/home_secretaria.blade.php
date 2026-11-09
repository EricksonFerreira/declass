@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Painel da Secretaria" subtitle="Fila de atendimento e resumo da escola" />
@stop

@push('css')
<style>
    .hs-card { display:flex; align-items:center; gap:.7rem; border:1px solid #eef1f6; border-radius:10px; padding:.8rem 1rem; background:#fff; height:100%; color:inherit; text-decoration:none !important; transition:box-shadow .12s, transform .12s; }
    .hs-card:hover { box-shadow:0 4px 14px rgba(37,99,235,.10); transform:translateY(-1px); }
    .hs-card .ic { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.05rem; color:#fff; flex-shrink:0; }
    .hs-card b { font-size:1.35rem; line-height:1; color:#1f2937; }
    .hs-card small { font-size:.72rem; color:#6b7280; display:block; }
    .hs-resumo { border:1px solid #eef1f6; border-radius:10px; padding:.6rem .9rem; background:#fff; text-align:center; }
    .hs-resumo b { display:block; font-size:1.3rem; color:#1e3a8a; }
    .hs-resumo small { font-size:.68rem; text-transform:uppercase; color:#9ca3af; font-weight:700; }
    .hs-acao { display:flex; align-items:center; gap:.55rem; border:1px solid #e5e7eb; border-radius:10px; padding:.6rem .9rem; background:#fff; color:#1e3a8a !important; font-weight:600; font-size:.85rem; text-decoration:none !important; }
    .hs-acao:hover { background:#eff6ff; border-color:#bfdbfe; }
    .hs-sec { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2563eb; margin:1.1rem 0 .6rem; }
    .hs-sec:first-child { margin-top:0; }
    .hs-zero { opacity:.55; }
</style>
@endpush

@section('content')

<div class="hs-sec"><i class="fa fa-bell mr-1"></i> Precisa de atenção</div>
<div class="row">
    <div class="col-12 col-md-6 col-lg-4 mb-2">
        <a href="{{ route('solicitacao.index') }}" class="hs-card {{ $solicitacoesAbertas ? '' : 'hs-zero' }}">
            <span class="ic" style="background:{{ $solicitacoesAbertas ? '#f59e0b' : '#9ca3af' }};"><i class="fas fa-inbox"></i></span>
            <span><b>{{ $solicitacoesAbertas }}</b><small>solicitações das famílias em aberto</small></span>
        </a>
    </div>
    <div class="col-12 col-md-6 col-lg-4 mb-2">
        <a href="{{ route('documento_solicitacao.index') }}" class="hs-card {{ $docEnviados ? '' : 'hs-zero' }}">
            <span class="ic" style="background:{{ $docEnviados ? '#2563eb' : '#9ca3af' }};"><i class="fas fa-file-upload"></i></span>
            <span><b>{{ $docEnviados }}</b><small>documentos enviados aguardando análise</small></span>
        </a>
    </div>
    <div class="col-12 col-md-6 col-lg-4 mb-2">
        <a href="{{ route('documento_solicitacao.index') }}" class="hs-card {{ $docPendentes ? '' : 'hs-zero' }}">
            <span class="ic" style="background:{{ $docPendentes ? '#ef4444' : '#9ca3af' }};"><i class="fas fa-file-alt"></i></span>
            <span><b>{{ $docPendentes }}</b><small>documentos pedidos ainda não entregues</small></span>
        </a>
    </div>
    <div class="col-12 col-md-6 col-lg-4 mb-2">
        <a href="{{ route('justificativa.index') }}" class="hs-card {{ $atestadosEmVigor ? '' : 'hs-zero' }}">
            <span class="ic" style="background:#8b5cf6;"><i class="fas fa-file-medical"></i></span>
            <span><b>{{ $atestadosEmVigor }}</b><small>atestados/abonos em vigor hoje</small></span>
        </a>
    </div>
    <div class="col-12 col-md-6 col-lg-4 mb-2">
        <a href="{{ route('ocorrencia.index') }}" class="hs-card {{ $ocorrenciasSemana ? '' : 'hs-zero' }}">
            <span class="ic" style="background:#ec4899;"><i class="fas fa-exclamation-circle"></i></span>
            <span><b>{{ $ocorrenciasSemana }}</b><small>ocorrências na última semana</small></span>
        </a>
    </div>
    <div class="col-12 col-md-6 col-lg-4 mb-2">
        <a href="{{ route('pesquisa.index') }}" class="hs-card {{ $pesquisasAbertas ? '' : 'hs-zero' }}">
            <span class="ic" style="background:#14b8a6;"><i class="fas fa-clipboard-question"></i></span>
            <span><b>{{ $pesquisasAbertas }}</b><small>pesquisas abertas para as famílias</small></span>
        </a>
    </div>
</div>

<div class="hs-sec"><i class="fa fa-school mr-1"></i> Resumo da escola</div>
<div class="row">
    <div class="col-4 col-md-3 col-lg-2 mb-2"><a href="{{ route('aluno.index') }}" class="d-block hs-resumo"><b>{{ $alunosAtivos }}</b><small>Alunos ativos</small></a></div>
    <div class="col-4 col-md-3 col-lg-2 mb-2"><a href="{{ route('matricula.index') }}" class="d-block hs-resumo"><b>{{ $matriculasAtivas }}</b><small>Matrículas ativas</small></a></div>
    <div class="col-4 col-md-3 col-lg-2 mb-2"><a href="{{ route('turma.index') }}" class="d-block hs-resumo"><b>{{ $turmasAno }}</b><small>Turmas no ano</small></a></div>
    <div class="col-4 col-md-3 col-lg-2 mb-2"><a href="{{ route('aviso.index') }}" class="d-block hs-resumo"><b>{{ $avisosAtivos }}</b><small>Avisos publicados</small></a></div>
    <div class="col-4 col-md-3 col-lg-2 mb-2"><a href="{{ route('evento.index') }}" class="d-block hs-resumo"><b>{{ $eventosHoje }}</b><small>Eventos hoje</small></a></div>
</div>

<div class="hs-sec"><i class="fa fa-bolt mr-1"></i> Ações rápidas</div>
<div class="row">
    @can('matricula.create')
        <div class="col-6 col-md-4 col-lg-3 mb-2"><a href="{{ route('matricula.create') }}" class="hs-acao"><i class="fas fa-id-card"></i> Nova matrícula</a></div>
    @endcan
    @can('aluno.create')
        <div class="col-6 col-md-4 col-lg-3 mb-2"><a href="{{ route('aluno.create') }}" class="hs-acao"><i class="fas fa-user-graduate"></i> Novo aluno</a></div>
    @endcan
    @can('aviso.create')
        <div class="col-6 col-md-4 col-lg-3 mb-2"><a href="{{ route('aviso.create') }}" class="hs-acao"><i class="fas fa-bullhorn"></i> Novo aviso</a></div>
    @endcan
    @can('justificativa.create')
        <div class="col-6 col-md-4 col-lg-3 mb-2"><a href="{{ route('justificativa.create') }}" class="hs-acao"><i class="fas fa-file-medical"></i> Registrar atestado</a></div>
    @endcan
    @can('documento_solicitacao.index')
        <div class="col-6 col-md-4 col-lg-3 mb-2"><a href="{{ route('documento_solicitacao.index') }}" class="hs-acao"><i class="fas fa-file-alt"></i> Solicitar documento</a></div>
    @endcan
    @can('ocorrencia.create')
        <div class="col-6 col-md-4 col-lg-3 mb-2"><a href="{{ route('ocorrencia.create') }}" class="hs-acao"><i class="fas fa-exclamation-circle"></i> Nova ocorrência</a></div>
    @endcan
</div>

@stop
