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
        ['icone' => 'fas fa-inbox', 'cor' => '#d97706', 'valor' => $solicitacoesAbertas, 'label' => 'Solicitações', 'url' => route('solicitacao.index')],
        ['icone' => 'fas fa-file-upload', 'cor' => '#0891b2', 'valor' => $docEnviados, 'label' => 'Docs p/ analisar', 'url' => route('documento_solicitacao.index')],
        ['icone' => 'fas fa-file-alt', 'cor' => '#dc2626', 'valor' => $docPendentes, 'label' => 'Docs pendentes', 'url' => route('documento_solicitacao.index')],
        ['icone' => 'fas fa-file-medical', 'cor' => '#7c3aed', 'valor' => $atestadosEmVigor, 'label' => 'Atestados hoje', 'url' => route('justificativa.index')],
        ['icone' => 'fas fa-exclamation-circle', 'cor' => '#db2777', 'valor' => $ocorrenciasSemana, 'label' => 'Ocorrências (7d)', 'url' => route('ocorrencia.index')],
        ['icone' => 'fas fa-clipboard-list', 'cor' => '#0d9488', 'valor' => $pesquisasAbertas, 'label' => 'Pesquisas abertas', 'url' => route('pesquisa.index')],
    ];

    $fila = collect([
        ['icone' => 'fas fa-inbox', 'titulo' => 'Solicitações das famílias', 'detalhe' => 'pedidos em aberto ou em análise', 'qtd' => $solicitacoesAbertas, 'cor' => '#d97706', 'url' => route('solicitacao.index')],
        ['icone' => 'fas fa-file-upload', 'titulo' => 'Documentos enviados', 'detalhe' => 'aguardando análise da secretaria', 'qtd' => $docEnviados, 'cor' => '#0891b2', 'url' => route('documento_solicitacao.index')],
        ['icone' => 'fas fa-file-alt', 'titulo' => 'Documentos pendentes', 'detalhe' => 'pedidos ainda não entregues', 'qtd' => $docPendentes, 'cor' => '#dc2626', 'url' => route('documento_solicitacao.index')],
        ['icone' => 'fas fa-file-medical', 'titulo' => 'Atestados e abonos', 'detalhe' => 'em vigor hoje', 'qtd' => $atestadosEmVigor, 'cor' => '#7c3aed', 'url' => route('justificativa.index')],
        ['icone' => 'fas fa-exclamation-circle', 'titulo' => 'Ocorrências da semana', 'detalhe' => 'registradas nos últimos 7 dias', 'qtd' => $ocorrenciasSemana, 'cor' => '#db2777', 'url' => route('ocorrencia.index')],
        ['icone' => 'fas fa-clipboard-list', 'titulo' => 'Pesquisas abertas', 'detalhe' => 'aguardando resposta das famílias', 'qtd' => $pesquisasAbertas, 'cor' => '#0d9488', 'url' => route('pesquisa.index')],
    ])->filter(fn ($item) => $item['qtd'] > 0)->values();

    $resumo = [
        ['icone' => 'fas fa-user-graduate', 'valor' => $alunosAtivos, 'label' => 'Alunos ativos', 'url' => route('aluno.index')],
        ['icone' => 'fas fa-id-card', 'valor' => $matriculasAtivas, 'label' => 'Matrículas ativas', 'url' => route('matricula.index')],
        ['icone' => 'fas fa-chalkboard-teacher', 'valor' => $turmasAno, 'label' => 'Turmas no ano', 'url' => route('turma.index')],
        ['icone' => 'fas fa-bullhorn', 'valor' => $avisosAtivos, 'label' => 'Avisos publicados', 'url' => route('aviso.index')],
        ['icone' => 'fas fa-calendar-alt', 'valor' => $eventosHoje, 'label' => 'Eventos hoje', 'url' => route('evento.index')],
    ];

    $acoes = [
        ['icone' => 'fas fa-id-card', 'label' => 'Nova matrícula', 'url' => 'matricula.create', 'rota' => route('matricula.create'), 'cor' => '#2563eb'],
        ['icone' => 'fas fa-user-graduate', 'label' => 'Novo aluno', 'url' => 'aluno.create', 'rota' => route('aluno.create'), 'cor' => '#7c3aed'],
        ['icone' => 'fas fa-bullhorn', 'label' => 'Novo aviso', 'url' => 'aviso.create', 'rota' => route('aviso.create'), 'cor' => '#d97706'],
        ['icone' => 'fas fa-file-medical', 'label' => 'Registrar atestado', 'url' => 'justificativa.create', 'rota' => route('justificativa.create'), 'cor' => '#0891b2'],
        ['icone' => 'fas fa-file-alt', 'label' => 'Solicitar documento', 'url' => 'documento_solicitacao.index', 'rota' => route('documento_solicitacao.index'), 'cor' => '#0d9488'],
        ['icone' => 'fas fa-exclamation-circle', 'label' => 'Nova ocorrência', 'url' => 'ocorrencia.create', 'rota' => route('ocorrencia.create'), 'cor' => '#dc2626'],
    ];
