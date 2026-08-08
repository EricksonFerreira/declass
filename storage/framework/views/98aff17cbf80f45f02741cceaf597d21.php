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
    <!-- Filtros de Período e Colaborador -->
    <div class="card shadow-sm mb-4">
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
                        <select name="user_ids[]" id="user_ids" class="form-control form-control-sm select2" multiple="multiple" style="min-width: 250px;">
                            <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>" <?php if(in_array($user->id, (array)$selectedUserIds)): echo 'selected'; endif; ?>><?php echo e($user->nome); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-sm btn-primary mb-2 font-weight-bold">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                <a href="<?php echo e(route('home')); ?>" class="btn btn-sm btn-default mb-2 ml-2">Limpar</a>
            </form>
        </div>
    </div>

    <!-- Indicadores Principais (KPIs) -->
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-gradient-info shadow-sm">
                <div class="inner">
                    <h3><?php echo e(number_format($hoursToday, 1)); ?>h</h3>
                    <p class="font-weight-bold">Tempo Rastreado Hoje</p>
                </div>
                <div class="icon">
                    <i class="fas fa-stopwatch"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-gradient-success shadow-sm">
                <div class="inner">
                    <h3><?php echo e(number_format($hoursThisWeek, 1)); ?>h</h3>
                    <p class="font-weight-bold">Tempo Rastreado Esta Semana</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-gradient-primary shadow-sm">
                <div class="inner">
                    <h3><?php echo e(number_format($hoursThisMonth, 1)); ?>h</h3>
                    <p class="font-weight-bold">Tempo Rastreado Este Mês</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-gradient-warning shadow-sm">
                <div class="inner">
                    <h3><?php echo e(number_format($hoursInPeriod, 1)); ?>h</h3>
                    <p class="font-weight-bold">Total no Período Selecionado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-sm text-bold"><i class="fas fa-spinner fa-pulse text-danger mr-1"></i> Rastreadores Ativos da Equipe</h3>
                </div>
                <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                    <ul class="nav nav-pills flex-column">
                        <?php $__empty_1 = true; $__currentLoopData = $activeTrackers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $active): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <li class="nav-item border-bottom p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-bold text-dark"><?php echo e($active->user->nome); ?></span> 
                                        <span class="text-muted text-sm">está trabalhando em</span>
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
                            <li class="p-4 text-center text-muted text-sm">Ninguém da equipe está rastreando tempo no momento.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-sm text-bold"><i class="fas fa-chart-bar mr-1"></i> Horas Trabalhadas por Dia</h3>
                </div>
                <div class="card-body">
                    <canvas id="memberBarChart" height="200"></canvas>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <ul class="list-group" id="memberList">
                                <?php $__currentLoopData = $selectedMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="cursor:pointer;" onclick="showMemberDetails(<?php echo e($member->id); ?>)">
                                        <span><i class="fas fa-user-circle" style="color: <?php echo e($memberStats[$member->id]['color'] ?? '#6c757d'); ?>;"></i> <?php echo e($member->nome); ?></span>
                                        <span class="badge badge-primary badge-pill"><?php echo e($memberStats[$member->id]['total'] ?? 0); ?>h</span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                        <div class="col-md-8" id="memberDetailCard">
                            <div class="card card-outline card-info shadow-sm">
                                <div class="card-header">
                                    <h3 class="card-title text-sm text-bold"><i class="fas fa-info-circle mr-1"></i> Detalhes do Membro</h3>
                                </div>
                                <div class="card-body" id="memberDetailBody">
                                    <p class="text-muted text-center">Selecione um membro para ver detalhes.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Atividades Recentes da Equipe -->
        <div class="col-md-6">
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-sm text-bold"><i class="fas fa-history mr-1"></i> Atividades no Período</h3>
                </div>
                <div class="card-body p-0" style="max-height: 650px; overflow-y: auto;">
                    <div class="timeline timeline-inverse mb-0 p-3">
                        <?php $__empty_1 = true; $__currentLoopData = $teamActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div>
                                <i class="fas fa-stopwatch bg-primary text-xs"></i>
                                <div class="timeline-item shadow-none border mb-3">
                                    <span class="time"><i class="far fa-clock"></i> <?php echo e($act->inicio->diffForHumans()); ?></span>
                                    <h3 class="timeline-header text-sm text-bold border-0">
                                        <?php echo e($act->user->nome); ?> logou <?php echo e($act->formatted_duration); ?>

                                    </h3>
                                    <div class="timeline-body text-xs pt-0">
                                        <span class="text-bold" style="color: <?php echo e($act->projeto->cor ?? '#6c757d'); ?>"><?php echo e($act->projeto->nome ?? 'Sem Projeto'); ?></span>
                                        - <?php echo e($act->descricao ?: 'Sem descrição'); ?>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center text-muted py-5">Nenhuma atividade registrada no período.</div>
                        <?php endif; ?>
                        <div>
                            <i class="far fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('memberBarChart').getContext('2d');
        const labels = <?php echo json_encode($dateLabels); ?>;
        const rawStats = <?php echo json_encode($memberStats); ?>;
        const datasets = Object.entries(rawStats).map(([id, data]) => ({
            label: data.name,
            backgroundColor: data.color,
            data: data.dailyOrdered
        }));

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    tooltip: { mode: 'index', intersect: false }
                }
            }
        });
    });

    function showMemberDetails(memberId) {
        const members = <?php echo json_encode($selectedMembers); ?>;
        const member = members.find(m => m.id == memberId);
        const stats = <?php echo json_encode($memberStats); ?>[memberId];
        const detailBody = document.getElementById('memberDetailBody');
        if (member && stats) {
            let dailyHtml = '';
            if (stats.daily) {
                dailyHtml = Object.entries(stats.daily).map(([date, hrs]) => `<li>${date}: ${hrs}h</li>`).join('');
            }
            detailBody.innerHTML = `
                <p><strong>Nome:</strong> ${member.nome}</p>
                <p><strong>Total de horas:</strong> ${stats.total}h</p>
                <p><strong>Horas por dia:</strong></p>
                <ul>${dailyHtml}</ul>`;
        } else {
            detailBody.innerHTML = '<p class="text-muted text-center">Detalhes não disponíveis.</p>';
        }
    }
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/clockify/dashboard.blade.php ENDPATH**/ ?>