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

    /* Modal de detalhes */
    #jfModal .modal-content.jfm { border:0; border-radius:16px; overflow:hidden; box-shadow:0 20px 50px rgba(15,23,42,.28); }
    .jfm-head { display:flex; align-items:flex-start; gap:.7rem; padding:1rem 1.15rem .85rem; background:linear-gradient(135deg,#1e3a8a,#3b82f6); }
    .jfm-avatar { width:44px; height:44px; border-radius:13px; background:rgba(255,255,255,.16); border:1.5px solid rgba(255,255,255,.35); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:.95rem; color:#fff; flex-shrink:0; }
    .jfm-title { margin:0; font-size:1.05rem; font-weight:800; color:#fff; }
    .jfm-sub { font-size:.72rem; color:rgba(255,255,255,.78); margin-top:1px; }
    .jfm-head .close { color:#fff; opacity:.8; margin-left:auto; padding:.2rem .3rem; }
    .jfm-head .badge { font-size:.62rem; font-weight:700; }
    .jfm-periodo { display:flex; align-items:center; gap:.55rem; padding:.7rem 1.15rem; background:#f8faff; border-bottom:1px solid #eef2fa; font-weight:700; color:#1e40af; font-size:.85rem; flex-wrap:wrap; }
    .jfm-periodo .jfm-arrow { color:#93c5fd; }
    .jfm-dias { margin-left:auto; font-size:.6rem; font-weight:800; color:#1d4ed8; background:#dbeafe; border-radius:999px; padding:.2rem .6rem; text-transform:uppercase; letter-spacing:.04em; }
    .jfm-grid { display:grid; grid-template-columns:1fr 1fr; gap:.55rem .9rem; padding:.9rem 1.15rem .2rem; }
    .jfm-item { min-width:0; }
    .jfm-item label { display:block; font-size:.6rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#9ca3af; margin:0 0 1px; }
    .jfm-item span { font-size:.82rem; font-weight:600; color:#1f2937; }
    .jfm-secoes { padding:.3rem 1.15rem 0; }
    .jfm-sec-t { font-size:.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#4f46e5; margin:.7rem 0 .35rem; display:flex; align-items:center; gap:.35rem; }
    .jfm-motivo { font-size:.84rem; color:#374151; white-space:pre-line; background:#f8fafc; border-left:3px solid #4f46e5; border-radius:0 10px 10px 0; padding:.6rem .8rem; }
    .jfm-anexo { display:flex; align-items:center; gap:.6rem; border:1.5px solid #e2e8f0; border-radius:10px; padding:.55rem .75rem; }
    .jfm-anexo i.anx-ic { width:34px; height:34px; border-radius:9px; background:#eff6ff; color:#3b82f6; display:flex; align-items:center; justify-content:center; font-size:.85rem; flex-shrink:0; }
    .jfm-anexo .n { font-size:.8rem; font-weight:700; color:#1f2937; word-break:break-all; }
    .jfm-anexo .t { font-size:.65rem; color:#94a3b8; }
    .jfm-anexo .btn { margin-left:auto; flex-shrink:0; border-radius:8px; }
    .jfm-foot { display:flex; justify-content:flex-end; gap:.45rem; padding:.7rem 1.15rem; border-top:1px solid #f1f5f9; background:#fbfcfe; }
    .jfm-foot .btn { border-radius:999px; font-weight:700; font-size:.8rem; }
    @media (max-width: 576px) {
        .jfm-grid { grid-template-columns:1fr; }
    }
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
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content jfm">
            <div class="jfm-head">
                <div class="jfm-avatar" id="jm-avatar"></div>
                <div style="min-width:0;">
                    <h5 class="jfm-title" id="jm-titulo"></h5>
                    <div class="jfm-sub" id="jm-sub"></div>
                    <div id="jm-badges" class="mt-1"></div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span>&times;</span></button>
            </div>
            <div class="jfm-periodo" id="jm-periodo"></div>
            <div class="jfm-grid" id="jm-info"></div>
            <div class="jfm-secoes" id="jm-secoes"></div>
            <div class="jfm-foot" id="jm-foot"></div>
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
            'dias' => $j->data_inicio->diffInDays($j->data_fim) + 1,
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
        return '<div class="jfm-item"><label>' + label + '</label><span>' + esc(valor || '—') + '</span></div>';
    }
    function iniciais(nome) {
        return String(nome || '?').trim().split(/\s+/).filter(Boolean)
            .slice(0, 2).map(function (s) { return s[0].toUpperCase(); }).join('');
    }
    function abrir(id) {
        const j = JF[id];
        if (!j) { return; }
        document.getElementById('jm-avatar').textContent = iniciais(j.aluno);
        document.getElementById('jm-titulo').textContent = j.aluno;
        document.getElementById('jm-sub').textContent = j.turma + ' · ' + j.escola;
        document.getElementById('jm-badges').innerHTML =
            '<span class="badge badge-' + j.efeito_cor + ' mr-1">' + esc(j.efeito) + '</span>'
            + '<span class="badge badge-light border">' + esc(j.tipo) + '</span>';
        document.getElementById('jm-periodo').innerHTML =
            '<i class="fa fa-calendar-check"></i> ' + esc(j.inicio)
            + ' <i class="fa fa-long-arrow-alt-right jfm-arrow"></i> ' + esc(j.fim)
            + '<span class="jfm-dias">' + j.dias + (j.dias > 1 ? ' dias' : ' dia') + '</span>';
        document.getElementById('jm-info').innerHTML =
            info('Disciplina', j.disciplina) + info('Registrado por', j.registrado_por)
            + info('Registrado em', j.registrado_em);

        let html = '';
        if (j.motivo) {
            html += '<div class="jfm-sec-t"><i class="fa fa-comment-alt"></i> Motivo / observação</div>'
                + '<div class="jfm-motivo">' + esc(j.motivo) + '</div>';
        }
        if (j.anexo) {
            html += '<div class="jfm-sec-t"><i class="fa fa-paperclip"></i> Anexo</div>'
                + '<div class="jfm-anexo"><i class="anx-ic fa fa-file-alt"></i>'
                + '<div style="min-width:0;"><div class="n">' + esc(j.nome_anexo || 'documento') + '</div>'
                + '<div class="t">Documento anexado à justificativa</div></div>'
                + '<a href="' + j.anexo + '" target="_blank" class="btn btn-sm btn-outline-primary">'
                + '<i class="fa fa-download mr-1"></i>Baixar</a></div>';
        }
        if (!j.motivo && !j.anexo) {
            html = '<div class="text-muted small py-2 text-center">Sem observações nem anexo.</div>';
        }
        document.getElementById('jm-secoes').innerHTML = html;
        document.getElementById('jm-foot').innerHTML =
            (j.anexo ? '<a href="' + j.anexo + '" target="_blank" class="btn btn-outline-primary"><i class="fa fa-download mr-1"></i>Baixar documento</a>' : '')
            + '<button type="button" class="btn btn-light" data-dismiss="modal">Fechar</button>';
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
