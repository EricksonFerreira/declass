@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Feriados" subtitle="Dias sem aula ou com registro especial — alimentam o calendário letivo"
        :create-url="auth()->user()->hasPermissao('feriado.create') ? route('feriado.create') : null" :create-can="'feriado.create'" />
@stop

@push('css')
    @include('partials.ux-index-styles')
    <style>
        .fer-index[data-view="calendario"] .ux-cards, .fer-index[data-view="calendario"] .ux-table { display: none; }
        .fer-index[data-view="cards"] .ux-cal, .fer-index[data-view="cards"] .ux-table { display: none; }
        .fer-index[data-view="table"] .ux-cards, .fer-index[data-view="table"] .ux-cal { display: none; }

        .cal-meses { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px; padding: 10px 8px 14px; }
        .cal-mes { border: 1px solid #e2e9f4; border-radius: 12px; overflow: hidden; background: #fff; }
        .cal-mes-tit { font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #475569; background: #f8fafc; padding: 7px 10px; border-bottom: 1px solid #eef2f7; }
        .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; padding: 8px; }
        .cal-wd { font-size: .6rem; font-weight: 700; color: #94a3b8; text-align: center; }
        .cal-d { font-size: .7rem; text-align: center; padding: 3px 0; border-radius: 5px; color: #64748b; }
        .cal-d.sus { background: #fee2e2; color: #b91c1c; font-weight: 700; cursor: help; }
        .cal-d.aula { background: #dbeafe; color: #1d4ed8; font-weight: 700; cursor: help; }
        .cal-d.hoje { outline: 2px solid #2563eb; outline-offset: -1px; }
        .cal-legend { display: flex; gap: 14px; padding: 0 12px 10px; font-size: .7rem; color: #64748b; }
        .cal-legend span i { margin-right: 4px; }

        .fer-cards.ux-cards { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 10px; padding: 8px 6px 12px; }
        .fer-cards .ux-card { border-radius: 12px; box-shadow: 0 1px 4px rgba(11,18,32,.05); }
        .fer-cards .ux-card:hover { transform: none; box-shadow: 0 4px 14px rgba(11,18,32,.10); }
        .fer-cards .ux-card__accent { display: none; }
        .fer-cards .ux-card__head { padding: 9px 11px 7px; gap: 8px; }
        .fer-cards .ux-card__avatar { width: 32px; height: 32px; border-radius: 8px; font-size: .68rem; }
        .fer-cards .ux-card__avatar--passado { background: #e2e8f0; }
        .fer-cards .ux-card__title { font-size: .8rem; white-space: normal; }
        .fer-cards .ux-card__sub { font-size: .68rem; }
        .fer-cards .ux-card__badges { flex-direction: row; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .fer-cards .ux-card__badge { font-size: .6rem; padding: 3px 6px; }
        .fer-cards .ux-card__meta { padding: 7px 11px 5px; gap: 5px 10px; }
        .fer-cards .ux-card__meta-label { font-size: .6rem; }
        .fer-cards .ux-card__meta-value { font-size: .74rem; }
        .fer-cards .ux-card__foot { padding: 7px 11px 9px; }
        .fer-cards .ux-card__foot .btn { padding: .15rem .45rem; font-size: .72rem; }
        .fer-cards .ux-card--muted { opacity: .6; }
    </style>
@endpush

@section('content')
@php
    // mapa dia => feriados que cobrem o dia (para a visao de calendario)
    $porDia = [];
    foreach ($feriados as $f) {
        $fim = $f->periodo_fim->copy();
        for ($d = $f->data_inicio->copy(); $d->lte($fim); $d->addDay()) {
            $porDia[$d->format('Y-m-d')][] = $f;
        }
    }
    $meses = collect(array_keys($porDia))->map(fn ($d) => substr($d, 0, 7))->unique()->sort()->values();
    $hoje = now()->toDateString();
@endphp
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <div class="ux-view-toggle" id="ferViewToggle">
            <button type="button" class="btn btn-xs btn-outline-secondary active" data-view="calendario"><i class="far fa-calendar-alt mr-1"></i>Calendário</button>
            <button type="button" class="btn btn-xs btn-outline-secondary" data-view="cards"><i class="fas fa-th-large mr-1"></i>Cards</button>
            <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table"><i class="fas fa-table mr-1"></i>Lista</button>
        </div>
    </div>

    <div class="card-body p-0 ux-index-body fer-index" data-view="calendario">
        {{-- Visao calendario --}}
        <div class="ux-cal">
            @if($meses->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="far fa-calendar-times fa-2x mb-2 d-block"></i>
                    Nenhum feriado cadastrado.
                </div>
            @else
                <div class="cal-legend pt-3">
                    <span><i class="fa fa-square" style="color:#fecaca;"></i>Suspende aulas</span>
                    <span><i class="fa fa-square" style="color:#bfdbfe;"></i>Aula normal</span>
                </div>
                <div class="cal-meses">
                    @foreach($meses as $ym)
                        @php
                            $mes = \Carbon\Carbon::createFromFormat('Y-m', $ym)->startOfMonth();
                            $offset = $mes->dayOfWeek;
                        @endphp
                        <div class="cal-mes">
                            <div class="cal-mes-tit">{{ ucfirst($mes->translatedFormat('F')) }} {{ $mes->format('Y') }}</div>
                            <div class="cal-grid">
                                @foreach(['D','S','T','Q','Q','S','S'] as $wd)<span class="cal-wd">{{ $wd }}</span>@endforeach
                                @for($i = 0; $i < $offset; $i++)<span></span>@endfor
                                @for($dia = 1; $dia <= $mes->daysInMonth; $dia++)
                                    @php($key = $mes->format('Y-m-').str_pad($dia, 2, '0', STR_PAD_LEFT))
                                    @php($fs = $porDia[$key] ?? [])
                                    @php($cls = $fs ? (collect($fs)->contains(fn ($f) => $f->suspende_aulas === 'S') ? 'sus' : 'aula') : '')
                                    <span class="cal-d {{ $cls }} {{ $key === $hoje ? 'hoje' : '' }}"
                                        @if($fs) title="{{ collect($fs)->map(fn ($f) => $f->nome)->implode(' · ') }}" @endif>{{ $dia }}</span>
                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Visao cards --}}
        <div class="ux-cards fer-cards">
            @forelse($feriados as $f)
                @php($passado = $f->periodo_fim->isPast() && $f->periodo_fim->toDateString() !== $hoje)
                <article class="ux-card {{ $passado ? 'ux-card--muted' : '' }}">
                    <div class="ux-card__accent"></div>
                    <div class="ux-card__head">
                        <div class="ux-card__avatar {{ $passado ? 'ux-card__avatar--passado' : '' }}">{{ $f->data_inicio->format('d') }}</div>
                        <div class="ux-card__head-info">
                            <div class="ux-card__title">{{ $f->nome }}</div>
                            <div class="ux-card__sub">{{ $f->escola->nome ?? 'Toda a rede' }}</div>
                        </div>
                        <div class="ux-card__badges">
                            @if($f->suspende_aulas === 'S')
                                <span class="ux-card__badge ux-card__badge--red">Suspende aulas</span>
                            @else
                                <span class="ux-card__badge ux-card__badge--blue">Aula normal</span>
                            @endif
                        </div>
                    </div>
                    <div class="ux-card__meta">
                        <div class="ux-card__meta-row ux-card__meta-row--full">
                            <span class="ux-card__meta-label">Período</span>
                            <span class="ux-card__meta-value">
                                {{ $f->data_inicio->format('d/m/Y') }}
                                @if($f->data_fim) — {{ $f->data_fim->format('d/m/Y') }} @endif
                            </span>
                        </div>
                    </div>
                    <div class="ux-card__foot">
                        @can('feriado.edit')
                            <a href="{{ route('feriado.edit', $f->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                        @endcan
                        @can('feriado.destroy')
                            <x-delete-button :action="route('feriado.destroy', $f->id)" titulo="Excluir" mensagem="Excluir o feriado &quot;{{ $f->nome }}&quot;?" />
                        @endcan
                    </div>
                </article>
            @empty
                <div class="text-center text-muted py-4" style="grid-column:1/-1;">
                    <i class="far fa-calendar-times fa-2x mb-2 d-block"></i>
                    Nenhum feriado cadastrado.
                </div>
            @endforelse
        </div>

        {{-- Visao lista (tabela) --}}
        <div class="ux-table table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Data</th>
                        <th>Nome</th>
                        <th>Escola</th>
                        <th>Aulas</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feriados as $f)
                        <tr class="{{ $f->data_inicio->isPast() ? 'text-muted' : '' }}">
                            <td class="text-nowrap">
                                {{ $f->data_inicio->format('d/m/Y') }}
                                @if($f->data_fim) — {{ $f->data_fim->format('d/m/Y') }} @endif
                            </td>
                            <td class="font-weight-bold">{{ $f->nome }}</td>
                            <td>{{ $f->escola->nome ?? 'Toda a rede' }}</td>
                            <td>
                                @if($f->suspende_aulas === 'S')
                                    <span class="badge badge-danger">Suspensas</span>
                                @else
                                    <span class="badge badge-secondary">Aula normal</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @can('feriado.edit')
                                    <a href="{{ route('feriado.edit', $f->id) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('feriado.destroy')
                                    <x-delete-button :action="route('feriado.destroy', $f->id)" titulo="Excluir" mensagem="Excluir o feriado &quot;{{ $f->nome }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Nenhum feriado cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
$(function () {
    var body = document.querySelector('.fer-index');
    var btns = document.querySelectorAll('#ferViewToggle [data-view]');
    function setView(view) {
        if (!body) return;
        body.dataset.view = view;
        btns.forEach(btn => btn.classList.toggle('active', btn.dataset.view === view));
        localStorage.setItem('feriado_index_view', view);
    }
    btns.forEach(btn => btn.addEventListener('click', () => setView(btn.dataset.view)));
    setView(localStorage.getItem('feriado_index_view') || 'calendario');
});
</script>
@endpush
