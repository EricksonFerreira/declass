@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Grade horária — '.$turma->nome" :subtitle="($turma->escola->nome ?? '').' · '.(\App\Models\Turma::TURNOS[$turma->turno ?? ''] ?? '')" />
@stop

@section('content')
@php
    $rotuloTurno = \App\Models\Turma::TURNOS[$turma->turno ?? ''] ?? $turma->turno;
    $disciplinasJson = $disciplinas->map(fn ($d) => [
        'id' => $d['id'], 'nome' => $d['nome'], 'sigla' => $d['sigla'],
        'carga' => $d['carga'], 'usadas' => $d['usadas'], 'restante' => $d['restante'],
    ])->values();
    $professoresJson = $professores->map(fn ($p) => [
        'id' => $p['id'], 'nome' => $p['nome'], 'disciplina_id' => $p['disciplina_id'],
    ])->values();
    $coresJson = [];
    foreach ($disciplinas as $d) { $coresJson[$d['id']] = \App\Services\TurmaGradeService::corDaDisciplina($d['id']); }
    $aulasJson = $aulas->map(fn ($a) => [
        'disciplina_id' => $a->disciplina_id,
        'user_id' => $a->user_id,
        'sala' => $a->sala,
    ])->all();
@endphp

<style>
    .grade-horarios .intervalo-cell { background: #f1f5f9; }
    .grade-horarios .intervalo-tag {
        display: inline-block;
        font-size: .68rem;
        font-style: italic;
        color: #64748b;
        border: 1px dashed #cbd5e1;
        border-radius: .8rem;
        padding: .05rem .5rem;
        background: #fff;
    }
    .grade-horarios .celula-aula { min-height: 52px; border-radius: 3px; padding: 4px 5px; }
    .grade-horarios .celula-aula.vazia { border: 1px dashed #cbd5e1; background: #f8fafc; }
    .card-header[data-toggle="modal"]:hover { background: #f4f6f9; }
    .gd-disc-table thead th { font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; color:#6b7280; border-top:0; }
    .gd-disc-table tbody tr { transition:background .12s; }
    .gd-disc-table tbody tr:hover { background:#f8fafc; }
    .gd-disc-chip { display:inline-flex; align-items:center; justify-content:center; min-width:46px; padding:.25em .5em; border-radius:6px; font-size:.72rem; font-weight:700; letter-spacing:.03em; }
    .gd-disc-progress { height:7px; border-radius:99px; background:#e5e7eb; }
    .gd-disc-rest { font-size:.85rem; min-width:34px; padding:.4em .6em; }
    .gd-disc-rest.badge-danger { box-shadow:0 0 0 3px rgba(220,38,38,.12); }
    .gd-picker { display:flex; flex-wrap:wrap; gap:.4rem; max-height:220px; overflow-y:auto; }
    .gd-pick { display:flex; align-items:center; gap:.4rem; padding:.35rem .6rem; border-radius:8px; border:1.5px solid #e5e7eb; background:#fff; font-size:.8rem; cursor:pointer; transition:all .12s; }
    .gd-pick:hover { border-color:#94a3b8; background:#f8fafc; }
    .gd-pick.selecionada { background:var(--pb, #eff6ff); border-color:var(--pt, #3b82f6); box-shadow:0 0 0 3px color-mix(in srgb, var(--pt, #3b82f6) 18%, transparent); font-weight:700; }
    .gd-pick-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
    .gd-pick-uso { font-size:.7rem; color:#94a3b8; margin-left:auto; }
</style>

<div class="row">
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">
                    <i class="fas fa-calendar-week"></i> Semana letiva
                    @can('turma.grade.celula.store')
                        <small class="text-muted font-weight-normal">— clique numa aula para editar</small>
                    @else
                        <small class="text-muted font-weight-normal">— visualização</small>
                    @endcan
                </span>
                <div>
                    @can('turma.grade.pdf')
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('turma.grade.pdf', [$turma->id, 'exibir' => 'disciplina']) }}" class="btn btn-outline-danger" title="PDF só com disciplinas"><i class="fa fa-file-pdf"></i> PDF</a>
                            <a href="{{ route('turma.grade.pdf', [$turma->id, 'exibir' => 'professor']) }}" class="btn btn-outline-danger" title="PDF com professor"><i class="fa fa-user-graduate"></i></a>
                        </div>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                @if(!$temMatriz)
                    <div class="text-center py-5">
                        <i class="fas fa-clock fa-3x text-muted mb-3 d-block"></i>
                        <h5>A turma ainda não tem uma grade de horário configurada</h5>
                        <p class="text-muted">Vincule um horário no cadastro da turma ou crie um novo na Matriz de Horários.</p>
                        @can('horario_matriz.create')
                            <a href="{{ route('horario_matriz.create', $turma->escola_id) }}" class="btn btn-primary">
                                <i class="fa fa-cog"></i> Criar horário na Matriz
                            </a>
                        @endcan
                        @can('turma.update')
                            <a href="{{ route('turma.edit', $turma->id) }}" class="btn btn-outline-primary">
                                <i class="fa fa-link"></i> Vincular horário na turma
                            </a>
                        @endcan
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 grade-horarios" id="grade-tabela">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:110px">Horário</th>
                                    @foreach($dias as $dia)
                                        <th class="text-center">{{ $diasLabels[$dia] ?? $dia }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ordens as $ordem)
                                    <tr>
                                        <td class="text-center font-weight-bold bg-light">
                                            {{ $ordem }}ª aula
                                            @php
                                                $horarioRef = '';
                                                foreach ($dias as $d) {
                                                    $s = $slots->get($d)?->get($ordem);
                                                    if ($s) { $horarioRef = substr((string)$s->hora_inicio,0,5).'–'.substr((string)$s->hora_fim,0,5); break; }
                                                }
                                            @endphp
                                            <br><small class="text-muted font-weight-normal">{{ $horarioRef }}</small>
                                        </td>
                                        @foreach($dias as $dia)
                                            @php
                                                $slot = $slots->get($dia)?->get($ordem);
                                                $aula = $aulas->get($dia.'-'.$ordem);
                                                $cor = $aula ? ($coresJson[$aula->disciplina_id] ?? null) : null;
                                            @endphp
                                            <td class="p-1 text-center"
                                                @if(!$slot) style="background:#f8fafc;" @endif>
                                                @if($slot)
                                                    <div class="celula-aula {{ $aula ? '' : 'vazia' }}"
                                                         data-dia="{{ $dia }}" data-ordem="{{ $ordem }}"
                                                         @if($aula) style="background:{{ $cor['bg'] ?? '#e2e8f0' }};border-left:4px solid {{ $cor['tx'] ?? '#64748b' }};" @endif>
                                                        @if($aula)
                                                            <div class="font-weight-bold" style="color:{{ $cor['tx'] ?? '#1e293b' }};font-size:.82rem;">
                                                                {{ $aula->disciplina?->sigla ?: $aula->disciplina?->nome }}
                                                            </div>
                                                            <div style="font-size:.72rem;color:{{ $cor['tx'] ?? '#475569' }};">
                                                                {{ $aula->user?->nome ? \Illuminate\Support\Str::limit($aula->user->nome, 18) : '' }}
                                                            </div>
                                                            @if($aula->sala)<div style="font-size:.68rem;color:#64748b;"><i class="fa fa-door-open"></i> {{ $aula->sala }}</div>@endif
                                                        @else
                                                            <i class="fa fa-plus text-muted"></i>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted" style="font-size:.75rem;">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                    @php
                                        $temIntervalo = collect($intervalos ?? [])->contains(fn ($porDia) => isset($porDia[$ordem]));
                                        $ivPrimeiro = $temIntervalo ? collect($intervalos)->map(fn ($porDia) => $porDia[$ordem] ?? null)->filter()->first() : null;
                                    @endphp
                                    @if($temIntervalo)
                                        <tr class="intervalo-row">
                                            <td class="text-center font-weight-bold bg-light" style="font-size:.8rem;">
                                                <i class="fa fa-coffee text-muted"></i> Intervalo
                                                @if($ivPrimeiro)<br><small class="text-muted font-weight-normal">{{ $ivPrimeiro['inicio'] }}–{{ $ivPrimeiro['fim'] }}</small>@endif
                                            </td>
                                            @foreach($dias as $dia)
                                                <td class="text-center intervalo-cell">
                                                    @if(isset($intervalos[$dia][$ordem]))<span class="intervalo-tag">Intervalo</span>@endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-muted" style="font-size:.78rem;">
                        <i class="fas fa-info-circle"></i>
                        Disciplinas coloridas conforme a grade; passe o mouse para ver detalhes. Células tracejadas estão livres.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-header font-weight-bold" data-toggle="modal" data-target="#modal-disciplinas" style="cursor:pointer;">
                <i class="fas fa-book text-primary"></i> Disciplinas ofertadas
                <span class="badge badge-light float-right">{{ $disciplinas->count() }}</span>
                <i class="fas fa-chevron-right float-right mr-2 text-muted" style="margin-top:2px;"></i>
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold" data-toggle="modal" data-target="#modal-professores" style="cursor:pointer;">
                <i class="fas fa-user-tie text-primary"></i> Professores na turma
                <span class="badge badge-light float-right">{{ $professores->count() }}</span>
                <i class="fas fa-chevron-right float-right mr-2 text-muted" style="margin-top:2px;"></i>
            </div>
        </div>
    </div>
</div>

{{-- Modal: disciplinas ofertadas --}}
<div class="modal fade" id="modal-disciplinas" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-book text-primary"></i> Disciplinas ofertadas — {{ $turma->nome }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                <table class="table mb-0 gd-disc-table">
                    <thead>
                        <tr>
                            <th>Disciplina</th>
                            <th style="width:190px;">Aulas na grade</th>
                            <th class="text-center" style="width:90px;">Restam</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-restantes">
                        @forelse($disciplinas as $d)
                            @php
                                $pct = $d['carga'] ? min(100, round($d['usadas'] * 100 / $d['carga'])) : null;
                                $corBarra = $d['carga'] ? ($d['usadas'] >= $d['carga'] ? '#10b981' : '#3b82f6') : '#94a3b8';
                            @endphp
                            <tr data-disciplina="{{ $d['id'] }}">
                                <td class="align-middle">
                                    <div class="d-flex align-items-center" style="gap:.6rem;">
                                        <span class="gd-disc-chip" style="background:{{ $coresJson[$d['id']]['bg'] }};color:{{ $coresJson[$d['id']]['tx'] }};">{{ $d['sigla'] ?: \Illuminate\Support\Str::limit($d['nome'], 12) }}</span>
                                        <span class="font-weight-bold">{{ $d['nome'] }}</span>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center" style="gap:.5rem;">
                                        <div class="progress flex-grow-1 gd-disc-progress">
                                            <div class="progress-bar" role="progressbar" style="width:{{ $pct ?? 0 }}%;background:{{ $corBarra }};"></div>
                                        </div>
                                        <small class="text-muted text-nowrap">{{ $d['usadas'] }}/{{ $d['carga'] ?: '∞' }}</small>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="gd-disc-rest badge restante{{ $d['restante'] > 0 ? ' badge-danger' : ' badge-light border' }}" data-id="{{ $d['id'] }}">{{ $d['restante'] ?? '∞' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Nenhuma disciplina ofertada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            @if($turma->disciplinas->isEmpty())
                <div class="modal-footer">
                    <a href="{{ route('turma.show', $turma->id) }}" class="btn btn-sm btn-primary">Ofertar disciplinas na turma</a>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal: professores na turma --}}
<div class="modal fade" id="modal-professores" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-user-tie text-primary"></i> Professores na turma — {{ $turma->nome }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                @if($professores->isNotEmpty())
                    <ul class="list-group list-group-flush">
                        @foreach($professores as $p)
                            <li class="list-group-item py-2" style="font-size:.9rem;">
                                <i class="fas fa-user-tie text-muted"></i> {{ $p['nome'] }}
                                <small class="text-muted d-block">{{ $p['disciplina_id'] ? ($disciplinas->firstWhere('id', $p['disciplina_id'])['nome'] ?? '—') : 'Toda a turma' }}</small>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted text-center py-4 mb-0">Nenhum professor atribuído a esta turma.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal da celula (somente para quem pode editar a grade) --}}
@can('turma.grade.celula.store')
<div class="modal fade" id="modal-celula" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-celula">
                @csrf
                <input type="hidden" name="dia_semana" id="cel-dia">
                <input type="hidden" name="ordem" id="cel-ordem">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="cel-titulo">Editar aula</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Disciplina <span class="text-danger">*</span></label>
                        <input type="hidden" name="disciplina_id" id="cel-disciplina">
                        <div id="cel-disc-lista" class="gd-picker">
                            @foreach($disciplinas as $d)
                                <button type="button" class="gd-pick" data-id="{{ $d['id'] }}" data-restante="{{ $d['restante'] ?? '' }}" data-bg="{{ $coresJson[$d['id']]['bg'] }}" data-tx="{{ $coresJson[$d['id']]['tx'] }}">
                                    <span class="gd-pick-dot" style="background:{{ $coresJson[$d['id']]['tx'] }};"></span>
                                    <span class="gd-pick-nome">{{ $d['nome'] }}</span>
                                    <span class="gd-pick-uso">{{ $d['carga'] ? $d['usadas'].'/'.$d['carga'] : '∞' }}</span>
                                </button>
                            @endforeach
                            @if($disciplinas->isEmpty())
                                <p class="text-muted text-center py-3 mb-0 w-100">Nenhuma disciplina ofertada.</p>
                            @endif
                        </div>
                        <small class="text-danger" id="cel-erro-disciplina"></small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Professor <small class="text-muted">(opcional)</small></label>
                        <select name="user_id" id="cel-professor" class="form-control">
                            <option value="">Sem professor</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Sala <small class="text-muted">(opcional)</small></label>
                        <input type="text" name="sala" id="cel-sala" class="form-control" maxlength="40" placeholder="Ex.: 12">
                        <small class="text-danger" id="cel-erro-sala"></small>
                    </div>
                    <div class="alert alert-danger mt-3 mb-0 d-none" id="cel-erro-geral"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger mr-auto" id="btn-limpar" style="display:none;">
                        <i class="fa fa-eraser"></i> Limpar célula
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-salvar-celula"><i class="fa fa-check"></i> Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@stop

@section('js')
@can('turma.grade.celula.store')
<script>
(function () {
    var disciplinas = @json($disciplinasJson);
    var professores = @json($professoresJson);
    var cores = @json($coresJson);
    var aulasMapa = @json($aulasJson);
    var diasLabels = @json($diasLabels);
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var storeUrl = @json(route('turma.grade.celula.store', $turma->id));
    var destroyUrl = @json(route('turma.grade.celula.destroy', $turma->id));

    var modal = document.getElementById('modal-celula');
    var $ = window.jQuery;
    var celDia, celOrdem, celAtual = null;

    function professoresDa(disciplinaId) {
        return professores.filter(function (p) {
            return !p.disciplina_id || p.disciplina_id === disciplinaId;
        });
    }

    function preencherProfessores(disciplinaId, selecionado) {
        var sel = document.getElementById('cel-professor');
        sel.innerHTML = '<option value="">Sem professor</option>';
        professoresDa(disciplinaId).forEach(function (p) {
            var op = document.createElement('option');
            op.value = p.id;
            op.textContent = p.nome + (p.disciplina_id ? '' : ' (turma toda)');
            if (selecionado && p.id === selecionado) op.selected = true;
            sel.appendChild(op);
        });
    }

    function abrirCelula(dia, ordem, aula) {
        celDia = dia; celOrdem = ordem; celAtual = aula;
        document.getElementById('cel-dia').value = dia;
        document.getElementById('cel-ordem').value = ordem;
        document.getElementById('cel-titulo').textContent =
            'Aula — ' + (diasLabels[dia] || dia) + ', ' + ordem + 'ª aula';
        document.getElementById('cel-erro-geral').classList.add('d-none');
        document.getElementById('cel-erro-disciplina').textContent = '';
        document.getElementById('cel-erro-sala').textContent = '';
        document.getElementById('btn-limpar').style.display = aula ? '' : 'none';

        var sel = document.getElementById('cel-disciplina');
        sel.value = aula && aula.disciplina_id ? aula.disciplina_id : '';
        marcarDisciplina();
        preencherProfessores(sel.value ? parseInt(sel.value, 10) : null, aula ? aula.user_id : null);
        document.getElementById('cel-sala').value = aula && aula.sala ? aula.sala : '';
        $(modal).modal('show');
    }

    function marcarDisciplina() {
        var val = document.getElementById('cel-disciplina').value;
        document.querySelectorAll('#cel-disc-lista .gd-pick').forEach(function (b) {
            var ativa = b.dataset.id === String(val);
            b.classList.toggle('selecionada', ativa);
            b.style.setProperty('--pb', ativa ? b.dataset.bg : '');
            b.style.setProperty('--pt', ativa ? b.dataset.tx : '');
        });
    }

    document.getElementById('cel-disc-lista').addEventListener('click', function (e) {
        var btn = e.target.closest('.gd-pick');
        if (!btn) return;
        var sel = document.getElementById('cel-disciplina');
        sel.value = btn.dataset.id;
        sel.dispatchEvent(new Event('change'));
    });

    document.getElementById('grade-tabela').addEventListener('click', function (e) {
        var cel = e.target.closest('.celula-aula');
        if (!cel) return;
        var chave = cel.dataset.dia + '-' + cel.dataset.ordem;
        abrirCelula(parseInt(cel.dataset.dia, 10), parseInt(cel.dataset.ordem, 10), aulasMapa[chave] || null);
    });

    document.getElementById('cel-disciplina').addEventListener('change', function () {
        marcarDisciplina();
        preencherProfessores(this.value ? parseInt(this.value, 10) : null, null);
        document.getElementById('cel-erro-disciplina').textContent = '';
    });

    function htmlCelula(disciplinaId, professor, sala) {
        var d = disciplinas.find(function (x) { return x.id === disciplinaId; });
        var cor = cores[disciplinaId] || { bg: '#e2e8f0', tx: '#1e293b' };
        var nome = d ? (d.sigla || d.nome) : '—';
        var html = '<div class="font-weight-bold" style="color:' + cor.tx + ';font-size:.82rem;">' + nome + '</div>';
        if (professor) html += '<div style="font-size:.72rem;color:' + cor.tx + ';">' + professor + '</div>';
        if (sala) html += '<div style="font-size:.68rem;color:#64748b;"><i class="fa fa-door-open"></i> ' + sala + '</div>';
        return html;
    }

    function atualizarCelula(dia, ordem, conteudo, meta) {
        var cel = document.querySelector('.celula-aula[data-dia="' + dia + '"][data-ordem="' + ordem + '"]');
        if (!cel) return;
        cel.classList.remove('vazia');
        cel.style.background = meta.bg;
        cel.style.borderLeft = '4px solid ' + meta.tx;
        cel.innerHTML = conteudo;
        aulasMapa[dia + '-' + ordem] = { disciplina_id: meta.disciplina_id, user_id: meta.user_id, sala: meta.sala };
    }

    function limparCelulaDom(dia, ordem) {
        var cel = document.querySelector('.celula-aula[data-dia="' + dia + '"][data-ordem="' + ordem + '"]');
        if (!cel) return;
        cel.classList.add('vazia');
        cel.style.background = '';
        cel.style.borderLeft = '';
        cel.innerHTML = '<i class="fa fa-plus text-muted"></i>';
        delete aulasMapa[dia + '-' + ordem];
    }

    function atualizarRestante(disciplinaId, delta) {
        var row = document.querySelector('#tbody-restantes tr[data-disciplina="' + disciplinaId + '"] .restante');
        if (!row) return;
        var d = disciplinas.find(function (x) { return x.id === disciplinaId; });
        if (!d || d.restante === null) return;
        d.usadas += delta;
        d.restante = Math.max(0, (d.carga || 0) - d.usadas);
        row.textContent = d.restante;
        row.classList.toggle('badge-danger', d.restante > 0);
        row.classList.toggle('badge-light', d.restante <= 0);
        row.classList.toggle('border', d.restante <= 0);
        var chip = document.querySelector('#cel-disc-lista .gd-pick[data-id="' + disciplinaId + '"] .gd-pick-uso');
        if (chip) chip.textContent = d.usadas + '/' + d.carga;
    }

    document.getElementById('form-celula').addEventListener('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(this);
        if (!fd.get('disciplina_id')) {
            document.getElementById('cel-erro-disciplina').textContent = 'Selecione uma disciplina na lista.';
            return;
        }
        var btn = document.getElementById('btn-salvar-celula');
        btn.disabled = true;
        fetch(storeUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: fd
        }).then(function (r) { return r.json().then(function (j) { return { status: r.status, body: j }; }); })
        .then(function (res) {
            btn.disabled = false;
            if (res.status === 422 || res.body.errors) {
                var errs = res.body.errors || {};
                document.getElementById('cel-erro-disciplina').textContent = errs.disciplina_id ? errs.disciplina_id[0] : '';
                document.getElementById('cel-erro-sala').textContent = errs.sala ? errs.sala[0] : '';
                document.getElementById('cel-erro-geral').textContent = errs.user_id ? errs.user_id[0] : (res.body.message || 'Não foi possível salvar.');
                document.getElementById('cel-erro-geral').classList.remove('d-none');
                return;
            }
            if (!res.body.ok) {
                document.getElementById('cel-erro-geral').textContent = res.body.message || 'Erro ao salvar.';
                document.getElementById('cel-erro-geral').classList.remove('d-none');
                return;
            }
            var disciplinaId = parseInt(fd.get('disciplina_id'), 10);
            // Ajusta contadores: remove 1 da disciplina anterior (se outra) e soma na nova.
            var anterior = celAtual && celAtual.disciplina_id ? parseInt(celAtual.disciplina_id, 10) : null;
            if (anterior && anterior !== disciplinaId) atualizarRestante(anterior, -1);
            atualizarRestante(disciplinaId, anterior === disciplinaId ? 0 : 1);

            var cor = cores[disciplinaId] || { bg: '#e2e8f0', tx: '#1e293b' };
            atualizarCelula(celDia, celOrdem, htmlCelula(disciplinaId, res.body.professor, res.body.sala), {
                bg: cor.bg, tx: cor.tx,
                disciplina_id: disciplinaId,
                user_id: fd.get('user_id') ? parseInt(fd.get('user_id'), 10) : null,
                sala: fd.get('sala') || null
            });
            $(modal).modal('hide');
        }).catch(function () {
            btn.disabled = false;
            document.getElementById('cel-erro-geral').textContent = 'Falha de comunicação. Tente novamente.';
            document.getElementById('cel-erro-geral').classList.remove('d-none');
        });
    });

    document.getElementById('btn-limpar').addEventListener('click', function () {
        var fd = new FormData();
        fd.append('dia_semana', celDia);
        fd.append('ordem', celOrdem);
        fetch(destroyUrl, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(fd).toString()
        }).then(function (r) { return r.json(); }).then(function (j) {
            if (j.ok) {
                if (celAtual && celAtual.disciplina_id) atualizarRestante(parseInt(celAtual.disciplina_id, 10), -1);
                limparCelulaDom(celDia, celOrdem);
                $(modal).modal('hide');
            }
        });
    });
})();
</script>
@endcan
@stop
