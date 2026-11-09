@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Pesquisas de satisfação e avaliações com as famílias" :create-url="auth()->user()->hasPermissao('pesquisa.create') ? route('pesquisa.create') : null" :create-can="'pesquisa.create'" />
@stop

@push('css')
<style>
    .pq-card { border:1px solid #eef1f6; border-radius:10px; padding:.75rem .9rem; background:#fff; height:100%; display:flex; flex-direction:column; cursor:pointer; transition:box-shadow .12s, transform .12s; }
    .pq-card:hover { box-shadow:0 4px 14px rgba(37,99,235,.10); transform:translateY(-1px); }
    .pq-meta { font-size:.74rem; color:#6b7280; }
    tr.pq-click { cursor:pointer; }
    #pqModal .modal-header { background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; border-radius:.3rem .3rem 0 0; padding:.6rem 1rem; }
    #pqModal .close { color:#fff; opacity:.85; }
    .pq-sec { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2563eb; margin:.8rem 0 .3rem; }
    .pq-txt { font-size:.84rem; color:#374151; white-space:pre-line; background:#f8fafc; border:1px solid #eef1f6; border-radius:8px; padding:.5rem .7rem; }
    .pq-info-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:.4rem; }
    .pq-info { border:1px solid #eef1f6; border-radius:8px; padding:.4rem .6rem; }
    .pq-info label { display:block; font-size:.62rem; font-weight:700; text-transform:uppercase; color:#9ca3af; margin:0; }
    .pq-info span { font-size:.84rem; font-weight:600; color:#1f2937; }
    .pq-cal { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
    .pq-cal-dow { font-size:.65rem; font-weight:700; text-transform:uppercase; color:#9ca3af; text-align:center; padding:.2rem 0; }
    .pq-cal-day { min-height:88px; border:1px solid #eef1f6; border-radius:8px; padding:.25rem .3rem; background:#fff; font-size:.72rem; }
    .pq-cal-day.vazio { background:#fafbfc; }
    .pq-cal-day .num { font-weight:700; color:#9ca3af; font-size:.7rem; }
    .pq-cal-day.hoje .num { background:#2563eb; color:#fff; border-radius:50%; padding:0 .35rem; }
    .pq-ev { display:block; border-radius:4px; padding:1px 4px; margin-top:2px; cursor:pointer; font-size:.68rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .pq-ev-aberta { background:#d1fae5; color:#065f46; }
    .pq-ev-rascunho { background:#e5e7eb; color:#4b5563; }
    .pq-ev-encerrada { background:#d1d5db; color:#374151; }
    .pq-legenda { font-size:.7rem; color:#6b7280; }
    .pq-legenda span { display:inline-block; width:10px; height:10px; border-radius:2px; margin-right:3px; }
</style>
@endpush

@section('content')
@php $statusCores = ['rascunho' => 'secondary', 'aberta' => 'success', 'encerrada' => 'dark']; @endphp
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <div class="btn-group btn-group-sm" role="group" aria-label="Modo de visualização">
            <button type="button" class="btn btn-outline-primary active" id="pq-btn-lista" title="Lista"><i class="fa fa-list"></i></button>
            <button type="button" class="btn btn-outline-primary" id="pq-btn-cards" title="Cards"><i class="fa fa-th-large"></i></button>
            <button type="button" class="btn btn-outline-primary" id="pq-btn-cal" title="Calendário"><i class="fa fa-calendar-alt"></i></button>
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0" id="pq-lista">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Título</th>
                        <th>Escola</th>
                        <th>Período</th>
                        <th>Status</th>
                        <th>Perguntas</th>
                        <th>Respostas</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pesquisas as $pesquisa)
                        <tr class="pq-click" data-id="{{ $pesquisa->id }}" title="Ver detalhes">
                            <td>
                                <strong>{{ $pesquisa->titulo }}</strong>
                                @if($pesquisa->descricao)
                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($pesquisa->descricao, 90) }}</small>
                                @endif
                            </td>
                            <td>{{ $pesquisa->escola->nome ?? 'Rede toda' }}</td>
                            <td class="text-nowrap">
                                <small>{{ $pesquisa->data_inicio->format('d/m/Y') }} a {{ $pesquisa->data_fim->format('d/m/Y') }}</small>
                                @if($pesquisa->estaAberta())
                                    <br><span class="badge badge-success">Aberta agora</span>
                                @endif
                            </td>
                            <td><span class="badge badge-{{ $statusCores[$pesquisa->status] ?? 'secondary' }}">{{ $pesquisa->statusLabel() }}</span></td>
                            <td>{{ $pesquisa->perguntas->count() }}</td>
                            <td>{{ $pesquisa->respostas->count() }}</td>
                            <td class="text-right text-nowrap" onclick="event.stopPropagation()">
                                <a href="{{ route('pesquisa.resultados', $pesquisa->id) }}" class="btn btn-xs btn-info" title="Resultados"><i class="fa fa-chart-bar"></i></a>
                                @can('pesquisa.edit')
                                    <a href="{{ route('pesquisa.edit', $pesquisa->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('pesquisa.destroy')
                                    <x-delete-button :action="route('pesquisa.destroy', $pesquisa->id)" titulo="Excluir" mensagem="Excluir a pesquisa &quot;{{ $pesquisa->titulo }}&quot; e todas as respostas?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-question fa-2x mb-2 d-block"></i>
                                Nenhuma pesquisa encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-body" id="pq-cards" style="display:none;">
        <div class="row">
            @forelse($pesquisas as $pesquisa)
                <div class="col-md-6 col-xl-4 mb-3">
                    <div class="pq-card" data-id="{{ $pesquisa->id }}" title="Ver detalhes">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <strong class="small">{{ \Illuminate\Support\Str::limit($pesquisa->titulo, 60) }}</strong>
                            <span class="badge badge-{{ $statusCores[$pesquisa->status] ?? 'secondary' }} ml-1">{{ $pesquisa->statusLabel() }}</span>
                        </div>
                        @if($pesquisa->descricao)
                            <div class="pq-meta mb-1">{{ \Illuminate\Support\Str::limit($pesquisa->descricao, 100) }}</div>
                        @endif
                        <div class="pq-meta">
                            <i class="fa fa-calendar mr-1"></i>{{ $pesquisa->data_inicio->format('d/m/Y') }} a {{ $pesquisa->data_fim->format('d/m/Y') }}
                            @if($pesquisa->estaAberta()) <span class="badge badge-success">Aberta agora</span>@endif<br>
                            <i class="fa fa-school mr-1"></i>{{ $pesquisa->escola->nome ?? 'Rede toda' }}
                        </div>
                        <div class="mt-1">
                            <span class="badge badge-light border"><i class="fa fa-question mr-1"></i>{{ $pesquisa->perguntas->count() }} perguntas</span>
                            <span class="badge badge-light border"><i class="fa fa-reply mr-1"></i>{{ $pesquisa->respostas->count() }} respostas</span>
                        </div>
                        <div class="mt-auto pt-2 d-flex" style="gap:.35rem;" onclick="event.stopPropagation()">
                            <a href="{{ route('pesquisa.resultados', $pesquisa->id) }}" class="btn btn-xs btn-info" title="Resultados"><i class="fa fa-chart-bar"></i></a>
                            @can('pesquisa.edit')
                                <a href="{{ route('pesquisa.edit', $pesquisa->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                            @endcan
                            @can('pesquisa.destroy')
                                <x-delete-button :action="route('pesquisa.destroy', $pesquisa->id)" titulo="Excluir" mensagem="Excluir a pesquisa &quot;{{ $pesquisa->titulo }}&quot; e todas as respostas?" />
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted text-center py-4">Nenhuma pesquisa encontrada.</div>
            @endforelse
        </div>
    </div>

    {{-- CALENDARIO --}}
    <div class="card-body" id="pq-cal" style="display:none;">
        @php
            $ref = $pesquisas->min('data_inicio') ?? now();
            $inicio = $ref->copy()->startOfMonth()->startOfWeek();
            $fim = $ref->copy()->endOfMonth()->endOfWeek();
        @endphp
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="font-weight-bold text-capitalize">{{ $ref->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }}</div>
            <div class="pq-legenda">
                <span style="background:#d1fae5;"></span>Aberta
                <span style="background:#e5e7eb; margin-left:.5rem;"></span>Rascunho
                <span style="background:#d1d5db; margin-left:.5rem;"></span>Encerrada
            </div>
        </div>
        <div class="pq-cal">
            @foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $dow)
                <div class="pq-cal-dow">{{ $dow }}</div>
            @endforeach
            @for($d = $inicio->copy(); $d->lte($fim); $d->addDay())
                <div class="pq-cal-day {{ $d->isToday() ? 'hoje' : '' }} {{ $d->month !== $ref->month ? 'vazio' : '' }}">
                    <span class="num">{{ $d->day }}</span>
                    @foreach($pesquisas as $pv)
                        @if($pv->data_inicio->lte($d) && $pv->data_fim->gte($d))
                            <span class="pq-ev pq-ev-{{ $pv->status }}" data-id="{{ $pv->id }}" title="{{ $pv->titulo }} ({{ $pv->data_inicio->format('d/m') }} a {{ $pv->data_fim->format('d/m') }})">
                                {{ $pv->data_inicio->isSameDay($d) ? '▸ ' : '' }}{{ \Illuminate\Support\Str::limit($pv->titulo, 18) }}
                            </span>
                        @endif
                    @endforeach
                </div>
            @endfor
        </div>
    </div>

    @if($pesquisas->hasPages())
        <div class="card-footer">
            {{ $pesquisas->links() }}
        </div>
    @endif
</div>

{{-- Modal de detalhes --}}
<div class="modal fade" id="pqModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="pm-titulo"></h5>
                    <span id="pm-badges"></span>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="pq-info-grid" id="pm-info"></div>
                <div id="pm-secoes"></div>
            </div>
        </div>
    </div>
</div>

@php
    $pqJson = $pesquisas->keyBy('id')->map(function ($p) use ($statusCores) {
        return [
            'titulo' => $p->titulo,
            'status' => $p->statusLabel(),
            'status_cor' => $statusCores[$p->status] ?? 'secondary',
            'aberta' => (bool) $p->estaAberta(),
            'escola' => $p->escola->nome ?? 'Rede toda',
            'inicio' => $p->data_inicio->format('d/m/Y'),
            'fim' => $p->data_fim->format('d/m/Y'),
            'perguntas' => $p->perguntas->count(),
            'respostas' => $p->respostas->count(),
            'descricao' => $p->descricao,
            'resultados_url' => route('pesquisa.resultados', $p->id),
        ];
    });
@endphp
<script>
(function () {
    const PQ = @json($pqJson);

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }
    function info(label, valor) {
        return '<div class="pq-info"><label>' + label + '</label><span>' + esc(valor || '—') + '</span></div>';
    }
    function abrir(id) {
        const p = PQ[id];
        if (!p) { return; }
        document.getElementById('pm-titulo').textContent = p.titulo;
        document.getElementById('pm-badges').innerHTML =
            '<span class="badge badge-' + p.status_cor + ' mr-1">' + esc(p.status) + '</span>'
            + (p.aberta ? '<span class="badge badge-success">Aberta agora</span>' : '');
        document.getElementById('pm-info').innerHTML =
            info('Escola', p.escola) + info('Início', p.inicio) + info('Fim', p.fim)
            + info('Perguntas', p.perguntas) + info('Respostas', p.respostas);
        let html = '';
        if (p.descricao) {
            html += '<div class="pq-sec"><i class="fa fa-align-left mr-1"></i>Descrição</div>'
                + '<div class="pq-txt">' + esc(p.descricao) + '</div>';
        }
        html += '<div class="mt-3"><a href="' + p.resultados_url + '" class="btn btn-sm btn-outline-info"><i class="fa fa-chart-bar mr-1"></i>Ver resultados</a></div>';
        document.getElementById('pm-secoes').innerHTML = html;
        $('#pqModal').modal('show');
    }

    document.querySelectorAll('.pq-click, .pq-card, .pq-ev').forEach(function (el) {
        el.addEventListener('click', function (e) { e.stopPropagation(); abrir(el.dataset.id); });
    });

    // Toggle Lista/Cards/Calendario
    const KEY = 'pesquisa_view';
    function setView(v) {
        ['lista', 'cards', 'cal'].forEach(function (m) {
            document.getElementById('pq-' + m).style.display = m === v ? '' : 'none';
            document.getElementById('pq-btn-' + m).classList.toggle('active', m === v);
        });
        try { localStorage.setItem(KEY, v); } catch (e) {}
    }
    document.getElementById('pq-btn-lista').addEventListener('click', function () { setView('lista'); });
    document.getElementById('pq-btn-cards').addEventListener('click', function () { setView('cards'); });
    document.getElementById('pq-btn-cal').addEventListener('click', function () { setView('cal'); });
    try { setView(localStorage.getItem(KEY) || 'lista'); } catch (e) {}
})();
</script>
@stop
