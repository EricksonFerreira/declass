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
            <a href="{{ route('clockify.timesheet', ['week' => $weekOffset - 1]) }}" class="btn btn-sm btn-outline-secondary mr-2">
                <i class="fas fa-chevron-left"></i> Semana Anterior
            </a>
            <a href="{{ route('clockify.timesheet', ['week' => 0]) }}" class="btn btn-sm btn-outline-primary mr-2">
                Hoje
            </a>
            <a href="{{ route('clockify.timesheet', ['week' => $weekOffset + 1]) }}" class="btn btn-sm btn-outline-secondary">
                Próxima Semana <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <h5 class="mb-0 text-bold text-secondary">
            {{ $startOfWeek->format('d/m/Y') }} - {{ $endOfWeek->format('d/m/Y') }}
        </h5>
    </div>

    <!-- Tabela Timesheet Grid -->
    <form action="{{ route('clockify.timesheet.save', ['week' => $weekOffset]) }}" method="POST">
        @csrf
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header bg-light">
                <h3 class="card-title text-sm text-bold text-muted">Lançamento Semanal de Horas</h3>
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
                                        <td class="align-middle p-1">
                                            @if ($hours > 0)
                                                <!-- If it has logged hours, show read-only or prefix -->
                                                <div class="font-weight-bold text-secondary">{{ number_format($hours, 1) }}h</div>
                                            @else
                                                <input type="number" step="0.5" min="0" max="24" 
                                                    name="hours[{{ $projId }}][{{ $dayIndex }}]" 
                                                    class="form-control form-control-sm text-center hour-input border-0" 
                                                    placeholder="-">
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
                <button type="submit" class="btn btn-primary float-right font-weight-bold">
                    <i class="fas fa-save mr-1"></i> Salvar Planilha de Horas
                </button>
            </div>
        </div>
    </form>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Live calculator for row and column totals when inputs change
            $('.hour-input').on('input', function() {
                recalculate();
            });

            function recalculate() {
                var grandTotal = 0.0;

                // 1. Recalculate daily column totals from pre-filled values
                var dailyTotals = [0, 0, 0, 0, 0, 0, 0];
                
                // Add initial values from php matrix
                @foreach ($matrix as $projId => $data)
                    @foreach ($data['days'] as $dayIndex => $hours)
                        dailyTotals[{{ $dayIndex }}] += {{ (float) $hours }};
                    @endforeach
                @endforeach

                // Add newly input values
                $('#timesheet-table tbody tr').each(function() {
                    var rowTotal = 0.0;
                    
                    // Add existing logged hours
                    var projId = $(this).data('project');
                    if (projId !== undefined) {
                        // Gather inputs from this row
                        $(this).find('.hour-input').each(function(index) {
                            var val = parseFloat($(this).val()) || 0.0;
                            rowTotal += val;
                            var colIndex = index;
                            // Find the correct colIndex in matrix (must match input index offset)
                            var matches = $(this).attr('name').match(/\[([0-9]+)\]$/);
                            if (matches && matches[1]) {
                                colIndex = parseInt(matches[1]);
                            }
                            dailyTotals[colIndex] += val;
                        });
                    }
                });

                // 2. Set row totals and calculate grand total
                $('#timesheet-table tbody tr').each(function() {
                    var projId = $(this).data('project');
                    var baseHours = 0.0;
                    if (projId === 0) {
                        baseHours = {{ array_sum($matrix[0]['days']) }};
                    } else if (projId > 0) {
                        // Find matching row in matrix
                        @foreach ($matrix as $pId => $d)
                            if (projId == {{ $pId }}) {
                                baseHours = {{ array_sum($d['days']) }};
                            }
                        @endforeach
                    }
                    var inputSum = 0.0;
                    $(this).find('.hour-input').each(function() {
                        inputSum += parseFloat($(this).val()) || 0.0;
                    });
                    var total = baseHours + inputSum;
                    $(this).find('.row-total').text(total.toFixed(1) + 'h');
                    grandTotal += total;
                });

                // 3. Update column total views
                $('.col-total').each(function(index) {
                    $(this).text(dailyTotals[index].toFixed(1) + 'h');
                });

                // 4. Update grand total view
                $('#grand-total').text(grandTotal.toFixed(1) + 'h');
            }
        });
    </script>
@stop
