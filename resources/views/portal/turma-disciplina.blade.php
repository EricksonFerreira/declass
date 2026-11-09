@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    @php
        $corDisc = \App\Services\TurmaGradeService::corDaDisciplina($disciplina->id);
        $profLabel = $professor
            ? (\Illuminate\Support\Str::startsWith(\Illuminate\Support\Str::lower($professor), 'prof') ? $professor : 'Prof. '.$professor)
            : null;
    @endphp
    <x-page-title :title="$disciplina->nome" subtitle="{{ $turma->nome }} · {{ $turma->etapa->nome ?? '' }} · {{ $turma->anoLetivo->ano ?? '' }}{{ $profLabel ? ' · '.$profLabel : '' }}">
        @if($disciplina->sigla)
            <span class="badge align-self-center" style="background:{{ $corDisc['tx'] }}; color:{{ $corDisc['bg'] }}">{{ $disciplina->sigla }}</span>
        @endif
    </x-page-title>
@stop

@section('content')
@php
    $fmtNum = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ',');
    $corNota = fn ($v) => $v >= 75 ? 'success' : ($v >= 50 ? 'warning' : 'danger');
    $corStatus = ['presente' => 'success', 'falta' => 'danger', 'justificada' => 'warning', 'abonada' => 'info'];
    $multiplosAlunos = $matriculas->count() > 1;
    $mediaUnica = $multiplosAlunos ? null : $medias->get($matriculas->first()->id);
    $freqUnica = $multiplosAlunos ? null : $frequencias->get($matriculas->first()->id);
