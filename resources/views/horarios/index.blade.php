@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Grades de Horário" subtitle="Todas as turmas da escola — exportar ou editar" />
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
                <span class="font-weight-bold">Turmas ({{ $turmas->count() }})</span>
                <form method="GET" action="{{ route('horarios.index') }}" class="form-inline flex-wrap" style="gap:.35rem;">
                    <select name="escola_id" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Todas as escolas</option>
                        @foreach($escolas as $id => $nome)
                            <option value="{{ $id }}" {{ (string) ($filtros['escola_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $nome }}</option>
                        @endforeach
                    </select>
                    <select name="ano_letivo_id" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Todos os anos letivos</option>
                        @foreach($anosLetivos as $ano)
                            <option value="{{ $ano->id }}" {{ $anoLetivoId == $ano->id ? 'selected' : '' }}>{{ $ano->ano }}</option>
                        @endforeach
                    </select>
                    <select name="turno" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Todos os turnos</option>
                        @foreach($turnos as $tk => $tl)
                            <option value="{{ $tk }}" {{ ($filtros['turno'] ?? '') === $tk ? 'selected' : '' }}>{{ $tl }}</option>
                        @endforeach
                    </select>
                    <select name="horario_id" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">Todas as matrizes</option>
                        <option value="sem" {{ ($filtros['horario_id'] ?? '') === 'sem' ? 'selected' : '' }}>Sem matriz vinculada</option>
                        @foreach($matrizes as $m)
                            <option value="{{ $m->id }}" {{ (string) ($filtros['horario_id'] ?? '') === (string) $m->id ? 'selected' : '' }}>{{ $m->nome }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="card-body p-0" id="arvore-horarios">
                @forelse($grupos as $grupo)
                    @php $showEscola = $grupos->count() === 1; @endphp
                    <div class="grupo-header d-flex align-items-center px-3 py-2" style="cursor:pointer;background:#f8fafc;border-bottom:1px solid #e9ecef;"
                         data-toggle="collapse" data-target="#grp-e{{ $grupo['id'] }}">
                        <i class="fas fa-school text-primary mr-2"></i>
                        <span class="font-weight-bold">{{ $grupo['escola']->nome ?? 'Escola' }}</span>
                        <span class="badge badge-light ml-2">{{ $grupo['total'] }} turma(s)</span>
                        <i class="fas fa-chevron-{{ $showEscola ? 'down' : 'right' }} ml-auto text-muted"></i>
                    </div>
                    <div id="grp-e{{ $grupo['id'] }}" class="collapse {{ $showEscola ? 'show' : '' }}">
                        @foreach($grupo['anos'] as $anoGrupo)
                            @php $showAno = $grupo['anos']->count() === 1; @endphp
                            <div class="grupo-header d-flex align-items-center pl-4 pr-3 py-2" style="cursor:pointer;background:#fff;border-bottom:1px solid #f1f3f5;"
                                 data-toggle="collapse" data-target="#grp-a{{ $grupo['id'] }}-{{ $anoGrupo['id'] }}">
                                <i class="fas fa-calendar-alt text-secondary mr-2"></i>
                                <span class="font-weight-bold">{{ $anoGrupo['ano'] }}</span>
                                <span class="badge badge-light ml-2">{{ $anoGrupo['total'] }} turma(s)</span>
                                <i class="fas fa-chevron-{{ $showAno ? 'down' : 'right' }} ml-auto text-muted"></i>
                            </div>
                            <div id="grp-a{{ $grupo['id'] }}-{{ $anoGrupo['id'] }}" class="collapse {{ $showAno ? 'show' : '' }}">
                                @foreach($anoGrupo['turnos'] as $turnoGrupo)
                                    @php $showTurno = $anoGrupo['turnos']->count() === 1; @endphp
                                    <div class="grupo-header d-flex align-items-center pl-5 pr-3 py-2" style="cursor:pointer;background:#fcfcfd;border-bottom:1px solid #f7f8fa;"
                                         data-toggle="collapse" data-target="#grp-t{{ $grupo['id'] }}-{{ $anoGrupo['id'] }}-{{ $turnoGrupo['turno'] }}">
                                        <i class="fas fa-clock text-info mr-2"></i>
                                        <span class="font-weight-bold">{{ $turnoGrupo['label'] }}</span>
                                        <span class="badge badge-light ml-2">{{ $turnoGrupo['total'] }} turma(s)</span>
                                        <i class="fas fa-chevron-{{ $showTurno ? 'down' : 'right' }} ml-auto text-muted"></i>
                                    </div>
                                    <div id="grp-t{{ $grupo['id'] }}-{{ $anoGrupo['id'] }}-{{ $turnoGrupo['turno'] }}" class="collapse {{ $showTurno ? 'show' : '' }}">
                                        <table class="table table-sm table-hover mb-0">
                                            <tbody>
                                                @foreach($turnoGrupo['turmas'] as $turma)
                                                    <tr>
                                                        <td class="pl-5 font-weight-bold">{{ $turma->nome }}</td>
                                                        <td>
                                                            @if($turma->horario)
                                                                {{ $turma->horario->nome }}
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center" style="width:90px;">
                                                            @if(!$turma->tem_matriz)
                                                                @can('horarios.vincular')
                                                                    <button type="button" class="badge badge-warning border-0 btn-vincular-matriz"
                                                                        title="Vincular uma matriz de horários a esta turma"
                                                                        data-toggle="modal" data-target="#modal-vincular-matriz"
                                                                        data-turma-id="{{ $turma->id }}" data-turma-nome="{{ $turma->nome }}"
                                                                        data-escola-id="{{ $turma->escola_id }}" data-turno="{{ $turma->turno }}">
                                                                        sem matriz — vincular
                                                                    </button>
                                                                @else
                                                                    <span class="badge badge-warning" title="Matriz de horários não configurada para este turno">sem matriz</span>
                                                                @endcan
                                                            @elseif($turma->aulas_count > 0)
                                                                <span class="badge badge-success">{{ $turma->aulas_count }} aula(s)</span>
                                                            @else
                                                                <span class="text-muted">0</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-right" style="width:90px;">
                                                            @can('turma.grade')
                                                                <a href="{{ route('turma.grade', $turma->id) }}" class="btn btn-xs btn-info" title="Abrir grade"><i class="fa fa-calendar-week"></i></a>
                                                            @endcan
                                                            @can('turma.grade.pdf')
                                                                <a href="{{ route('turma.grade.pdf', [$turma->id, 'exibir' => 'disciplina']) }}" class="btn btn-xs btn-outline-danger" title="PDF da grade"><i class="fa fa-file-pdf"></i></a>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @empty
                    <div class="text-center text-muted py-4">Nenhuma turma encontrada.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header font-weight-bold">Exportar todas as grades</div>
            <div class="card-body">
                <p class="small text-muted">Gera um arquivo com a grade de <strong>todas as turmas</strong> listadas ao lado.</p>
                <form method="GET" action="{{ route('horarios.exportar') }}">
                    @if($anoLetivoId)<input type="hidden" name="ano_letivo_id" value="{{ $anoLetivoId }}">@endif
                    <div class="form-group">
                        <label class="font-weight-bold">Conteúdo das células</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="exibir" value="disciplina" id="ex-d" checked>
                            <label class="form-check-label" for="ex-d">Somente disciplina <small class="text-muted">(padrão)</small></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="exibir" value="professor" id="ex-p">
                            <label class="form-check-label" for="ex-p">Disciplina + professor</label>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold">Formato</label><br>
                        <button type="submit" name="formato" value="xlsx" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel"></i> Excel (.xlsx — uma aba por turma)
                        </button>
                        <button type="submit" name="formato" value="pdf" class="btn btn-danger btn-sm">
                            <i class="fa fa-file-pdf"></i> PDF (todas as turmas)
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header font-weight-bold">Matriz de horários</div>
            <div class="card-body">
                <p class="small text-muted">Antes de montar as grades, configure as faixas de horário (aulas por dia) de cada turno.</p>
                @can('horario_matriz.index')
                    <a href="{{ route('horario_matriz.index') }}" class="btn btn-outline-primary btn-sm btn-block">
                        <i class="fa fa-cog"></i> Configurar matrizes
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>

@can('horarios.vincular')
<div class="modal fade" id="modal-vincular-matriz" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="form-vincular-matriz" action="">
                @csrf
                @method('PUT')
                @foreach(['ano_letivo_id' => $anoLetivoId, 'escola_id' => $filtros['escola_id'], 'turno' => $filtros['turno'], 'horario_id' => $filtros['horario_id']] as $fk => $fv)
                    @if($fv)<input type="hidden" name="{{ $fk }}" value="{{ $fv }}">@endif
                @endforeach
                <div class="modal-header">
                    <h5 class="modal-title">Vincular matriz — <span id="vm-turma-nome"></span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted" id="vm-descricao"></p>
                    <div id="vm-lista" class="list-group"></div>
                    <div id="vm-vazio" class="alert alert-warning small d-none">
                        Nenhuma matriz cadastrada para esta escola e turno.
                        @can('horario_matriz.index')
                            <a href="{{ route('horario_matriz.index') }}">Configurar matrizes de horário</a>.
                        @endcan
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="vm-salvar" disabled>Vincular</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@stop

@push('js')
<script>
    // Chevron do cabecalho acompanha o estado do collapse.
    document.querySelectorAll('#arvore-horarios .collapse').forEach(function (el) {
        var header = document.querySelector('.grupo-header[data-target="#' + el.id + '"]');
        if (!header) return;
        var icone = header.querySelector('.fa-chevron-down, .fa-chevron-right');
        el.addEventListener('show.bs.collapse', function () {
            if (icone) { icone.classList.remove('fa-chevron-right'); icone.classList.add('fa-chevron-down'); }
        });
        el.addEventListener('hide.bs.collapse', function () {
            if (icone) { icone.classList.remove('fa-chevron-down'); icone.classList.add('fa-chevron-right'); }
        });
    });
</script>
@endpush

@push('js')
@can('horarios.vincular')
<script>
    const VM_MATRIZES = @json($matrizesJs);
    const VM_TURNOS = @json(\App\Models\Turma::TURNOS);
    const VM_ROTA = @json(route('horarios.vincular', '__TURMA__'));

    document.querySelectorAll('.btn-vincular-matriz').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const escolaId = parseInt(btn.dataset.escolaId);
            const turno = btn.dataset.turno;
            const form = document.getElementById('form-vincular-matriz');
            const lista = document.getElementById('vm-lista');
            const vazio = document.getElementById('vm-vazio');
            const salvar = document.getElementById('vm-salvar');

            form.action = VM_ROTA.replace('__TURMA__', btn.dataset.turmaId);
            document.getElementById('vm-turma-nome').textContent = btn.dataset.turmaNome;
            document.getElementById('vm-descricao').textContent =
                'Matrizes da escola para o turno ' + (VM_TURNOS[turno] || turno) + ':';

            const opcoes = VM_MATRIZES.filter(m => m.escola_id === escolaId && m.turno === turno);
            lista.innerHTML = '';
            opcoes.forEach(function (m) {
                lista.insertAdjacentHTML('beforeend',
                    '<label class="list-group-item list-group-item-action mb-1 d-flex justify-content-between align-items-center">' +
                    '<span><input type="radio" name="horario_id" value="' + m.id + '" class="mr-2" required>' +
                    '<strong>' + m.nome + '</strong>' + (m.ativo === 'N' ? ' <span class="badge badge-secondary">inativa</span>' : '') + '</span>' +
                    '<span class="badge badge-light">' + m.slots + ' aula(s)</span></label>');
            });

            vazio.classList.toggle('d-none', opcoes.length > 0);
            salvar.disabled = true;
            lista.addEventListener('change', function () { salvar.disabled = false; }, { once: true });
        });
    });
</script>
@endcan
@endpush
