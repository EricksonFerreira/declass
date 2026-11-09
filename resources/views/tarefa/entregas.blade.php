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

    .al-av { width: 30px; height: 30px; border-radius: 8px; background: linear-gradient(135deg,#2563eb,#7c3aed); color: #fff; font-size: .62rem; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .ind { display: inline-flex; align-items: center; gap: 2px; font-size: .66rem; font-weight: 600; color: #64748b; background: #f1f5f9; border-radius: 5px; padding: 1px 5px; margin-right: 3px; }
    .ind i { font-size: .6rem; }

    .st-sel { border-left-width: 4px !important; font-weight: 600; }
    .st-sel.st-pendente { border-left-color: #94a3b8; }
    .st-sel.st-entregue { border-left-color: #f59e0b; background: #fffbeb; }
    .st-sel.st-avaliada { border-left-color: #22c55e; background: #f0fdf4; }
    tr.st-row-entregue > td { background: #fffdf5; }
    tr.st-row-avaliada > td { background: #f7fdf9; }

    .det-panel { padding: 12px 16px 12px 14px; background: #f8fafc; border-left: 3px solid #3b82f6; }
    .det-bloco { margin-bottom: 10px; }
    .det-bloco:last-child { margin-bottom: 0; }
    .det-lbl { font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 3px; }
    .det-lbl i { margin-right: 3px; }
    .det-txt { font-size: .82rem; background: #fff; border: 1px solid #e5e9f0; border-radius: 8px; padding: 8px 10px; }
    .det-anx { display: inline-flex; align-items: center; gap: 4px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; border-radius: 6px; padding: 3px 9px; font-size: .75rem; margin: 0 4px 4px 0; }
    .det-anx:hover { background: #dbeafe; color: #1e40af; text-decoration: none; }
    .det-resp li { margin-bottom: 3px; }

    .table-entregas thead th { font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: #64748b; border-top: none; }
    .table-entregas td { vertical-align: middle; }
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
@endphp

{{-- Monitoramento: panorama dos envios --}}
<div class="row mb-1" id="entregas-stats">
    <div class="col-md-3 col-6 mb-2">
        <div class="card stat-card mb-0"><div class="card-body">
            <div class="stat-ico" style="background:#2563eb;"><i class="fa fa-paper-plane"></i></div>
            <div><div class="stat-num" data-stat="enviaram">{{ $enviaram }}<small class="text-muted" style="font-size:.75rem;font-weight:400;">/{{ $totalAlunos }}</small></div>
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
                <div class="progress-bar" style="width: {{ $pctEnviaram }}%"></div>
            </div>
            <small class="text-muted text-nowrap">{{ $pctEnviaram }}% da turma enviou</small>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header py-2 d-flex flex-wrap align-items-center">
        <h3 class="card-title text-sm font-weight-bold mb-0">Envios dos alunos</h3>
        <div class="card-tools d-flex align-items-center" style="gap:.4rem;">
            <div class="btn-group btn-group-sm" role="group" id="filtroEnvios">
                <button type="button" class="btn btn-outline-secondary active" data-env="todas">Todas</button>
                <button type="button" class="btn btn-outline-secondary" data-env="enviadas">Só quem enviou</button>
                <button type="button" class="btn btn-outline-secondary" data-env="pendentes">Pendentes</button>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('tarefa.entregas.salvar', $tarefa->id) }}">
        @csrf
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover table-entregas mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:40px;"></th>
                            <th style="width:50px;">#</th>
                            <th>Aluno</th>
                            <th style="width:140px;">Status</th>
                            <th style="width:130px;">Nota</th>
                            <th style="width:150px;">Data entrega</th>
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matriculas as $matricula)
                            @php($e = $entregas->get($matricula->id))
                            @php($temConteudo = $e && ($e->resposta || $e->anexos->isNotEmpty() || $e->respostas->isNotEmpty()))
                            @php($st = $e->status ?? 'pendente')
                            @php($nomeAluno = $matricula->aluno->pessoa->nome ?? 'Aluno #'.$matricula->aluno_id)
                            @php($iniciais = collect(explode(' ', trim($nomeAluno)))->filter()->take(2)->map(fn ($s) => mb_strtoupper(mb_substr($s, 0, 1)))->implode('') ?: 'A')
                            <tr data-envio="{{ $st === 'pendente' ? 'pendente' : 'enviado' }}" class="st-row-{{ $st }}">
                                <td class="text-center">
                                    @if($temConteudo)
                                        <button type="button" class="btn btn-xs btn-outline-secondary" data-toggle="collapse"
                                                data-target="#det-{{ $matricula->id }}" title="Ver o que o aluno enviou">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $matricula->numero_chamada ?? '—' }}</td>
                                <td>
                                    <div class="d-flex align-items-center" style="gap:.55rem;">
                                        <span class="al-av">{{ $iniciais }}</span>
                                        <div>
                                            <div class="font-weight-bold" style="line-height:1.15;">{{ $nomeAluno }}</div>
                                            <small class="text-muted">RA {{ $matricula->aluno->registro_academico ?? '—' }}</small>
                                            @if($temConteudo)
                                                <div class="mt-1">
                                                    @if($e->resposta)<span class="ind" title="Texto enviado"><i class="fa fa-font"></i> texto</span>@endif
                                                    @if($e->anexos->isNotEmpty())<span class="ind" title="Anexos"><i class="fa fa-paperclip"></i> {{ $e->anexos->count() }}</span>@endif
                                                    @if($e->respostas->isNotEmpty())<span class="ind" title="Respostas de questões"><i class="fa fa-list-ol"></i> {{ $e->respostas->count() }}</span>@endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select name="entregas[{{ $matricula->id }}][status]" class="form-control form-control-sm st-sel st-{{ $st }}">
                                        @foreach(\App\Models\Tarefa::STATUS_ENTREGA as $stOpt)
                                            <option value="{{ $stOpt }}" {{ $st === $stOpt ? 'selected' : '' }}>
                                                {{ \App\Models\EntregaTarefa::STATUS[$stOpt] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="entregas[{{ $matricula->id }}][nota]" class="form-control"
                                            step="0.5" min="0" max="{{ $tarefa->nota_maxima ?? 100 }}"
                                            value="{{ $e->nota ?? '' }}" {{ $st !== 'avaliada' ? 'disabled' : '' }}>
                                        <div class="input-group-append">
                                            <span class="input-group-text text-muted" style="font-size:.72rem;">/{{ $tarefa->nota_maxima ?? 100 }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <input type="date" name="entregas[{{ $matricula->id }}][data_entrega]" class="form-control form-control-sm"
                                        value="{{ $e?->data_entrega?->format('Y-m-d') ?? '' }}">
                                </td>
                                <td>
                                    <input type="text" name="entregas[{{ $matricula->id }}][observacoes]" class="form-control form-control-sm"
                                        maxlength="1000" value="{{ $e->observacoes ?? '' }}" placeholder="Feedback ao aluno...">
                                </td>
                            </tr>
                            @if($temConteudo)
                                <tr class="collapse" id="det-{{ $matricula->id }}">
                                    <td colspan="7" class="p-0 border-top-0">
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
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Turma sem alunos matriculados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
            <small class="text-muted">{{ $totalAlunos }} aluno(s) · as alterações de status, nota e observações são salvas de uma vez.</small>
            <div class="d-flex" style="gap:.4rem;">
                <a href="{{ route('tarefa.index') }}" class="btn btn-secondary btn-sm">Voltar</a>
                @if($matriculas->isNotEmpty())
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save mr-1"></i>Salvar entregas</button>
                @endif
            </div>
        </div>
    </form>
</div>
@stop

@push('js')
<script>
// Habilita campo de nota apenas quando status = avaliada;
// digitar uma nota ja marca "Avaliada" automaticamente.
document.querySelectorAll('select.st-sel[name^="entregas"]').forEach(sel => {
    const row = sel.closest('tr');
    const nota = row.querySelector('input[name$="[nota]"]');
    function syncNota() {
        nota.disabled = sel.value !== 'avaliada';
        if (sel.value !== 'avaliada') nota.value = '';
        // cor do select + tinta da linha + flag do filtro rapido
        sel.classList.remove('st-pendente', 'st-entregue', 'st-avaliada');
        sel.classList.add('st-' + sel.value);
        row.classList.remove('st-row-pendente', 'st-row-entregue', 'st-row-avaliada');
        row.classList.add('st-row-' + sel.value);
        row.dataset.envio = sel.value === 'pendente' ? 'pendente' : 'enviado';
    }
    sel.addEventListener('change', syncNota);
    nota.addEventListener('input', () => {
        if (nota.value !== '' && sel.value !== 'avaliada') {
            sel.value = 'avaliada';
            sel.dispatchEvent(new Event('change'));
        }
    });
});

// Filtro rapido: todas / so quem enviou / pendentes.
document.querySelectorAll('#filtroEnvios [data-env]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('#filtroEnvios [data-env]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const modo = btn.dataset.env;
        document.querySelectorAll('tr[data-envio]').forEach(tr => {
            const enviou = tr.dataset.envio === 'enviado';
            tr.style.display =
                modo === 'todas' ? '' :
                modo === 'enviadas' ? (enviou ? '' : 'none') :
                (enviou ? 'none' : '');
            // linha de detalhe (collapse) acompanha a principal
            const det = tr.nextElementSibling;
            if (det && det.classList.contains('collapse')) det.style.display = tr.style.display;
        });
    });
});
</script>
@endpush
