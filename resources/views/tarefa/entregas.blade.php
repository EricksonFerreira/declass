@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Entregas — '.$tarefa->titulo" :subtitle="$tarefa->turma->nome.' · entrega até '.$tarefa->data_entrega->format('d/m/Y')" />
@stop

@push('css')
<style>
    .stat-card { border: none; border-radius: 12px; box-shadow: 0 1px 4px rgba(11,18,32,.06); }
    .stat-card .card-body { display: flex; align-items: center; gap: .7rem; padding: .6rem .8rem; }
    .stat-ico { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .95rem; color: #fff; flex-shrink: 0; }
    .stat-num { font-size: 1.25rem; font-weight: 800; line-height: 1; }
    .stat-lbl { font-size: .68rem; color: #64748b; }
    .entregas-progress { height: 6px; border-radius: 99px; background: #e9edf3; }
    .entregas-progress .progress-bar { background: linear-gradient(90deg,#2563eb,#22c55e); }

    .al-av { width: 34px; height: 34px; border-radius: 9px; background: linear-gradient(135deg,#2563eb,#7c3aed); color: #fff; font-size: .68rem; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .ind { display: inline-flex; align-items: center; gap: 2px; font-size: .66rem; font-weight: 600; color: #64748b; background: #f1f5f9; border-radius: 5px; padding: 1px 5px; margin-right: 3px; }
    .ind i { font-size: .6rem; }

    /* Card do aluno — acento lateral por status */
    .ent-card { background:#fff; border:1px solid #e8edf4; border-left:4px solid #94a3b8; border-radius:12px; padding:.75rem .85rem; height:100%; display:flex; flex-direction:column; box-shadow:0 1px 4px rgba(11,18,32,.05); }
    .ent-card.st-entregue { border-left-color:#f59e0b; background:#fffdf6; }
    .ent-card.st-avaliada { border-left-color:#22c55e; background:#f7fdf9; }
    .ent-head { display:flex; gap:.6rem; align-items:flex-start; }
    .ent-id { flex:1; min-width:0; }
    .ent-nome { font-size:.9rem; font-weight:700; color:#1f2937; line-height:1.2; }
    .ent-meta { font-size:.7rem; color:#94a3b8; }
    .ent-eye { border:1px solid #e2e8f0; background:#fff; color:#64748b; border-radius:8px; width:32px; height:32px; font-size:.8rem; flex-shrink:0; }
    .ent-eye:hover { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }

    /* Pills de status — alvo de toque grande */
    .ent-pills { display:flex; gap:.35rem; margin:.65rem 0 .55rem; }
    .ent-pills .p { flex:1; border:1.5px solid #e2e8f0; border-radius:9px; padding:.45rem .2rem; font-size:.73rem; font-weight:700; background:#fff; color:#64748b; text-align:center; }
    .ent-pills .p.p-pendente.active { background:#f1f5f9; border-color:#94a3b8; color:#475569; }
    .ent-pills .p.p-entregue.active { background:#fffbeb; border-color:#f59e0b; color:#b45309; }
    .ent-pills .p.p-avaliada.active { background:#f0fdf4; border-color:#22c55e; color:#15803d; }
    .ent-pills .p:active { transform:scale(.97); }

    .ent-grid { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; }
    .ent-campo label { font-size:.64rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:#94a3b8; margin-bottom:2px; display:block; }
    .ent-nota-wrap { display:flex; align-items:center; gap:.3rem; }
    .ent-nota-wrap .mx { font-size:.7rem; color:#94a3b8; white-space:nowrap; }
    .ent-obs { margin-top:.5rem; }
    .ent-campos-extra { transition:opacity .15s; }
    .ent-card.st-bloq-nota .ent-campos-extra { opacity:.45; }

    .det-panel { margin-top:.6rem; padding:10px 12px 10px 11px; background:#f8fafc; border-radius:10px; border-left:3px solid #3b82f6; }
    .det-bloco { margin-bottom:10px; }
    .det-bloco:last-child { margin-bottom:0; }
    .det-lbl { font-size:.65rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#64748b; margin-bottom:3px; }
    .det-lbl i { margin-right:3px; }
    .det-txt { font-size:.82rem; background:#fff; border:1px solid #e5e9f0; border-radius:8px; padding:8px 10px; }
    .det-anx { display:inline-flex; align-items:center; gap:4px; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; border-radius:6px; padding:3px 9px; font-size:.75rem; margin:0 4px 4px 0; }
    .det-anx:hover { background:#dbeafe; color:#1e40af; text-decoration:none; }
    .det-resp li { margin-bottom:3px; }

    /* Acoes em lote */
    .ent-bulk { display:flex; flex-wrap:wrap; gap:.4rem; align-items:center; }
    .ent-bulk-nota { display:flex; gap:.35rem; align-items:center; margin-left:auto; }
    .ent-bulk-nota input { width:90px; }
    @media (max-width:575.98px) {
        .ent-bulk-nota { margin-left:0; width:100%; }
        .ent-bulk-nota input { flex:1; width:auto; }
        .ent-bulk > .btn { flex:1; }
    }

    /* Barra de salvar sempre visivel enquanto o card estiver na tela */
    .ent-savebar { position:sticky; bottom:0; z-index:20; background:#fff; margin:0 -1px -1px; border-top:1px solid #e8edf4; padding:.65rem .85rem; display:flex; align-items:center; gap:.7rem; border-radius:0 0 12px 12px; box-shadow:0 -6px 16px rgba(11,18,32,.08); }
    .ent-savebar .sb-info { font-size:.75rem; color:#64748b; min-width:0; }
    .ent-savebar .sb-info strong { color:#1f2937; }
    .ent-savebar .sb-bar { height:5px; border-radius:99px; background:#e9edf3; width:110px; max-width:100%; margin-top:3px; overflow:hidden; }
    .ent-savebar .sb-bar > div { height:100%; border-radius:99px; background:linear-gradient(90deg,#22c55e,#16a34a); width:0; transition:width .25s; }
    .ent-savebar .btn { margin-left:auto; flex-shrink:0; }
    @media (max-width:575.98px) {
        .ent-savebar { padding:.6rem .6rem; }
        .ent-savebar .btn { padding:.5rem .9rem; font-size:.85rem; }
    }
</style>
@endpush

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@php
    $totalAlunos = $matriculas->count();
    $enviaram = $matriculas->filter(fn ($m) => ($entregas->get($m->id)?->status ?? '') !== 'pendente' && ($entregas->get($m->id)?->status ?? '') !== '')->count();
    $avaliadas = $matriculas->filter(fn ($m) => ($entregas->get($m->id)?->status ?? '') === 'avaliada')->count();
    $aguardando = $enviaram - $avaliadas;
    $pendentes = $totalAlunos - $enviaram;
    $pctEnviaram = $totalAlunos ? round($enviaram / $totalAlunos * 100) : 0;
    $statusOpcoes = \App\Models\EntregaTarefa::STATUS;
@endphp

{{-- Monitoramento: panorama dos envios --}}
<div class="row mb-1" id="entregas-stats">
    <div class="col-md-3 col-6 mb-2">
        <div class="card stat-card mb-0"><div class="card-body">
            <div class="stat-ico" style="background:#2563eb;"><i class="fa fa-paper-plane"></i></div>
            <div><div class="stat-num"><span data-stat="enviaram">{{ $enviaram }}</span><small class="text-muted" style="font-size:.75rem;font-weight:400;">/{{ $totalAlunos }}</small></div>
            <div class="stat-lbl">Enviaram</div></div>
        </div></div>
    </div>
    <div class="col-md-3 col-6 mb-2">
        <div class="card stat-card mb-0"><div class="card-body">
            <div class="stat-ico" style="background:#f59e0b;"><i class="fa fa-hourglass-half"></i></div>
            <div><div class="stat-num" data-stat="aguardando">{{ $aguardando }}</div>
            <div class="stat-lbl">Aguardando correção</div></div>
        </div></div>
    </div>
    <div class="col-md-3 col-6 mb-2">
        <div class="card stat-card mb-0"><div class="card-body">
            <div class="stat-ico" style="background:#22c55e;"><i class="fa fa-check-circle"></i></div>
            <div><div class="stat-num" data-stat="avaliadas">{{ $avaliadas }}</div>
            <div class="stat-lbl">Avaliadas</div></div>
        </div></div>
    </div>
    <div class="col-md-3 col-6 mb-2">
        <div class="card stat-card mb-0"><div class="card-body">
            <div class="stat-ico" style="background:#ef4444;"><i class="fa fa-clock"></i></div>
            <div><div class="stat-num" data-stat="pendentes">{{ $pendentes }}</div>
            <div class="stat-lbl">Pendentes</div></div>
        </div></div>
    </div>
</div>
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center" style="gap:.6rem;">
            <div class="progress entregas-progress flex-grow-1">
                <div class="progress-bar" id="prog-envios" style="width: {{ $pctEnviaram }}%"></div>
            </div>
            <small class="text-muted text-nowrap"><span id="prog-pct">{{ $pctEnviaram }}</span>% da turma enviou</small>
        </div>
    </div>
</div>

<div class="card" style="border-radius:12px;">
    <div class="card-header py-2">
        <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
            <h3 class="card-title text-sm font-weight-bold mb-0 mr-1">Envios dos alunos</h3>
            <div class="btn-group btn-group-sm" role="group" id="filtroEnvios">
                <button type="button" class="btn btn-outline-secondary active" data-env="todas">Todas</button>
                <button type="button" class="btn btn-outline-secondary" data-env="entregue">A corrigir</button>
                <button type="button" class="btn btn-outline-secondary" data-env="pendente">Pendentes</button>
                <button type="button" class="btn btn-outline-secondary" data-env="avaliada">Avaliadas</button>
            </div>
        </div>
        <div class="ent-bulk mt-2">
            <button type="button" class="btn btn-outline-success btn-sm" id="btnTodosEntregaram">
                <i class="fa fa-inbox mr-1"></i>Todos entregaram
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" id="btnTodosAvaliados">
                <i class="fa fa-check-double mr-1"></i>Todos avaliados
            </button>
            <div class="ent-bulk-nota">
                <input type="number" id="notaLote" class="form-control form-control-sm" step="0.5" min="0"
                       max="{{ $tarefa->nota_maxima ?? 100 }}" placeholder="Nota /{{ $tarefa->nota_maxima ?? 100 }}">
                <button type="button" class="btn btn-primary btn-sm text-nowrap" id="btnNotaLote">Aplicar a todos</button>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('tarefa.entregas.salvar', $tarefa->id) }}">
        @csrf
        <div class="card-body">
            <div class="row">
                @forelse($matriculas as $matricula)
                    @php($e = $entregas->get($matricula->id))
                    @php($temConteudo = $e && ($e->resposta || $e->anexos->isNotEmpty() || $e->respostas->isNotEmpty()))
                    @php($st = $e->status ?? 'pendente')
                    @php($nomeAluno = $matricula->aluno->pessoa->nome ?? 'Aluno #'.$matricula->aluno_id)
                    @php($iniciais = collect(explode(' ', trim($nomeAluno)))->filter()->take(2)->map(fn ($s) => mb_strtoupper(mb_substr($s, 0, 1)))->implode('') ?: 'A')
                    <div class="col-12 col-md-6 col-xl-4 mb-3 ent-col" data-envio="{{ $st }}">
                        <div class="ent-card st-{{ $st }}">
                            <div class="ent-head">
                                <span class="al-av">{{ $iniciais }}</span>
                                <div class="ent-id">
                                    <div class="ent-nome">{{ $nomeAluno }}</div>
                                    <div class="ent-meta">nº {{ $matricula->numero_chamada ?? '—' }} · RA {{ $matricula->aluno->registro_academico ?? '—' }}</div>
                                    @if($temConteudo)
                                        <div class="mt-1">
                                            @if($e->resposta)<span class="ind" title="Texto enviado"><i class="fa fa-font"></i> texto</span>@endif
                                            @if($e->anexos->isNotEmpty())<span class="ind" title="Anexos"><i class="fa fa-paperclip"></i> {{ $e->anexos->count() }}</span>@endif
                                            @if($e->respostas->isNotEmpty())<span class="ind" title="Respostas de questões"><i class="fa fa-list-ol"></i> {{ $e->respostas->count() }}</span>@endif
                                        </div>
                                    @endif
                                </div>
                                @if($temConteudo)
                                    <button type="button" class="ent-eye" data-toggle="collapse"
                                            data-target="#det-{{ $matricula->id }}" title="Ver o que o aluno enviou">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                @endif
                            </div>

                            <input type="hidden" name="entregas[{{ $matricula->id }}][status]" class="ent-st" value="{{ $st }}">
                            <div class="ent-pills">
                                @foreach($statusOpcoes as $stOpt => $stRotulo)
                                    <button type="button" class="p p-{{ $stOpt }} {{ $st === $stOpt ? 'active' : '' }}" data-st="{{ $stOpt }}">{{ $stRotulo }}</button>
                                @endforeach
                            </div>

                            <div class="ent-grid">
                                <div class="ent-campo">
                                    <label>Nota</label>
                                    <div class="ent-nota-wrap">
                                        <input type="number" name="entregas[{{ $matricula->id }}][nota]" class="form-control form-control-sm ent-nota"
                                            step="0.5" min="0" max="{{ $tarefa->nota_maxima ?? 100 }}"
                                            value="{{ $e->nota ?? '' }}" {{ $st !== 'avaliada' ? 'disabled' : '' }}>
                                        <span class="mx">/{{ $tarefa->nota_maxima ?? 100 }}</span>
                                    </div>
                                </div>
                                <div class="ent-campo">
                                    <label>Data entrega</label>
                                    <input type="date" name="entregas[{{ $matricula->id }}][data_entrega]" class="form-control form-control-sm"
                                        value="{{ $e?->data_entrega?->format('Y-m-d') ?? '' }}">
                                </div>
                            </div>
                            <input type="text" name="entregas[{{ $matricula->id }}][observacoes]" class="form-control form-control-sm ent-obs"
                                maxlength="1000" value="{{ $e->observacoes ?? '' }}" placeholder="Feedback ao aluno...">

                            @if($temConteudo)
                                <div class="collapse" id="det-{{ $matricula->id }}">
                                    <div class="det-panel">
                                        @if($e->resposta)
                                            <div class="det-bloco">
                                                <div class="det-lbl"><i class="fa fa-align-left"></i>Texto enviado</div>
                                                <div class="det-txt">{!! nl2br(e($e->resposta)) !!}</div>
                                            </div>
                                        @endif
                                        @if($e->anexos->isNotEmpty())
                                            <div class="det-bloco">
                                                <div class="det-lbl"><i class="fa fa-paperclip"></i>Anexos</div>
                                                <div>
                                                    @foreach($e->anexos as $anexo)
                                                        <a href="{{ $anexo->url() }}" target="_blank" class="det-anx">
                                                            <i class="fa fa-file"></i> {{ $anexo->nome_original ?? 'Arquivo' }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        @if($e->respostas->isNotEmpty())
                                            <div class="det-bloco">
                                                <div class="det-lbl"><i class="fa fa-list-ol"></i>Respostas das questões</div>
                                                <ul class="small mb-0 pl-3 det-resp">
                                                    @foreach($e->respostas->sortBy('questao.ordem') as $resp)
                                                        <li>
                                                            <strong>{{ $resp->questao->enunciado ?? 'Questão' }}</strong>:
                                                            {{ $resp->resposta !== '' ? $resp->resposta : '—' }}
                                                            @if($resp->correta === true)<span class="badge badge-success">correta</span>@endif
                                                            @if($resp->correta === false)<span class="badge badge-danger">incorreta</span>@endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-4">Turma sem alunos matriculados.</div>
                @endforelse
            </div>
        </div>

        <div class="ent-savebar">
            <div class="sb-info">
                <strong><span id="sb-aval">{{ $avaliadas }}</span>/{{ $totalAlunos }}</strong> avaliadas
                <span class="d-none d-sm-inline">· alterações salvas de uma vez</span>
                <div class="sb-bar"><div id="sb-bar-fill" style="width:{{ $totalAlunos ? round($avaliadas / $totalAlunos * 100) : 0 }}%"></div></div>
            </div>
            <a href="{{ route('tarefa.index') }}" class="btn btn-light btn-sm d-none d-sm-inline-block">Voltar</a>
            @if($matriculas->isNotEmpty())
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save mr-1"></i>Salvar entregas</button>
            @endif
        </div>
    </form>
</div>
@stop

@push('js')
<script>
(function () {
    const TOTAL = {{ $totalAlunos }};

    function definirStatus(card, st, opts) {
        opts = opts || {};
        card.querySelector('.ent-st').value = st;
        card.dataset.envio = st;
        card.classList.remove('st-pendente', 'st-entregue', 'st-avaliada');
        card.classList.add('st-' + st);
        card.querySelectorAll('.ent-pills .p').forEach(function (p) {
            p.classList.toggle('active', p.dataset.st === st);
        });
        const nota = card.querySelector('.ent-nota');
        nota.disabled = st !== 'avaliada';
        if (st !== 'avaliada') { nota.value = ''; }
        if (!opts.semContar) { atualizarContadores(); }
    }

    function atualizarContadores() {
        let enviaram = 0, avaliadas = 0;
        document.querySelectorAll('.ent-col').forEach(function (col) {
            const st = col.querySelector('.ent-st').value;
            if (st !== 'pendente') { enviaram++; }
            if (st === 'avaliada') { avaliadas++; }
        });
        const pendentes = TOTAL - enviaram;
        const set = function (k, v) {
            const el = document.querySelector('[data-stat="' + k + '"]');
            if (el) { el.textContent = v; }
        };
        set('enviaram', enviaram);
        set('aguardando', enviaram - avaliadas);
        set('avaliadas', avaliadas);
        set('pendentes', pendentes);
        const pct = TOTAL ? Math.round(enviaram / TOTAL * 100) : 0;
        document.getElementById('prog-envios').style.width = pct + '%';
        document.getElementById('prog-pct').textContent = pct;
        document.getElementById('sb-aval').textContent = avaliadas;
        document.getElementById('sb-bar-fill').style.width = (TOTAL ? Math.round(avaliadas / TOTAL * 100) : 0) + '%';
    }

    // Pills de status + nota auto-marca "Avaliada"
    document.querySelectorAll('.ent-card').forEach(function (card) {
        card.querySelectorAll('.ent-pills .p').forEach(function (p) {
            p.addEventListener('click', function () { definirStatus(card, p.dataset.st); });
        });
        const nota = card.querySelector('.ent-nota');
        nota.addEventListener('input', function () {
            if (nota.value !== '' && card.querySelector('.ent-st').value !== 'avaliada') {
                definirStatus(card, 'avaliada');
            }
        });
    });

    // Acoes em lote
    document.getElementById('btnTodosEntregaram').addEventListener('click', function () {
        document.querySelectorAll('.ent-card').forEach(function (card) {
            if (card.querySelector('.ent-st').value === 'pendente') {
                definirStatus(card, 'entregue', { semContar: true });
            }
        });
        atualizarContadores();
    });
    document.getElementById('btnTodosAvaliados').addEventListener('click', function () {
        document.querySelectorAll('.ent-card').forEach(function (card) {
            definirStatus(card, 'avaliada', { semContar: true });
        });
        atualizarContadores();
    });
    document.getElementById('btnNotaLote').addEventListener('click', function () {
        const v = document.getElementById('notaLote').value;
        if (v === '') {
            document.getElementById('notaLote').classList.add('is-invalid');
            return;
        }
        document.getElementById('notaLote').classList.remove('is-invalid');
        document.querySelectorAll('.ent-card').forEach(function (card) {
            definirStatus(card, 'avaliada', { semContar: true });
            card.querySelector('.ent-nota').value = v;
        });
        atualizarContadores();
    });
    document.getElementById('notaLote').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnNotaLote').click(); }
    });

    // Filtro rapido por status
    document.querySelectorAll('#filtroEnvios [data-env]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#filtroEnvios [data-env]').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            const modo = btn.dataset.env;
            document.querySelectorAll('.ent-col').forEach(function (col) {
                col.style.display = (modo === 'todas' || col.dataset.envio === modo) ? '' : 'none';
            });
        });
    });
})();
</script>
@endpush
