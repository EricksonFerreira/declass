@extends('adminlte::page')

@section('title', $titulo)

@section('content')
@php
    $usuario = auth()->user();
    $hora = (int) now()->format('H');
    $saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
    $primeiroNome = \Illuminate\Support\Str::of((string) $usuario->nome)->explode(' ')->first();
    $dataExtenso = ucfirst(\Carbon\Carbon::now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM'));

    $tiles = [
        ['icone' => 'fas fa-inbox', 'cor' => 'bg-warning', 'valor' => $solicitacoesAbertas, 'label' => 'Solicitações', 'url' => route('solicitacao.index')],
        ['icone' => 'fas fa-file-upload', 'cor' => 'bg-info', 'valor' => $docEnviados, 'label' => 'Docs p/ analisar', 'url' => route('documento_solicitacao.index')],
        ['icone' => 'fas fa-file-alt', 'cor' => 'bg-danger', 'valor' => $docPendentes, 'label' => 'Docs pendentes', 'url' => route('documento_solicitacao.index')],
        ['icone' => 'fas fa-file-medical', 'cor' => 'bg-purple', 'valor' => $atestadosEmVigor, 'label' => 'Atestados hoje', 'url' => route('justificativa.index')],
        ['icone' => 'fas fa-exclamation-circle', 'cor' => 'bg-pink', 'valor' => $ocorrenciasSemana, 'label' => 'Ocorrências (7d)', 'url' => route('ocorrencia.index')],
        ['icone' => 'fas fa-clipboard-question', 'cor' => 'bg-teal', 'valor' => $pesquisasAbertas, 'label' => 'Pesquisas abertas', 'url' => route('pesquisa.index')],
    ];

    $fila = collect([
        ['titulo' => 'Solicitações das famílias', 'detalhe' => 'pedidos em aberto ou em análise', 'qtd' => $solicitacoesAbertas, 'cor' => 'bg-warning', 'url' => route('solicitacao.index')],
        ['titulo' => 'Documentos enviados', 'detalhe' => 'aguardando análise da secretaria', 'qtd' => $docEnviados, 'cor' => 'bg-info', 'url' => route('documento_solicitacao.index')],
        ['titulo' => 'Documentos pendentes', 'detalhe' => 'pedidos ainda não entregues', 'qtd' => $docPendentes, 'cor' => 'bg-danger', 'url' => route('documento_solicitacao.index')],
        ['titulo' => 'Atestados e abonos', 'detalhe' => 'em vigor hoje', 'qtd' => $atestadosEmVigor, 'cor' => 'bg-purple', 'url' => route('justificativa.index')],
        ['titulo' => 'Ocorrências da semana', 'detalhe' => 'registradas nos últimos 7 dias', 'qtd' => $ocorrenciasSemana, 'cor' => 'bg-pink', 'url' => route('ocorrencia.index')],
        ['titulo' => 'Pesquisas abertas', 'detalhe' => 'aguardando resposta das famílias', 'qtd' => $pesquisasAbertas, 'cor' => 'bg-teal', 'url' => route('pesquisa.index')],
    ])->filter(fn ($item) => $item['qtd'] > 0)->values();
@endphp

{{-- Saudacao --}}
<div class="callout callout-info hs-saudacao">
    <img class="hs-avatar" src="{{ $usuario->foto_perfil_url }}" alt="{{ $usuario->nome }}">
    <div>
        <h5 class="mb-0">{{ $saudacao }}, {{ $primeiroNome }}</h5>
        <small class="text-muted"><i class="far fa-calendar mr-1"></i>{{ $dataExtenso }}</small>
    </div>
</div>

{{-- Resumo do dia (padrao small-box do AdminLTE) --}}
<div class="row">
    @foreach($tiles as $t)
        <div class="col-md-4 col-6">
            <div class="small-box {{ $t['cor'] }}">
                <div class="inner">
                    <h3>{{ $t['valor'] }}</h3>
                    <p>{{ $t['label'] }}</p>
                </div>
                <div class="icon"><i class="{{ $t['icone'] }}"></i></div>
                <a href="{{ $t['url'] }}" class="small-box-footer">Abrir <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    @endforeach
</div>

{{-- Fila de atendimento: so o que precisa de acao --}}
<div class="card">
    <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
        <span><i class="fas fa-bell mr-1"></i> Precisa de atenção</span>
        @if($fila->isNotEmpty())
            <span class="badge badge-danger">{{ $fila->sum('qtd') }}</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                @forelse($fila as $item)
                    <tr class="hs-fila-linha" onclick="window.location='{{ $item['url'] }}'" style="cursor:pointer;">
                        <td class="text-center" style="width:70px;">
                            <span class="badge {{ $item['cor'] }} px-2 py-1">{{ $item['qtd'] }}</span>
                        </td>
                        <td class="font-weight-bold">{{ $item['titulo'] }}</td>
                        <td class="text-muted">{{ $item['detalhe'] }}</td>
                        <td class="text-right text-nowrap" style="width:60px;">
                            <i class="fas fa-chevron-right text-muted"></i>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center text-muted py-4">
                            <i class="fas fa-check-circle mb-1 d-block" style="font-size:1.4rem;opacity:.4;"></i>
                            <strong>Tudo em dia</strong><br>
                            <small>Nenhuma pendência na fila de atendimento.</small>
                        </td>
                    </tr>
                @endforelse
            </table>
        </div>
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

<style>
    .hs-saudacao { display: flex; align-items: center; gap: .8rem; }
    .hs-avatar { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .hs-fila-linha td { vertical-align: middle; }
    .hs-fila-linha:hover td { background: #f8fafc; }
    .hs-resumo { border:1px solid #eef1f6; border-radius:10px; padding:.6rem .9rem; background:#fff; text-align:center; }
    .hs-resumo b { display:block; font-size:1.3rem; color:#1e3a8a; }
    .hs-resumo small { font-size:.68rem; text-transform:uppercase; color:#9ca3af; font-weight:700; }
    .hs-acao { display:flex; align-items:center; gap:.55rem; border:1px solid #e5e7eb; border-radius:10px; padding:.6rem .9rem; background:#fff; color:#1e3a8a !important; font-weight:600; font-size:.85rem; text-decoration:none !important; }
    .hs-acao:hover { background:#eff6ff; border-color:#bfdbfe; }
    .hs-sec { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2563eb; margin:1.1rem 0 .6rem; }
    .hs-sec:first-child { margin-top:0; }
</style>
@stop
