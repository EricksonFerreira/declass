@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="PEI, PDI e planos de atendimento educacional especializado" :create-url="auth()->user()->hasPermissao('plano_atendimento.create') ? route('plano_atendimento.create') : null" :create-can="'plano_atendimento.create'" />
@stop

@push('css')
<style>
    .pa-card { border:1px solid #eef1f6; border-radius:10px; padding:.75rem .9rem; background:#fff; height:100%; display:flex; flex-direction:column; cursor:pointer; transition:box-shadow .12s, transform .12s; }
    .pa-card:hover { box-shadow:0 4px 14px rgba(37,99,235,.10); transform:translateY(-1px); }
    .pa-avatar { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1d4ed8; display:inline-flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:700; flex-shrink:0; }
    .pa-meta { font-size:.74rem; color:#6b7280; }
    tr.pa-click { cursor:pointer; }
    #paModal .modal-header { background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; border-radius:.3rem .3rem 0 0; padding:.6rem 1rem; }
    #paModal .close { color:#fff; opacity:.85; }
    .pa-sec { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2563eb; margin:.8rem 0 .3rem; }
    .pa-sec i { width:14px; }
    .pa-txt { font-size:.84rem; color:#374151; white-space:pre-line; background:#f8fafc; border:1px solid #eef1f6; border-radius:8px; padding:.5rem .7rem; }
    .pa-info-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:.4rem; }
    .pa-info { border:1px solid #eef1f6; border-radius:8px; padding:.4rem .6rem; }
    .pa-info label { display:block; font-size:.62rem; font-weight:700; text-transform:uppercase; color:#9ca3af; margin:0; }
    .pa-info span { font-size:.84rem; font-weight:600; color:#1f2937; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <div class="btn-group btn-group-sm" role="group" aria-label="Modo de visualização">
            <button type="button" class="btn btn-outline-primary active" id="pa-btn-lista" title="Lista"><i class="fa fa-list"></i></button>
            <button type="button" class="btn btn-outline-primary" id="pa-btn-cards" title="Cards"><i class="fa fa-th-large"></i></button>
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0" id="pa-lista">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Aluno</th>
                        <th>Escola</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Elaboração</th>
                        <th>Próx. revisão</th>
                        <th>Profissional</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planos as $plano)
                        <tr class="pa-click" data-id="{{ $plano->id }}" title="Ver detalhes">
                            <td class="font-weight-bold">{{ $plano->aluno->pessoa->nome ?? '—' }}</td>
                            <td>{{ $plano->escola->nome ?? '—' }}</td>
                            <td><span class="badge badge-primary">{{ $plano->tipo_label }}</span></td>
                            <td>
                                @php $cor = ['ativo' => 'success', 'elaboracao' => 'info', 'suspenso' => 'warning', 'encerrado' => 'secondary'][$plano->status] ?? 'secondary'; @endphp
                                <span class="badge badge-{{ $cor }}">{{ $plano->status_label }}</span>
                                @if($plano->revisao_vencida)
                                    <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> revisão vencida</small>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $plano->data_elaboracao?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-nowrap">{{ $plano->proxima_revisao?->format('d/m/Y') ?? '—' }}</td>
                            <td><small>{{ $plano->profissional->name ?? '—' }}</small></td>
                            <td class="text-right" onclick="event.stopPropagation()">
                                @can('plano_atendimento.edit')
                                    <a href="{{ route('plano_atendimento.edit', $plano->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('plano_atendimento.destroy')
                                    <x-delete-button :action="route('plano_atendimento.destroy', $plano->id)" titulo="Excluir" mensagem="Excluir o plano de {{ $plano->aluno->pessoa->nome ?? 'aluno' }}?" />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-hands-helping fa-2x mb-2 d-block"></i>
                                Nenhum plano de atendimento cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-body" id="pa-cards" style="display:none;">
        <div class="row">
            @forelse($planos as $plano)
                @php $cor = ['ativo' => 'success', 'elaboracao' => 'info', 'suspenso' => 'warning', 'encerrado' => 'secondary'][$plano->status] ?? 'secondary'; @endphp
                <div class="col-md-6 col-xl-4 mb-3">
                    <div class="pa-card" data-id="{{ $plano->id }}" title="Ver detalhes">
                        <div class="d-flex align-items-center mb-1">
                            <span class="pa-avatar mr-2">{{ collect(explode(' ', trim($plano->aluno->pessoa->nome ?? '?')))->filter()->map(fn ($s) => mb_strtoupper(mb_substr($s, 0, 1)))->take(2)->implode('') }}</span>
                            <div class="flex-grow-1" style="min-width:0;">
                                <strong class="d-block text-truncate">{{ $plano->aluno->pessoa->nome ?? '—' }}</strong>
                                <span class="pa-meta">{{ $plano->escola->nome ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="mb-1">
                            <span class="badge badge-primary">{{ $plano->tipo_label }}</span>
                            <span class="badge badge-{{ $cor }}">{{ $plano->status_label }}</span>
                            @if($plano->revisao_vencida)
                                <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> revisão vencida</span>
                            @endif
                        </div>
                        <div class="pa-meta">
                            <i class="fa fa-calendar mr-1"></i>Elaboração {{ $plano->data_elaboracao?->format('d/m/Y') ?? '—' }}
                            · Revisão {{ $plano->proxima_revisao?->format('d/m/Y') ?? '—' }}<br>
                            <i class="fa fa-user-md mr-1"></i>{{ $plano->profissional->name ?? '—' }}
                        </div>
                        <div class="mt-auto pt-2 d-flex" style="gap:.35rem;" onclick="event.stopPropagation()">
                            @can('plano_atendimento.edit')
                                <a href="{{ route('plano_atendimento.edit', $plano->id) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i></a>
                            @endcan
                            @can('plano_atendimento.destroy')
                                <x-delete-button :action="route('plano_atendimento.destroy', $plano->id)" titulo="Excluir" mensagem="Excluir o plano de {{ $plano->aluno->pessoa->nome ?? 'aluno' }}?" />
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted text-center py-4">Nenhum plano de atendimento cadastrado.</div>
            @endforelse
        </div>
    </div>

    @if($planos->hasPages())
        <div class="card-footer">
            {{ $planos->links() }}
        </div>
    @endif
</div>

{{-- Modal de detalhes --}}
<div class="modal fade" id="paModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="pa-titulo"></h5>
                    <span id="pa-badges"></span>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="pa-info-grid" id="pa-info"></div>
                <div id="pa-secoes"></div>
            </div>
        </div>
    </div>
</div>

@php
    $coresStatus = ['ativo' => 'success', 'elaboracao' => 'info', 'suspenso' => 'warning', 'encerrado' => 'secondary'];
    $planosJson = $planos->keyBy('id')->map(function ($plano) use ($coresStatus) {
        return [
            'nome' => $plano->aluno->pessoa->nome ?? '—',
            'escola' => $plano->escola->nome ?? '—',
            'tipo' => $plano->tipo_label,
            'status' => $plano->status_label,
            'status_cor' => $coresStatus[$plano->status] ?? 'secondary',
            'elaboracao' => $plano->data_elaboracao?->format('d/m/Y'),
            'revisao' => $plano->proxima_revisao?->format('d/m/Y'),
            'revisao_vencida' => (bool) $plano->revisao_vencida,
            'profissional' => $plano->profissional->name ?? '—',
            'diagnostico' => $plano->diagnostico,
            'necessidades' => $plano->necessidades,
            'objetivos' => $plano->objetivos,
            'estrategias' => $plano->estrategias,
            'observacoes' => $plano->observacoes,
        ];
    });
@endphp
<script>
(function () {
    const PLANOS = @json($planosJson);

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }
    function sec(icone, titulo, texto) {
        if (!texto) { return ''; }
        return '<div class="pa-sec"><i class="fa ' + icone + ' mr-1"></i>' + titulo + '</div>'
            + '<div class="pa-txt">' + esc(texto) + '</div>';
    }
    function info(label, valor) {
        return '<div class="pa-info"><label>' + label + '</label><span>' + esc(valor || '—') + '</span></div>';
    }
    function abrir(id) {
        const p = PLANOS[id];
        if (!p) { return; }
        document.getElementById('pa-titulo').textContent = p.nome;
        document.getElementById('pa-badges').innerHTML =
            '<span class="badge badge-primary mr-1">' + esc(p.tipo) + '</span>'
            + '<span class="badge badge-' + p.status_cor + '">' + esc(p.status) + '</span>'
            + (p.revisao_vencida ? ' <span class="badge badge-danger"><i class="fa fa-exclamation-triangle"></i> revisão vencida</span>' : '');
        document.getElementById('pa-info').innerHTML =
            info('Escola', p.escola) + info('Profissional', p.profissional)
            + info('Elaboração', p.elaboracao) + info('Próxima revisão', p.revisao);
        document.getElementById('pa-secoes').innerHTML =
            sec('fa-stethoscope', 'Diagnóstico', p.diagnostico)
            + sec('fa-universal-access', 'Necessidades', p.necessidades)
            + sec('fa-bullseye', 'Objetivos', p.objetivos)
            + sec('fa-route', 'Estratégias', p.estrategias)
            + sec('fa-sticky-note', 'Observações', p.observacoes);
        $('#paModal').modal('show');
    }

    document.querySelectorAll('.pa-click, .pa-card').forEach(function (el) {
        el.addEventListener('click', function () { abrir(el.dataset.id); });
    });

    // Toggle Lista/Cards
    const KEY = 'plano_atend_view';
    function setView(v) {
        const cards = v === 'cards';
        document.getElementById('pa-lista').style.display = cards ? 'none' : '';
        document.getElementById('pa-cards').style.display = cards ? '' : 'none';
        document.getElementById('pa-btn-lista').classList.toggle('active', !cards);
        document.getElementById('pa-btn-cards').classList.toggle('active', cards);
        try { localStorage.setItem(KEY, v); } catch (e) {}
    }
    document.getElementById('pa-btn-lista').addEventListener('click', function () { setView('lista'); });
    document.getElementById('pa-btn-cards').addEventListener('click', function () { setView('cards'); });
    try { setView(localStorage.getItem(KEY) || 'lista'); } catch (e) {}
})();
</script>
@stop
