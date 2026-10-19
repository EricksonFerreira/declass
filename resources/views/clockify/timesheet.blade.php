@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <!-- Navegador de Semana -->
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <div class="d-flex align-items-center">
            <a href="{{ route('operacao.timesheet', ['week' => $weekOffset - 1, 'user_id' => $alvo->id]) }}" class="btn btn-sm btn-outline-secondary mr-2">
                <i class="fas fa-chevron-left"></i> Semana Anterior
            </a>
            <a href="{{ route('operacao.timesheet', ['week' => 0, 'user_id' => $alvo->id]) }}" class="btn btn-sm btn-outline-primary mr-2">
                Hoje
            </a>
            <a href="{{ route('operacao.timesheet', ['week' => $weekOffset + 1, 'user_id' => $alvo->id]) }}" class="btn btn-sm btn-outline-secondary">
                Próxima Semana <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <div class="d-flex align-items-center" style="gap:12px;">
            @if ($colaboradores->count() > 1)
                <form method="GET" action="{{ route('operacao.timesheet') }}" class="d-flex align-items-center mb-0" style="gap:6px;">
                    <input type="hidden" name="week" value="{{ $weekOffset }}">
                    <label for="ts-user" class="text-muted text-sm mb-0">Colaborador:</label>
                    <select name="user_id" id="ts-user" class="form-control form-control-sm" onchange="this.form.submit()">
                        @foreach ($colaboradores as $colab)
                            <option value="{{ $colab->id }}" {{ (int) $colab->id === (int) $alvo->id ? 'selected' : '' }}>
                                {{ (int) $colab->id === (int) auth()->id() ? $colab->nome . ' (você)' : $colab->nome }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @else
                <span class="badge badge-info">Lançando para: {{ $alvo->nome }} (você)</span>
            @endif
            <h5 class="mb-0 text-bold text-secondary">
                {{ $startOfWeek->format('d/m/Y') }} - {{ $endOfWeek->format('d/m/Y') }}
            </h5>
        </div>
    </div>

    <!-- Tabela Timesheet Grid -->
    <form action="{{ route('operacao.timesheet.save', ['week' => $weekOffset]) }}" method="POST">
        @csrf
        <input type="hidden" name="user_id" value="{{ $alvo->id }}">
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                <h3 class="card-title text-sm text-bold text-muted mb-0">
                    Lançamento Semanal de Horas
                    <span class="badge badge-info ml-1">{{ $alvo->nome }}</span>
                </h3>
                @can('operacao.timesheet.copiar')
                    <button type="button" id="btn-copy-prev" class="btn btn-sm btn-outline-info"
                            data-nome="{{ $alvo->id === auth()->id() ? 'sua' : 'de ' . $alvo->nome }}">
                        <i class="fas fa-copy mr-1"></i> Copiar semana anterior
                    </button>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 text-center" id="timesheet-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-left" style="min-width: 250px;">Projeto</th>
                                @foreach ($weekDays as $day)
                                    <th style="width: 100px;">
                                        {{ $day->locale('pt_BR')->minDayName }}
                                        <br>
                                        <small class="text-muted">{{ $day->format('d/m') }}</small>
                                    </th>
                                @endforeach
                                <th style="width: 100px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Projetos existentes na Matrix -->
                            @foreach ($matrix as $projId => $data)
                                <tr data-project="{{ $projId }}">
                                    <td class="text-left align-middle font-weight-bold">
                                        @if ($data['projeto'])
                                            <span class="badge mr-2" style="background-color: {{ $data['projeto']->cor ?? '#6c757d' }}; width: 12px; height: 12px; display: inline-block;">&nbsp;</span>
                                            {{ $data['projeto']->nome }}
                                            <br>
                                            <small class="text-muted font-weight-normal">{{ $data['projeto']->cliente->nome ?? 'Sem Cliente' }}</small>
                                        @else
                                            <span class="text-muted">Sem Projeto / Geral</span>
                                        @endif
                                    </td>
                                    @foreach ($data['days'] as $dayIndex => $hours)
                                        @php
                                            $tsVal = round($tsMatrix[$projId][$dayIndex] ?? 0, 2);
                                            $otherVal = round($hours - $tsVal, 2);
                                        @endphp
                                        <td class="align-middle p-1">
                                            <input type="number" step="0.5" min="0" max="24"
                                                name="hours[{{ $projId }}][{{ $dayIndex }}]"
                                                value="{{ $tsVal > 0 ? $tsVal : '' }}"
                                                data-other="{{ $otherVal > 0 ? $otherVal : 0 }}"
                                                class="form-control form-control-sm text-center hour-input border-0"
                                                placeholder="-">
                                            @if ($otherVal > 0)
                                                <small class="text-info d-block" style="font-size:.68rem;" title="Horas lançadas pelo rastreador ou manualmente">
                                                    <i class="fas fa-stopwatch"></i> {{ number_format($otherVal, 1, ',', '.') }}h
                                                </small>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="align-middle font-weight-bold text-dark row-total">
                                        {{ number_format(array_sum($data['days']), 1) }}h
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td class="text-left align-middle">Total Diário</td>
                                @for ($i = 0; $i < 7; $i++)
                                    @php
                                        $dayTotal = 0;
                                        foreach ($matrix as $data) {
                                            $dayTotal += $data['days'][$i];
                                        }
                                    @endphp
                                    <td class="align-middle col-total" data-col="{{ $i }}">
                                        {{ number_format($dayTotal, 1) }}h
                                    </td>
                                @endfor
                                <td class="align-middle text-primary text-lg" id="grand-total">
                                    @php
                                        $grandTotal = 0;
                                        foreach ($matrix as $data) {
                                            $grandTotal += array_sum($data['days']);
                                        }
                                    @endphp
                                    {{ number_format($grandTotal, 1) }}h
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                @can('operacao.timesheet.save')
                    <button type="submit" class="btn btn-primary float-right font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Salvar Planilha de Horas
                    </button>
                @endcan
            </div>
        </div>
    </form>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            var prevWeek = @json($prevTsMatrix);

            $('.hour-input').on('input', function() {
                recalculate();
            });

            function recalculate() {
                var dailyTotals = [0, 0, 0, 0, 0, 0, 0];
                var grandTotal = 0.0;

                $('#timesheet-table tbody tr').each(function() {
                    var rowTotal = 0.0;
                    $(this).find('.hour-input').each(function() {
                        var other = parseFloat($(this).data('other')) || 0.0;
                        var val = parseFloat($(this).val()) || 0.0;
                        var cell = other + val;
                        rowTotal += cell;

                        var matches = $(this).attr('name').match(/\[([0-9]+)\]$/);
                        var colIndex = matches && matches[1] ? parseInt(matches[1]) : 0;
                        dailyTotals[colIndex] += cell;
                    });
                    $(this).find('.row-total').text(rowTotal.toFixed(1) + 'h');
                    grandTotal += rowTotal;
                });

                $('.col-total').each(function(index) {
                    $(this).text(dailyTotals[index].toFixed(1) + 'h');
                });
                $('#grand-total').text(grandTotal.toFixed(1) + 'h');
            }

            $('#btn-copy-prev').on('click', function() {
                var nome = $(this).data('nome') || 'sua';
                if (!confirm('Copiar a semana anterior ' + nome + ' para esta semana?')) {
                    return;
                }
                var filled = 0;
                $('#timesheet-table tbody tr').each(function() {
                    var projId = $(this).data('project');
                    var days = prevWeek[projId];
                    if (!days) return;
                    $(this).find('.hour-input').each(function() {
                        var matches = $(this).attr('name').match(/\[([0-9]+)\]$/);
                        var dayIndex = matches && matches[1] ? parseInt(matches[1]) : 0;
                        if (days[dayIndex] > 0) {
                            $(this).val(days[dayIndex]);
                            filled++;
                        }
                    });
                });
                recalculate();
                if (filled === 0) {
                    alert('A semana anterior ' + nome + ' não tem horas lançadas via Timesheet para copiar.');
                }
            });

            recalculate();
        });
    </script>
@stop
