@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Eventos das escolas dos seus alunos, mês a mês" />
@stop

@section('content')
@php
    $tipoEstilos = [
        'reuniao'   => ['cor' => '#007bff', 'fundo' => '#e3f0ff', 'texto' => '#0056b3'],
        'evento'    => ['cor' => '#17a2b8', 'fundo' => '#dff5f8', 'texto' => '#117a8b'],
        'formatura' => ['cor' => '#6f42c1', 'fundo' => '#efe9fb', 'texto' => '#5a32a3'],
        'feriado'   => ['cor' => '#6c757d', 'fundo' => '#eceef0', 'texto' => '#495057'],
        'avaliacao' => ['cor' => '#e0a800', 'fundo' => '#fff3cd', 'texto' => '#856404'],
        'prazo'     => ['cor' => '#dc3545', 'fundo' => '#fbe7ea', 'texto' => '#a71d2a'],
        'outro'     => ['cor' => '#343a40', 'fundo' => '#e9eaec', 'texto' => '#23272b'],
    ];
    $tipoIcones = [
        'reuniao' => 'fa-users',
        'evento' => 'fa-calendar-check',
        'formatura' => 'fa-graduation-cap',
        'feriado' => 'fa-umbrella-beach',
        'avaliacao' => 'fa-clipboard-list',
        'prazo' => 'fa-flag',
        'outro' => 'fa-bookmark',
    ];
    $estilo = fn ($tipo) => $tipoEstilos[$tipo] ?? $tipoEstilos['outro'];
    $icone = fn ($tipo) => $tipoIcones[$tipo] ?? 'fa-bookmark';
    $diasSemana = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
    $tiposNoMes = $eventos->pluck('tipo')->filter()->unique();
    // Eventos que comecam antes do mes agrupam no primeiro dia exibido.
    $porDia = $eventos
        ->groupBy(fn ($e) => $e->data_inicio->lt($mes) ? $mes->toDateString() : $e->data_inicio->toDateString())
        ->sortKeys();
    $hora = fn ($e, $campo) => $e->{$campo} ? \Carbon\Carbon::parse($e->{$campo})->format('H:i') : null;
    $dataLonga = fn ($d) => ucfirst($d->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y'));
    $eventosJson = $eventos->keyBy('id')->map(function ($e) use ($estilo, $icone, $hora, $dataLonga) {
        $fim = $e->periodo_fim;
        return [
            'titulo' => $e->titulo,
            'tipo' => $e->tipo_label,
            'icone' => $icone($e->tipo),
            'cor' => $estilo($e->tipo)['cor'],
            'fundo' => $estilo($e->tipo)['fundo'],
            'data' => $dataLonga($e->data_inicio),
            'ate' => $fim->gt($e->data_inicio) ? $dataLonga($fim) : null,
            'hora' => $e->hora_inicio
                ? $hora($e, 'hora_inicio').($e->hora_fim ? ' às '.$hora($e, 'hora_fim') : '')
                : null,
            'escola' => $e->escola->nome ?? 'Toda a rede',
            'local' => $e->local,
            'descricao' => $e->descricao,
        ];
    });
@endphp

<x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />

<div class="card">
    <div class="card-body py-2 d-flex align-items-center flex-wrap" style="gap:.6rem;">
        <div class="btn-group btn-group-sm">
            <a class="btn btn-outline-secondary ag-nav-mes" href="{{ route('portal.agenda', ['mes' => $mesAnterior]) }}" title="Mês anterior">
                <i class="fa fa-chevron-left"></i>
            </a>
            <a class="btn btn-outline-secondary ag-nav-mes {{ $ehMesAtual ? 'disabled' : '' }}" href="{{ route('portal.agenda') }}" title="Voltar ao mês atual">
                <i class="fa fa-calendar-day"></i> Hoje
            </a>
            <a class="btn btn-outline-secondary ag-nav-mes" href="{{ route('portal.agenda', ['mes' => $mesProximo]) }}" title="Próximo mês">
                <i class="fa fa-chevron-right"></i>
            </a>
        </div>
        <h5 class="mb-0 font-weight-bold">{{ $rotuloMes }}</h5>
        @if(! empty($temAulaPorDia) || $tiposNoMes->isNotEmpty())
            <div class="ml-auto d-flex align-items-center flex-wrap" style="gap:.7rem;">
                @if(! empty($temAulaPorDia))
                    <span class="ag-legenda"><i class="fas fa-square" style="color:#5cb85c"></i> Com aula</span>
                    <span class="ag-legenda"><i class="fas fa-square" style="color:#e0939d"></i> Sem aula</span>
                    @if(! empty($feriadosPorDia))
                        <span class="ag-legenda"><i class="fas fa-umbrella-beach" style="color:#6c757d"></i> Feriado</span>
                    @endif
                @endif
                @foreach($tiposNoMes as $tipo)
                    <span class="ag-legenda">
                        <i class="fas {{ $icone($tipo) }}" style="color: {{ $estilo($tipo)['cor'] }}"></i>
                        {{ \App\Models\Evento::TIPOS[$tipo] ?? ucfirst($tipo) }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</div>

<ul class="nav nav-tabs nav-tabs-app" id="agendaTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#tab-calendario" role="tab"><i class="fas fa-calendar-alt mr-1"></i> Calendário</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-lista" role="tab"><i class="fas fa-list-ul mr-1"></i> Lista <span class="badge badge-light border ml-1">{{ $eventos->count() }}</span></a>
    </li>
</ul>

<div class="tab-content pt-3">
<div class="tab-pane fade show active" id="tab-calendario" role="tabpanel">
<div class="card">
    <div class="card-body p-2 p-md-3">
        <div class="agenda-cal">
            @foreach($diasSemana as $ds)
                <div class="cal-head">{{ $ds }}</div>
            @endforeach
            @foreach($semanas as $semana)
                @foreach($semana as $dia)
                    @php
                        $temAula = $dia['do_mes'] ? ($temAulaPorDia[$dia['data']->toDateString()] ?? null) : null;
                        $feriadosDia = $dia['do_mes'] ? ($feriadosPorDia[$dia['data']->toDateString()] ?? []) : [];
                    @endphp
                    <div class="cal-cell {{ $dia['do_mes'] ? '' : 'cal-fora' }} {{ $dia['data']->isToday() ? 'cal-hoje' : '' }} {{ $temAula === true ? 'cal-com-aula' : ($temAula === false ? 'cal-sem-aula' : '') }}">
                        <div class="cal-num {{ $dia['data']->isToday() ? 'hoje' : '' }}">{{ $dia['data']->day }}</div>
                        @foreach($feriadosDia as $nomeFeriado)
                            <div class="cal-feriado" title="{{ $nomeFeriado }} — dia sem aula">
                                <i class="fas fa-umbrella-beach"></i> {{ $nomeFeriado }}
                            </div>
                        @endforeach
                        @foreach($dia['eventos']->take(3) as $evento)
                            @php $est = $estilo($evento->tipo); @endphp
                            <a href="#" class="cal-evt" data-evt="{{ $evento->id }}"
                                style="background: {{ $est['fundo'] }}; border-left-color: {{ $est['cor'] }}; color: {{ $est['texto'] }}"
                                title="{{ $evento->titulo }}{{ $hora($evento, 'hora_inicio') ? ' — '.$hora($evento, 'hora_inicio') : '' }}">
                                <i class="fas {{ $icone($evento->tipo) }}"></i>
                                {{ $hora($evento, 'hora_inicio') ? $hora($evento, 'hora_inicio').' ' : '' }}{{ $evento->titulo }}
                            </a>
                        @endforeach
                        @if($dia['eventos']->count() > 3)
                            <a href="#" class="cal-mais"
                                data-dia="{{ $dataLonga($dia['data']) }}"
                                data-eventos="{{ $dia['eventos']->pluck('id')->implode(',') }}">
                                +{{ $dia['eventos']->count() - 3 }} mais
                            </a>
                        @endif
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>
</div>
</div>

<div class="tab-pane fade" id="tab-lista" role="tabpanel">
<div class="card">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <span class="font-weight-bold"><i class="fas fa-list-ul"></i> Eventos de {{ $rotuloMes }}</span>
        <span class="badge badge-secondary">{{ $eventos->count() }}</span>
    </div>
    <div class="card-body pt-1">
        @forelse($porDia as $dia => $eventosDoDia)
            @php $dataDia = \Carbon\Carbon::parse($dia); @endphp
            <div class="ag-dia">
                <div class="ag-data {{ $dataDia->isToday() ? 'ag-hoje' : '' }}">
                    <span class="ag-num">{{ $dataDia->format('d') }}</span>
                    <span class="ag-mes">{{ $dataDia->locale('pt_BR')->translatedFormat('M') }}</span>
                    <span class="ag-sem">{{ $dataDia->locale('pt_BR')->translatedFormat('D') }}</span>
                </div>
                <div class="ag-lista">
                    @foreach($eventosDoDia as $evento)
                        @php
                            $est = $estilo($evento->tipo);
                            $fim = $evento->periodo_fim;
                        @endphp
                        <div class="ag-evento" id="evt-{{ $evento->id }}" style="border-left-color: {{ $est['cor'] }}">
                            <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.3rem;">
                                <div>
                                    <i class="fas {{ $icone($evento->tipo) }}" style="color: {{ $est['cor'] }}"></i>
                                    <strong>{{ $evento->titulo }}</strong>
                                    <span class="badge ml-1" style="background: {{ $est['cor'] }}">{{ $evento->tipo_label }}</span>
                                    @if($dataDia->isToday())
                                        <span class="badge badge-success ml-1">Hoje</span>
                                    @endif
                                </div>
                                @if($evento->hora_inicio)
                                    <span class="ag-hora">
                                        <i class="far fa-clock"></i> {{ $hora($evento, 'hora_inicio') }}
                                        @if($evento->hora_fim) às {{ $hora($evento, 'hora_fim') }}@endif
                                    </span>
                                @endif
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="fas fa-school"></i> {{ $evento->escola->nome ?? 'Toda a rede' }}
                                @if($evento->local)
                                    · <i class="fas fa-map-marker-alt"></i> {{ $evento->local }}
                                @endif
                                @if($fim->gt($evento->data_inicio))
                                    · <i class="fas fa-arrows-alt-h"></i> até {{ $fim->format('d/m/Y') }}
                                @endif
                                @if($evento->data_inicio->lt($mes))
                                    · <span class="badge badge-light border">vem de {{ $evento->data_inicio->format('d/m') }}</span>
                                @endif
                            </div>
                            @if($evento->descricao)
                                <div class="small mt-1">{{ \Illuminate\Support\Str::limit($evento->descricao, 300) }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-alt fa-3x mb-3 d-block"></i>
                Nenhum evento neste mês para as escolas dos seus alunos.
            </div>
        @endforelse
    </div>
</div>
</div>
</div>

<div class="modal fade" id="modalEvento" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content evt-modal">
            <div class="evt-topo">
                <span class="evt-icone"><i class="fas fa-bookmark"></i></span>
                <button type="button" class="evt-fechar" data-dismiss="modal" aria-label="Fechar">&times;</button>
                <span class="badge evt-badge"></span>
                <h5 class="evt-titulo"></h5>
            </div>
            <div class="modal-body evt-corpo">
                <div class="evt-detalhe">
                    <div class="evt-linha m-data"><i class="far fa-calendar-alt"></i><span></span></div>
                    <div class="evt-linha m-ate"><i class="fas fa-arrows-alt-h"></i><span></span></div>
                    <div class="evt-linha m-hora"><i class="far fa-clock"></i><span></span></div>
                    <div class="evt-linha m-escola"><i class="fas fa-school"></i><span></span></div>
                    <div class="evt-linha m-local"><i class="fas fa-map-marker-alt"></i><span></span></div>
                    <div class="evt-desc"></div>
                </div>
                <div class="evt-dia-lista"></div>
            </div>
            <div class="modal-footer evt-rodape">
                <button type="button" class="btn btn-sm btn-outline-secondary evt-voltar">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar ao dia
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(function () {
        var abas = { calendario: '#tab-calendario', lista: '#tab-lista' };
        var hash = window.location.hash;
        var link = hash ? $('#agendaTabs a[href="' + hash + '"]') : $();

        // Hash de evento (#evt-N) abre a aba que contem o elemento.
        if (!link.length && hash && $(hash).length) {
            var pane = $(hash).closest('.tab-pane');
            link = $('#agendaTabs a[href="#' + pane.attr('id') + '"]');
        }
        if (!link.length) {
            link = $('#agendaTabs a[href="' + (abas['{{ $aba }}'] || '#tab-calendario') + '"]');
        }
        link.tab('show');

        $('#agendaTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            history.replaceState(null, '', e.target.hash);
            // Navegar entre meses mantem a aba ativa.
            $('.ag-nav-mes').each(function () {
                this.href = this.href.split('#')[0] + e.target.hash;
            });
        });

        // Clicar num evento do calendario abre o modal de detalhes.
        var EVENTOS = @json($eventosJson);
        var $modal = $('#modalEvento');
        var ultimoDia = null;

        function linha(seletor, texto) {
            $modal.find(seletor).toggle(!!texto).find('span').text(texto || '');
        }

        function pintar(cor, fundo) {
            $modal.find('.evt-modal').css('--evt-cor', cor).css('--evt-fundo', fundo);
        }

        function abrirEvento(id, voltar) {
            var ev = EVENTOS[id];
            if (!ev) return;
            pintar(ev.cor, ev.fundo);
            $modal.find('.evt-icone i').attr('class', 'fas ' + ev.icone);
            $modal.find('.evt-badge').text(ev.tipo).css('background', ev.cor);
            $modal.find('.evt-titulo').text(ev.titulo);
            linha('.m-data', ev.data);
            linha('.m-ate', ev.ate ? 'até ' + ev.ate : null);
            linha('.m-hora', ev.hora);
            linha('.m-escola', ev.escola);
            linha('.m-local', ev.local);
            $modal.find('.evt-desc').toggle(!!ev.descricao).text(ev.descricao || '');
            $modal.find('.evt-detalhe').show();
            $modal.find('.evt-dia-lista').hide().empty();
            $modal.find('.evt-rodape').toggle(!!voltar);
            if (!$modal.hasClass('show')) $modal.modal('show');
        }

        function abrirDia(rotulo, ids) {
            ultimoDia = { rotulo: rotulo, ids: ids };
            pintar('#007bff', '#e3f0ff');
            $modal.find('.evt-icone i').attr('class', 'fas fa-calendar-day');
            $modal.find('.evt-badge').text(ids.length + (ids.length > 1 ? ' eventos' : ' evento')).css('background', '#007bff');
            $modal.find('.evt-titulo').text(rotulo);
            var lista = $modal.find('.evt-dia-lista').empty();
            ids.forEach(function (id) {
                var ev = EVENTOS[id];
                if (!ev) return;
                $('<a href="#" class="dia-evt">')
                    .attr('data-evt', id)
                    .css({ background: ev.fundo, 'border-left-color': ev.cor })
                    .append($('<i class="fas">').addClass(ev.icone).css('color', ev.cor))
                    .append($('<span>').text((ev.hora ? ev.hora.split(' às')[0] + '  ' : '') + ev.titulo))
                    .appendTo(lista);
            });
            $modal.find('.evt-detalhe').hide();
            lista.show();
            $modal.find('.evt-rodape').hide();
            $modal.modal('show');
        }

        $('.agenda-cal').on('click', 'a.cal-evt', function (e) {
            e.preventDefault();
            ultimoDia = null;
            abrirEvento($(this).data('evt'), false);
        });
        $('.agenda-cal').on('click', 'a.cal-mais', function (e) {
            e.preventDefault();
            abrirDia($(this).data('dia'), String($(this).data('eventos')).split(','));
        });
        $modal.on('click', '.dia-evt', function (e) {
            e.preventDefault();
            abrirEvento($(this).data('evt'), true);
        });
        $modal.on('click', '.evt-voltar', function () {
            if (ultimoDia) abrirDia(ultimoDia.rotulo, ultimoDia.ids);
        });
    });
</script>
@stop

@push('css')
<style>
    .ag-legenda { font-size: .75rem; color: #6c757d; white-space: nowrap; }

    .agenda-cal {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: #dee2e6;
        border: 1px solid #dee2e6;
        border-radius: .4rem;
        overflow: hidden;
    }
    .cal-head {
        background: #f1f3f5;
        text-align: center;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #6c757d;
        padding: .45rem .25rem;
    }
    .cal-cell { background: #fff; min-height: 104px; padding: .3rem .35rem; }
    .cal-cell.cal-fora { background: #f8f9fa; }
    .cal-cell.cal-fora .cal-num { color: #c3c8cd; }
    .cal-cell.cal-hoje { background: #f0f7ff; box-shadow: inset 0 0 0 2px #007bff; }
    .cal-cell.cal-com-aula { background: #d4edda; }
    .cal-cell.cal-sem-aula { background: #f8d7da; }
    .cal-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        font-size: .78rem;
        font-weight: 700;
        color: #495057;
    }
    .cal-num.hoje { background: #007bff; color: #fff; }
    .cal-evt {
        display: block;
        margin-top: 2px;
        padding: 1px 5px;
        font-size: 11px;
        line-height: 1.45;
        border-radius: 3px;
        border-left: 3px solid transparent;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cal-evt:hover { filter: brightness(.94); text-decoration: none; }
    .cal-feriado {
        margin-top: 2px;
        padding: 1px 5px;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.45;
        border-radius: 3px;
        background: #eceef0;
        border-left: 3px solid #6c757d;
        color: #495057;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cal-mais { display: block; margin-top: 2px; padding-left: 5px; font-size: 10px; color: #6c757d; }

    .ag-dia { display: flex; gap: .9rem; padding: .75rem 0; border-bottom: 1px dashed #e3e6ea; }
    .ag-dia:last-child { border-bottom: 0; }
    .ag-data { flex: 0 0 58px; text-align: center; align-self: flex-start; }
    .ag-num { display: block; font-size: 1.45rem; font-weight: 800; line-height: 1; color: #343a40; }
    .ag-mes { display: block; font-size: .7rem; font-weight: 700; text-transform: uppercase; color: #007bff; }
    .ag-sem { display: block; font-size: .68rem; color: #6c757d; }
    .ag-data.ag-hoje .ag-num { color: #007bff; }

    .ag-lista { flex: 1; min-width: 0; }
    .ag-evento {
        background: #fff;
        border: 1px solid #e9ecef;
        border-left: 4px solid transparent;
        border-radius: .3rem;
        padding: .55rem .7rem;
        margin-bottom: .45rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        scroll-margin-top: 16px;
    }
    .ag-evento:last-child { margin-bottom: 0; }
    .ag-hora { font-size: .78rem; font-weight: 700; color: #495057; white-space: nowrap; }
    .ag-evento:target { animation: ag-flash 1.8s ease; }
    @keyframes ag-flash {
        0%, 40% { background: #fff8d6; border-color: #ffc107; }
        100% { background: #fff; }
    }

    .evt-modal { --evt-cor: #007bff; --evt-fundo: #e3f0ff; border: 0; border-radius: .9rem; overflow: hidden; box-shadow: 0 12px 40px rgba(0, 0, 0, .22); }
    .evt-topo { position: relative; background: var(--evt-fundo); padding: 1.35rem 1.35rem 1.05rem; transition: background .2s; }
    .evt-icone {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: var(--evt-cor);
        color: #fff;
        font-size: 1.35rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, .18);
        margin-bottom: .65rem;
    }
    .evt-badge { font-size: .68rem; letter-spacing: .06em; text-transform: uppercase; padding: .35em .7em; color: #fff; }
    .evt-titulo { margin: .45rem 0 0; font-weight: 800; color: #212529; line-height: 1.25; padding-right: 1.5rem; }
    .evt-fechar {
        position: absolute;
        top: .7rem;
        right: .9rem;
        border: 0;
        background: rgba(255, 255, 255, .55);
        width: 30px;
        height: 30px;
        border-radius: 50%;
        font-size: 1.25rem;
        line-height: 1;
        color: #343a40;
    }
    .evt-fechar:hover { background: rgba(255, 255, 255, .85); }
    .evt-corpo { padding: 1.1rem 1.35rem 1.25rem; }
    .evt-linha { display: flex; align-items: baseline; gap: .6rem; padding: .3rem 0; font-size: .9rem; color: #343a40; }
    .evt-linha i { width: 16px; text-align: center; color: var(--evt-cor); flex: none; }
    .evt-desc { margin-top: .8rem; background: #f6f8fa; border: 1px solid #edf0f3; border-radius: .5rem; padding: .7rem .85rem; font-size: .86rem; color: #495057; white-space: pre-line; }
    .evt-rodape { padding: .7rem 1.35rem; border-top: 1px solid #eef1f4; }
    .dia-evt {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .55rem .75rem;
        border-radius: .45rem;
        border-left: 4px solid transparent;
        margin-bottom: .45rem;
        font-size: .88rem;
        font-weight: 600;
        color: #343a40;
    }
    .dia-evt:last-child { margin-bottom: 0; }
    .dia-evt:hover { filter: brightness(.95); text-decoration: none; color: #212529; }
    .dia-evt i { width: 15px; text-align: center; flex: none; }
    .dia-evt span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    @media (max-width: 575.98px) {
        .cal-cell { min-height: 62px; }
        .cal-evt { font-size: 10px; }
        .ag-num { font-size: 1.15rem; }
    }
</style>
@endpush
