@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Atestados e Abonos" subtitle="Justificativa e abono de faltas por período — aplica automaticamente nas chamadas" :create-url="auth()->user()->hasPermissao('justificativa.create') ? ($turmaIdFiltro ? route('turma.justificativa.create', $turmaIdFiltro) : route('justificativa.create')) : null" :create-can="'justificativa.create'" />
@stop

@push('css')
<style>
    .jf-card { border:1px solid #eef1f6; border-radius:10px; padding:.75rem .9rem; background:#fff; height:100%; display:flex; flex-direction:column; cursor:pointer; transition:box-shadow .12s, transform .12s; }
    .jf-card:hover { box-shadow:0 4px 14px rgba(37,99,235,.10); transform:translateY(-1px); }
    .jf-avatar { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1d4ed8; display:inline-flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:700; flex-shrink:0; }
    .jf-meta { font-size:.74rem; color:#6b7280; }
    tr.jf-click { cursor:pointer; }
    #jfModal .modal-header { background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; border-radius:.3rem .3rem 0 0; padding:.6rem 1rem; }
    #jfModal .close { color:#fff; opacity:.85; }
    .jf-sec { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#2563eb; margin:.8rem 0 .3rem; }
    .jf-txt { font-size:.84rem; color:#374151; white-space:pre-line; background:#f8fafc; border:1px solid #eef1f6; border-radius:8px; padding:.5rem .7rem; }
    .jf-info-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:.4rem; }
    .jf-info { border:1px solid #eef1f6; border-radius:8px; padding:.4rem .6rem; }
    .jf-info label { display:block; font-size:.62rem; font-weight:700; text-transform:uppercase; color:#9ca3af; margin:0; }
    .jf-info span { font-size:.84rem; font-weight:600; color:#1f2937; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
        <div class="flex-grow-1">
            <x-filter :filters="$filters" :additional-filters="$additionalFilters" :itens-filtered="$itensFiltered" :nome-filtro="$nomeFiltro" />
        </div>
        <div class="btn-group btn-group-sm" role="group" aria-label="Modo de visualização">
            <button type="button" class="btn btn-outline-primary active" id="jf-btn-lista" title="Lista"><i class="fa fa-list"></i></button>
            <button type="button" class="btn btn-outline-primary" id="jf-btn-cards" title="Cards"><i class="fa fa-th-large"></i></button>
        </div>
        <x-per-page-selector :per-pages="$perPages" :selected-per-page="$perPage" />
    </div>

    <div class="card-body p-0" id="jf-lista">
        <div class="table-responsive"><table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Período</th>
                    <th>Aluno</th>
                    <th>Turma</th>
                    <th>Tipo</th>
                    <th>Efeito</th>
                    <th>Disciplina</th>
                    <th>Registrado por</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($justificativas as $j)
                    <tr class="jf-click" data-id="{{ $j->id }}" title="Ver detalhes">
                        <td class="text-nowrap">{{ $j->data_inicio->format('d/m/Y') }} — {{ $j->data_fim->format('d/m/Y') }}</td>
                        <td class="font-weight-bold">{{ $j->matricula->aluno->pessoa->nome ?? '—' }}</td>
                        <td>{{ $j->matricula->turma->nome ?? '—' }}</td>
                        <td>{{ $j->tipo_label }}
                            @if($j->motivo)
                                <small class="d-block text-muted">{{ \Illuminate\Support\Str::limit($j->motivo, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($j->efeito === 'abonada')
                                <span class="badge badge-success">Abonada</span>
                            @else
                                <span class="badge badge-info">Justificada</span>
                            @endif
                        </td>
                        <td>{{ $j->disciplina->nome ?? 'Todas' }}</td>
                        <td class="text-muted small">{{ $j->registradoPor->nome ?? '—' }}</td>
                        <td class="text-right text-nowrap" onclick="event.stopPropagation()">
                            @if($j->arquivo_path)
                                <a href="{{ route('justificativa.arquivo', $j->id) }}" class="btn btn-xs btn-secondary" title="Baixar documento"><i class="fa fa-paperclip"></i></a>
                            @endif
                            @can('justificativa.destroy')
                                <form method="POST" action="{{ route('justificativa.destroy', $j->id) }}" class="d-inline"
                                    onsubmit="return confirm('Excluir esta justificativa? As faltas do período voltarão a contar.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Nenhuma justificativa registrada.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>

    <div class="card-body" id="jf-cards" style="display:none;">
        <div class="row">
            @forelse($justificativas as $j)
                <div class="col-md-6 col-xl-4 mb-3">
                    <div class="jf-card" data-id="{{ $j->id }}" title="Ver detalhes">
                        <div class="d-flex align-items-center mb-1">
                            <span class="jf-avatar mr-2">{{ collect(explode(' ', trim($j->matricula->aluno->pessoa->nome ?? '?')))->filter()->map(fn ($s) => mb_strtoupper(mb_substr($s, 0, 1)))->take(2)->implode('') }}</span>
                            <div class="flex-grow-1" style="min-width:0;">
                                <strong class="d-block text-truncate">{{ $j->matricula->aluno->pessoa->nome ?? '—' }}</strong>
                                <span class="jf-meta">{{ $j->matricula->turma->nome ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="mb-1">
                            @if($j->efeito === 'abonada')
                                <span class="badge badge-success">Abonada</span>
                            @else
                                <span class="badge badge-info">Justificada</span>
                            @endif
                            <span class="badge badge-light border">{{ $j->tipo_label }}</span>
                            @if($j->arquivo_path)
                                <i class="fa fa-paperclip text-muted" title="Com anexo"></i>
                            @endif
                        </div>
                        <div class="jf-meta">
                            <i class="fa fa-calendar mr-1"></i>{{ $j->data_inicio->format('d/m/Y') }} — {{ $j->data_fim->format('d/m/Y') }}
                            @if($j->disciplina)<br><i class="fa fa-book mr-1"></i>{{ $j->disciplina->nome }}@endif
                        </div>
                        <div class="mt-auto pt-2 d-flex" style="gap:.35rem;" onclick="event.stopPropagation()">
                            @if($j->arquivo_path)
                                <a href="{{ route('justificativa.arquivo', $j->id) }}" class="btn btn-xs btn-secondary" title="Baixar documento"><i class="fa fa-paperclip"></i></a>
                            @endif
                            @can('justificativa.destroy')
                                <form method="POST" action="{{ route('justificativa.destroy', $j->id) }}" class="d-inline"
                                    onsubmit="return confirm('Excluir esta justificativa? As faltas do período voltarão a contar.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-muted text-center py-4">Nenhuma justificativa registrada.</div>
            @endforelse
        </div>
    </div>

    @if($justificativas->hasPages())
        <div class="card-footer">{{ $justificativas->links() }}</div>
    @endif
</div>

{{-- Modal de detalhes --}}
<div class="modal fade" id="jfModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="jm-titulo"></h5>
                    <span id="jm-badges"></span>
                </div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="jf-info-grid" id="jm-info"></div>
                <div id="jm-secoes"></div>
            </div>
        </div>
    </div>
</div>

@php
    $jJson = $justificativas->keyBy('id')->map(function ($j) {
        return [
            'aluno' => $j->matricula->aluno->pessoa->nome ?? '—',
            'turma' => $j->matricula->turma->nome ?? '—',
            'escola' => $j->escola->nome ?? '—',
            'tipo' => $j->tipo_label,
            'efeito' => $j->efeito === 'abonada' ? 'Abonada' : 'Justificada',
            'efeito_cor' => $j->efeito === 'abonada' ? 'success' : 'info',
            'inicio' => $j->data_inicio->format('d/m/Y'),
            'fim' => $j->data_fim->format('d/m/Y'),
            'disciplina' => $j->disciplina->nome ?? 'Todas as disciplinas',
            'motivo' => $j->motivo,
            'registrado_por' => $j->registradoPor->nome ?? '—',
            'registrado_em' => $j->created_at?->format('d/m/Y H:i'),
            'anexo' => $j->arquivo_path ? route('justificativa.arquivo', $j->id) : null,
            'nome_anexo' => $j->arquivo_path ? basename($j->arquivo_path) : null,
        ];
    });
@endphp
<script>
(function () {
    const JF = @json($jJson);

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }
    function info(label, valor) {
        return '<div class="jf-info"><label>' + label + '</label><span>' + esc(valor || '—') + '</span></div>';
    }
    function abrir(id) {
        const j = JF[id];
        if (!j) { return; }
        document.getElementById('jm-titulo').textContent = j.aluno;
        document.getElementById('jm-badges').innerHTML =
            '<span class="badge badge-' + j.efeito_cor + ' mr-1">' + esc(j.efeito) + '</span>'
            + '<span class="badge badge-light border">' + esc(j.tipo) + '</span>';
        document.getElementById('jm-info').innerHTML =
            info('Turma', j.turma) + info('Escola', j.escola)
            + info('Início', j.inicio) + info('Fim', j.fim)
            + info('Disciplina', j.disciplina) + info('Registrado por', j.registrado_por)
            + info('Registrado em', j.registrado_em);

        let html = '';
        if (j.motivo) {
            html += '<div class="jf-sec"><i class="fa fa-comment-alt mr-1"></i>Motivo / observação</div>'
                + '<div class="jf-txt">' + esc(j.motivo) + '</div>';
        }
        if (j.anexo) {
            html += '<div class="jf-sec"><i class="fa fa-paperclip mr-1"></i>Anexo</div>'
                + '<a href="' + j.anexo + '" target="_blank" class="btn btn-sm btn-outline-primary">'
                + '<i class="fa fa-download mr-1"></i>' + esc(j.nome_anexo || 'Baixar documento') + '</a>';
        }
        if (!j.motivo && !j.anexo) {
            html = '<div class="text-muted small mt-2">Sem observações nem anexo.</div>';
        }
        document.getElementById('jm-secoes').innerHTML = html;
        $('#jfModal').modal('show');
    }

    document.querySelectorAll('.jf-click, .jf-card').forEach(function (el) {
        el.addEventListener('click', function () { abrir(el.dataset.id); });
    });

    // Toggle Lista/Cards
    const KEY = 'justificativa_view';
    function setView(v) {
        const cards = v === 'cards';
        document.getElementById('jf-lista').style.display = cards ? 'none' : '';
        document.getElementById('jf-cards').style.display = cards ? '' : 'none';
        document.getElementById('jf-btn-lista').classList.toggle('active', !cards);
        document.getElementById('jf-btn-cards').classList.toggle('active', cards);
        try { localStorage.setItem(KEY, v); } catch (e) {}
    }
    document.getElementById('jf-btn-lista').addEventListener('click', function () { setView('lista'); });
    document.getElementById('jf-btn-cards').addEventListener('click', function () { setView('cards'); });
    try { setView(localStorage.getItem(KEY) || 'lista'); } catch (e) {}
})();
</script>
@stop
