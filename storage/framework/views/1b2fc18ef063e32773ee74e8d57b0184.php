<?php $__env->startSection('title', 'Horas Trabalhadas'); ?>

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
<?php $component = App\View\Components\PageTitle::resolve(['title' => 'Horas Trabalhadas'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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

<?php $__env->startSection('content'); ?>
<?php
    $fmtHora = fn ($v) => number_format($v, 2, ',', '.').'h';
?>


<div class="row mb-3">
    <div class="col-lg-4 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">Total de horas</div>
            <div class="h4 font-weight-bold mb-0"><?php echo e($fmtHora($totais['horas'])); ?></div>
        </div>
    </div>
    <div class="col-lg-4 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">Projetos com apontamento</div>
            <div class="h4 font-weight-bold mb-0"><?php echo e($totais['projetos']); ?></div>
        </div>
    </div>
    <div class="col-lg-4 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">Colaboradores</div>
            <div class="h4 font-weight-bold mb-0"><?php echo e($totais['colaboradores']); ?></div>
        </div>
    </div>
</div>

<div class="row">
    
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-clock mr-1"></i> Horas por projeto e colaborador</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-3">Projeto</th>
                            <th>Colaborador</th>
                            <th class="text-right pr-3">Horas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $linhas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="pl-3 font-weight-bold"><?php echo e($l->projeto?->nome ?? 'Sem projeto'); ?></td>
                                <td><?php echo e($l->user?->nome ?? '—'); ?></td>
                                <td class="text-right pr-3"><?php echo e($fmtHora(round($l->segundos / 3600, 2))); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="fas fa-clock fa-2x mb-2 d-block"></i>
                                    Nenhuma hora registrada ainda.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Horas por projeto</h3>
            </div>
            <div class="card-body">
                <?php if(count($chart['labels'])): ?>
                    <canvas id="hoursChart" height="220"></canvas>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                        Sem dados para exibir.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
<?php if(count($chart['labels'])): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('hoursChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chart['labels'], 15, 512) ?>,
            datasets: [{
                label: 'Horas',
                data: <?php echo json_encode($chart['data'], 15, 512) ?>,
                backgroundColor: 'rgba(37, 99, 235, 0.55)',
                borderColor: 'rgba(37, 99, 235, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (item) => ' ' + item.parsed.x.toFixed(2).replace('.', ',') + 'h'
                    }
                }
            },
            scales: {
                x: { beginAtZero: true, ticks: { callback: (v) => v + 'h' } }
            }
        }
    });
</script>
<?php endif; ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\relatorios\horas-trabalhadas.blade.php ENDPATH**/ ?>