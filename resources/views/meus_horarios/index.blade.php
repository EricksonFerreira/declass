@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title title="Meus Horários" subtitle="Sua grade semanal de aulas por dia e horário" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('meus_horarios.index') }}" class="form-row align-items-end">
            <div class="col-md-3">
                <label class="form-label">Escola</label>
                <select name="escola_id" class="form-control" onchange="this.form.submit()">
                    @foreach($escolas as $id => $nome)
                        <option value="{{ $id }}" {{ $escolaId === $id ? 'selected' : '' }}>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Ano letivo</label>
                <select name="ano_letivo_id" class="form-control" onchange="this.form.submit()">
                    @foreach($anos as $a)
                        <option value="{{ $a->id }}" {{ $anoLetivoId === $a->id ? 'selected' : '' }}>{{ $a->ano }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Turno</label>
                <select name="turno" class="form-control" onchange="this.form.submit()">
                    @foreach($turnos as $valor => $label)
                        <option value="{{ $valor }}" {{ $turno === $valor ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="font-weight-bold">
            Grade semanal — {{ $turnos[$turno] ?? $turno }}
        </span>
        <span class="text-muted small ml-2">{{ $grade['turmas']->count() }} turma(s) neste filtro</span>
        <span class="badge badge-light border ml-2" style="font-weight:normal">
            <i class="fas fa-hand-pointer"></i> Clique numa aula para ver opções
        </span>
    </div>
    <div class="card-body p-0">
        @if($grade['dias']->isEmpty() || $grade['ordens']->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-times fa-3x mb-3 d-block"></i>
                Nenhuma aula alocada para você neste filtro. Verifique ano, escola e turno —
                ou a grade das suas turmas ainda não foi montada.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:90px">Aula</th>
                            @foreach($grade['dias'] as $dia)
                                <th class="text-center">{{ $diasLabels[$dia] ?? $dia }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grade['ordens'] as $ordem)
                            <tr>
                                <td class="font-weight-bold text-center small">{{ $ordem }}ª</td>
                                @foreach($grade['dias'] as $dia)
                                    <td class="p-1" style="min-width:120px">
                                        @foreach($grade['celulas'][$dia.'-'.$ordem] ?? [] as $aula)
                                            @php
                                                $cor = \App\Services\TurmaGradeService::corDaDisciplina($aula['disciplina_id']);
                                                $rotuloSala = $aula['sala']
                                                    ? (\Illuminate\Support\Str::startsWith(mb_strtolower($aula['sala']), 'sala') ? $aula['sala'] : 'sala '.$aula['sala'])
                                                    : null;
                                            @endphp
                                            <div class="aula-cell p-1 mb-1 rounded" style="background:{{ $cor['bg'] }}; color:{{ $cor['tx'] }}; font-size:12px; line-height:1.25; cursor:pointer"
                                                 title="{{ $aula['disciplina'] }} — {{ $aula['turma'] }}"
                                                 data-turma-url="{{ route('turma.show', $aula['turma_id']) }}"
                                                 data-chamada-url="{{ route('diario.chamada', ['turma_id' => $aula['turma_id'], 'disciplina_id' => $aula['disciplina_id'], 'data' => $aula['chamada_data']]) }}"
                                                 data-disciplina="{{ $aula['disciplina'] }}{{ $aula['sigla'] ? ' ('.$aula['sigla'].')' : '' }}"
                                                 data-turma="{{ $aula['turma'] }}"
                                                 data-quando="{{ $diasLabels[$dia] ?? $dia }} · {{ $ordem }}ª aula{{ $aula['inicio'] ? ' · '.$aula['inicio'].'–'.$aula['fim'] : '' }}"
                                                 data-sala="{{ $rotuloSala }}">
                                                <div class="font-weight-bold d-flex justify-content-between align-items-start">
                                                    <span>{{ $aula['disciplina'] }}@if($aula['sigla']) <span class="font-weight-normal small">({{ $aula['sigla'] }})</span>@endif</span>
                                                    <i class="fas fa-ellipsis-v" style="opacity:.45; font-size:10px"></i>
                                                </div>
                                                <div>{{ $aula['turma'] }}</div>
                                                <div class="small">
                                                    {{ $aula['inicio'] }}–{{ $aula['fim'] }}
                                                    @if($rotuloSala) · {{ $rotuloSala }}@endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @php
                $legenda = [];
                foreach ($grade['celulas'] as $lista) {
                    foreach ($lista as $aula) {
                        $legenda[$aula['disciplina_id']] = $aula['disciplina'];
                    }
                }
            @endphp
            @if(count($legenda) > 0)
                <div class="p-2 border-top d-flex flex-wrap align-items-center" style="gap:.75rem; font-size:.8rem;">
                    <span class="text-muted font-weight-bold"><i class="fas fa-palette"></i> Legenda:</span>
                    @foreach($legenda as $discId => $discNome)
                        @php $corLeg = \App\Services\TurmaGradeService::corDaDisciplina($discId); @endphp
                        <span class="px-2 py-1 rounded" style="background:{{ $corLeg['bg'] }}; color:{{ $corLeg['tx'] }};">{{ $discNome }}</span>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Modal de opções da aula --}}
<div class="modal fade" id="modalAula" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold mb-0" id="modalAulaTitulo">Aula</h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body py-3">
                <div class="text-muted small mb-1" id="modalAulaTurma"></div>
                <div class="small mb-3">
                    <span id="modalAulaQuando"></span>
                    <span id="modalAulaSala" class="ml-1"></span>
                </div>
                <div class="d-flex" style="gap:.5rem">
                    <a href="#" id="modalBtnTurma" class="btn btn-outline-primary flex-fill">
                        <i class="fas fa-users"></i> Ver turma
                    </a>
                    <a href="#" id="modalBtnChamada" class="btn btn-success flex-fill">
                        <i class="fas fa-clipboard-check"></i> Fazer chamada
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .aula-cell { transition: box-shadow .15s, transform .15s; }
    .aula-cell:hover { box-shadow: 0 0 0 2px rgba(0,0,0,.25); transform: translateY(-1px); }
</style>

@push('js')
<script>
    $(function () {
        var $btnTurma = $('#modalBtnTurma'), $btnChamada = $('#modalBtnChamada');
        var htmlTurma = $btnTurma.html(), htmlChamada = $btnChamada.html();
        var navegando = false;

        function resetBotoes() {
            navegando = false;
            $btnTurma.add($btnChamada)
                .prop('disabled', false).removeClass('disabled')
                .css('pointer-events', '');
            $btnTurma.html(htmlTurma);
            $btnChamada.html(htmlChamada);
        }

        $(document).on('click', '.aula-cell', function () {
            var $c = $(this);
            $('#modalAulaTitulo').text($c.data('disciplina'));
            $('#modalAulaTurma').text($c.data('turma'));
            $('#modalAulaQuando').text($c.data('quando'));
            $('#modalAulaSala').text($c.data('sala') ? '· ' + $c.data('sala') : '');
            $('#modalBtnTurma').attr('href', $c.data('turma-url'));
            $('#modalBtnChamada').attr('href', $c.data('chamada-url'));
            resetBotoes();
            $('#modalAula').modal('show');
        });

        $('#modalAula').on('hidden.bs.modal', resetBotoes);

        $btnTurma.add($btnChamada).on('click', function (e) {
            if (navegando) { e.preventDefault(); return; }
            navegando = true;
            var $clicado = $(this);
            $btnTurma.add($btnChamada)
                .addClass('disabled').css('pointer-events', 'none');
            $clicado.html('<i class="fas fa-spinner fa-spin"></i> Processando...');
        });
    });
</script>
@endpush
@stop
