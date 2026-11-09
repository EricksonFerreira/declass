@extends('adminlte::page')
@section('title', $titulo)
@section('content_header')
    @php
        $corTipo = ['prova' => 'danger', 'trabalho' => 'warning', 'atividade' => 'info'];
        $corDisc = $tarefa->disciplina ? \App\Services\TurmaGradeService::corDaDisciplina($tarefa->disciplina->id) : null;
        $situacao = $entrega?->status === 'avaliada' ? 'avaliada'
            : ($entrega?->status === 'entregue' ? 'entregue'
            : ($tarefa->data_entrega?->isPast() ? 'atrasada' : 'pendente'));
        $corSituacao = ['avaliada' => 'success', 'entregue' => 'info', 'atrasada' => 'danger', 'pendente' => 'warning'][$situacao];
        $txtSituacao = ['avaliada' => 'Avaliada', 'entregue' => 'Entregue', 'atrasada' => 'Atrasada', 'pendente' => 'Pendente'][$situacao];
        $fmtNum = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ',');
    @endphp
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="{{ $turma->nome }}{{ $tarefa->disciplina ? ' · '.$tarefa->disciplina->nome : '' }}">
        <span class="badge badge-{{ $corTipo[$tarefa->tipo] ?? 'info' }} align-self-center">{{ $tarefa->tipoLabel() }}</span>
        @if($tarefa->disciplina?->sigla)
            <span class="badge align-self-center" style="background:{{ $corDisc['tx'] }}; color:{{ $corDisc['bg'] }}">{{ $tarefa->disciplina->sigla }}</span>
        @endif
        <span class="badge badge-{{ $corSituacao }} align-self-center">{{ $txtSituacao }}</span>
    </x-page-title>
@stop

@section('content')
@php
    $enviada = $entrega && in_array($entrega->status, ['entregue', 'avaliada']);
    $totalQuestoes = $tarefa->questoes->count();
@endphp

@if(session('success'))<div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Confira as respostas: {{ $errors->first() }}</div>@endif
@if($isResponsavel)
    <div class="callout callout-info py-2">
        <i class="fas fa-eye mr-1"></i> Você está acompanhando esta atividade — apenas o aluno pode responder.
    </div>
@endif

{{-- Cabecalho da atividade com a cor da disciplina (mesmo padrao da pagina da disciplina) --}}
<div class="card card-outline {{ $corDisc ? '' : 'card-primary' }}" @if($corDisc) style="border-top-color:{{ $corDisc['tx'] }};" @endif>
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;{{ $corDisc ? ' background:'.$corDisc['bg'].'; border-bottom:0;' : '' }}">
        <span class="font-weight-bold" @if($corDisc) style="color:{{ $corDisc['tx'] }};" @endif>
            <i class="fas fa-clipboard-list mr-1"></i> {{ $tarefa->disciplina->nome ?? 'Atividade geral' }}
        </span>
        <div class="d-flex align-items-center flex-wrap small {{ $corDisc ? '' : 'text-muted' }}" style="gap:.9rem;{{ $corDisc ? ' color:'.$corDisc['tx'].';' : '' }}">
            @if($tarefa->data_entrega)
                <span><i class="far fa-calendar-alt mr-1"></i>Entrega até <strong>{{ $tarefa->data_entrega->format('d/m/Y') }}</strong></span>
            @endif
            @if($tarefa->nota_maxima)
                <span><i class="fas fa-star mr-1"></i>Vale {{ $fmtNum($tarefa->nota_maxima) }} pts</span>
            @endif
            @if($totalQuestoes)
                <span><i class="fas fa-list-ol mr-1"></i>{{ $totalQuestoes }} {{ $totalQuestoes === 1 ? 'questão' : 'questões' }}</span>
            @endif
            @if($tarefa->exigeEntregaOnline())
                <span><i class="fas fa-inbox mr-1"></i>{{ $tarefa->tipoEntregaLabel() }}</span>
            @endif
        </div>
    </div>
    @if($tarefa->descricao || $tarefa->instrucoes)
        <div class="card-body">
            @if($tarefa->descricao)<p class="mb-2">{!! nl2br(e($tarefa->descricao)) !!}</p>@endif
            @if($tarefa->instrucoes)
                <div class="callout callout-info py-2 mb-0 small"><i class="fas fa-info-circle mr-1"></i> {!! nl2br(e($tarefa->instrucoes)) !!}</div>
            @endif
        </div>
    @endif
</div>

