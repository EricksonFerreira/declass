@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Dever de casa e atividades por turma" :create-url="auth()->user()->hasPermissao('tarefa.create') ? route('tarefa.create') : null" :create-can="'tarefa.create'" />
@stop

@push('css')
<style>
    .ta-card { border:1px solid #eef1f6; border-radius:10px; padding:.75rem .9rem; background:#fff; height:100%; display:flex; flex-direction:column; cursor:pointer; transition:box-shadow .12s, transform .12s; }
    .ta-card:hover { box-shadow:0 4px 14px rgba(37,99,235,.10); transform:translateY(-1px); }
    .ta-meta { font-size:.74rem; color:#6b7280; }
    tr.ta-click { cursor:pointer; }
    #taModal .modal-header { background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; border-radius:.3rem .3rem 0 0; padding:.6rem 1rem; }
    #taModal .close { color:#fff; opacity:.85; }
    .ta-sec { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2563eb; margin:.8rem 0 .3rem; }
    .ta-txt { font-size:.84rem; color:#374151; white-space:pre-line; background:#f8fafc; border:1px solid #eef1f6; border-radius:8px; padding:.5rem .7rem; }
    .ta-info-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:.4rem; }
    .ta-info { border:1px solid #eef1f6; border-radius:8px; padding:.4rem .6rem; }
    .ta-info label { display:block; font-size:.62rem; font-weight:700; text-transform:uppercase; color:#9ca3af; margin:0; }
    .ta-info span { font-size:.84rem; font-weight:600; color:#1f2937; }
    .ta-prog { height:6px; border-radius:4px; background:#e5e7eb; overflow:hidden; margin-top:3px; }
    .ta-prog > div { height:100%; background:linear-gradient(90deg,#2563eb,#10b981); }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <div class="btn-group btn-group-sm" role="group" aria-label="Modo de visualização">
            <button type="button" class="btn btn-outline-primary active" id="ta-btn-lista" title="Lista"><i class="fa fa-list"></i></button>
            <button type="button" class="btn btn-outline-primary" id="ta-btn-cards" title="Cards"><i class="fa fa-th-large"></i></button>
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0" id="ta-lista">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Título</th>
                        <th>Turma</th>
                        <th>Disciplina</th>
                        <th>Entrega</th>
                        <th>Progresso</th>
                        <th>Autor</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tarefas as $tarefa)
                        <tr class="ta-click" data-id="{{ $tarefa->id }}" title="Ver detalhes">
                            <td class="font-weight-bold" style="max-width:260px;">
                                {{ \Illuminate\Support\Str::limit($tarefa->titulo, 60) }}
                                @if($tarefa->atraso())
                                    <span class="badge badge-danger">vencida</span>
                                @endif
                            </td>
                            <td>{{ $tarefa->turma->nome ?? '—' }}</td>
                            <td>{{ $tarefa->disciplina->nome ?? '—' }}</td>
                            <td class="text-nowrap">{{ $tarefa->data_entrega->format('d/m/Y') }}</td>
                            <td class="text-center">
                                @if($tarefa->entregasTotal() > 0)
                                    {{ $tarefa->entregasEntregues() }}/{{ $tarefa->entregasTotal() }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><small>{{ $tarefa->autor->name ?? '—' }}</small></td>
                            <td class="text-right" onclick="event.stopPropagation()">
                                @can('tarefa.entregas')
                                    <a href="{{ route('tarefa.entregas', $tarefa->id) }}" class="btn btn-xs btn-info" title="Entregas"><i class="fa fa-check-double"></i></a>
                                @endcan
                                @can('tarefa.edit')
                                    <a href="{{ route('tarefa.edit', $tarefa->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('tarefa.destroy')
                                    <x-delete-button :action="route('tarefa.destroy', $tarefa->id)" titulo="Excluir" mensagem="Excluir a tarefa &quot;{{ $tarefa->titulo }}&quot;? As entregas também serão removidas." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-tasks fa-2x mb-2 d-block"></i>
                                Nenhuma tarefa cadastrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-body" id="ta-cards" style="display:none;">
        <div class="row">
            @forelse($tarefas as $tarefa)
                @php $pct = $tarefa->entregasTotal() > 0 ? round($tarefa->entregasEntregues() / $tarefa->entregasTotal() * 100) : null; @endphp
                <div class="col-md-6 col-xl-4 mb-3">
                    <div class="ta-card" data-id="{{ $tarefa->id }}" title="Ver detalhes">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <strong class="small">{{ \Illuminate\Support\Str::limit($tarefa->titulo, 70) }}</strong>
                            @if($tarefa->atraso())
                                <span class="badge badge-danger ml-1">vencida</span>
                            @endif
                        </div>
                        <div class="mb-1">
                            <span class="badge badge-light border">{{ $tarefa->turma->nome ?? '—' }}</span>
                            <span class="badge badge-light border">{{ $tarefa->disciplina->nome ?? '—' }}</span>
                        </div>
                        <div class="ta-meta">
                            <i class="fa fa-calendar mr-1"></i>Entrega {{ $tarefa->data_entrega->format('d/m/Y') }}
                            · <i class="fa fa-user mr-1"></i>{{ $tarefa->autor->name ?? '—' }}
                        </div>
                        @if($pct !== null)
                            <div class="ta-meta mt-1">Entregas {{ $tarefa->entregasEntregues() }}/{{ $tarefa->entregasTotal() }} ({{ $pct }}%)</div>
                            <div class="ta-prog"><div style="width:{{ $pct }}%;"></div></div>
                        @endif
                        <div class="mt-auto pt-2 d-flex" style="gap:.35rem;" onclick="event.stopPropagation()">
                            @can('tarefa.entregas')
                                <a href="{{ route('tarefa.entregas', $tarefa->id) }}" class="btn btn-xs btn-info" title="Entregas"><i class="fa fa-check-double"></i></a>
                            @endcan
                            @can('tarefa.edit')
                                <a href="{{ route('tarefa.edit', $tarefa->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                            @endcan
                            @can('tarefa.destroy')
                                <x-delete-button :action="route('tarefa.destroy', $tarefa->id)" titulo="Excluir" mensagem="Excluir a tarefa &quot;{{ $tarefa->titulo }}&quot;? As entregas também serão removidas." />
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted text-center py-4">Nenhuma tarefa cadastrada.</div>
            @endforelse
        </div>
    </div>

    @if($tarefas->hasPages())
        <div class="card-footer">
            {{ $tarefas->links() }}
        </div>
    @endif
</div>

{{-- Modal de detalhes --}}
<div class="modal fade" id="taModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="tm-titulo"></h5>
                    <span id="tm-badges"></span>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="ta-info-grid" id="tm-info"></div>
                <div id="tm-secoes"></div>
            </div>
        </div>
    </div>
</div>

@php
    $tJson = $tarefas->keyBy('id')->map(function ($tarefa) {
        return [
            'titulo' => $tarefa->titulo,
            'turma' => $tarefa->turma->nome ?? '—',
            'escola' => $tarefa->turma->escola->nome ?? '—',
            'disciplina' => $tarefa->disciplina->nome ?? '—',
            'entrega' => $tarefa->data_entrega->format('d/m/Y'),
            'nota_maxima' => $tarefa->nota_maxima,
            'tipo' => \App\Models\Tarefa::TIPOS[$tarefa->tipo] ?? $tarefa->tipo,
            'tipo_entrega' => \App\Models\Tarefa::TIPOS_ENTREGA[$tarefa->tipo_entrega] ?? $tarefa->tipo_entrega,
            'progresso' => $tarefa->entregasTotal() > 0
                ? $tarefa->entregasEntregues().'/'.$tarefa->entregasTotal().' entregues'
                : 'Sem entregas',
            'autor' => $tarefa->autor->name ?? '—',
            'vencida' => (bool) $tarefa->atraso(),
            'descricao' => $tarefa->descricao,
            'instrucoes' => $tarefa->instrucoes,
            'entregas_url' => route('tarefa.entregas', $tarefa->id),
        ];
    });
@endphp
<script>
(function () {
    const TA = @json($tJson);

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }
    function info(label, valor) {
        return '<div class="ta-info"><label>' + label + '</label><span>' + esc(valor || '—') + '</span></div>';
    }
    function sec(icone, titulo, texto) {
        if (!texto) { return ''; }
        return '<div class="ta-sec"><i class="fa ' + icone + ' mr-1"></i>' + titulo + '</div>'
            + '<div class="ta-txt">' + esc(texto) + '</div>';
    }
    function abrir(id) {
        const t = TA[id];
        if (!t) { return; }
        document.getElementById('tm-titulo').textContent = t.titulo;
        document.getElementById('tm-badges').innerHTML =
            '<span class="badge badge-light border mr-1">' + esc(t.tipo) + '</span>'
            + '<span class="badge badge-light border">' + esc(t.tipo_entrega) + '</span>'
            + (t.vencida ? ' <span class="badge badge-danger">vencida</span>' : '');
        document.getElementById('tm-info').innerHTML =
            info('Turma', t.turma) + info('Escola', t.escola)
            + info('Disciplina', t.disciplina) + info('Entrega', t.entrega)
            + info('Nota máxima', t.nota_maxima) + info('Autor', t.autor)
            + info('Progresso', t.progresso);
        document.getElementById('tm-secoes').innerHTML =
            sec('fa-align-left', 'Descrição', t.descricao)
            + sec('fa-list-ol', 'Instruções', t.instrucoes)
            + '<div class="mt-3"><a href="' + t.entregas_url + '" class="btn btn-sm btn-outline-primary"><i class="fa fa-check-double mr-1"></i>Acompanhar entregas</a></div>';
        $('#taModal').modal('show');
    }

    document.querySelectorAll('.ta-click, .ta-card').forEach(function (el) {
        el.addEventListener('click', function () { abrir(el.dataset.id); });
    });

    // Toggle Lista/Cards
    const KEY = 'tarefa_view';
    function setView(v) {
        const cards = v === 'cards';
        document.getElementById('ta-lista').style.display = cards ? 'none' : '';
        document.getElementById('ta-cards').style.display = cards ? '' : 'none';
        document.getElementById('ta-btn-lista').classList.toggle('active', !cards);
        document.getElementById('ta-btn-cards').classList.toggle('active', cards);
        try { localStorage.setItem(KEY, v); } catch (e) {}
    }
    document.getElementById('ta-btn-lista').addEventListener('click', function () { setView('lista'); });
    document.getElementById('ta-btn-cards').addEventListener('click', function () { setView('cards'); });
    try { setView(localStorage.getItem(KEY) || 'lista'); } catch (e) {}
})();
</script>
@stop
