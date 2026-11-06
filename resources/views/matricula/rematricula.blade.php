@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Rematrícula em lote" subtitle="Copiar matrículas ativas para o próximo ano letivo" />
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
        <h3 class="card-title font-weight-bold"><i class="fas fa-exchange-alt mr-1"></i> 1. Anos letivos</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('matricula.rematricula') }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Ano letivo de origem</label>
                        <select name="ano_origem" class="form-control" required>
                            <option value="">— selecione —</option>
                            @foreach($anosLetivos as $ano)
                                <option value="{{ $ano->id }}" {{ (int) $anoOrigemId === $ano->id ? 'selected' : '' }}>
                                    {{ $ano->ano }} — {{ $ano->escola->nome ?? '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Somente matrículas ativas deste ano serão listadas.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Ano letivo de destino</label>
                        <select name="ano_destino" class="form-control" required>
                            <option value="">— selecione —</option>
                            @foreach($anosLetivos as $ano)
                                <option value="{{ $ano->id }}" {{ (int) $anoDestinoId === $ano->id ? 'selected' : '' }}>
                                    {{ $ano->ano }} — {{ $ano->escola->nome ?? '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Deve ser da mesma escola do ano de origem.</small>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-list mr-1"></i> Listar alunos elegíveis
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if($erroAnos)
    <div class="alert alert-danger">{{ $erroAnos }}</div>
@endif

@if($dados)
    <div class="card card-outline card-secondary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold mb-0">
                <i class="fas fa-users mr-1"></i>
                {{ $dados['anoOrigem']->ano }} → {{ $dados['anoDestino']->ano }}
                — {{ $dados['elegiveis']->count() }} aluno(s) elegível(is)
            </h3>
        </div>
        <div class="card-body p-0">
            @if($dados['elegiveis']->isEmpty())
                <div class="alert alert-info m-3 mb-0">
                    Nenhum aluno elegível: ou não há matrículas ativas no ano de origem,
                    ou todos já possuem matrícula no ano de destino.
                </div>
            @else
                <form method="POST" action="{{ route('matricula.rematricular') }}" id="form-rematricula">
                    @csrf
                    <input type="hidden" name="ano_origem" value="{{ $anoOrigemId }}">
                    <input type="hidden" name="ano_destino" value="{{ $anoDestinoId }}">

                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:40px;"><input type="checkbox" id="check-all" title="Selecionar todos"></th>
                                    <th>Aluno</th>
                                    <th>RA</th>
                                    <th>Turma atual ({{ $dados['anoOrigem']->ano }})</th>
                                    <th style="min-width:220px;">Turma de destino ({{ $dados['anoDestino']->ano }})</th>
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
                                        <td>{{ $matricula->turma?->nome ?? '—' }}</td>
                                        <td>
                                            <select class="form-control form-control-sm select-turma" data-matricula="{{ $matricula->id }}">
                                                <option value="">— escolha a turma —</option>
                                                @foreach($dados['turmasDestino'] as $turma)
                                                    <option value="{{ $turma->id }}">
                                                        {{ $turma->nome }}{{ $turma->etapa?->nome ? ' ('.$turma->etapa->nome.')' : '' }}
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
    var checkAll = document.getElementById('check-all');
    var btn = document.getElementById('btn-rematricular');
    var selects = document.querySelectorAll('.select-turma');
    var form = document.getElementById('form-rematricula');
    if (!form) return;

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
    sincronizar();
})();
</script>
@endpush