@endphp

{{-- Header estilo app --}}
<div class="hs-app-header">
    <img class="hs-app-avatar" src="{{ $usuario->foto_perfil_url }}" alt="{{ $usuario->nome }}">
    <div class="hs-app-ola">
        <b>{{ $saudacao }}, {{ $primeiroNome }}</b>
        <small>{{ $dataExtenso }}</small>
    </div>
</div>

{{-- Atalhos estilo tela de celular (grid de icones com badge) --}}
<div class="hs-app-grid">
    @foreach($tiles as $t)
        <a href="{{ $t['url'] }}" class="hs-app" style="--app:{{ $t['cor'] }}">
            <span class="hs-app-icone">
                <i class="{{ $t['icone'] }}"></i>
                <em class="hs-app-badge">{{ $t['valor'] }}</em>
            </span>
            <span class="hs-app-label">{{ $t['label'] }}</span>
        </a>
    @endforeach
</div>

{{-- Fila de atendimento: lista estilo app --}}
<div class="hs-sec"><i class="fa fa-bell mr-1"></i> Precisa de atenção @if($fila->isNotEmpty())<span class="hs-sec-badge">{{ $fila->sum('qtd') }}</span>@endif</div>
<div class="hs-lista">
    @forelse($fila as $item)
        <a href="{{ $item['url'] }}" class="hs-item" style="--app:{{ $item['cor'] }}">
            <span class="hs-item-icone"><i class="{{ $item['icone'] }}"></i></span>
            <span class="hs-item-info">
                <b>{{ $item['titulo'] }}</b>
                <small>{{ $item['detalhe'] }}</small>
            </span>
            <span class="hs-item-qtd">{{ $item['qtd'] }}</span>
            <i class="fas fa-chevron-right hs-item-seta"></i>
        </a>
    @empty
        <div class="hs-item hs-item--ok">
            <span class="hs-item-icone"><i class="fas fa-check-circle"></i></span>
            <span class="hs-item-info"><b>Tudo em dia</b><small>Nenhuma pendência na fila de atendimento.</small></span>
        </div>
    @endforelse
</div>

<div class="hs-sec"><i class="fa fa-school mr-1"></i> Resumo da escola</div>
<div class="hs-widgets">
    @foreach($resumo as $r)
        <a href="{{ $r['url'] }}" class="hs-widget">
            <span class="hs-widget-icone"><i class="{{ $r['icone'] }}"></i></span>
            <b>{{ $r['valor'] }}</b>
            <small>{{ $r['label'] }}</small>
        </a>
    @endforeach
</div>

<div class="hs-sec"><i class="fa fa-bolt mr-1"></i> Ações rápidas</div>
<div class="hs-chips">
    @foreach($acoes as $a)
        @can($a['url'])
            <a href="{{ $a['rota'] }}" class="hs-chip" style="--app:{{ $a['cor'] }}"><i class="{{ $a['icone'] }}"></i> {{ $a['label'] }}</a>
        @endcan
    @endforeach
</div>

@include('partials.home-app-styles')
@stop