{{-- Conta com mais de um aluno escolhe de quem e a entrega --}}
@if($matriculas->count() > 1)
    <div class="card">
        <div class="card-body py-2 d-flex align-items-center flex-wrap" style="gap:.5rem;">
            <span class="small font-weight-bold"><i class="fas fa-user-graduate mr-1 text-muted"></i> {{ $isResponsavel ? 'Aluno:' : 'Respondendo como:' }}</span>
            <form method="GET" class="mb-0">
                <select name="matricula_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    @foreach($matriculas as $m)
                        <option value="{{ $m->id }}" {{ $matricula->id === $m->id ? 'selected' : '' }}>{{ $m->aluno->pessoa->nome ?? $m->aluno->nome }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
@endif

@if($entrega && $entrega->status === 'avaliada')
    <div class="callout callout-success">
        <i class="fas fa-award mr-1"></i> <strong>Avaliada</strong>
        @if($entrega->nota !== null) <span class="badge badge-success ml-1">Nota {{ $fmtNum($entrega->nota) }}</span> @endif
        @if($entrega->observacoes) <div class="small mt-1">{{ $entrega->observacoes }}</div> @endif
    </div>
@endif

{{-- Questoes online (objetivas/discursivas) --}}
@if($totalQuestoes)
    @php
        $somenteLeitura = $isResponsavel || ($entrega && in_array($entrega->status, ['entregue', 'avaliada']));
        $respondidas = $respostas->count();
    @endphp

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-1" style="gap:.5rem;">
        <span class="font-weight-bold text-muted"><i class="fas fa-list-ol mr-1"></i> Questões</span>
        <span class="small text-muted"><strong id="qtd-respondidas">{{ $respondidas }}</strong> de {{ $totalQuestoes }} respondidas</span>
    </div>
    <div class="progress progress-xs mb-3">
        <div id="barra-respondidas" class="progress-bar bg-primary" role="progressbar" style="width: {{ round($respondidas / $totalQuestoes * 100) }}%"></div>
    </div>

    <form method="POST" action="{{ $rotaPost }}">
        @csrf
        <input type="hidden" name="matricula_id" value="{{ $matricula->id }}">

        @foreach($tarefa->questoes->sortBy('ordem')->values() as $i => $questao)
            @php $minha = $respostas->get($questao->id); @endphp
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start" style="gap:.6rem;">
                        <span class="questao-num">{{ $i + 1 }}</span>
                        <p class="font-weight-bold mb-0 flex-grow-1">{{ $questao->enunciado }}</p>
                        @if($entrega?->status === 'avaliada' && $minha?->correta !== null)
                            <span class="badge badge-{{ $minha->correta ? 'success' : 'danger' }} text-nowrap">
                                <i class="fas {{ $minha->correta ? 'fa-check' : 'fa-times' }} mr-1"></i>
                                {{ $minha->correta ? 'Correta' : 'Incorreta' }}
                            </span>
                        @endif
                    </div>

                    @if($questao->tipo === 'objetiva')
                        <div class="mt-3">
                            @foreach(($questao->alternativas ?? []) as $letra => $texto)
                                @php $marcada = $minha && strtoupper($minha->resposta) === strtoupper($letra); @endphp
                                <div class="custom-control custom-radio alternativa {{ $marcada ? 'alt-marcada' : '' }}">
                                    <input type="radio" class="custom-control-input" id="q{{ $questao->id }}{{ $letra }}"
                                           name="respostas[{{ $questao->id }}]" value="{{ $letra }}"
                                           {{ $marcada ? 'checked' : '' }} {{ $somenteLeitura ? 'disabled' : '' }}>
                                    <label class="custom-control-label font-weight-normal w-100 mb-0" for="q{{ $questao->id }}{{ $letra }}">
                                        <strong class="mr-1">{{ $letra }})</strong> {{ $texto }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @if($entrega && $entrega->status === 'avaliada' && $minha?->correta === false && $questao->gabarito)
                            <small class="text-danger d-block"><i class="fas fa-times-circle mr-1"></i> Gabarito: <strong>{{ $questao->gabarito }}</strong></small>
                        @endif
                    @else
                        <textarea name="respostas[{{ $questao->id }}]" class="form-control mt-3" rows="4" maxlength="2000"
                                  placeholder="Digite sua resposta..." {{ $somenteLeitura ? 'disabled' : '' }}>{{ $minha->resposta ?? '' }}</textarea>
                    @endif
                </div>
            </div>
        @endforeach

        @unless($somenteLeitura)
            <button type="submit" class="btn btn-primary btn-block btn-lg mb-4">
                <i class="fas fa-paper-plane mr-1"></i> Enviar respostas
            </button>
        @else
            <div class="alert alert-info"><i class="fas fa-info-circle mr-1"></i>
                @if($isResponsavel && ! $enviada)
                    O aluno ainda não respondeu esta atividade.
                @else
                    Respostas já enviadas{{ $entrega->status === 'entregue' ? ' — aguardando correção do professor' : '' }}.
                @endif
            </div>
        @endunless
    </form>
@endif

{{-- Entrega simples: confirmacao de leitura, texto digitado ou fotos --}}
@if($tarefa->exigeEntregaOnline())
    <div class="card card-outline card-primary">
        <div class="card-header font-weight-bold"><i class="fas fa-inbox mr-1"></i> {{ $isResponsavel ? 'Entrega do aluno' : 'Sua entrega' }}</div>
        <div class="card-body">

            @if($entrega && $entrega->resposta)
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">{{ $isResponsavel ? 'Texto do aluno:' : 'Seu texto:' }}</small>
                    <div class="border rounded p-2 bg-light small">{!! nl2br(e($entrega->resposta)) !!}</div>
                </div>
            @endif
            @if($entrega && $entrega->anexos->isNotEmpty())
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Arquivos enviados:</small>
                    <div class="d-flex flex-wrap" style="gap:.5rem;">
                        @foreach($entrega->anexos as $anexo)
                            <a href="{{ $anexo->url() }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-paperclip mr-1"></i> {{ \Illuminate\Support\Str::limit($anexo->nome_original ?? 'Arquivo', 25) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($tarefa->tipo_entrega === 'leitura')
                @if($enviada)
                    <div class="mb-0 text-success"><i class="fas fa-check-circle mr-1"></i> Leitura confirmada{{ $entrega->status === 'entregue' ? ' — aguardando avaliação do professor' : '' }}.</div>
                @elseif($isResponsavel)
                    <p class="small text-muted mb-0">O aluno ainda não confirmou a leitura.</p>
                @else
                    <p class="small text-muted">Esta atividade pede apenas a confirmação de que você leu o conteúdo.</p>
                    <form method="POST" action="{{ $rotaPost }}">
                        @csrf
                        <input type="hidden" name="modo" value="entrega">
                        <input type="hidden" name="matricula_id" value="{{ $matricula->id }}">
                        <button type="submit" class="btn btn-success btn-block"><i class="fas fa-check mr-1"></i> Li e entendi</button>
                    </form>
                @endif
            @elseif($isResponsavel)
                @unless($enviada)<p class="small text-muted mb-0">O aluno ainda não enviou esta atividade.</p>@endunless
            @else
                <form method="POST" action="{{ $rotaPost }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="modo" value="entrega">
                    <input type="hidden" name="matricula_id" value="{{ $matricula->id }}">

                    @if($tarefa->tipo_entrega === 'foto')
                        <div class="form-group">
                            <label>Fotos do que foi feito</label>
                            <input type="file" name="anexos[]" class="form-control-file" accept="image/*,.pdf" multiple {{ $entrega?->status === 'avaliada' ? 'disabled' : '' }}>
                            <small class="text-muted">Até 5 arquivos (imagem ou PDF, 8MB cada).</small>
                        </div>
                        <div class="form-group">
                            <label>Comentário <small class="text-muted">(opcional)</small></label>
                            <textarea name="texto" class="form-control" rows="3" maxlength="4000" {{ $entrega?->status === 'avaliada' ? 'disabled' : '' }}>{{ old('texto', $entrega->resposta ?? '') }}</textarea>
                        </div>
                    @elseif($tarefa->tipo_entrega === 'texto')
                        <div class="form-group">
                            <label>Seu texto</label>
                            <textarea name="texto" class="form-control" rows="6" maxlength="4000" required
                                      placeholder="Digite aqui o que a atividade pede..." {{ $entrega?->status === 'avaliada' ? 'disabled' : '' }}>{{ old('texto', $entrega->resposta ?? '') }}</textarea>
                        </div>
                    @endif

                    @if($entrega?->status !== 'avaliada')
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane mr-1"></i> {{ $enviada ? 'Atualizar entrega' : 'Enviar para correção' }}
                        </button>
                        @if($enviada)<small class="text-muted d-block mt-1">Enviada — aguardando correção do professor. Você ainda pode atualizar.</small>@endif
                    @endif
                </form>
            @endif
        </div>
    </div>
@elseif(!$totalQuestoes)
    <div class="alert alert-info"><i class="fas fa-info-circle mr-1"></i> Esta atividade não possui envio online — veja as instruções acima.</div>
@endif

<style>
    .progress-xs { height: 6px; }
    .questao-num {
        display: inline-flex; align-items: center; justify-content: center;
        width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
        background: #e9ecef; color: #495057; font-size: .8rem; font-weight: 700;
    }
    .alternativa {
        padding: .55rem .75rem .55rem 2.2rem; margin-bottom: .5rem;
        border: 1px solid #dee2e6; border-radius: .5rem; cursor: pointer;
        transition: background .15s ease, border-color .15s ease;
    }
    .alternativa:hover { background: #f8f9fa; border-color: #adb5bd; }
    .alternativa.alt-marcada { background: #e8f0ff; border-color: #7ea7f0; }
</style>
@stop

@section('js')
@if($totalQuestoes)
<script>
    $(function () {
        var total = {{ $totalQuestoes }};

        function atualizarProgresso() {
            var respondidas = $('input[type=radio][name^="respostas"]:checked').length;
            $('textarea[name^="respostas"]').each(function () {
                if ($.trim($(this).val()) !== '') { respondidas++; }
            });
            $('#qtd-respondidas').text(respondidas);
            $('#barra-respondidas').css('width', Math.round(respondidas / total * 100) + '%');
        }

        $(document).on('change', 'input[type=radio][name^="respostas"]', function () {
            $('input[name="' + $(this).attr('name') + '"]').closest('.alternativa').removeClass('alt-marcada');
            $(this).closest('.alternativa').addClass('alt-marcada');
            atualizarProgresso();
        });
        $(document).on('input', 'textarea[name^="respostas"]', atualizarProgresso);
    });
</script>
@endif
@stop
