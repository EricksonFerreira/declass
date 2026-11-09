@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    @php $anoEmCurso = ($turma->anoLetivo?->status ?? '') === 'letivo'; @endphp
    <x-page-title :title="$turma->nome" subtitle="{{ $turma->etapa->nome ?? '' }} · {{ $turma->escola->nome ?? '' }} · {{ $turma->anoLetivo->ano ?? '' }}{{ $turma->turno ? ' · '.(\App\Models\Turma::TURNOS[$turma->turno] ?? $turma->turno) : '' }}{{ $turma->sala ? ' · Sala '.$turma->sala : '' }}">
        @unless($anoEmCurso)
            <span class="badge badge-dark align-self-center">{{ \App\Models\AnoLetivo::STATUS[$turma->anoLetivo?->status ?? ''] ?? 'Ano encerrado' }}</span>
        @endunless
        <a href="{{ route('portal.turmas') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left"></i> Voltar</a>
    </x-page-title>
@stop

@section('content')
@php
    $corPct = fn ($v) => $v >= 75 ? 'success' : ($v >= 50 ? 'warning' : 'danger');
    $matriculaUnica = $matriculas->count() === 1 ? $matriculas->first() : null;
    $mediaGeral = $matriculaUnica ? $mediasGerais->get($matriculaUnica->id) : null;
    $freqGeral = $matriculaUnica ? ($frequenciaBlocos->first()['percentualGeral'] ?? null) : null;
    $totalNotas = $notas->sum(fn ($d) => $d->sum(fn ($l) => $l->count()));
    $totalAulasFreq = $frequenciaBlocos->sum(fn ($b) => $b['disciplinas']->sum('total'));
