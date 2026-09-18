<?php $__env->startSection('title', $titulo); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => $breadcrumb] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Breadcrumb::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $attributes = $__attributesOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__attributesOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal269900abaed345884ce342681cdc99f6)): ?>
<?php $component = $__componentOriginal269900abaed345884ce342681cdc99f6; ?>
<?php unset($__componentOriginal269900abaed345884ce342681cdc99f6); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal2ff7b3043c692896c2a650ccae21c5e4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4 = $attributes; } ?>
<?php $component = App\View\Components\PageTitle::resolve(['title' => $titulo] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-title'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PageTitle::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4)): ?>
<?php $attributes = $__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4; ?>
<?php unset($__attributesOriginal2ff7b3043c692896c2a650ccae21c5e4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ff7b3043c692896c2a650ccae21c5e4)): ?>
<?php $component = $__componentOriginal2ff7b3043c692896c2a650ccae21c5e4; ?>
<?php unset($__componentOriginal2ff7b3043c692896c2a650ccae21c5e4); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    /* =====================================================
       PADRÃO VISUAL DO SISTEMA (mesmo da tela de projeto)
    ===================================================== */
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; }

    .dash-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 18px rgba(0,0,0,0.05);
    }
    .dash-card > .card-header:first-child { border-radius: 12px 12px 0 0; }
    .dash-card > .card-header { border-bottom: 1px solid #eef1f6; }

    .dash-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .dash-kpi {
        background: #fff; border-radius: 12px; padding: 18px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: none;
        position: relative; overflow: hidden; transition: transform .2s, box-shadow .2s;
    }
    .dash-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.08); }
    .dash-kpi__label { font-family:'Plus Jakarta Sans',sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#64748b; margin:0 0 6px; }
    .dash-kpi__value { font-family:'Plus Jakarta Sans',sans-serif; font-size:26px; font-weight:800; color:#0f172a; line-height:1.1; margin:0; letter-spacing:-.02em; }
    .dash-kpi__sub { font-size:12px; color:#94a3b8; margin:4px 0 0; }
    .dash-kpi__icon { position:absolute; top:16px; right:16px; width:40px; height:40px; border-radius:10px; background:var(--accent-soft, #e0f5fa); display:flex; align-items:center; justify-content:center; font-size:16px; color:var(--accent, #0891b2); }
    .c-teal { --accent:#0891b2; --accent-soft:#e0f5fa; }
    .c-emerald { --accent:#059669; --accent-soft:#d1fae5; }
    .c-amber { --accent:#d97706; --accent-soft:#fef3c7; }
    .c-indigo { --accent:#4f46e5; --accent-soft:#e0e7ff; }
    .c-violet { --accent:#7c3aed; --accent-soft:#ede9fe; }


    .tracker-row { transition: background .15s; }
    .tracker-row:hover { background-color: #f8fafc; }

    /* Select2 compacto para alinhar com form-control-sm */
    .select2-container--bootstrap4 .select2-selection--multiple { min-height: 31px; }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered { padding: 0 8px; }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice { margin-top: 3px; font-size: 12px; }
    .select2-container { max-width: 100%; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <!-- Filtros -->
    <div class="card dash-card mb-4">
        <div class="card-body p-3">
            <form action="<?php echo e(route('home')); ?>" method="GET" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <label for="data_inicio" class="mr-2 text-sm text-bold text-muted">Período de:</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control form-control-sm" value="<?php echo e($selectedStartDate); ?>">
                </div>
                <div class="form-group mr-3 mb-2">
                    <label for="data_fim" class="mr-2 text-sm text-bold text-muted">Até:</label>
                    <input type="date" name="data_fim" id="data_fim" class="form-control form-control-sm" value="<?php echo e($selectedEndDate); ?>">
                </div>
                <?php if(Auth::user()->can('gerenciar-empresa')): ?>
                    <div class="form-group mr-3 mb-2">
                        <label for="user_ids" class="mr-2 text-sm text-bold text-muted">Colaboradores:</label>
                        <select name="user_ids[]" id="user_ids" class="form-control form-control-sm select2" multiple="multiple" style="width: 300px; max-width: 100%;">
                            <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>" <?php if(in_array($user->id, (array)$selectedUserIds)): echo 'selected'; endif; ?>><?php echo e($user->nome); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-sm btn-primary mb-2 font-weight-bold shadow-sm">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                <a href="<?php echo e(route('home')); ?>" class="btn btn-sm btn-default mb-2 ml-2 font-weight-bold shadow-sm">Limpar</a>
            </form>
        </div>
    </div>

    <!-- KPIs Principais -->
    <div class="dash-grid">
        <div class="dash-kpi c-teal">
            <div class="dash-kpi__icon"><i class="fas fa-stopwatch"></i></div>
            <p class="dash-kpi__label">Horas Hoje</p>
            <p class="dash-kpi__value"><?php echo e(number_format($hoursToday, 1)); ?>h</p>
            <p class="dash-kpi__sub">Tempo rastreado</p>
        </div>
        <div class="dash-kpi c-emerald">
            <div class="dash-kpi__icon"><i class="fas fa-calendar-day"></i></div>
            <p class="dash-kpi__label">Esta Semana</p>
            <p class="dash-kpi__value"><?php echo e(number_format($hoursThisWeek, 1)); ?>h</p>
            <p class="dash-kpi__sub">Semana atual</p>
        </div>
        <div class="dash-kpi c-indigo">
            <div class="dash-kpi__icon"><i class="fas fa-chart-line"></i></div>
            <p class="dash-kpi__label">Este Mês</p>
            <p class="dash-kpi__value"><?php echo e(number_format($hoursThisMonth, 1)); ?>h</p>
            <p class="dash-kpi__sub">Mês atual</p>
        </div>
        <div class="dash-kpi c-amber">
            <div class="dash-kpi__icon"><i class="fas fa-hourglass-half"></i></div>
            <p class="dash-kpi__label">Período</p>
            <p class="dash-kpi__value"><?php echo e(number_format($hoursInPeriod, 1)); ?>h</p>
            <p class="dash-kpi__sub">Total selecionado</p>
        </div>
    </div>

    <div class="row">
        <!-- Rastreadores Ativos -->
        <div class="col-md-6">
            <div class="card dash-card">
                <div class="card-header bg-white py-3 border-bottom">
                    <h3 class="card-title text-bold text-dark mb-0">
                        <i class="fas fa-spinner fa-pulse text-danger mr-2"></i> Rastreadores Ativos
                        <span class="badge badge-danger ml-1"><?php echo e($activeTrackers->count()); ?></span>
                    </h3>
                </div>
                <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                    <ul class="nav nav-pills flex-column">
                        <?php $__empty_1 = true; $__currentLoopData = $activeTrackers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $active): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <li class="nav-item border-bottom p-3 tracker-row">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-bold text-dark"><?php echo e($active->user->nome); ?></span>
                                        <span class="text-muted text-sm">em</span>
                                        <span class="text-bold" style="color: <?php echo e($active->projeto->cor ?? '#6c757d'); ?>"><?php echo e($active->projeto->nome ?? 'Sem Projeto'); ?></span>
                                        <br>
                                        <span class="text-muted text-xs"><i class="far fa-edit"></i> <?php echo e($active->descricao ?: 'Sem descrição'); ?></span>
                                    </div>
                                    <span class="badge badge-success px-2 py-1 text-sm font-weight-bold">
                                        <i class="far fa-clock mr-1"></i> <?php echo e($active->tempoFormato); ?>

                                    </span>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="p-4 text-center text-muted text-sm">Ninguém rastreando tempo no momento.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Mini Gráfico de Horas -->
        <div class="col-md-6">
            <div class="card dash-card">
                <div class="card-header bg-white py-3 border-bottom">
                    <h3 class="card-title text-bold text-dark mb-0">
                        <i class="fas fa-chart-bar text-primary mr-2"></i> Horas por dia no período
                    </h3>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 180px;">
                        <canvas id="sparklineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('#user_ids').select2({
            theme: 'bootstrap4',
            width: 'resolve',
            placeholder: 'Todos os colaboradores',
            allowClear: true,
            closeOnSelect: false
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('sparklineChart').getContext('2d');
    const labels = <?php echo json_encode($dateLabels); ?>;
    const rawStats = <?php echo json_encode($memberStats); ?>;

    // Aggregate all members into a single daily total
    const dailyTotals = new Array(labels.length).fill(0);
    Object.values(rawStats).forEach(member => {
        member.dailyOrdered.forEach((val, i) => { dailyTotals[i] += val; });
    });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels.map(d => {
                const parts = d.split('-');
                return parts[2] + '/' + parts[1];
            }),
            datasets: [{
                label: 'Horas',
                data: dailyTotals,
                backgroundColor: 'rgba(8,145,178,0.7)',
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 20,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { cornerRadius: 8, padding: 10 }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 15 } },
                y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 } }, beginAtZero: true }
            }
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\clockify\dashboard.blade.php ENDPATH**/ ?>