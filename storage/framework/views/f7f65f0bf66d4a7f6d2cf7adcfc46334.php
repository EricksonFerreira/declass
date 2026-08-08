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
    <div class="row">
        <!-- Listagem de Time Off -->
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Histórico de Folgas e Férias</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped table-bordered table-sm text-nowrap">
                        <thead>
                            <tr>
                                <th>Colaborador</th>
                                <th>Tipo</th>
                                <th>Início</th>
                                <th>Fim</th>
                                <th>Status</th>
                                <th>Observação</th>
                                <th style="width: 200px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $timeoffs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $to): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="text-bold"><?php echo e($to->user->nome); ?></td>
                                    <td>
                                        <?php switch($to->tipo):
                                            case ('ferias'): ?> <span class="badge bg-indigo">Férias</span> <?php break; ?>
                                            <?php case ('medico'): ?> <span class="badge bg-danger">Médico</span> <?php break; ?>
                                            <?php case ('pessoal'): ?> <span class="badge bg-lightblue">Pessoal</span> <?php break; ?>
                                            <?php default: ?> <span class="badge bg-secondary">Outros</span>
                                        <?php endswitch; ?>
                                    </td>
                                    <td><?php echo e($to->data_inicio->format('d/m/Y')); ?></td>
                                    <td><?php echo e($to->data_fim->format('d/m/Y')); ?></td>
                                    <td>
                                        <?php switch($to->status):
                                            case ('aprovado'): ?> <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aprovado</span> <?php break; ?>
                                            <?php case ('rejeitado'): ?> <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rejeitado</span> <?php break; ?>
                                            <?php default: ?> <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendente</span>
                                        <?php endswitch; ?>
                                    </td>
                                    <td><?php echo e(Str::limit($to->observacao, 40) ?? '-'); ?></td>
                                    <td>
                                        <?php if($to->status === 'pendente' && auth()->user()->can('gerenciar-empresa')): ?>
                                            <form action="<?php echo e(route('clockify.timeoff.approve', $to->id)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-xs btn-success"><i class="fa fa-check"></i> Aprovar</button>
                                            </form>
                                            <form action="<?php echo e(route('clockify.timeoff.reject', $to->id)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-times"></i> Rejeitar</button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="<?php echo e(route('clockify.timeoff.destroy', $to->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente remover esta solicitação?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-xs btn-default text-danger"><i class="fa fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">Nenhuma solicitação de Time Off encontrada.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    <?php echo e($timeoffs->links()); ?>

                </div>
            </div>
        </div>

        <!-- Solicitar Time Off -->
        <div class="col-md-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Solicitar Time Off</h3>
                </div>
                <form action="<?php echo e(route('clockify.timeoff.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="card-body">
                        <?php if(auth()->user()->can('gerenciar-empresa')): ?>
                            <div class="form-group">
                                <label for="user_id">Colaborador</label>
                                <select name="user_id" id="user_id" class="form-control form-control-sm" required>
                                    <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($usuario->id); ?>" <?php if($usuario->id === auth()->id()): echo 'selected'; endif; ?>><?php echo e($usuario->nome); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="user_id" value="<?php echo e(auth()->id()); ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="tipo">Tipo de Folga <span class="text-danger">*</span></label>
                            <select name="tipo" id="tipo" class="form-control form-control-sm" required>
                                <option value="ferias">Férias</option>
                                <option value="medico">Médico (Licença Saúde)</option>
                                <option value="pessoal">Assuntos Pessoais</option>
                                <option value="outro">Outro Motivo</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="data_inicio">Data de Início <span class="text-danger">*</span></label>
                                    <input type="date" name="data_inicio" id="data_inicio" class="form-control form-control-sm" required value="<?php echo e(date('Y-m-d')); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="data_fim">Data de Fim <span class="text-danger">*</span></label>
                                    <input type="date" name="data_fim" id="data_fim" class="form-control form-control-sm" required value="<?php echo e(date('Y-m-d')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="observacao">Observação / Justificativa</label>
                            <textarea name="observacao" id="observacao" rows="3" class="form-control form-control-sm" placeholder="Opcional..."></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-sm btn-success float-right">Enviar Solicitação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/clockify/timeoff.blade.php ENDPATH**/ ?>