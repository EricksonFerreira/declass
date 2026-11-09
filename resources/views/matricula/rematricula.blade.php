@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Rematrícula em lote" subtitle="Copiar matrículas ativas de uma turma para outro ano letivo" />
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ session('success') }}
    </div>
@endif

@if(session('ignoradas') && count(session('ignoradas')))
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Alguns alunos foram ignorados:</strong>
        <ul class="mb-0 mt-1">
            @foreach(session('ignoradas') as $motivo)
                <li>{{ $motivo }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-exchange-alt mr-1"></i> 1. Origem e destino</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('matricula.rematricula') }}">
            <div class="row">
                <div class="col-md-6 border-right">
                    <h6 class="text-muted font-weight-bold text-uppercase">Origem</h6>
                    <div class="form-group">
                        <label for="escola_origem">Escola de origem</label>
                        <select name="escola_origem" id="escola_origem" class="form-control" required>
                            <option value="">— selecione —</option>
                            @foreach($escolas as $escola)
                                <option value="{{ $escola->id }}" {{ (int) $selecao['escola_origem'] === $escola->id ? 'selected' : '' }}>
                                    {{ $escola->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ano_origem">Ano letivo de origem <span id="badge-ano-origem"></span></label>
                        <select name="ano_origem" id="ano_origem" class="form-control" required disabled>
                            <option value="">— selecione a escola —</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label for="turma_origem">Turma de origem</label>
                        <select name="turma_origem" id="turma_origem" class="form-control" required disabled>
                            <option value="">— selecione o ano letivo —</option>
                        </select>
                        <small class="text-muted">Somente matrículas ativas desta turma serão listadas.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted font-weight-bold text-uppercase">Destino</h6>
                    <div class="form-group">
                        <label for="escola_destino">Escola de destino</label>
                        <select name="escola_destino" id="escola_destino" class="form-control" required>
                            <option value="">— selecione —</option>
                            @foreach($escolas as $escola)
                                <option value="{{ $escola->id }}" {{ (int) $selecao['escola_destino'] === $escola->id ? 'selected' : '' }}>
                                    {{ $escola->nome }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pode ser outra unidade (transferência em lote).</small>
                    </div>
                    <div class="form-group">
                        <label for="ano_destino">Ano letivo de destino <span id="badge-ano-destino"></span></label>
                        <select name="ano_destino" id="ano_destino" class="form-control" required disabled>
                            <option value="">— selecione a escola —</option>
                        </select>
                    </div>
                    <div class="form-group mb-0 d-flex align-items-end" style="min-height: 62px;">
                        <button type="submit" class="btn btn-primary" id="btn-listar" disabled>
                            <i class="fas fa-list mr-1"></i> Listar alunos elegíveis
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if($erroSelecao)
    <div class="alert alert-danger">{{ $erroSelecao }}</div>
@endif

@if($dados)
    @php
        $turmaOrigem = $dados['turmaOrigem'];
        $anoDestino = $dados['anoDestino'];
    @endphp
    <div class="card card-outline card-secondary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold mb-0">
                <i class="fas fa-users mr-1"></i>
                {{ $turmaOrigem->nome }}{{ $turmaOrigem->etapa?->nome ? ' ('.$turmaOrigem->etapa->nome.')' : '' }}
                — {{ $turmaOrigem->anoLetivo?->ano }} · {{ $turmaOrigem->anoLetivo?->escola?->nome }}
                <i class="fas fa-long-arrow-alt-right mx-1"></i>
                {{ $anoDestino->ano }} · {{ $anoDestino->escola?->nome }}
                — {{ $dados['elegiveis']->count() }} aluno(s) elegível(is)
            </h3>
        </div>
        <div class="card-body p-0">
            @if($dados['elegiveis']->isEmpty())
                <div class="alert alert-info m-3 mb-0">
                    Nenhum aluno elegível: ou não há matrículas ativas nesta turma,
                    ou todos já possuem matrícula no ano de destino.
                </div>
            @elseif($dados['turmasDestino']->isEmpty())
                <div class="alert alert-warning m-3 mb-0">
                    O ano letivo de destino não possui turmas cadastradas.
                </div>
            @else
                <form method="POST" action="{{ route('matricula.rematricular') }}" id="form-rematricula">
                    @csrf
                    <input type="hidden" name="turma_origem" value="{{ $selecao['turma_origem'] }}">
                    <input type="hidden" name="ano_destino" value="{{ $selecao['ano_destino'] }}">
                    <input type="hidden" name="escola_origem" value="{{ $selecao['escola_origem'] }}">
                    <input type="hidden" name="ano_origem" value="{{ $selecao['ano_origem'] }}">
                    <input type="hidden" name="escola_destino" value="{{ $selecao['escola_destino'] }}">

                    <div class="px-3 py-2 border-bottom bg-light d-flex align-items-center flex-wrap">
                        <label for="turma-todos" class="mb-0 mr-2 small font-weight-bold">Aplicar a todos:</label>
                        <select id="turma-todos" class="form-control form-control-sm" style="max-width: 320px;">
                            <option value="">— escolha a turma de destino —</option>
                            @foreach($dados['turmasDestino'] as $turma)
                                <option value="{{ $turma->id }}">
                                    {{ $turma->nome }}{{ $turma->etapa?->nome ? ' ('.$turma->etapa->nome.')' : '' }}
                                    — {{ $turma->alunos_ativos_count }}{{ $turma->capacidade_maxima ? '/'.$turma->capacidade_maxima : '' }} aluno(s)
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted ml-2">Preenche e marca todos; desmarque quem não deve ir.</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:40px;"><input type="checkbox" id="check-all" title="Selecionar todos"></th>
                                    <th>Aluno</th>
                                    <th>RA</th>
                                    <th style="min-width:240px;">Turma de destino</th>
                                    <th>Plano de mensalidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dados['elegiveis'] as $matricula)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="check-item" data-matricula="{{ $matricula->id }}" disabled>
                                        </td>
                                        <td>
                                            <strong>{{ $matricula->aluno?->pessoa?->nome ?? '—' }}</strong>
                                            <div><small class="text-muted">Matrícula #{{ $matricula->id }} · chamada {{ $matricula->numero_chamada ?? '—' }}</small></div>
                                        </td>
                                        <td>{{ $matricula->aluno?->registro_academico ?? '—' }}</td>
                                        <td>
                                            <select class="form-control form-control-sm select-turma" data-matricula="{{ $matricula->id }}">
                                                <option value="">— escolha a turma —</option>
                                                @foreach($dados['turmasDestino'] as $turma)
                                                    <option value="{{ $turma->id }}">
                                                        {{ $turma->nome }}{{ $turma->etapa?->nome ? ' ('.$turma->etapa->nome.')' : '' }}
                                                        — {{ $turma->alunos_ativos_count }}{{ $turma->capacidade_maxima ? '/'.$turma->capacidade_maxima : '' }} aluno(s)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            @if($matricula->planoMensalidade)
                                                <span class="badge badge-info">{{ $matricula->planoMensalidade->nome }}</span>
                                                @if($matricula->bolsa_percentual)
                                                    <br><small class="text-success">bolsa {{ rtrim(rtrim(number_format($matricula->bolsa_percentual, 2, ',', ''), '0'), ',') }}%</small>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            O plano de mensalidade é copiado da matrícula atual. Alunos que já tiverem
                            matrícula no ano de destino são ignorados automaticamente.
                        </span>
                        <button type="submit" class="btn btn-success" id="btn-rematricular" disabled>
                            <i class="fas fa-check mr-1"></i> Confirmar rematrícula
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endif
@stop

@push('js')
<script>
(function () {
    var ANOS = @json($anosLetivos);
    var STATUS_LABELS = @json(\App\Models\AnoLetivo::STATUS);
    var STATUS_BADGES = @json(\App\Models\AnoLetivo::STATUS_BADGES);
    var TURMAS = @json($turmas);
    var SEL = @json($selecao);

    var selEscolaO = document.getElementById('escola_origem');
    var selAnoO = document.getElementById('ano_origem');
    var selTurmaO = document.getElementById('turma_origem');
    var selEscolaD = document.getElementById('escola_destino');
    var selAnoD = document.getElementById('ano_destino');
    var btnListar = document.getElementById('btn-listar');

    function option(v, texto) {
        var o = document.createElement('option');
        o.value = v;
        o.textContent = texto;
        return o;
    }

    function preencher(select, itens, selecionado, placeholder) {
        select.innerHTML = '';
        select.appendChild(option('', placeholder));
        itens.forEach(function (i) {
            var o = option(i.id, i.rotulo);
            if (String(selecionado) === String(i.id)) o.selected = true;
            select.appendChild(o);
        });
        select.disabled = false;
    }

    function anosDaEscola(escolaId) {
        return ANOS.filter(function (a) { return String(a.escola_id) === String(escolaId); })
            .map(function (a) {
                return { id: a.id, rotulo: a.rotulo + ' — ' + (STATUS_LABELS[a.status] || a.status) };
            });
    }

    function turmasDoAno(anoId) {
        return TURMAS.filter(function (t) { return String(t.ano_letivo_id) === String(anoId); });
    }

    function badgeAno(spanId, anoId) {
        var span = document.getElementById(spanId);
        var ano = ANOS.find(function (a) { return String(a.id) === String(anoId); });
        span.innerHTML = ano
            ? '<span class="badge badge-' + (STATUS_BADGES[ano.status] || 'secondary') + '">'
                + (STATUS_LABELS[ano.status] || ano.status) + '</span>'
            : '';
    }

    function atualizarBotao() {
        btnListar.disabled = !(selTurmaO.value && selAnoD.value);
    }

    selEscolaO.addEventListener('change', function () {
        if (selEscolaO.value) {
            preencher(selAnoO, anosDaEscola(selEscolaO.value), 0, '— selecione —');
        } else {
            selAnoO.innerHTML = ''; selAnoO.appendChild(option('', '— selecione a escola —')); selAnoO.disabled = true;
        }
        selTurmaO.innerHTML = ''; selTurmaO.appendChild(option('', '— selecione o ano letivo —')); selTurmaO.disabled = true;
        badgeAno('badge-ano-origem', 0);
        atualizarBotao();
    });

    selAnoO.addEventListener('change', function () {
        if (selAnoO.value) {
            preencher(selTurmaO, turmasDoAno(selAnoO.value), 0, '— selecione —');
        } else {
            selTurmaO.innerHTML = ''; selTurmaO.appendChild(option('', '— selecione o ano letivo —')); selTurmaO.disabled = true;
        }
        badgeAno('badge-ano-origem', selAnoO.value);
        atualizarBotao();
    });

    selTurmaO.addEventListener('change', atualizarBotao);

    selEscolaD.addEventListener('change', function () {
        if (selEscolaD.value) {
            preencher(selAnoD, anosDaEscola(selEscolaD.value), 0, '— selecione —');
        } else {
            selAnoD.innerHTML = ''; selAnoD.appendChild(option('', '— selecione a escola —')); selAnoD.disabled = true;
        }
        badgeAno('badge-ano-destino', 0);
        atualizarBotao();
    });

    selAnoD.addEventListener('change', function () {
        badgeAno('badge-ano-destino', selAnoD.value);
        atualizarBotao();
    });

    // Estado inicial (retorno de GET/redirect): reconstroi a cascata selecionada.
    if (SEL.escola_origem) {
        preencher(selAnoO, anosDaEscola(SEL.escola_origem), SEL.ano_origem, '— selecione —');
        badgeAno('badge-ano-origem', SEL.ano_origem);
        if (SEL.ano_origem) preencher(selTurmaO, turmasDoAno(SEL.ano_origem), SEL.turma_origem, '— selecione —');
    }
    if (SEL.escola_destino) {
        preencher(selAnoD, anosDaEscola(SEL.escola_destino), SEL.ano_destino, '— selecione —');
        badgeAno('badge-ano-destino', SEL.ano_destino);
    }
    atualizarBotao();

    var form = document.getElementById('form-rematricula');
    if (!form) return;

    var checkAll = document.getElementById('check-all');
    var btn = document.getElementById('btn-rematricular');
    var selects = document.querySelectorAll('.select-turma');
    var selTodos = document.getElementById('turma-todos');

    function checkDe(matriculaId) {
        return form.querySelector('.check-item[data-matricula="' + matriculaId + '"]');
    }

    function sincronizar() {
        var selecionados = 0, total = 0;
        selects.forEach(function (sel) {
            var chk = checkDe(sel.dataset.matricula);
            if (!chk) return;
            total++;
            // checkbox so participa do submit quando habilitado (turma escolhida)
            chk.name = sel.value !== '' ? 'itens[' + sel.dataset.matricula + ']' : '';
            chk.value = sel.value;
            if (chk.checked && sel.value !== '') selecionados++;
        });
        if (checkAll) checkAll.checked = selecionados === total && total > 0;
        if (btn) btn.disabled = selecionados === 0;
    }

    selects.forEach(function (sel) {
        sel.addEventListener('change', function () {
            var chk = checkDe(sel.dataset.matricula);
            chk.disabled = sel.value === '';
            if (sel.value === '') chk.checked = false;
            sincronizar();
        });
    });
    form.querySelectorAll('.check-item').forEach(function (chk) {
        chk.addEventListener('change', sincronizar);
    });
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            selects.forEach(function (sel) {
                var chk = checkDe(sel.dataset.matricula);
                if (chk && !chk.disabled) chk.checked = checkAll.checked;
            });
            sincronizar();
        });
    }
    if (selTodos) {
        selTodos.addEventListener('change', function () {
            if (selTodos.value === '') return;
            selects.forEach(function (sel) {
                sel.value = selTodos.value;
                var chk = checkDe(sel.dataset.matricula);
                if (chk) { chk.disabled = false; chk.checked = true; }
            });
            sincronizar();
        });
    }
    sincronizar();
})();
</script>
@endpush
