@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Comunicados da escola para as famílias" :create-url="auth()->user()->hasPermissao('aviso.create') ? route('aviso.create') : null" :create-can="'aviso.create'" />
@stop

@push('css')
<style>
    .as-card { border:1px solid #eef1f6; border-radius:10px; padding:.75rem .9rem; background:#fff; height:100%; display:flex; flex-direction:column; cursor:pointer; transition:box-shadow .12s, transform .12s; }
    .as-card:hover { box-shadow:0 4px 14px rgba(37,99,235,.10); transform:translateY(-1px); }
    .as-meta { font-size:.74rem; color:#6b7280; }
    tr.as-click { cursor:pointer; }
    .as-cal { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
    .as-cal-dow { font-size:.65rem; font-weight:700; text-transform:uppercase; color:#9ca3af; text-align:center; padding:.2rem 0; }
    .as-cal-day { min-height:88px; border:1px solid #eef1f6; border-radius:8px; padding:.25rem .3rem; background:#fff; font-size:.72rem; }
    .as-cal-day.vazio { background:#fafbfc; }
    .as-cal-day .num { font-weight:700; color:#9ca3af; font-size:.7rem; }
    .as-cal-day.hoje .num { background:#2563eb; color:#fff; border-radius:50%; padding:0 .35rem; }
    .as-ev { display:block; border-radius:4px; padding:1px 4px; margin-top:2px; cursor:pointer; font-size:.68rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .as-ev-info { background:#dbeafe; color:#1d4ed8; }
    .as-ev-urgente { background:#fee2e2; color:#b91c1c; }
    .as-ev-evento { background:#d1fae5; color:#065f46; }
    #asModal .modal-header { background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; border-radius:.3rem .3rem 0 0; padding:.6rem 1rem; }
    #asModal .close { color:#fff; opacity:.85; }
    .as-info-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:.4rem; }
    .as-info { border:1px solid #eef1f6; border-radius:8px; padding:.4rem .6rem; }
    .as-info label { display:block; font-size:.62rem; font-weight:700; text-transform:uppercase; color:#9ca3af; margin:0; }
    .as-info span { font-size:.84rem; font-weight:600; color:#1f2937; }
    .as-sec { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2563eb; margin:.8rem 0 .3rem; }
    .as-txt { font-size:.86rem; color:#374151; white-space:pre-line; background:#f8fafc; border:1px solid #eef1f6; border-radius:8px; padding:.6rem .8rem; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <div class="btn-group btn-group-sm" role="group" aria-label="Modo de visualização">
            <button type="button" class="btn btn-outline-primary active" id="as-btn-lista" title="Lista"><i class="fa fa-list"></i></button>
            <button type="button" class="btn btn-outline-primary" id="as-btn-cards" title="Cards"><i class="fa fa-th-large"></i></button>
            <button type="button" class="btn btn-outline-primary" id="as-btn-cal" title="Calendário"><i class="fa fa-calendar-alt"></i></button>
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    {{-- LISTA --}}
    <div class="card-body p-0" id="as-lista">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Publicação</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Escola</th>
                        <th>Público</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($avisos as $aviso)
                        <tr class="as-click" data-id="{{ $aviso->id }}" title="Ver detalhes">
                            <td class="text-nowrap">{{ optional($aviso->data_publicacao)->format('d/m/Y') }}</td>
                            <td>
                                <div class="font-weight-bold">{{ $aviso->titulo }}</div>
                                <div class="text-muted" style="font-size:.8rem; max-width:340px;">{{ \Illuminate\Support\Str::limit($aviso->mensagem, 90) }}</div>
                            </td>
                            <td>
                                @php $badgeTipo = ['info' => 'primary', 'urgente' => 'danger', 'evento' => 'success'][$aviso->tipo] ?? 'secondary'; @endphp
                                <span class="badge badge-{{ $badgeTipo }}">{{ \App\Models\Aviso::TIPOS[$aviso->tipo] ?? $aviso->tipo }}</span>
                                @if(($aviso->destino ?? 'familias') === 'staff')
                                    <span class="badge badge-dark"><i class="fa fa-users"></i> Equipe</span>
                                @endif
                            </td>
                            <td>{{ $aviso->escola->nome ?? '—' }}</td>
                            <td>
                                @if($aviso->ehGeral())
                                    <span class="badge badge-light border">Escola toda</span>
                                @else
                                    <span class="badge badge-info" title="{{ $aviso->turmas->pluck('nome')->implode(', ') }}">{{ $aviso->publicoLabel() }}</span>
                                @endif
                            </td>
                            <td>
                                @if($aviso->estaAtivo())
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-secondary">Inativo</span>
                                @endif
                            </td>
                            <td class="text-right" onclick="event.stopPropagation()">
                                @can('aviso.edit')
                                    <a href="{{ route('aviso.edit', $aviso->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('aviso.destroy')
                                    <x-delete-button :action="route('aviso.destroy', $aviso->id)" titulo="Excluir" mensagem="Excluir o aviso &quot;{{ $aviso->titulo }}&quot;?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-bullhorn fa-2x mb-2 d-block"></i>
                                Nenhum aviso encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- CARDS --}}
    <div class="card-body" id="as-cards" style="display:none;">
        <div class="row">
            @forelse($avisos as $aviso)
                @php $badgeTipo = ['info' => 'primary', 'urgente' => 'danger', 'evento' => 'success'][$aviso->tipo] ?? 'secondary'; @endphp
                <div class="col-md-6 col-xl-4 mb-3">
                    <div class="as-card" data-id="{{ $aviso->id }}" title="Ver detalhes">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <strong class="small">{{ \Illuminate\Support\Str::limit($aviso->titulo, 60) }}</strong>
                            <span class="badge badge-{{ $badgeTipo }} ml-1">{{ \App\Models\Aviso::TIPOS[$aviso->tipo] ?? $aviso->tipo }}</span>
                        </div>
                        <div class="as-meta mb-1">{{ \Illuminate\Support\Str::limit($aviso->mensagem, 110) }}</div>
                        <div class="as-meta">
                            <i class="fa fa-calendar mr-1"></i>{{ optional($aviso->data_publicacao)->format('d/m/Y') ?? '—' }}
                            · {{ $aviso->escola->nome ?? '—' }}
                            @if(($aviso->destino ?? 'familias') === 'staff') · <i class="fa fa-users"></i> Equipe @endif
                        </div>
                        <div class="mt-auto pt-2">
                            @if(!$aviso->estaAtivo())<span class="badge badge-secondary">Inativo</span>@endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted text-center py-4">Nenhum aviso encontrado.</div>
            @endforelse
        </div>
    </div>

    {{-- CALENDARIO --}}
    <div class="card-body" id="as-cal" style="display:none;">
        @php
            $porDia = $avisos->filter(fn ($a) => $a->data_publicacao)->groupBy(fn ($a) => $a->data_publicacao->format('Y-m-d'));
            $ref = $avisos->filter(fn ($a) => $a->data_publicacao)->min('data_publicacao') ?? now();
            $inicio = $ref->copy()->startOfMonth()->startOfWeek();
            $fim = $ref->copy()->endOfMonth()->endOfWeek();
        @endphp
        <div class="text-center font-weight-bold mb-2">{{ $ref->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }}</div>
        <div class="as-cal">
            @foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $dow)
                <div class="as-cal-dow">{{ $dow }}</div>
            @endforeach
            @for($d = $inicio->copy(); $d->lte($fim); $d->addDay())
                <div class="as-cal-day {{ $d->isToday() ? 'hoje' : '' }} {{ $d->month !== $ref->month ? 'vazio' : '' }}">
                    <span class="num">{{ $d->day }}</span>
                    @foreach($porDia->get($d->format('Y-m-d'), collect()) as $av)
                        <span class="as-ev as-ev-{{ $av->tipo }}" data-id="{{ $av->id }}" title="{{ $av->titulo }}">{{ $av->titulo }}</span>
                    @endforeach
                </div>
            @endfor
        </div>
    </div>

    @if($avisos->hasPages())
        <div class="card-footer">
            {{ $avisos->links() }}
        </div>
    @endif
</div>

{{-- Modal de detalhes --}}
<div class="modal fade" id="asModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="asm-titulo"></h5>
                    <span id="asm-badges"></span>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="as-info-grid" id="asm-info"></div>
                <div id="asm-secoes"></div>
            </div>
        </div>
    </div>
</div>

@php
    $avJson = $avisos->keyBy('id')->map(function ($a) {
        return [
            'titulo' => $a->titulo,
            'tipo' => \App\Models\Aviso::TIPOS[$a->tipo] ?? $a->tipo,
            'tipo_cor' => ['info' => 'primary', 'urgente' => 'danger', 'evento' => 'success'][$a->tipo] ?? 'secondary',
            'escola' => $a->escola->nome ?? '—',
            'publico' => $a->ehGeral() ? 'Escola toda' : ($a->turma->nome ?? 'Turma #'.$a->turma_id),
            'destino' => ($a->destino ?? 'familias') === 'staff' ? 'Equipe (mural interno)' : 'Famílias',
            'publicacao' => $a->data_publicacao?->format('d/m/Y'),
            'status' => $a->estaAtivo() ? 'Ativo' : 'Inativo',
            'mensagem' => $a->mensagem,
            'edit_url' => route('aviso.edit', $a->id),
        ];
    });
@endphp
<script>
(function () {
    const AV = @json($avJson);

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }
    function info(label, valor) {
        return '<div class="as-info"><label>' + label + '</label><span>' + esc(valor || '—') + '</span></div>';
    }
    function abrir(id) {
        const a = AV[id];
        if (!a) { return; }
        document.getElementById('asm-titulo').textContent = a.titulo;
        document.getElementById('asm-badges').innerHTML =
            '<span class="badge badge-' + a.tipo_cor + ' mr-1">' + esc(a.tipo) + '</span>'
            + '<span class="badge badge-' + (a.status === 'Ativo' ? 'success' : 'secondary') + '">' + a.status + '</span>';
        document.getElementById('asm-info').innerHTML =
            info('Publicação', a.publicacao) + info('Escola', a.escola)
            + info('Público', a.publico) + info('Destino', a.destino);
        document.getElementById('asm-secoes').innerHTML =
            '<div class="as-sec"><i class="fa fa-envelope-open-text mr-1"></i>Mensagem</div>'
            + '<div class="as-txt">' + esc(a.mensagem || '—') + '</div>';
        $('#asModal').modal('show');
    }

    document.querySelectorAll('.as-click, .as-card, .as-ev').forEach(function (el) {
        el.addEventListener('click', function (e) { e.stopPropagation(); abrir(el.dataset.id); });
    });

    // Toggle Lista/Cards/Calendario
    const KEY = 'aviso_view';
    function setView(v) {
        ['lista', 'cards', 'cal'].forEach(function (m) {
            document.getElementById('as-' + m).style.display = m === v ? '' : 'none';
            document.getElementById('as-btn-' + m).classList.toggle('active', m === v);
        });
        try { localStorage.setItem(KEY, v); } catch (e) {}
    }
    document.getElementById('as-btn-lista').addEventListener('click', function () { setView('lista'); });
    document.getElementById('as-btn-cards').addEventListener('click', function () { setView('cards'); });
    document.getElementById('as-btn-cal').addEventListener('click', function () { setView('cal'); });
    try { setView(localStorage.getItem(KEY) || 'lista'); } catch (e) {}
})();
</script>
@stop