@endphp

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<ul class="nav nav-tabs nav-tabs-app" id="disciplinaTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="tab-atividades-link" data-toggle="tab" href="#tab-atividades" role="tab">
            <i class="fas fa-tasks mr-1"></i> Atividades e provas <span class="badge badge-light border ml-1">{{ $tarefas->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-rendimento-link" data-toggle="tab" href="#tab-rendimento" role="tab">
            <i class="fas fa-chart-line mr-1"></i> Rendimento
            @if($mediaUnica !== null)
                <span class="badge badge-{{ $corNota($mediaUnica) }} ml-1">{{ number_format($mediaUnica, 1, ',', '') }}%</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-frequencia-link" data-toggle="tab" href="#tab-frequencia" role="tab">
            <i class="fas fa-user-check mr-1"></i> Frequência
            @if($freqUnica && $freqUnica['total'] > 0)
                <span class="badge badge-{{ $corNota($freqUnica['percentual']) }} ml-1">{{ $freqUnica['percentual'] }}%</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-materiais-link" data-toggle="tab" href="#tab-materiais" role="tab">
            <i class="fas fa-folder-open mr-1"></i> Conteúdos <span class="badge badge-light border ml-1">{{ $materiais->count() }}</span>
        </a>
    </li>
    @unless($isResponsavel)
        <li class="nav-item">
            <a class="nav-link" id="tab-forum-link" data-toggle="tab" href="#tab-forum" role="tab">
                <i class="fas fa-comments mr-1"></i> Fórum <span class="badge badge-light border ml-1">{{ $posts->count() }}</span>
            </a>
        </li>
    @endunless
</ul>

<div class="tab-content pt-3">

{{-- Atividades/provas da disciplina: e o que mais pede acao no dia a dia --}}
<div class="tab-pane fade show active" id="tab-atividades" role="tabpanel">
    <div class="card">
        <div class="list-group list-group-flush">
            @forelse($tarefas as $tarefa)
                @php
                    $entrega = $entregas->get($tarefa->id.'|'.$matriculas->first()->id);
                    $cor = $entrega?->status === 'avaliada' ? 'success' : ($entrega?->status === 'entregue' ? 'info' : ($tarefa->data_entrega?->isPast() ? 'danger' : 'warning'));
                    $statusTxt = $entrega ? \App\Models\EntregaTarefa::STATUS[$entrega->status] ?? $entrega->status : 'Pendente';
                @endphp
                <a href="{{ route('portal.turma.disciplina.tarefa', [$turma->id, $disciplina->id, $tarefa->id]) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between flex-wrap">
                        <strong>
                            {{ $tarefa->titulo }}
                            @if($tarefa->tipo !== 'atividade')
                                <span class="badge badge-{{ $tarefa->tipo === 'prova' ? 'danger' : 'warning' }}">{{ $tarefa->tipoLabel() }}</span>
                            @endif
                        </strong>
                        <span class="badge badge-{{ $cor }}">{{ $statusTxt }}</span>
                    </div>
                    <small class="text-muted">
                        @if($tarefa->data_entrega) entrega até {{ $tarefa->data_entrega->format('d/m/Y') }} @endif
                        @if($tarefa->questoes->isNotEmpty()) · {{ $tarefa->questoes->count() }} questão(ões) — responda pelo celular @endif
                        @if($tarefa->exigeEntregaOnline()) · {{ $tarefa->tipoEntregaLabel() }} @endif
                        @if($entrega?->nota !== null) · nota {{ $fmtNum($entrega->nota) }} @endif
                    </small>
                </a>
            @empty
                <div class="list-group-item text-muted text-center py-4">
                    <i class="fas fa-tasks mb-2 d-block fa-2x"></i>Nenhuma atividade publicada nesta disciplina.
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Rendimento por aluno da conta (notas e media na disciplina) --}}
<div class="tab-pane fade" id="tab-rendimento" role="tabpanel">
    @foreach($matriculas as $matricula)
        @php
            $nomeAluno = $matricula->aluno->pessoa->nome ?? $matricula->aluno->nome;
            $grupos = $notasPorPeriodo->get($matricula->id) ?? collect();
            $media = $medias->get($matricula->id);
            $comPeriodos = $grupos->contains(fn ($g) => $g['nome'] !== 'Sem período');
        @endphp
        <div class="card card-outline" style="border-top-color:{{ $corDisc['tx'] }};">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap"
                 style="background:{{ $corDisc['bg'] }}; border-bottom:0;">
                <span class="font-weight-bold" style="color:{{ $corDisc['tx'] }};"><i class="fas fa-chart-line mr-1"></i> {{ $multiplosAlunos ? $nomeAluno : 'Notas e média' }}</span>
                @if($media !== null)
                    <span class="badge badge-{{ $corNota($media) }}">Média {{ number_format($media, 1, ',', '') }}%</span>
                @endif
            </div>
            @if($comPeriodos)
                {{-- Resumo das notas por periodo letivo (bimestre, semestre...) --}}
                <div class="card-body border-bottom py-2">
                    <div class="d-flex flex-wrap align-items-center" style="gap:.75rem;">
                        @foreach($grupos->where('nome', '!=', 'Sem período') as $grupo)
                            <span class="text-nowrap">
                                <small class="text-muted mr-1">{{ $grupo['nome'] }}:</small>
                                @if($grupo['media'] !== null)
                                    <span class="badge badge-{{ $corNota($grupo['media']) }}">{{ number_format($grupo['media'], 1, ',', '') }}%</span>
                                @else
                                    <span class="badge badge-secondary">—</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Avaliação</th><th>Tipo</th><th>Período</th><th>Data</th><th class="text-right">Nota</th></tr></thead>
                    <tbody>
                    @forelse($grupos as $grupo)
                        @if($comPeriodos)
                            <tr class="periodo-row">
                                <td colspan="4">
                                    <i class="far fa-calendar-alt mr-1 text-muted"></i><strong>{{ $grupo['nome'] }}</strong>
                                    <small class="text-muted">· {{ $grupo['notas']->count() }} {{ $grupo['notas']->count() === 1 ? 'avaliação' : 'avaliações' }}</small>
                                </td>
                                <td class="text-right text-nowrap">
                                    @if($grupo['media'] !== null)
                                        <span class="badge badge-{{ $corNota($grupo['media']) }}">Média {{ number_format($grupo['media'], 1, ',', '') }}%</span>
                                    @else
                                        <small class="text-muted">sem notas</small>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        @foreach($grupo['notas'] as $nota)
                            @php $aprov = $nota->nota !== null && $nota->avaliacao->nota_maxima > 0 ? $nota->nota / $nota->avaliacao->nota_maxima * 100 : null; @endphp
                            <tr>
                                <td>{{ $nota->avaliacao->nome }}</td>
                                <td><span class="badge badge-info">{{ \App\Models\Avaliacao::TIPOS[$nota->avaliacao->tipo] ?? $nota->avaliacao->tipo }}</span></td>
                                <td>{{ $nota->avaliacao->periodoLetivo->nome ?? $nota->avaliacao->periodo ?? '—' }}</td>
                                <td>{{ $nota->avaliacao->data?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-right text-nowrap">
                                    @if($aprov !== null)
                                        <span class="badge badge-{{ $corNota($aprov) }}">{{ number_format($nota->nota, 1, ',', '') }}</span>
                                        <small class="text-muted">/ {{ $fmtNum($nota->avaliacao->nota_maxima) }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="5" class="text-muted text-center py-3">Nenhuma nota lançada nesta disciplina.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>

{{-- Frequencia detalhada na disciplina, por aluno da conta --}}
<div class="tab-pane fade" id="tab-frequencia" role="tabpanel">
    @foreach($matriculas as $matricula)
        @php
            $nomeAluno = $matricula->aluno->pessoa->nome ?? $matricula->aluno->nome;
            $freq = $frequencias->get($matricula->id);
        @endphp
        <div class="card card-outline" style="border-top-color:{{ $corDisc['tx'] }};">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap"
                 style="background:{{ $corDisc['bg'] }}; border-bottom:0;">
                <span class="font-weight-bold" style="color:{{ $corDisc['tx'] }};"><i class="fas fa-user-check mr-1"></i> {{ $multiplosAlunos ? $nomeAluno : 'Presenças e faltas' }}</span>
                @if($freq && $freq['total'] > 0)
                    <div class="d-flex align-items-center" style="gap:.5rem;">
                        <small class="text-muted">{{ $freq['presencas'] }} presença(s) · {{ $freq['faltas'] }} falta(s){{ $freq['justificadas'] ? ' · '.$freq['justificadas'].' justificada(s)' : '' }}</small>
                        <span class="badge badge-{{ $corNota($freq['percentual']) }}">{{ $freq['percentual'] }}%</span>
                    </div>
                @endif
            </div>
            @if($freq && $freq['total'] > 0)
                <div class="card-body border-bottom">
                    <span class="small text-muted">
                        <strong>{{ $freq['percentual'] }}%</strong> de presença em {{ $freq['total'] }} {{ $freq['total'] === 1 ? 'aula' : 'aulas' }}
                    </span>
                    <div class="progress progress-xs mt-2">
                        <div class="progress-bar bg-{{ $corNota($freq['percentual']) }}" role="progressbar" style="width: {{ min(100, max(0, $freq['percentual'])) }}%"></div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width:110px;">Data</th>
                                <th style="width:160px;">Situação</th>
                                <th>Justificativa</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($freq['registros'] as $reg)
                            <tr>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($reg->data)->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge badge-{{ $corStatus[$reg->status] ?? 'secondary' }}">
                                        {{ \App\Models\Frequencia::STATUS[$reg->status] ?? $reg->status }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $reg->justificativa ?: '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="card-body text-muted text-center py-4">Nenhuma chamada registrada nesta disciplina.</div>
            @endif
        </div>
    @endforeach
</div>

{{-- Conteudos da disciplina publicados pelo professor --}}
<div class="tab-pane fade" id="tab-materiais" role="tabpanel">
    <div class="card">
        <div class="list-group list-group-flush">
            @forelse($materiais as $material)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between flex-wrap mb-1">
                        <strong>{{ $material->titulo }}</strong>
                        <small class="text-muted">{{ $material->autor->nome ?? '' }} · {{ $material->created_at?->format('d/m/Y') }}</small>
                    </div>
                    @if($material->descricao)<p class="mb-2 small">{{ $material->descricao }}</p>@endif
                    @if($material->arquivo_path)
                        <a href="{{ asset('storage/'.$material->arquivo_path) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-download mr-1"></i> Baixar arquivo</a>
                    @elseif($material->url)
                        <a href="{{ $material->url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info"><i class="fas fa-external-link-alt mr-1"></i> Abrir link</a>
                    @endif
                </div>
            @empty
                <div class="list-group-item text-muted text-center py-4">
                    <i class="fas fa-folder-open mb-2 d-block fa-2x"></i>O professor ainda não publicou conteúdos nesta disciplina.
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Forum da disciplina (so aluno participa): chat estilo WhatsApp Web, atualiza a cada 5s --}}
@unless($isResponsavel)
<div class="tab-pane fade" id="tab-forum" role="tabpanel">
    @php
        $forumHtml = view('portal.partials.forum-chat', [
            'turma' => $turma,
            'disciplina' => $disciplina,
            'posts' => $posts,
        ])->render();
    @endphp
    <div id="forum-chat"
         data-url="{{ route('portal.turma.disciplina.forum.posts', [$turma->id, $disciplina->id]) }}"
         data-url-lida="{{ route('portal.turma.disciplina.forum.lida', [$turma->id, $disciplina->id, '__POST__']) }}"
         data-sig="{{ md5($forumHtml) }}"
         data-chave="forum-wa-{{ $turma->id }}-{{ $disciplina->id }}">{!! $forumHtml !!}</div>
</div>
@endunless

</div>

<style>
    .progress-xs { height: 6px; }
    #tab-rendimento .periodo-row td { background: #eef1f5; }
    .portal-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .portal-avatar-sm { width: 28px; height: 28px; }
    .min-w-0 { min-width: 0; }

    /* Forum estilo WhatsApp Web */
    .wa-app { display: flex; height: 620px; max-height: 78vh; border: 1px solid #d1d7db; border-radius: 10px; overflow: hidden; background: #fff; }
    .wa-avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; flex-shrink: 0; background: #dfe5e7; }

    /* Lista de conversas */
    .wa-list { width: 320px; flex-shrink: 0; background: #fff; border-right: 1px solid #d1d7db; display: flex; flex-direction: column; }
    .wa-list-head { display: flex; align-items: center; gap: 12px; background: #f0f2f5; padding: 8px 14px; }
    .wa-list-head-titulo { flex: 1; font-weight: 600; color: #111b21; }
    .wa-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border: 0; border-radius: 50%; background: transparent; color: #54656f; font-size: 16px; flex-shrink: 0; }
    .wa-icon-btn:hover { background: rgba(11,20,26,.08); color: #111b21; }
    .wa-topics { flex: 1; overflow-y: auto; }
    .wa-topic { display: flex; align-items: center; gap: 12px; padding: 10px 13px; border-bottom: 1px solid #f0f2f5; cursor: pointer; }
    .wa-topic:hover, .wa-topic:focus { background: #f5f6f6; outline: none; }
    .wa-topic.ativo { background: #f0f2f5; }
    .wa-topic-info { flex: 1; min-width: 0; }
    .wa-topic-top { display: flex; justify-content: space-between; align-items: baseline; gap: 8px; }
    .wa-topic-titulo { font-size: 15px; color: #111b21; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wa-topic-hora { font-size: 12px; color: #667781; flex-shrink: 0; }
    .wa-topic-sub { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-top: 2px; }
    .wa-topic-preview { font-size: 13px; color: #667781; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wa-topic-count { flex-shrink: 0; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background: #00a884; color: #fff; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; }
    .wa-hora-nao-lida { color: #00a884; font-weight: 600; }
    .wa-nao-lidas { text-align: center; margin: 10px 0; }
    .wa-nao-lidas span { display: inline-block; background: #d9f5e8; color: #0b846f; font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 8px; box-shadow: 0 1px .5px rgba(11,20,26,.13); }
    .wa-vazio-lista { text-align: center; color: #8696a0; padding: 48px 16px; font-size: 14px; }
    .wa-vazio-lista i { font-size: 42px; color: #d1d7db; margin-bottom: 10px; }

    /* Painel da conversa */
    .wa-pane { position: relative; flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .wa-pane-vazio { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; background: #f0f2f5; color: #8696a0; text-align: center; padding: 20px; }
    .wa-app.aberta .wa-pane-vazio { display: none; }
    .wa-pane-vazio i { font-size: 72px; color: #d1d7db; }
    .wa-pane-vazio-titulo { font-size: 20px; font-weight: 300; color: #41525d; }
    .wa-conv { display: none; flex-direction: column; height: 100%; min-height: 0; }
    .wa-conv.ativa { display: flex; }
    .wa-head { display: flex; align-items: center; gap: 10px; background: #f0f2f5; padding: 7px 12px; border-bottom: 1px solid #d1d7db; }
    .wa-head .wa-avatar { width: 38px; height: 38px; }
    .wa-head-info { flex: 1; }
    .wa-head-titulo { font-weight: 600; color: #111b21; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wa-head-sub { font-size: 12px; color: #667781; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wa-voltar { display: none; }
    .wa-msgs, .wa-novo-body { flex: 1; min-height: 0; overflow-y: auto; padding: 18px 6%;
        background-color: #efeae2;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140' viewBox='0 0 140 140'%3E%3Cg fill='none' stroke='%23cfc7b8' stroke-width='1.3' opacity='.6'%3E%3Ccircle cx='22' cy='22' r='7'/%3E%3Cpath d='M55 14q9 9 0 18'/%3E%3Crect x='92' y='15' width='13' height='13' rx='3'/%3E%3Cpath d='M16 60l11 11M27 60L16 71'/%3E%3Ccircle cx='65' cy='68' r='4'/%3E%3Cpath d='M98 58q11 6 0 13t0 13'/%3E%3Crect x='20' y='98' width='11' height='11' rx='5'/%3E%3Cpath d='M60 104h15M67 96v15'/%3E%3Ccircle cx='108' cy='106' r='6'/%3E%3Cpath d='M40 38l6-9 6 9z'/%3E%3Cpath d='M120 40q-8 4-8 12'/%3E%3C/g%3E%3C/svg%3E"); }
    .wa-novo-body { display: flex; flex-direction: column; gap: 12px; }
    .wa-novo-form { display: flex; flex-direction: column; height: 100%; min-height: 0; }
    .wa-novo-dica { flex: 1; font-size: 12.5px; color: #667781; }

    /* Bolhas e composer */
    .wa-data { text-align: center; margin: 10px 0; }
    .wa-data span { display: inline-block; background: #fff; color: #54656f; font-size: 12px; padding: 5px 12px; border-radius: 8px; box-shadow: 0 1px .5px rgba(11,20,26,.13); }
    .wa-msg { position: relative; max-width: 75%; width: fit-content; padding: 6px 9px 8px; margin-bottom: 8px; border-radius: 8px; box-shadow: 0 1px .5px rgba(11,20,26,.13); font-size: 14.5px; line-height: 1.4; color: #111b21; word-wrap: break-word; }
    .wa-msg-in { background: #fff; margin-right: auto; }
    .wa-msg-out { background: #d9fdd3; margin-left: auto; }
    .wa-msg-in.wa-tail { border-top-left-radius: 0; }
    .wa-msg-in.wa-tail::before { content: ''; position: absolute; top: 0; left: -8px; border: 4px solid transparent; border-top-color: #fff; border-right-color: #fff; }
    .wa-msg-out.wa-tail { border-top-right-radius: 0; }
    .wa-msg-out.wa-tail::before { content: ''; position: absolute; top: 0; right: -8px; border: 4px solid transparent; border-top-color: #d9fdd3; border-left-color: #d9fdd3; }
    .wa-autor { font-size: 12.8px; font-weight: 600; margin-bottom: 2px; }
    .wa-titulo { font-weight: 700; margin-bottom: 2px; }
    .wa-texto { white-space: pre-wrap; }
    .wa-hora-espaco { display: inline-block; width: 62px; height: 0; }
    .wa-hora { position: absolute; right: 8px; bottom: 5px; font-size: 11px; color: #667781; white-space: nowrap; }
    .wa-hora .fa-check-double { color: #53bdeb; margin-left: 2px; }
    .wa-composer { display: flex; align-items: center; gap: 8px; background: #f0f2f5; padding: 7px 12px; }
    .wa-composer .wa-icon-btn { width: 34px; height: 34px; font-size: 13px; }
    .wa-input { flex: 1; min-width: 0; border: 0; background: #fff; border-radius: 8px; padding: 9px 12px; font-size: 15px; outline: none; box-shadow: 0 1px .5px rgba(11,20,26,.08); }
    .wa-textarea { flex: none; min-height: 140px; resize: vertical; }
    .wa-send { flex-shrink: 0; width: 42px; height: 42px; border-radius: 50%; background: #00a884; color: #fff; border: 0; font-size: 15px; }
    .wa-send:hover { background: #06cf9c; }
    .wa-lock { font-size: 11px; color: #8696a0; margin-right: 4px; }
    .wa-editar { display: flex; align-items: center; gap: 8px; background: #f0f2f5; padding: 7px 12px; border-bottom: 1px solid #d1d7db; }
    .wa-editar[hidden] { display: none; }
    .wa-composer-off { justify-content: center; color: #8696a0; font-size: 13.5px; }
    .wa-head .dropdown-menu { font-size: 14px; }
    .wa-head .dropdown-item { padding: 8px 18px; }
    .wa-head .dropdown-menu form { margin: 0; }

    /* Celular: vira navegacao lista -> conversa com botao voltar */
    @media (max-width: 767.98px) {
        .wa-app { display: block; height: 72vh; min-height: 460px; }
        .wa-list { width: 100%; height: 100%; border-right: 0; }
        .wa-pane { display: none; height: 100%; }
        .wa-app.aberta .wa-list { display: none; }
        .wa-app.aberta .wa-pane { display: flex; }
        .wa-pane-vazio { display: none; }
        .wa-voltar { display: inline-flex; }
        .wa-msgs, .wa-novo-body { padding: 14px 12px; }
        .wa-msg { max-width: 85%; }
    }
</style>
@stop

@section('js')
<script>
    $(function () {
        var hash = window.location.hash || '';
        if (hash && $('#disciplinaTabs a[href="' + hash + '"]').length) {
            $('#disciplinaTabs a[href="' + hash + '"]').tab('show');
        }
        $('#disciplinaTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            history.replaceState(null, '', e.target.hash);
        });

        // Chat do forum estilo WhatsApp Web: lista de conversas +
        // conversa ativa. Polling a cada 5s preserva conversa aberta,
        // textos digitados e posicao da rolagem.
        var $chat = $('#forum-chat');
        if ($chat.length) {
            var sig = $chat.data('sig');
            var chave = String($chat.data('chave'));
            var celular = function () { return window.innerWidth < 768; };

            var guardarAtiva = function (id) {
                try { sessionStorage.setItem(chave, id); } catch (e) {}
            };
            var ativaLembrada = function () {
                try { return sessionStorage.getItem(chave); } catch (e) { return null; }
            };

            var forumVisivel = function () {
                return $('#tab-forum').hasClass('active');
            };

            // Marca a conversa como lida no servidor e some com o badge
            // na hora — estilo WhatsApp, abrir a conversa ja conta como lida.
            var marcarLida = function (id) {
                if (!id || id === 'novo') {
                    return;
                }
                var $topic = $chat.find('.wa-topic[data-post="' + id + '"]');
                if (!$topic.find('.wa-topic-count').length) {
                    return; // sem nao lidas, nada a marcar
                }
                $topic.find('.wa-topic-count').remove();
                $topic.find('.wa-topic-hora').removeClass('wa-hora-nao-lida');
                var url = String($chat.data('url-lida') || '').replace('__POST__', id);
                $.post(url, { _token: $('meta[name="csrf-token"]').attr('content') });
            };

            // Abre uma conversa (post id ou 'novo'); sem id valido cai
            // na primeira conversa e, se nao houver nenhuma, no form.
            // marcar=false pula a marcacao de lida (re-render do polling).
            var abrir = function (id, rolarFim, marcar) {
                var $app = $chat.find('.wa-app');
                var $conv = id ? $app.find('.wa-conv[data-post="' + id + '"]') : $();
                if (!$conv.length) {
                    $conv = $app.find('.wa-conv').not('.wa-conv-novo').first();
                }
                if (!$conv.length) {
                    $conv = $app.find('.wa-conv-novo');
                }
                id = $conv.data('post') || null;
                $app.find('.wa-topic').removeClass('ativo');
                $app.find('.wa-topic[data-post="' + id + '"]').addClass('ativo');
                $app.find('.wa-conv').removeClass('ativa');
                if (!id) {
                    return;
                }
                $conv.addClass('ativa');
                $app.addClass('aberta');
                guardarAtiva(String(id));
                if (marcar !== false && forumVisivel()) {
                    marcarLida(id);
                }
                if (rolarFim) {
                    var $msgs = $conv.find('.wa-msgs');
                    if ($msgs.length) {
                        // Com nao lidas, posiciona no divisor (como o WhatsApp);
                        // senao vai para o fim da conversa.
                        var $divisor = $msgs.find('.wa-nao-lidas');
                        if ($divisor.length) {
                            $msgs.scrollTop($msgs.scrollTop()
                                + $divisor[0].getBoundingClientRect().top
                                - $msgs[0].getBoundingClientRect().top - 56);
                        } else {
                            $msgs.scrollTop($msgs[0].scrollHeight);
                        }
                    }
                }
            };

            var fechar = function () {
                var $app = $chat.find('.wa-app');
                $app.removeClass('aberta');
                $app.find('.wa-conv').removeClass('ativa');
                $app.find('.wa-topic').removeClass('ativo');
                try { sessionStorage.removeItem(chave); } catch (e) {}
            };

            $chat.on('click', '.wa-topic', function () { abrir($(this).data('post'), true); });
            $chat.on('keydown', '.wa-topic', function (e) {
                if (e.which === 13) { abrir($(this).data('post'), true); }
            });
            $chat.on('click', '.wa-novo-btn', function () { abrir('novo', false); });
            $chat.on('click', '.wa-voltar', fechar);

            // Conversa aberta com a aba oculta so conta como lida quando
            // o forum aparece na tela (ex.: carregou em outra aba).
            $('#disciplinaTabs a[href="#tab-forum"]').on('shown.bs.tab', function () {
                var $ativa = $chat.find('.wa-conv.ativa');
                if ($ativa.length) {
                    marcarLida($ativa.data('post'));
                }
            });

            // "Editar titulo" do menu da conversa: abre o form inline
            // no cabecalho e foca o campo.
            $chat.on('click', '.wa-editar-btn', function () {
                var $form = $(this).closest('.wa-conv').find('.wa-editar');
                $form.prop('hidden', function (_, v) { return !v; });
                if (!$form.prop('hidden')) {
                    $form.find('input[name="titulo"]').trigger('focus');
                }
            });

            // Botoes B/I envolvem a selecao do campo com marcadores
            // estilo WhatsApp (*negrito*, _italico_); o servidor renderiza.
            $chat.on('click', '.wa-fmt', function () {
                var el = $(this).closest('form').find('[name="mensagem"]')[0];
                if (!el) {
                    return;
                }
                var m = String($(this).data('fmt'));
                var ini = el.selectionStart || 0;
                var fim = el.selectionEnd || 0;
                var v = el.value;
                el.value = v.substring(0, ini) + m + v.substring(ini, fim) + m + v.substring(fim);
                el.focus();
                var pos = ini === fim ? ini + m.length : fim + m.length * 2;
                el.setSelectionRange(pos, pos);
            });

            // Estado inicial: desktop abre a ultima conversa usada (ou a
            // primeira); no celular mostra a lista, a menos que haja uma
            // conversa lembrada (ex.: voltou do envio de uma mensagem).
            var lembrada = ativaLembrada();
            if (!celular() || lembrada) {
                abrir(lembrada, true);
            }

            setInterval(function () {
                if (document.hidden) {
                    return;
                }
                // ?aberta=<post>: o servidor marca a conversa visivel como
                // lida — mensagem que chega com ela aberta nao vira "nao lida".
                var abertaAgora = forumVisivel()
                    ? ($chat.find('.wa-conv.ativa').data('post') || '')
                    : '';
                $.getJSON($chat.data('url'), { aberta: abertaAgora }, function (r) {
                    if (r.sig === sig) {
                        return;
                    }
                    var $ativa = $chat.find('.wa-conv.ativa');
                    var ativaId = $ativa.data('post') || null;
                    var $msgs = $ativa.find('.wa-msgs');
                    var pos = $msgs.length ? $msgs.scrollTop() : 0;
                    var noFim = $msgs.length
                        && ($msgs[0].scrollHeight - $msgs.scrollTop() - $msgs[0].clientHeight) < 80;

                    // Guarda o divisor "N mensagens nao lidas" da conversa
                    // aberta: o HTML novo ja vem com ela toda lida, e o
                    // divisor precisa ficar onde estava (como no WhatsApp).
                    var divisor = null;
                    var $divisor = $msgs.find('.wa-nao-lidas');
                    if ($divisor.length) {
                        divisor = {
                            post: ativaId,
                            html: $divisor.prop('outerHTML'),
                            antesDe: $divisor.nextAll('.wa-msg').first().data('msg') || null
                        };
                    }

                    var rascunhos = {};
                    $chat.find('input[name="mensagem"], textarea[name="mensagem"]').each(function () {
                        var valor = $(this).val();
                        if (valor !== '') {
                            var id = $(this).data('post') || 'novo-' + this.name;
                            rascunhos[id] = { valor: valor, foco: document.activeElement === this };
                        }
                    });

                    $chat.html(r.html);
                    sig = r.sig;

                    // Recoloca o divisor de nao lidas na mesma posicao.
                    if (divisor && divisor.post) {
                        var $convNova = $chat.find('.wa-conv[data-post="' + divisor.post + '"]');
                        if ($convNova.length && !$convNova.find('.wa-nao-lidas').length) {
                            var $alvoDiv = divisor.antesDe
                                ? $convNova.find('.wa-msg[data-msg="' + divisor.antesDe + '"]')
                                : $();
                            if ($alvoDiv.length) {
                                $alvoDiv.before(divisor.html);
                            } else {
                                $convNova.find('.wa-msgs').append(divisor.html);
                            }
                        }
                    }

                    $chat.find('input[name="mensagem"], textarea[name="mensagem"]').each(function () {
                        var id = $(this).data('post') || 'novo-' + this.name;
                        var rascunho = rascunhos[id];
                        if (rascunho) {
                            $(this).val(rascunho.valor);
                            if (rascunho.foco) {
                                this.focus();
                            }
                        }
                    });

                    if (ativaId || !celular()) {
                        abrir(ativaId || ativaLembrada(), false, false);
                        var $nova = $chat.find('.wa-conv.ativa .wa-msgs');
                        if ($nova.length) {
                            $nova.scrollTop(noFim ? $nova[0].scrollHeight : pos);
                        }
                    }
                    $('#tab-forum-link .badge').text(r.total);
                });
            }, 5000);
        }
    });
</script>
@stop
