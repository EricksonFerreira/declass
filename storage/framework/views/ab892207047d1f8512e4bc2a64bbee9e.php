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

<?php $__env->startSection('content'); ?>
    <!-- Navegador de Semana -->
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <div class="d-flex align-items-center">
            <a href="<?php echo e(route('clockify.timesheet', ['week' => $weekOffset - 1])); ?>" class="btn btn-sm btn-outline-secondary mr-2">
                <i class="fas fa-chevron-left"></i> Semana Anterior
            </a>
            <a href="<?php echo e(route('clockify.timesheet', ['week' => 0])); ?>" class="btn btn-sm btn-outline-primary mr-2">
                Hoje
            </a>
            <a href="<?php echo e(route('clockify.timesheet', ['week' => $weekOffset + 1])); ?>" class="btn btn-sm btn-outline-secondary">
                Próxima Semana <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <h5 class="mb-0 text-bold text-secondary">
            <?php echo e($startOfWeek->format('d/m/Y')); ?> - <?php echo e($endOfWeek->format('d/m/Y')); ?>

        </h5>
    </div>

    <!-- Tabela Timesheet Grid -->
    <form action="<?php echo e(route('clockify.timesheet.save', ['week' => $weekOffset])); ?>" method="POST">
        <?php echo csrf_field(); ?>
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
                                <?php $__currentLoopData = $weekDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th style="width: 100px;">
                                        <?php echo e($day->locale('pt_BR')->minDayName); ?>

                                        <br>
                                        <small class="text-muted"><?php echo e($day->format('d/m')); ?></small>
                                    </th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <th style="width: 100px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Projetos existentes na Matrix -->
                            <?php $__currentLoopData = $matrix; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projId => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr data-project="<?php echo e($projId); ?>">
                                    <td class="text-left align-middle font-weight-bold">
                                        <?php if($data['projeto']): ?>
                                            <span class="badge mr-2" style="background-color: <?php echo e($data['projeto']->cor ?? '#6c757d'); ?>; width: 12px; height: 12px; display: inline-block;">&nbsp;</span>
                                            <?php echo e($data['projeto']->nome); ?>

                                            <br>
                                            <small class="text-muted font-weight-normal"><?php echo e($data['projeto']->cliente->nome ?? 'Sem Cliente'); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Sem Projeto / Geral</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php $__currentLoopData = $data['days']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayIndex => $hours): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="align-middle p-1">
                                            <?php if($hours > 0): ?>
                                                <!-- If it has logged hours, show read-only or prefix -->
                                                <div class="font-weight-bold text-secondary"><?php echo e(number_format($hours, 1)); ?>h</div>
                                            <?php else: ?>
                                                <input type="number" step="0.5" min="0" max="24" 
                                                    name="hours[<?php echo e($projId); ?>][<?php echo e($dayIndex); ?>]" 
                                                    class="form-control form-control-sm text-center hour-input border-0" 
                                                    placeholder="-">
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <td class="align-middle font-weight-bold text-dark row-total">
                                        <?php echo e(number_format(array_sum($data['days']), 1)); ?>h
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td class="text-left align-middle">Total Diário</td>
                                <?php for($i = 0; $i < 7; $i++): ?>
                                    <?php
                                        $dayTotal = 0;
                                        foreach ($matrix as $data) {
                                            $dayTotal += $data['days'][$i];
                                        }
                                    ?>
                                    <td class="align-middle col-total" data-col="<?php echo e($i); ?>">
                                        <?php echo e(number_format($dayTotal, 1)); ?>h
                                    </td>
                                <?php endfor; ?>
                                <td class="align-middle text-primary text-lg" id="grand-total">
                                    <?php
                                        $grandTotal = 0;
                                        foreach ($matrix as $data) {
                                            $grandTotal += array_sum($data['days']);
                                        }
                                    ?>
                                    <?php echo e(number_format($grandTotal, 1)); ?>h
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
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
                <?php $__currentLoopData = $matrix; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projId => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $data['days']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayIndex => $hours): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        dailyTotals[<?php echo e($dayIndex); ?>] += <?php echo e((float) $hours); ?>;
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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
                        baseHours = <?php echo e(array_sum($matrix[0]['days'])); ?>;
                    } else if (projId > 0) {
                        // Find matching row in matrix
                        <?php $__currentLoopData = $matrix; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pId => $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            if (projId == <?php echo e($pId); ?>) {
                                baseHours = <?php echo e(array_sum($d['days'])); ?>;
                            }
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/clockify/timesheet.blade.php ENDPATH**/ ?>