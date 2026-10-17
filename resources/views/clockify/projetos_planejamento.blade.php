@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; }
    
    .planning-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
    }
    
    .day-row {
        transition: all 0.2s ease;
    }
    .day-row:hover {
        background-color: #f8fafc;
    }
    .weekend-day {
        background-color: #fcfcfc;
        color: #94a3b8;
    }
    
    /* Live Summary Card */
    .summary-card {
        position: sticky;
        top: 20px;
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        background: #fff;
    }
    
    .calc-value {
        font-family: monospace;
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
    }
</style>
@stop

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card planning-card shadow-sm border-0">
            <div class="card-body">
                <form action="{{ route('operacao.projetos.planejamento', $projeto->id) }}" method="GET" id="version-selector-form">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="text-xs text-bold text-muted d-block mb-1"><i class="fas fa-code-branch text-primary mr-1"></i> Versões de Planejamento deste Projeto</label>
                            @if($projeto->cronogramas->count() > 0)
                                <select name="cronograma_id" class="form-control form-control-sm select2" onchange="this.form.submit()">
                                    @foreach($projeto->cronogramas as $cron)
                                        <option value="{{ $cron->id }}" @selected($selectedCronograma && $selectedCronograma->id == $cron->id)>
                                            {{ $cron->nome }} — Status: {{ $cron->status == 'valido' ? 'VÁLIDO / ATIVO' : strtoupper($cron->status) }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <div class="text-muted text-sm"><i class="fas fa-info-circle mr-1"></i> Nenhuma versão de planejamento criada ainda. Crie a primeira ao lado!</div>
                            @endif
                        </div>
                        <div class="col-md-6 text-md-right">
                            <a href="{{ route('operacao.projetos.cronogramas.novo', $projeto->id) }}" class="btn btn-sm btn-primary font-weight-bold shadow-sm">
                                <i class="fas fa-plus mr-1"></i> Criar Nova Versão / Etapa
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($selectedCronograma)
<form action="{{ route('operacao.projetos.planejamento.store', $projeto->id) }}" method="POST" id="planning-form">
    @csrf
    <input type="hidden" name="cronograma_id" value="{{ $selectedCronograma->id }}">
    
    <div class="row">
        <!-- Gerador de Período e Tabela de Dias -->
        <div class="col-md-8">
            <div class="card planning-card card-outline card-primary">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h3 class="card-title text-bold text-dark mb-0">
                        <i class="fas fa-calendar-alt text-primary mr-2"></i> Cronograma Diário: <span class="text-primary">{{ $selectedCronograma->nome }}</span>
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Parâmetros do Gerador -->
                    <div class="row align-items-end mb-4 p-3 bg-light rounded border">
                        <div class="col-sm-4 mb-2 mb-sm-0">
                            <label class="text-xs text-bold text-muted">Data de Início</label>
                            <input type="date" id="start-date" class="form-control form-control-sm" value="{{ $selectedCronograma->data_inicio ? $selectedCronograma->data_inicio->format('Y-m-d') : date('Y-m-d') }}">
                        </div>
                        <div class="col-sm-4 mb-2 mb-sm-0">
                            <label class="text-xs text-bold text-muted">Data de Fim</label>
                            <input type="date" id="end-date" class="form-control form-control-sm" value="{{ $selectedCronograma->data_fim ? $selectedCronograma->data_fim->format('Y-m-d') : date('Y-m-d', strtotime('+7 days')) }}">
                        </div>
                        <div class="col-sm-4">
                            <button type="button" class="btn btn-sm btn-primary btn-block font-weight-bold" onclick="generateDailyGrid()">
                                <i class="fas fa-magic mr-1"></i> GERAR DIAS
                            </button>
                        </div>
                    </div>

                    <!-- Tabela de Dias Gerados -->
                    <div class="table-responsive">
                        <table class="table table-sm border-0" id="daily-table">
                            <thead>
                                <tr class="text-xs text-uppercase text-secondary border-bottom">
                                    <th>Data</th>
                                    <th>Dia da Semana</th>
                                    <th style="width: 150px;">Horas Planejadas</th>
                                    <th style="width: 120px;" class="text-center">Hora Extra?</th>
                                    <th style="width: 150px;" class="text-right">Custo Estimado</th>
                                </tr>
                            </thead>
                            <tbody id="daily-grid-tbody">
                                <!-- Generated by JS -->
                            </tbody>
                        </table>
                    </div>

                    <div id="no-grid-placeholder" class="text-center py-5 text-muted">
                        <i class="far fa-calendar-times fa-3x mb-3 text-muted"></i>
                        <p class="mb-0">Informe o período acima e clique em <strong>Gerar Dias</strong> para preencher o cronograma.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Painel de Indicadores e Sizing (Metragem) -->
        <div class="col-md-4">
            <div class="card summary-card card-outline card-info">
                <div class="card-header bg-white py-3 border-bottom">
                    <h3 class="card-title text-bold text-dark mb-0">
                        <i class="fas fa-chart-pie text-info mr-2"></i> Análise da Versão
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Informações do Projeto -->
                    <div class="mb-3">
                        <h6 class="font-weight-bold text-dark mb-1">{{ $selectedCronograma->nome }}</h6>
                        <span class="badge badge-{{ $selectedCronograma->status == 'valido' ? 'success' : ($selectedCronograma->status == 'rascunho' ? 'warning' : 'secondary') }} text-xs">
                            {{ $selectedCronograma->status == 'valido' ? 'VÁLIDO / ATIVO' : strtoupper($selectedCronograma->status) }}
                        </span>
                    </div>

                    <hr class="mt-0">

                    <!-- Status Selection -->
                    <div class="form-group mb-3">
                        <label for="status" class="text-xs text-bold text-muted mb-1"><i class="fas fa-toggle-on text-info mr-1"></i> Validar / Ativar Versão</label>
                        <select name="status" id="status" class="form-control form-control-sm font-weight-bold">
                            <option value="valido" @selected($selectedCronograma->status === 'valido')>Válido (Planejamento Ativo)</option>
                            <option value="rascunho" @selected($selectedCronograma->status === 'rascunho')>Rascunho</option>
                            <option value="invalido" @selected($selectedCronograma->status === 'invalido')>Inválido</option>
                        </select>
                    </div>

                    <!-- Editar preço da hora diretamente na análise de decisão -->
                    <div class="form-group mb-3">
                        <label for="valor_hora" class="text-xs text-bold text-muted mb-1"><i class="fas fa-hand-holding-usd text-info mr-1"></i> Preço da Hora (R$)</label>
                        <input type="number" step="0.01" min="0" name="valor_hora" id="valor_hora" class="form-control form-control-sm font-weight-bold" value="{{ $selectedCronograma->valor_hora ?? 0.00 }}">
                    </div>

                    <!-- Editar valor faturado da etapa na análise de decisão -->
                    <div class="form-group mb-3">
                        <label for="valor_faturado" class="text-xs text-bold text-muted mb-1"><i class="fas fa-file-invoice-dollar text-info mr-1"></i> Valor Faturado da Etapa (R$)</label>
                        <input type="number" step="0.01" min="0" name="valor_faturado" id="valor_faturado" class="form-control form-control-sm font-weight-bold" value="{{ $selectedCronograma->valor_faturado ?? 0.00 }}">
                    </div>

                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <span class="text-sm text-muted">Orçamento de Horas:</span>
                        <span class="calc-value text-primary">{{ number_format($selectedCronograma->horas_orcadas ?? 0, 1, ',', '.') }} h</span>
                    </div>
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <span class="text-sm text-muted">Valor Orçado Total:</span>
                        <span class="calc-value text-primary" id="summary-budgeted-cost">R$ 0,00</span>
                    </div>

                    <div class="border-top pt-3 bg-light p-3 rounded border">
                        <h6 class="text-xs text-bold text-uppercase text-secondary mb-3"><i class="fas fa-calculator mr-1"></i> Total Planejado</h6>
                        
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <span class="text-xs text-muted">Total de Dias Úteis:</span>
                            <span class="font-weight-bold text-dark" id="summary-workdays">0 dias</span>
                        </div>
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <span class="text-xs text-muted">Total de Horas:</span>
                            <span class="calc-value text-dark" id="summary-hours">0,0 h</span>
                        </div>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="text-xs text-muted">Custo Total Planejado:</span>
                            <span class="calc-value text-dark" id="summary-cost">R$ 0,00</span>
                        </div>

                        <!-- Metragem Check / Status Message -->
                        <div class="border-top mt-2 pt-2 text-center" id="summary-sizing-status">
                            <!-- Message populated by JS -->
                        </div>
                    </div>

                    <!-- Botão de Salvar -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-success btn-block font-weight-bold shadow-sm" id="btn-save-planning" style="display: none;">
                            <i class="fas fa-save mr-1"></i> Salvar Planejamento
                        </button>
                        {{-- <a href="{{ route('operacao.projetos.index') }}" class="btn btn-default btn-block mt-2 font-weight-bold text-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Voltar para Projetos
                        </a> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@else
<div class="row">
    <div class="col-12">
        <div class="card planning-card text-center py-5 text-muted shadow-sm">
            <i class="fas fa-project-diagram fa-4x mb-3 text-muted"></i>
            <h4>Nenhuma versão de planejamento ativa</h4>
            <p>Por favor, clique em <strong>Criar Nova Versão / Etapa</strong> no canto superior direito para iniciar o planejamento deste projeto.</p>
        </div>
    </div>
</div>
@endif

@stop

@section('js')
<script>
    const budgetedHours = parseFloat("{{ $selectedCronograma ? $selectedCronograma->horas_orcadas : 0 }}") || 0.0;
    const existingData = {!! json_encode($existingPlanning) !!};

    $(document).ready(function() {
        // Pre-fill grid if existing data is present
        if (Object.keys(existingData).length > 0) {
            generateDailyGrid(existingData);
        }

        // Recalculate everything when Hourly Rate is changed
        $('#valor_hora').on('input change', function() {
            calculateTotals();
        });
    });

    function generateDailyGrid(preloadData = null) {
        const startStr = $('#start-date').val();
        const endStr = $('#end-date').val();

        if (!startStr || !endStr) {
            alert('Por favor, informe a data de início e fim.');
            return;
        }

        const startParts = startStr.split('-');
        const endParts = endStr.split('-');

        const startDate = new Date(startParts[0], startParts[1] - 1, startParts[2]);
        const endDate = new Date(endParts[0], endParts[1] - 1, endParts[2]);

        if (startDate > endDate) {
            alert('A data de início não pode ser maior que a data de fim.');
            return;
        }

        const tbody = $('#daily-grid-tbody');
        tbody.empty();

        let current = new Date(startDate);
        const dayNames = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
        const monthNames = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];

        let lastMonthKey = '';
        let hasItems = false;
        while (current <= endDate) {
            hasItems = true;
            const year = current.getFullYear();
            const monthVal = current.getMonth(); // 0-11
            const month = String(monthVal + 1).padStart(2, '0');
            const day = String(current.getDate()).padStart(2, '0');
            const dateKey = `${year}-${month}-${day}`;
            const displayDate = `${day}/${month}/${year}`;
            
            // Check for month transition
            const currentMonthKey = `${year}-${month}`;
            if (currentMonthKey !== lastMonthKey) {
                const monthName = monthNames[monthVal];
                const dividerTr = $('<tr>').css({
                    'background-color': '#e2e8f0',
                    'color': '#1e293b',
                    'font-weight': 'bold'
                });
                dividerTr.append(
                    $('<td>')
                        .attr('colspan', 5)
                        .addClass('py-2 text-center text-xs text-uppercase font-weight-bold')
                        .html(`<i class="fas fa-calendar-day mr-1 text-primary"></i> ${monthName} de ${year}`)
                );
                tbody.append(dividerTr);
                lastMonthKey = currentMonthKey;
            }

            const dayOfWeekVal = current.getDay();
            const dayOfWeekName = dayNames[dayOfWeekVal];
            const isWeekend = (dayOfWeekVal === 0 || dayOfWeekVal === 6);

            // Default value determination: 8h for business days, 0h for weekends
            let defaultHours = isWeekend ? 0.0 : 8.0;
            let isExtra = false;
            if (preloadData && preloadData[dateKey] !== undefined) {
                defaultHours = preloadData[dateKey].horas;
                isExtra = preloadData[dateKey].eh_extra;
            } else if (preloadData) {
                // If we are rebuilding with preload data but this date wasn't in it, set it to 0
                defaultHours = 0.0;
            }

            const tr = $('<tr>').addClass('day-row').addClass(isWeekend ? 'weekend-day' : '');
            
            // Cell 1: Date
            tr.append($('<td>').addClass('align-middle font-weight-bold text-sm').text(displayDate));
            
            // Cell 2: Day of Week
            tr.append($('<td>').addClass('align-middle text-sm').html(isWeekend ? `<span class="badge badge-light text-muted">${dayOfWeekName}</span>` : `<span>${dayOfWeekName}</span>`));
            
            // Cell 3: Input Field
            const input = $('<input>')
                .attr('type', 'number')
                .attr('step', '0.5')
                .attr('min', '0')
                .attr('max', '24')
                .attr('name', `planejamento[${dateKey}][horas]`)
                .addClass('form-control form-control-sm text-center font-weight-bold daily-hours-input')
                .val(defaultHours)
                .on('input change', calculateTotals);
            
            tr.append($('<td>').addClass('align-middle').append(input));

            // Cell 4: Hora Extra Checkbox
            const hiddenExtra = $('<input>')
                .attr('type', 'hidden')
                .attr('name', `planejamento[${dateKey}][eh_extra]`)
                .val('0');
            const checkboxExtra = $('<input>')
                .attr('type', 'checkbox')
                .attr('name', `planejamento[${dateKey}][eh_extra]`)
                .attr('value', '1')
                .prop('checked', isExtra)
                .addClass('daily-extra-checkbox');
            
            tr.append($('<td>').addClass('align-middle text-center').append(hiddenExtra).append(checkboxExtra));
            
            // Cell 5: Cost Preview
            const costCell = $('<td>').addClass('align-middle text-right font-weight-bold text-sm text-secondary daily-cost-preview').text('R$ 0,00');
            tr.append(costCell);
            
            tbody.append(tr);

            current.setDate(current.getDate() + 1);
        }

        if (hasItems) {
            $('#no-grid-placeholder').addClass('d-none');
            $('#btn-save-planning').show();
            calculateTotals();
        } else {
            $('#no-grid-placeholder').removeClass('d-none');
            $('#btn-save-planning').hide();
        }
    }

    function calculateTotals() {
        const hourlyRate = parseFloat($('#valor_hora').val()) || 0.0;
        let totalHours = 0.0;
        let totalWorkdays = 0;

        $('.daily-hours-input').each(function() {
            const val = parseFloat($(this).val()) || 0.0;
            totalHours += val;
            if (val > 0) {
                totalWorkdays++;
            }

            // Update row cost preview
            const cost = val * hourlyRate;
            $(this).closest('tr').find('.daily-cost-preview').text('R$ ' + cost.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        });

        const totalCost = totalHours * hourlyRate;
        const totalBudgetedCost = budgetedHours * hourlyRate;

        // Update summaries
        $('#summary-budgeted-cost').text('R$ ' + totalBudgetedCost.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#summary-workdays').text(totalWorkdays + ' ' + (totalWorkdays === 1 ? 'dia' : 'dias'));
        $('#summary-hours').text(totalHours.toFixed(1) + ' h');
        $('#summary-cost').text('R$ ' + totalCost.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        // Sizing comparison check
        const compDiv = $('#summary-sizing-status');
        if (budgetedHours > 0) {
            compDiv.show();
            if (totalHours <= budgetedHours) {
                compDiv.html('<div class="alert alert-success mb-0 text-left text-xs"><i class="fas fa-check-circle mr-1"></i> <strong>Sizing Ok (Metragem Boa)</strong><br>As horas planejadas (' + totalHours.toFixed(1) + 'h) estão de acordo com o orçamento de ' + budgetedHours.toFixed(1) + 'h. A decisão foi tomada corretamente!</div>');
            } else {
                compDiv.html('<div class="alert alert-danger mb-0 text-left text-xs"><i class="fas fa-exclamation-triangle mr-1"></i> <strong>Decisão em Risco</strong><br>As horas planejadas (' + totalHours.toFixed(1) + 'h) excedem o orçamento contratado (' + budgetedHours.toFixed(1) + 'h) em ' + (totalHours - budgetedHours).toFixed(1) + 'h. Revise a metragem.</div>');
            }
        } else {
            compDiv.html('<div class="alert alert-warning mb-0 text-left text-xs"><i class="fas fa-info-circle mr-1"></i> <strong>Sem Orçamento</strong><br>Este planejamento não possui horas orçadas definidas para comparação.</div>');
        }
    }
</script>
@stop