@endphp
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<ul class="nav nav-tabs nav-tabs-app" id="portalTurmaTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="tab-avisos-link" data-toggle="tab" href="#tab-avisos" role="tab"><i class="fas fa-bullhorn mr-1"></i> Avisos <span class="badge badge-light border ml-1">{{ $avisos->count() }}</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-atividades-link" data-toggle="tab" href="#tab-atividades" role="tab"><i class="fas fa-tasks mr-1"></i> Atividades e provas <span class="badge badge-light border ml-1">{{ $tarefas->count() }}</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-notas-link" data-toggle="tab" href="#tab-notas" role="tab"><i class="fas fa-star mr-1"></i> Notas
            @if($mediaGeral !== null)
                <span class="badge badge-{{ $corPct($mediaGeral) }} ml-1">{{ number_format($mediaGeral, 1, ',', '') }}%</span>
            @else
                <span class="badge badge-light border ml-1">{{ $totalNotas }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-frequencia-link" data-toggle="tab" href="#tab-frequencia" role="tab"><i class="fas fa-calendar-check mr-1"></i> Frequência
            @if($freqGeral !== null)
                <span class="badge badge-{{ $corPct($freqGeral) }} ml-1">{{ $freqGeral }}%</span>
            @else
                <span class="badge badge-light border ml-1">{{ $totalAulasFreq }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-disciplinas-link" data-toggle="tab" href="#tab-disciplinas" role="tab"><i class="fas fa-book mr-1"></i> Disciplinas <span class="badge badge-light border ml-1">{{ $turma->disciplinas->count() }}</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-materiais-link" data-toggle="tab" href="#tab-materiais" role="tab"><i class="fas fa-folder-open mr-1"></i> Materiais <span class="badge badge-light border ml-1">{{ $materiais->count() }}</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-pessoas-link" data-toggle="tab" href="#tab-pessoas" role="tab"><i class="fas fa-users mr-1"></i> Pessoas <span class="badge badge-light border ml-1">{{ $colegas->count() }}</span></a>
    </li>
</ul>

<div class="tab-content pt-3">
<div class="tab-pane fade show active" id="tab-avisos" role="tabpanel">
    @php
        $estilosCal = [
            'primary' => ['cor' => '#007bff', 'fundo' => '#e3f0ff', 'texto' => '#0056b3'],
            'danger' => ['cor' => '#dc3545', 'fundo' => '#fbe7ea', 'texto' => '#a71d2a'],
            'success' => ['cor' => '#28a745', 'fundo' => '#e6f4ea', 'texto' => '#1e7e34'],
            'info' => ['cor' => '#17a2b8', 'fundo' => '#dff5f8', 'texto' => '#117a8b'],
            'warning' => ['cor' => '#e0a800', 'fundo' => '#fff3cd', 'texto' => '#856404'],
            'secondary' => ['cor' => '#6c757d', 'fundo' => '#eceef0', 'texto' => '#495057'],
        ];
    @endphp
    <div class="card">
        <div class="card-header py-2 d-flex align-items-center flex-wrap" style="gap:.6rem;">
            <div class="d-flex align-items-center flex-wrap" id="avisosNavCal" style="gap:.6rem;">
                <div class="btn-group btn-group-sm">
                    <a class="btn btn-outline-secondary" href="{{ route('portal.turma', $turma->id) }}?mes={{ $mesAnteriorCal }}#tab-avisos" title="Mês anterior">
                        <i class="fa fa-chevron-left"></i>
                    </a>
                    <a class="btn btn-outline-secondary {{ $ehMesAtualCal ? 'disabled' : '' }}" href="{{ route('portal.turma', $turma->id) }}#tab-avisos" title="Voltar ao mês atual">
                        <i class="fa fa-calendar-day"></i> Hoje
                    </a>
                    <a class="btn btn-outline-secondary" href="{{ route('portal.turma', $turma->id) }}?mes={{ $mesProximoCal }}#tab-avisos" title="Próximo mês">
                        <i class="fa fa-chevron-right"></i>
                    </a>
                </div>
                <span class="font-weight-bold">{{ $rotuloMesCal }}</span>
                <span class="small text-muted">
                    <i class="fas fa-bullhorn"></i> Aviso
                    <i class="fas fa-tasks ml-1"></i> Atividade/prova
                </span>
            </div>
            <div class="btn-group btn-group-sm ml-auto" role="group" aria-label="Modo de exibição dos avisos">
                <button type="button" class="btn btn-outline-secondary" data-visao="lista"><i class="fas fa-list mr-1"></i> Lista</button>
                <button type="button" class="btn btn-outline-secondary" data-visao="calendario"><i class="fas fa-calendar-alt mr-1"></i> Calendário</button>
            </div>
        </div>
        <div class="card-body p-1 p-md-2" id="avisosCalendario">
            <div class="tcal">
                @foreach(['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'] as $ds)
                    <div class="tcal-head">{{ $ds }}</div>
                @endforeach
                @foreach($semanasCal as $semana)
                    @foreach($semana as $dia)
                        <div class="tcal-cell {{ $dia['do_mes'] ? '' : 'tcal-fora' }} {{ $dia['data']->isToday() ? 'tcal-hoje' : '' }}">
                            <div class="tcal-num {{ $dia['data']->isToday() ? 'hoje' : '' }}">{{ $dia['data']->day }}</div>
                            @foreach($dia['itens'] as $item)
                                @php $est = $estilosCal[$item['cor']] ?? $estilosCal['secondary']; @endphp
                                <a href="#" class="tcal-evt" data-item="{{ $item['id'] }}"
                                    style="background:{{ $est['fundo'] }}; border-left-color:{{ $est['cor'] }}; color:{{ $est['texto'] }}"
                                    title="{{ $item['titulo'] }}">
                                    <i class="fas {{ $item['icone'] }}"></i> {{ $item['titulo'] }}
                                </a>
                            @endforeach
                            @if($dia['itens']->count() > 3)
                                <a href="#" class="tcal-mais" data-n="{{ $dia['itens']->count() - 3 }}">+{{ $dia['itens']->count() - 3 }} mais</a>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
        <div class="list-group list-group-flush d-none" id="avisosLista">
            @forelse($avisos as $aviso)
                @php
                    $badgeTipo = ['info' => 'primary', 'urgente' => 'danger', 'evento' => 'success'][$aviso->tipo] ?? 'secondary';
                @endphp
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:4px;">
                        <strong>{{ $aviso->titulo }}</strong>
                        <small class="text-nowrap">
                            <span class="badge badge-{{ $badgeTipo }}">{{ \App\Models\Aviso::TIPOS[$aviso->tipo] ?? $aviso->tipo }}</span>
                            <span class="text-muted">{{ $aviso->data_publicacao?->format('d/m/Y') }}</span>
                        </small>
                    </div>
                    <div class="mt-1" style="white-space:pre-line;">{{ $aviso->mensagem }}</div>
                </div>
            @empty
                <div class="list-group-item text-center text-muted py-4">
                    <i class="fas fa-bullhorn mb-2 d-block fa-2x"></i>Nenhum aviso da turma no momento.
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-atividades" role="tabpanel">
    <div class="card">
        <div class="card-header font-weight-bold"><i class="fas fa-tasks mr-1 text-muted"></i> Atividades e provas</div>
        <div class="list-group list-group-flush">
            @forelse($tarefas as $tarefa)
                @php
                    $entrega = $entregas->get($tarefa->id.'|'.$matriculas->first()->id);
                    $cor = $entrega?->status === 'avaliada' ? 'success' : ($entrega?->status === 'entregue' ? 'info' : ($tarefa->data_entrega?->isPast() ? 'danger' : 'warning'));
                    $statusTxt = $entrega ? \App\Models\EntregaTarefa::STATUS[$entrega->status] ?? $entrega->status : 'Pendente';
                @endphp
                <a href="{{ route('portal.turma.tarefa', [$turma->id, $tarefa->id]) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between align-items-start" style="gap:6px;">
                        <strong>{{ $tarefa->titulo }}</strong>
                        <span class="badge badge-{{ $cor }}">{{ $statusTxt }}</span>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-book fa-fw"></i> {{ $tarefa->disciplina->nome ?? 'Geral' }}
                        @if($tarefa->tipo && $tarefa->tipo !== 'atividade')
                            · <span class="badge badge-light border">{{ $tarefa->tipoLabel() }}</span>
                        @endif
                        @if($tarefa->data_entrega)
                            · <i class="fas fa-calendar-day fa-fw"></i> entrega até {{ $tarefa->data_entrega->format('d/m/Y') }}
                        @endif
                        @if($tarefa->questoes->isNotEmpty())
                            · {{ $tarefa->questoes->count() }} questão(ões) — responda pelo celular
                        @endif
                    </small>
                </a>
            @empty
                <div class="list-group-item text-center text-muted py-4">
                    <i class="fas fa-tasks mb-2 d-block fa-2x"></i>Nenhuma atividade publicada.
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-notas" role="tabpanel">
    @foreach($matriculas as $matricula)
        @include('portal.partials.notas-aluno', [
            'matricula' => $matricula,
            'notasMatricula' => $notas->get($matricula->id, collect()),
            'mediasMatricula' => $medias->get($matricula->id, collect()),
        ])
    @endforeach
</div>

<div class="tab-pane fade" id="tab-frequencia" role="tabpanel">
    @foreach($frequenciaBlocos as $bloco)
        @include('portal.partials.frequencia-aluno', ['bloco' => $bloco])
    @endforeach
</div>

<div class="tab-pane fade" id="tab-disciplinas" role="tabpanel">
    <div class="row">
        @forelse($turma->disciplinas->sortBy('nome') as $disciplina)
            @php
                $cor = \App\Services\TurmaGradeService::corDaDisciplina($disciplina->id);
                $prof = $turma->atribuicoes->where('disciplina_id', $disciplina->id)->whereNotNull('user_id')->first()?->user?->nome;
                $qtdMateriais = $materiais->where('disciplina_id', $disciplina->id)->count();
                $qtdTarefas = $tarefas->where('disciplina_id', $disciplina->id)->count();
            @endphp
            <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                <div class="card card-outline h-100 position-relative" style="border-top-color:{{ $cor['tx'] }}; cursor:pointer;">
                    <a href="{{ route('portal.turma.disciplina', [$turma->id, $disciplina->id]) }}"
                       class="stretched-link" title="Abrir disciplina"></a>
                    <div class="card-header d-flex justify-content-between align-items-center py-1 px-2"
                         style="background:{{ $cor['bg'] }}; border-bottom:0">
                        <span class="font-weight-bold text-truncate" style="color:{{ $cor['tx'] }}; min-width:0" title="{{ $disciplina->nome }}">
                            {{ $disciplina->nome }}
                            @if($disciplina->sigla)
                                <span class="badge align-middle" style="background:{{ $cor['tx'] }}; color:{{ $cor['bg'] }}">{{ $disciplina->sigla }}</span>
                            @endif
                        </span>
                        <i class="fas fa-chevron-right small" style="color:{{ $cor['tx'] }};"></i>
                    </div>
                    <div class="card-body py-2 px-2">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-1">
                                <i class="fas fa-chalkboard-teacher fa-fw text-muted mr-1"></i>{{ $prof ?? 'Sem professor atribuído' }}
                            </li>
                            <li class="mb-1">
                                <i class="fas fa-folder-open fa-fw text-muted mr-1"></i>{{ $qtdMateriais }} material(is)
                            </li>
                            <li class="mb-1">
                                <i class="fas fa-tasks fa-fw text-muted mr-1"></i>{{ $qtdTarefas }} atividade(s)
                            </li>
                            @if($disciplina->pivot?->carga_horaria_semanal)
                                <li>
                                    <i class="fas fa-clock fa-fw text-muted mr-1"></i>{{ $disciplina->pivot->carga_horaria_semanal }}h/semana
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-book fa-2x mb-2 d-block"></i>Nenhuma disciplina vinculada a esta turma.
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

<div class="tab-pane fade" id="tab-materiais" role="tabpanel">
    <div class="card">
        <div class="card-header font-weight-bold"><i class="fas fa-folder-open mr-1 text-muted"></i> Materiais publicados</div>
        <div class="list-group list-group-flush">
            @forelse($materiais as $material)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between flex-wrap mb-1" style="gap:4px;">
                        <strong>{{ $material->titulo }}</strong>
                        <small class="text-muted">
                            <span class="badge badge-light border">{{ $material->disciplina->nome ?? 'Geral' }}</span>
                            {{ $material->autor->nome ?? '' }} · {{ $material->created_at?->format('d/m/Y') }}
                        </small>
                    </div>
                    @if($material->descricao)<p class="mb-2 small">{{ $material->descricao }}</p>@endif
                    @if($material->arquivo_path)
                        <a href="{{ asset('storage/'.$material->arquivo_path) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-download mr-1"></i> Baixar arquivo</a>
                    @elseif($material->url)
                        <a href="{{ $material->url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info"><i class="fas fa-external-link-alt mr-1"></i> Abrir link</a>
                    @endif
                </div>
            @empty
                <div class="list-group-item text-center text-muted py-4">
                    <i class="fas fa-folder-open mb-2 d-block fa-2x"></i>O professor ainda não publicou materiais.
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="tab-pane fade" id="tab-pessoas" role="tabpanel">
    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header font-weight-bold"><i class="fas fa-chalkboard-teacher mr-1 text-muted"></i> Professores</div>
                <div class="list-group list-group-flush">
                    @forelse($turma->atribuicoes->whereNotNull('user_id')->unique('user_id') as $atribuicao)
                        <div class="list-group-item d-flex align-items-center" style="gap:10px;">
                            <img class="portal-avatar" src="{{ $atribuicao->user->foto_perfil_url ?? '' }}" alt="{{ $atribuicao->user->nome ?? '' }}">
                            <div class="min-w-0">
                                <div class="font-weight-bold">{{ $atribuicao->user->nome ?? '' }}</div>
                                <small class="text-muted">{{ $atribuicao->disciplina->nome ?? 'Professor da turma' }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="list-group-item text-center text-muted py-4">
                            <i class="fas fa-chalkboard-teacher mb-2 d-block fa-2x"></i>Sem professores atribuídos.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-users mr-1 text-muted"></i> Colegas de turma</span>
                    <span class="badge badge-light border">{{ $colegas->count() }}</span>
                </div>
                <div class="list-group list-group-flush" style="max-height:480px; overflow-y:auto;">
                    @foreach($colegas as $colega)
                        @php
                            $nomeColega = $colega->aluno->pessoa->nome ?? $colega->aluno->nome;
                            $fotoColega = $colega->aluno->foto_url;
                            $iniciais = collect(explode(' ', (string) $nomeColega))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
                        @endphp
                        <div class="list-group-item d-flex justify-content-between align-items-center" style="gap:10px;">
                            <div class="d-flex align-items-center min-w-0" style="gap:10px;">
                                @if($fotoColega)
                                    <img class="portal-avatar" src="{{ $fotoColega }}" alt="{{ $nomeColega }}">
                                @else
                                    <span class="portal-avatar portal-avatar-inicial">{{ mb_strtoupper($iniciais) }}</span>
                                @endif
                                <div>
                                    <span class="font-weight-bold">{{ $nomeColega }}</span>
                                    @if($matriculas->contains('aluno_id', $colega->aluno_id)) <span class="badge badge-primary ml-1">você</span> @endif
                                </div>
                            </div>
                            <span class="badge badge-light border">Nº {{ $colega->numero_chamada ?? '—' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="modalItemCal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content mi-card">
            <div class="mi-head" id="miHead">
                <span class="mi-icone" id="miIconeWrap"><i class="fas fa-bullhorn" id="miIcone"></i></span>
                <div class="min-w-0 flex-grow-1">
                    <div class="mi-kind" id="miKind"></div>
                    <h5 class="mi-titulo mb-0" id="miTitulo"></h5>
                </div>
                <button type="button" class="close mi-close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center flex-wrap mb-3" style="gap:.4rem;">
                    <span class="badge badge-pill" id="miBadge"></span>
                    <span class="mi-meta"><i class="far fa-calendar-alt mr-1"></i><span id="miData"></span></span>
                    <span class="mi-meta" id="miInfoWrap"><i class="fas fa-fw mr-1" id="miInfoIcone"></i><span id="miInfo"></span></span>
                </div>
                <div id="miMensagem" class="mi-mensagem"></div>
            </div>
            <div class="modal-footer py-2">
                <a href="#" id="miLink" class="btn btn-primary btn-sm"><i class="fas fa-external-link-alt mr-1"></i> Abrir atividade</a>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<style>
    .portal-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .portal-avatar-sm { width: 28px; height: 28px; }
    .portal-avatar-inicial { display: inline-flex; align-items: center; justify-content: center; background: #eef2f7; color: #4a6da7; font-weight: 800; font-size: .72rem; }
    .min-w-0 { min-width: 0; }

    .tcal { display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: #dee2e6; border: 1px solid #dee2e6; border-radius: .4rem; overflow: hidden; }
    .tcal-head { background: #f1f3f5; text-align: center; font-size: .7rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #6c757d; padding: .4rem .25rem; }
    .tcal-cell { background: #fff; min-height: 88px; padding: .25rem .3rem; }
    .tcal-cell.tcal-fora { background: #f8f9fa; }
    .tcal-cell.tcal-fora .tcal-num { color: #c3c8cd; }
    .tcal-cell.tcal-hoje { background: #f0f7ff; }
    .tcal-num { display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 50%; font-size: .75rem; font-weight: 700; color: #495057; }
    .tcal-num.hoje { background: #007bff; color: #fff; }
    .tcal-evt { display: block; margin-top: 2px; padding: 1px 5px; font-size: 11px; line-height: 1.4; border-radius: 3px; border-left: 3px solid transparent; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; }
    .tcal-evt:hover { filter: brightness(.94); text-decoration: none; }
    .tcal-cell:not(.tcal-aberto) a.tcal-evt:nth-of-type(n+4) { display: none; }
    .tcal-mais { display: block; margin-top: 2px; padding-left: 5px; font-size: 10px; color: #6c757d; }

    #modalItemCal .modal-content { border: 0; border-radius: .8rem; overflow: hidden; box-shadow: 0 1rem 3rem rgba(0,0,0,.28); }
    .mi-head { display: flex; align-items: flex-start; gap: .8rem; padding: 1rem 1.15rem; }
    .mi-icone { flex-shrink: 0; width: 42px; height: 42px; border-radius: .65rem; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 1.05rem; box-shadow: 0 .3rem .7rem rgba(0,0,0,.18); }
    .mi-kind { font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .72; }
    .mi-titulo { font-weight: 700; line-height: 1.25; }
    .mi-close { margin: -.15rem -.25rem 0 auto; padding: 0 .25rem; color: inherit; opacity: .55; text-shadow: none; font-size: 1.45rem; line-height: 1; }
    .mi-close:hover { color: inherit; opacity: 1; }
    .mi-meta { display: inline-flex; align-items: center; font-size: .78rem; color: #5a6169; background: #f1f3f5; border-radius: 999px; padding: .22rem .65rem; }
    .mi-mensagem { white-space: pre-line; max-height: 45vh; overflow-y: auto; color: #3a4046; }
    #modalItemCal .modal-footer { border-top-color: #eef1f4; }
    @media (max-width: 575.98px) {
        .tcal-cell { min-height: 60px; }
        .tcal-evt { font-size: 10px; }
    }
</style>
@stop

@section('js')
<script>
    $(function () {
        var abas = { avisos: '#tab-avisos', atividades: '#tab-atividades', notas: '#tab-notas', frequencia: '#tab-frequencia', disciplinas: '#tab-disciplinas', materiais: '#tab-materiais', pessoas: '#tab-pessoas' };
        var hash = window.location.hash || abas['{{ $aba }}'] || '';
        if (hash && $('#portalTurmaTabs a[href="' + hash + '"]').length) {
            $('#portalTurmaTabs a[href="' + hash + '"]').tab('show');
        }
        $('#portalTurmaTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            history.replaceState(null, '', e.target.hash);
        });
        // Aba Avisos: alterna lista/calendario; ?mes= forca calendario e a
        // escolha fica salva no navegador
        var chaveVisao = 'portalTurmaAvisosVisao';
        function setVisaoAvisos(v) {
            var cal = v === 'calendario';
            $('#avisosCalendario').toggleClass('d-none', !cal);
            $('#avisosLista').toggleClass('d-none', cal);
            $('#avisosNavCal').toggle(cal);
            $('#tab-avisos [data-visao]').removeClass('active')
                .filter('[data-visao="' + v + '"]').addClass('active');
            try { localStorage.setItem(chaveVisao, v); } catch (e) {}
        }
        $('#tab-avisos [data-visao]').on('click', function () {
            setVisaoAvisos($(this).data('visao'));
        });
        var visaoSalva = 'calendario';
        try { visaoSalva = localStorage.getItem(chaveVisao) || visaoSalva; } catch (e) {}
        setVisaoAvisos(@json(request()->has('mes')) ? 'calendario' : visaoSalva);

        // Calendario da aba Avisos: clique num item abre modal com os detalhes
        var itensCal = @json($itensCalJson);
        var coresCal = @json($estilosCal);
        $('.tcal').on('click', '.tcal-evt', function (e) {
            e.preventDefault();
            var item = itensCal[$(this).data('item')];
            if (!item) return;
            var est = coresCal[item.cor] || coresCal.secondary;
            $('#miHead').css({ background: est.fundo, color: est.texto });
            $('#miIconeWrap').css('background', est.cor);
            $('#miIcone').attr('class', 'fas ' + item.icone);
            $('#miKind').text(item.rotulo || '');
            $('#miTitulo').text(item.titulo);
            $('#miBadge').attr('class', 'badge badge-pill badge-' + item.cor).text(item.badge);
            $('#miData').text(item.data);
            $('#miInfoWrap').toggle(!!item.info);
            $('#miInfoIcone').attr('class', 'fas ' + (item.info_icone || 'fa-info-circle') + ' fa-fw mr-1');
            $('#miInfo').text(item.info || '');
            $('#miMensagem').toggle(!!item.mensagem).text(item.mensagem || '');
            $('#miLink').toggle(!!item.url).attr('href', item.url || '#')
                .attr('class', 'btn btn-sm btn-' + item.cor)
                .html('<i class="fas fa-external-link-alt mr-1"></i> Abrir ' + (item.rotulo || 'item').toLowerCase());
            $('#modalItemCal').modal('show');
        });
        // "+N mais" expande/recolhe os itens do dia
        $('.tcal').on('click', '.tcal-mais', function (e) {
            e.preventDefault();
            var cell = $(this).closest('.tcal-cell').toggleClass('tcal-aberto');
            $(this).text(cell.hasClass('tcal-aberto') ? 'ver menos' : '+' + $(this).data('n') + ' mais');
        });
    });
</script>
@stop
