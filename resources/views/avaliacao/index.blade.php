@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Instrumentos de avaliação por turma e disciplina" :create-url="auth()->user()->hasPermissao('avaliacao.create') ? route('avaliacao.create') : null" :create-can="'avaliacao.create'" />
@stop

@push('css')
<style>
    .av-card { border:1px solid #eef1f6; border-radius:10px; padding:.75rem .9rem; background:#fff; height:100%; display:flex; flex-direction:column; cursor:pointer; transition:box-shadow .12s, transform .12s; }
    .av-card:hover { box-shadow:0 4px 14px rgba(37,99,235,.10); transform:translateY(-1px); }
    .av-meta { font-size:.74rem; color:#6b7280; }
    tr.av-click { cursor:pointer; }
    #avModal .modal-header { background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; border-radius:.3rem .3rem 0 0; padding:.6rem 1rem; }
    #avModal .close { color:#fff; opacity:.85; }
    .av-sec { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2563eb; margin:.8rem 0 .3rem; }
    .av-txt { font-size:.84rem; color:#374151; white-space:pre-line; background:#f8fafc; border:1px solid #eef1f6; border-radius:8px; padding:.5rem .7rem; }
    .av-info-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:.4rem; }
    .av-info { border:1px solid #eef1f6; border-radius:8px; padding:.4rem .6rem; }
    .av-info label { display:block; font-size:.62rem; font-weight:700; text-transform:uppercase; color:#9ca3af; margin:0; }
    .av-info span { font-size:.84rem; font-weight:600; color:#1f2937; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <div class="btn-group btn-group-sm" role="group" aria-label="Modo de visualização">
            <button type="button" class="btn btn-outline-primary active" id="av-btn-lista" title="Lista"><i class="fa fa-list"></i></button>
            <button type="button" class="btn btn-outline-primary" id="av-btn-cards" title="Cards"><i class="fa fa-th-large"></i></button>
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0" id="av-lista">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Turma</th>
                        <th>Disciplina</th>
                        <th>Período</th>
                        <th>Data</th>
                        <th>Nota máx.</th>
                        <th>Professor</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($avaliacoes as $avaliacao)
                        <tr class="av-click" data-id="{{ $avaliacao->id }}" title="Ver detalhes">
                            <td class="font-weight-bold">{{ $avaliacao->nome }}</td>
                            <td><span class="badge badge-info">{{ \App\Models\Avaliacao::TIPOS[$avaliacao->tipo] ?? $avaliacao->tipo }}</span></td>
                            <td>{{ $avaliacao->turma->nome ?? '—' }}</td>
                            <td>{{ $avaliacao->disciplina->nome ?? '—' }}</td>
                            <td>{{ $avaliacao->periodo ?? '—' }}</td>
                            <td>{{ $avaliacao->data?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ number_format($avaliacao->nota_maxima, 1, ',', '.') }}</td>
                            <td>{{ $avaliacao->professor->nome ?? '—' }}</td>
                            <td class="text-right text-nowrap" onclick="event.stopPropagation()">
                                @can('avaliacao.notas')
                                    <a href="{{ route('avaliacao.notas', $avaliacao->id) }}" class="btn btn-xs btn-success" title="Lançar notas"><i class="fa fa-pencil-alt"></i></a>
                                @endcan
                                @can('avaliacao.edit')
                                    <a href="{{ route('avaliacao.edit', $avaliacao->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                                @endcan
                                @can('avaliacao.destroy')
                                    <x-delete-button :action="route('avaliacao.destroy', $avaliacao->id)" titulo="Excluir" mensagem="Excluir a avaliação &quot;{{ $avaliacao->nome }}&quot;? As notas lançadas também serão removidas." />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-check fa-2x mb-2 d-block"></i>
                                Nenhuma avaliação encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-body" id="av-cards" style="display:none;">
        <div class="row">
            @forelse($avaliacoes as $avaliacao)
                <div class="col-md-6 col-xl-4 mb-3">
                    <div class="av-card" data-id="{{ $avaliacao->id }}" title="Ver detalhes">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <strong class="small">{{ \Illuminate\Support\Str::limit($avaliacao->nome, 70) }}</strong>
                            <span class="badge badge-info ml-1">{{ \App\Models\Avaliacao::TIPOS[$avaliacao->tipo] ?? $avaliacao->tipo }}</span>
                        </div>
                        <div class="mb-1">
                            <span class="badge badge-light border">{{ $avaliacao->turma->nome ?? '—' }}</span>
                            <span class="badge badge-light border">{{ $avaliacao->disciplina->nome ?? '—' }}</span>
                        </div>
                        <div class="av-meta">
                            <i class="fa fa-calendar mr-1"></i>{{ $avaliacao->data?->format('d/m/Y') ?? '—' }}
                            @if($avaliacao->periodo) · {{ $avaliacao->periodo }}@endif<br>
                            <i class="fa fa-user mr-1"></i>{{ $avaliacao->professor->nome ?? '—' }}
                            · nota máx. {{ number_format($avaliacao->nota_maxima, 1, ',', '.') }}
                        </div>
                        <div class="mt-auto pt-2 d-flex" style="gap:.35rem;" onclick="event.stopPropagation()">
                            @can('avaliacao.notas')
                                <a href="{{ route('avaliacao.notas', $avaliacao->id) }}" class="btn btn-xs btn-success" title="Lançar notas"><i class="fa fa-pencil-alt"></i></a>
                            @endcan
                            @can('avaliacao.edit')
                                <a href="{{ route('avaliacao.edit', $avaliacao->id) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fa fa-edit"></i></a>
                            @endcan
                            @can('avaliacao.destroy')
                                <x-delete-button :action="route('avaliacao.destroy', $avaliacao->id)" titulo="Excluir" mensagem="Excluir a avaliação &quot;{{ $avaliacao->nome }}&quot;? As notas lançadas também serão removidas." />
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted text-center py-4">Nenhuma avaliação encontrada.</div>
            @endforelse
        </div>
    </div>

    @if($avaliacoes->hasPages())
        <div class="card-footer">
            {{ $avaliacoes->links() }}
        </div>
    @endif
</div>

{{-- Modal de detalhes --}}
<div class="modal fade" id="avModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="am-titulo"></h5>
                    <span id="am-badges"></span>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="av-info-grid" id="am-info"></div>
                <div id="am-secoes"></div>
            </div>
        </div>
    </div>
</div>

@php
    $avJson = $avaliacoes->keyBy('id')->map(function ($a) {
        return [
            'nome' => $a->nome,
            'tipo' => \App\Models\Avaliacao::TIPOS[$a->tipo] ?? $a->tipo,
            'turma' => $a->turma->nome ?? '—',
            'escola' => $a->turma->escola->nome ?? '—',
            'disciplina' => $a->disciplina->nome ?? '—',
            'periodo' => $a->periodo,
            'data' => $a->data?->format('d/m/Y'),
            'nota_maxima' => number_format($a->nota_maxima, 1, ',', '.'),
            'professor' => $a->professor->nome ?? '—',
            'descricao' => $a->descricao,
            'peso' => $a->peso,
            'notas_url' => route('avaliacao.notas', $a->id),
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
        return '<div class="av-info"><label>' + label + '</label><span>' + esc(valor || '—') + '</span></div>';
    }
    function abrir(id) {
        const a = AV[id];
        if (!a) { return; }
        document.getElementById('am-titulo').textContent = a.nome;
        document.getElementById('am-badges').innerHTML = '<span class="badge badge-info">' + esc(a.tipo) + '</span>';
        document.getElementById('am-info').innerHTML =
            info('Turma', a.turma) + info('Escola', a.escola)
            + info('Disciplina', a.disciplina) + info('Professor', a.professor)
            + info('Período', a.periodo) + info('Data', a.data)
            + info('Nota máxima', a.nota_maxima) + info('Peso', a.peso);
        let html = '';
        if (a.descricao) {
            html += '<div class="av-sec"><i class="fa fa-align-left mr-1"></i>Descrição</div>'
                + '<div class="av-txt">' + esc(a.descricao) + '</div>';
        }
        html += '<div class="mt-3"><a href="' + a.notas_url + '" class="btn btn-sm btn-outline-success"><i class="fa fa-pencil-alt mr-1"></i>Lançar notas</a></div>';
        document.getElementById('am-secoes').innerHTML = html;
        $('#avModal').modal('show');
    }

    document.querySelectorAll('.av-click, .av-card').forEach(function (el) {
        el.addEventListener('click', function () { abrir(el.dataset.id); });
    });

    // Toggle Lista/Cards
    const KEY = 'avaliacao_view';
    function setView(v) {
        const cards = v === 'cards';
        document.getElementById('av-lista').style.display = cards ? 'none' : '';
        document.getElementById('av-cards').style.display = cards ? '' : 'none';
        document.getElementById('av-btn-lista').classList.toggle('active', !cards);
        document.getElementById('av-btn-cards').classList.toggle('active', cards);
        try { localStorage.setItem(KEY, v); } catch (e) {}
    }
    document.getElementById('av-btn-lista').addEventListener('click', function () { setView('lista'); });
    document.getElementById('av-btn-cards').addEventListener('click', function () { setView('cards'); });
    try { setView(localStorage.getItem(KEY) || 'lista'); } catch (e) {}
})();
</script>
@stop
