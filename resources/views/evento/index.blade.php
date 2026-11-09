@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Calendário de eventos e compromissos da escola" :create-url="auth()->user()->hasPermissao('evento.create') ? route('evento.create') : null" :create-can="'evento.create'" />
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
    $tipoCores = ['reuniao' => 'primary', 'evento' => 'info', 'formatura' => 'purple', 'feriado' => 'secondary', 'avaliacao' => 'warning', 'prazo' => 'danger', 'outro' => 'dark'];
    $statusCores = ['agendado' => 'info', 'realizado' => 'success', 'cancelado' => 'danger'];
    $hora = fn ($e, $campo) => $e->{$campo} ? \Carbon\Carbon::parse($e->{$campo})->format('H:i') : null;
    $dataLonga = fn ($d) => ucfirst($d->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y'));
    $podeEditar = auth()->user()->can('evento.edit');
    $eventosJson = $eventos->keyBy('id')->map(function ($e) use ($estilo, $icone, $hora, $dataLonga, $statusCores, $podeEditar) {
        $fim = $e->periodoFim();
        return [
            'titulo' => $e->titulo,
            'tipo' => $e->tipoLabel(),
            'status' => $e->statusLabel(),
            'statusCor' => $statusCores[$e->status] ?? 'secondary',
            'cancelado' => $e->status === 'cancelado',
            'icone' => $icone($e->tipo),
            'cor' => $estilo($e->tipo)['cor'],
            'fundo' => $estilo($e->tipo)['fundo'],
            'data' => $dataLonga($e->data_inicio),
            'ate' => $fim->gt($e->data_inicio) ? $dataLonga($fim) : null,
            'hora' => $e->hora_inicio
                ? $hora($e, 'hora_inicio').($e->hora_fim ? ' às '.$hora($e, 'hora_fim') : '')
                : null,
            'escola' => $e->escola->nome ?? 'Rede toda',
            'local' => $e->local,
            'descricao' => $e->descricao,
            'turmas' => $e->turmas->pluck('nome')->implode(', '),
            'publico' => \App\Models\Evento::PUBLICOS[$e->publico] ?? $e->publico,
            'suspende' => $e->suspende_aulas === 'S',
            'editUrl' => $podeEditar ? route('evento.edit', $e->id) : null,
        ];
    });
    $porDia = $eventos->groupBy(fn ($e) => $e->data_inicio->format('Y-m-d'));
@endphp

<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="btn-group">
            <a href="{{ route('evento.index', ['mes' => $mesAnterior]) }}" class="btn btn-sm btn-outline-secondary ag-nav-mes"><i class="fa fa-chevron-left"></i></a>
            <a href="{{ route('evento.index', ['mes' => $mesAtual]) }}" class="btn btn-sm btn-outline-secondary ag-nav-mes {{ $ehMesAtual ? 'disabled' : '' }}">Hoje</a>
            <a href="{{ route('evento.index', ['mes' => $mesProximo]) }}" class="btn btn-sm btn-outline-secondary ag-nav-mes"><i class="fa fa-chevron-right"></i></a>
        </div>
        <h5 class="mb-0 font-weight-bold">{{ $mesCarbon->locale('pt_BR')->translatedFormat('F \d\e Y') }}</h5>
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
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
                    <div class="cal-cell {{ $dia['do_mes'] ? '' : 'cal-fora' }} {{ $dia['data']->isToday() ? 'cal-hoje' : '' }}">
                        <div class="cal-num {{ $dia['data']->isToday() ? 'hoje' : '' }}">{{ $dia['data']->day }}</div>
                        @foreach($dia['eventos']->take(3) as $evento)
                            @php $est = $estilo($evento->tipo); @endphp
                            <a href="#" class="cal-evt {{ $evento->status === 'cancelado' ? 'cal-cancelado' : '' }}" data-evt="{{ $evento->id }}"
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
    <div class="card-body">
        @forelse($porDia as $dia => $eventosDoDia)
            @php $dataDia = \Carbon\Carbon::parse($dia); @endphp
            <div class="d-flex align-items-center mb-2 mt-3">
                <span class="badge badge-dark p-2">{{ $dataDia->format('d/m/Y') }}</span>
                <span class="ml-2 text-muted small">{{ $dataDia->locale('pt_BR')->translatedFormat('l') }}</span>
                @if($dataDia->isToday())
                    <span class="badge badge-success ml-2">Hoje</span>
                @endif
            </div>
            <div class="list-group mb-3">
                @foreach($eventosDoDia as $evento)
                    <div class="list-group-item d-flex align-items-start flex-wrap" style="gap:.5rem;">
                        <div class="flex-grow-1">
                            <div>
                                <strong>{{ $evento->titulo }}</strong>
                                <span class="badge badge-{{ $tipoCores[$evento->tipo] ?? 'secondary' }} ml-1">{{ $evento->tipoLabel() }}</span>
                                <span class="badge badge-{{ $statusCores[$evento->status] ?? 'secondary' }} ml-1">{{ $evento->statusLabel() }}</span>
                                @if($evento->publico === 'todos')
                                    <span class="badge badge-outline-primary ml-1" title="Visível para as famílias"><i class="fa fa-users"></i> famílias</span>
                                @endif
                                @if($evento->suspende_aulas === 'S')
                                    <span class="badge badge-danger ml-1" title="Suspende as aulas do dia"><i class="fa fa-ban"></i> sem aula</span>
                                @endif
                                @if($evento->turmas->isNotEmpty())
                                    <span class="badge badge-info ml-1" title="{{ $evento->turmas->pluck('nome')->implode(', ') }}"><i class="fa fa-chalkboard-teacher"></i> {{ $evento->turmas->count() }} turma(s)</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                @if($evento->hora_inicio)
                                    {{ \Carbon\Carbon::parse($evento->hora_inicio)->format('H:i') }}
                                    @if($evento->hora_fim) às {{ \Carbon\Carbon::parse($evento->hora_fim)->format('H:i') }}@endif
                                    ·
                                @endif
                                {{ $evento->escola->nome ?? 'Rede toda' }}
                                @if($evento->local) · {{ $evento->local }}@endif
                                @if($evento->data_fim && !$evento->data_fim->eq($evento->data_inicio))
                                    · até {{ $evento->data_fim->format('d/m/Y') }}
                                @endif
                            </small>
                            @if($evento->descricao)
                                <div class="small mt-1">{{ \Illuminate\Support\Str::limit($evento->descricao, 200) }}</div>
                            @endif
                        </div>
                        <div class="text-right">
                            @can('evento.edit')
                                <a href="{{ route('evento.edit', $evento->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                            @endcan
                            @can('evento.destroy')
                                <x-delete-button :action="route('evento.destroy', $evento->id)" titulo="Excluir" mensagem="Excluir o evento &quot;{{ $evento->titulo }}&quot;?" />
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-alt fa-3x mb-3 d-block"></i>
                Nenhum evento neste mês{{ count($itensFiltered ?? []) ? ' com os filtros aplicados' : '' }}.
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
                <span class="badge evt-badge-status"></span>
                <h5 class="evt-titulo"></h5>
                <div class="evt-cancelado-banner" style="display:none;"><i class="fas fa-ban"></i> Evento cancelado</div>
            </div>
            <div class="modal-body evt-corpo">
                <div class="evt-detalhe">
                    <div class="evt-linha m-data"><i class="far fa-calendar-alt"></i><span></span></div>
                    <div class="evt-linha m-ate"><i class="fas fa-arrows-alt-h"></i><span></span></div>
                    <div class="evt-linha m-hora"><i class="far fa-clock"></i><span></span></div>
                    <div class="evt-linha m-escola"><i class="fas fa-school"></i><span></span></div>
                    <div class="evt-linha m-local"><i class="fas fa-map-marker-alt"></i><span></span></div>
                    <div class="evt-linha m-turmas"><i class="fas fa-chalkboard-teacher"></i><span></span></div>
                    <div class="evt-linha m-publico"><i class="fas fa-users"></i><span></span></div>
                    <div class="evt-linha m-suspende"><i class="fas fa-ban"></i><span class="text-danger font-weight-bold">Suspende as aulas</span></div>
                    <div class="evt-desc"></div>
                </div>
                <div class="evt-dia-lista"></div>
            </div>
            <div class="modal-footer evt-rodape">
                <button type="button" class="btn btn-sm btn-outline-secondary evt-voltar">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar ao dia
                </button>
                <a href="#" class="btn btn-sm btn-warning evt-editar" style="display:none;"><i class="fas fa-edit mr-1"></i> Editar</a>
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
            link = $('#agendaTabs a[href="#tab-calendario"]');
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
            $modal.find(seletor).toggle(!!texto).find('span').first().text(texto || '');
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
            $modal.find('.evt-badge-status')
                .attr('class', 'badge evt-badge-status badge-' + ev.statusCor)
                .text(ev.status);
            $modal.find('.evt-modal').toggleClass('evt-cancelado', !!ev.cancelado);
            $modal.find('.evt-cancelado-banner').toggle(!!ev.cancelado);
            $modal.find('.evt-titulo').text(ev.titulo);
            linha('.m-data', ev.data);
            linha('.m-ate', ev.ate ? 'até ' + ev.ate : null);
            linha('.m-hora', ev.hora);
            linha('.m-escola', ev.escola);
            linha('.m-local', ev.local);
            linha('.m-turmas', ev.turmas || null);
            linha('.m-publico', ev.publico || null);
            $modal.find('.m-suspende').toggle(!!ev.suspende);
            $modal.find('.evt-desc').toggle(!!ev.descricao).text(ev.descricao || '');
            $modal.find('.evt-detalhe').show();
            $modal.find('.evt-dia-lista').hide().empty();
            $modal.find('.evt-voltar').toggle(!!voltar);
            $modal.find('.evt-editar').toggle(!!ev.editUrl).attr('href', ev.editUrl || '#');
            $modal.find('.evt-rodape').toggle(!!voltar || !!ev.editUrl);
            if (!$modal.hasClass('show')) $modal.modal('show');
        }

        function abrirDia(rotulo, ids) {
            ultimoDia = { rotulo: rotulo, ids: ids };
            pintar('#007bff', '#e3f0ff');
            $modal.find('.evt-icone i').attr('class', 'fas fa-calendar-day');
            $modal.find('.evt-badge').text(ids.length + (ids.length > 1 ? ' eventos' : ' evento')).css('background', '#007bff');
            $modal.find('.evt-badge-status').attr('class', 'badge evt-badge-status badge-light border').text('');
            $modal.find('.evt-titulo').text(rotulo);
            var lista = $modal.find('.evt-dia-lista').empty();
            ids.forEach(function (id) {
                var ev = EVENTOS[id];
                if (!ev) return;
                $('<a href="#" class="dia-evt">')
                    .attr('data-evt', id)
                    .css({ background: ev.fundo, 'border-left-color': ev.cor, opacity: ev.cancelado ? .55 : 1 })
                    .append($('<i class="fas">').addClass(ev.icone).css('color', ev.cor))
                    .append($('<span>').text((ev.hora ? ev.hora.split(' às')[0] + '  ' : '') + ev.titulo + (ev.cancelado ? ' (cancelado)' : '')))
                    .appendTo(lista);
            });
            $modal.find('.evt-detalhe').hide();
            $modal.find('.evt-editar').hide();
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
    .ag-nav-mes.disabled { pointer-events: none; opacity: .65; }

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
    .cal-evt.cal-cancelado {
        opacity: .6;
        text-decoration: line-through;
        border: 1px dashed #ef4444;
        background: repeating-linear-gradient(-45deg, #fef2f2, #fef2f2 6px, #fee2e2 6px, #fee2e2 12px) !important;
    }
    .cal-evt.cal-cancelado::after { content: ' ✕'; color: #dc2626; font-weight: 900; }

    .evt-cancelado-banner {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        background: #dc2626; color: #fff; font-size: .8rem; font-weight: 800;
        letter-spacing: .08em; text-transform: uppercase;
        padding: 6px 10px; margin: 8px -10px 0;
    }
    .evt-modal.evt-cancelado .evt-titulo { text-decoration: line-through; color: #6b7280; }
    .evt-modal.evt-cancelado .evt-corpo { opacity: .75; }
    .cal-mais { display: block; margin-top: 2px; padding-left: 5px; font-size: 10px; color: #6c757d; }

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
    .evt-badge, .evt-badge-status { font-size: .68rem; letter-spacing: .06em; text-transform: uppercase; padding: .35em .7em; color: #fff; margin-right: .3rem; }
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
    }
</style>
@endpush
