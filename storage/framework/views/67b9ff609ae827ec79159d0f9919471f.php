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

<?php $__env->startPush('css'); ?>
    <?php echo $__env->make('partials.ux-index-styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php if(!empty($filters)): ?>
        <div class="veiculo-filter-sticky">
            <?php if (isset($component)) { $__componentOriginalb68d555c13273f9d26aedfa8ff6c880d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d = $attributes; } ?>
<?php $component = App\View\Components\Filter::resolve(['filters' => $filters,'additionalFilters' => $additionalFilters,'itensFiltered' => $itensFiltered] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Filter::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['nomeFiltro' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($nomeFiltro)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d)): ?>
<?php $attributes = $__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d; ?>
<?php unset($__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb68d555c13273f9d26aedfa8ff6c880d)): ?>
<?php $component = $__componentOriginalb68d555c13273f9d26aedfa8ff6c880d; ?>
<?php unset($__componentOriginalb68d555c13273f9d26aedfa8ff6c880d); ?>
<?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Listagem de Folgas e Férias -->
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary"><?php echo e($timeoffs->total()); ?></span></h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 10px;">
                        <form action="<?php echo e(route($nomeFiltro . '.setPerPage')); ?>" method="POST" class="mb-0">
                            <?php echo csrf_field(); ?>
                            <?php if (isset($component)) { $__componentOriginal65c0c73c271c840668c600386ae808f3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal65c0c73c271c840668c600386ae808f3 = $attributes; } ?>
<?php $component = App\View\Components\PerPageSelector::resolve(['perPages' => $perPages,'selectedPerPage' => $perPage] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('per-page-selector'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\PerPageSelector::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal65c0c73c271c840668c600386ae808f3)): ?>
<?php $attributes = $__attributesOriginal65c0c73c271c840668c600386ae808f3; ?>
<?php unset($__attributesOriginal65c0c73c271c840668c600386ae808f3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal65c0c73c271c840668c600386ae808f3)): ?>
<?php $component = $__componentOriginal65c0c73c271c840668c600386ae808f3; ?>
<?php unset($__componentOriginal65c0c73c271c840668c600386ae808f3); ?>
<?php endif; ?>
                        </form>
                        <?php echo $__env->make('partials.ux-view-toggle', ['viewKey' => 'timeoff_index_view', 'default' => 'table'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <button type="button" class="btn btn-sm btn-primary" id="btn-create-timeoff">
                            <i class="fas fa-plus mr-1"></i> Solicitar Folga ou Férias
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 ux-index-body" data-view="table">
                    <div class="ux-cards">
                        <?php $__empty_1 = true; $__currentLoopData = $timeoffs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $to): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <article class="ux-card">
                                <div class="ux-card__accent"></div>
                                <div class="ux-card__head">
                                    <div class="ux-card__avatar"><i class="fas fa-plane"></i></div>
                                    <div class="ux-card__head-info">
                                        <div class="ux-card__title"><?php echo e($to->user->nome); ?></div>
                                        <div class="ux-card__sub">
                                            <?php switch($to->tipo):
                                                case ('ferias'): ?> Férias <?php break; ?>
                                                <?php case ('medico'): ?> Médico <?php break; ?>
                                                <?php case ('pessoal'): ?> Pessoal <?php break; ?>
                                                <?php default: ?> Outro
                                            <?php endswitch; ?>
                                        </div>
                                    </div>
                                    <div class="ux-card__badges">
                                        <?php switch(strtolower($to->status ?? '')):
                                            case ('aprovado'): ?> <span class="ux-card__badge ux-card__badge--green">Aprovado</span> <?php break; ?>
                                            <?php case ('rejeitado'): ?> <span class="ux-card__badge ux-card__badge--red">Rejeitado</span> <?php break; ?>
                                            <?php default: ?> <span class="ux-card__badge ux-card__badge--amber">Pendente</span>
                                        <?php endswitch; ?>
                                    </div>
                                </div>
                                <div class="ux-card__meta">
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="far fa-calendar-alt mr-1"></i>Período</span>
                                        <strong class="ux-card__meta-value"><?php echo e($to->data_inicio->format('d/m/Y')); ?> → <?php echo e($to->data_fim->format('d/m/Y')); ?></strong>
                                    </div>
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="fas fa-align-left mr-1"></i>Observação</span>
                                        <strong class="ux-card__meta-value"><?php echo e(Str::limit($to->observacao, 40) ?? '—'); ?></strong>
                                    </div>
                                </div>
                                <div class="ux-card__foot">
                                    <?php if(strtolower($to->status ?? '') === 'pendente' && auth()->user()->can('gerenciar-empresa')): ?>
                                        <form action="<?php echo e(route('clockify.timeoff.approve', $to->id)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-xs btn-success"><i class="fa fa-check mr-1"></i>Aprovar</button>
                                        </form>
                                        <form action="<?php echo e(route('clockify.timeoff.reject', $to->id)); ?>" method="POST" class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-times mr-1"></i>Rejeitar</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if(auth()->user()->can('gerenciar-empresa') || $to->user_id === auth()->id()): ?>
                                        <button type="button" class="btn btn-xs btn-warning btn-edit-timeoff"
                                            data-id="<?php echo e($to->id); ?>"
                                            data-user="<?php echo e($to->user_id); ?>"
                                            data-tipo="<?php echo e($to->tipo); ?>"
                                            data-inicio="<?php echo e($to->data_inicio->format('Y-m-d')); ?>"
                                            data-fim="<?php echo e($to->data_fim->format('Y-m-d')); ?>"
                                            data-obs="<?php echo e($to->observacao); ?>">
                                            <i class="fas fa-edit mr-1"></i>Editar
                                        </button>
                                    <?php endif; ?>
                                    <form action="<?php echo e(route('clockify.timeoff.destroy', $to->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente remover esta solicitação?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash mr-1"></i>Excluir</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center text-muted py-4 w-100">
                                <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                Nenhuma solicitação de folga ou férias encontrada.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="ux-table table-responsive">
                    <table class="table table-hover table-striped table-bordered table-sm text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Colaborador</th>
                                <th>Tipo</th>
                                <th>Início</th>
                                <th>Fim</th>
                                <th>Status</th>
                                <th>Observação</th>
                                <th style="width: 200px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $timeoffs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $to): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="align-middle text-bold"><?php echo e($to->user->nome); ?></td>
                                    <td class="align-middle">
                                        <?php switch($to->tipo):
                                            case ('ferias'): ?> <span class="badge bg-indigo">Férias</span> <?php break; ?>
                                            <?php case ('medico'): ?> <span class="badge bg-danger">Médico</span> <?php break; ?>
                                            <?php case ('pessoal'): ?> <span class="badge bg-lightblue">Pessoal</span> <?php break; ?>
                                            <?php default: ?> <span class="badge bg-secondary">Outros</span>
                                        <?php endswitch; ?>
                                    </td>
                                    <td class="align-middle"><?php echo e($to->data_inicio->format('d/m/Y')); ?></td>
                                    <td class="align-middle"><?php echo e($to->data_fim->format('d/m/Y')); ?></td>
                                    <td class="align-middle">
                                        <?php switch(strtolower($to->status ?? '')):
                                            case ('aprovado'): ?> <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aprovado</span> <?php break; ?>
                                            <?php case ('rejeitado'): ?> <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rejeitado</span> <?php break; ?>
                                            <?php default: ?> <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendente</span>
                                        <?php endswitch; ?>
                                    </td>
                                    <td class="align-middle"><?php echo e(Str::limit($to->observacao, 40) ?? '-'); ?></td>
                                    <td class="align-middle text-center">
                                        <?php if(strtolower($to->status ?? '') === 'pendente' && auth()->user()->can('gerenciar-empresa')): ?>
                                            <form action="<?php echo e(route('clockify.timeoff.approve', $to->id)); ?>" method="POST" class="d-inline mr-1">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-xs btn-success"><i class="fa fa-check"></i> Aprovar</button>
                                            </form>
                                            <form action="<?php echo e(route('clockify.timeoff.reject', $to->id)); ?>" method="POST" class="d-inline mr-1">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-times"></i> Rejeitar</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if(auth()->user()->can('gerenciar-empresa') || $to->user_id === auth()->id()): ?>
                                            <button type="button" class="btn btn-xs btn-info mr-1 btn-edit-timeoff"
                                                data-id="<?php echo e($to->id); ?>"
                                                data-user="<?php echo e($to->user_id); ?>"
                                                data-tipo="<?php echo e($to->tipo); ?>"
                                                data-inicio="<?php echo e($to->data_inicio->format('Y-m-d')); ?>"
                                                data-fim="<?php echo e($to->data_fim->format('Y-m-d')); ?>"
                                                data-obs="<?php echo e($to->observacao); ?>">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
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
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                        Nenhuma solicitação de folga ou férias encontrada.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <?php if($timeoffs->hasPages()): ?>
                    <div class="card-footer py-2 d-flex justify-content-end">
                        <?php echo e($timeoffs->appends(request()->query())->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Side Drawer Component -->
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['id' => 'timeoffDrawer','icon' => 'fas fa-plane','title' => 'Solicitar Folga ou Férias','formId' => 'timeoff-form','action' => ''.e(route('clockify.timeoff.store')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'timeoffDrawer','icon' => 'fas fa-plane','title' => 'Solicitar Folga ou Férias','formId' => 'timeoff-form','action' => ''.e(route('clockify.timeoff.store')).'']); ?>
        <input type="hidden" name="id" id="timeoff_id" value="<?php echo e(old('id')); ?>">
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
        <div class="form-row">
            <div class="col-md-6 form-group">
                <label for="data_inicio">Data de Início <span class="text-danger">*</span></label>
                <input type="date" name="data_inicio" id="data_inicio" class="form-control form-control-sm" required value="<?php echo e(date('Y-m-d')); ?>">
            </div>
            <div class="col-md-6 form-group">
                <label for="data_fim">Data de Fim <span class="text-danger">*</span></label>
                <input type="date" name="data_fim" id="data_fim" class="form-control form-control-sm" required value="<?php echo e(date('Y-m-d')); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="observacao">Observação / Justificativa</label>
            <textarea name="observacao" id="observacao" rows="3" class="form-control form-control-sm" placeholder="Opcional..."></textarea>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(function() {
            var updateUrlTemplate = "<?php echo e(route('clockify.timeoff.update', ['id' => '__ID__'])); ?>";
            var storeUrl = "<?php echo e(route('clockify.timeoff.store')); ?>";

            $('#btn-create-timeoff').on('click', function() {
                var form = $('#timeoff-form');
                form[0].reset();
                form.attr('action', storeUrl);
                $('#timeoff-formMethod').val('POST');
                $('#timeoff_id').val('');
                $('#timeoffDrawerTitle').text('Solicitar Folga ou Férias');
                $('#timeoffDrawerBackdrop').addClass('show');
                $('#timeoffDrawer').addClass('open');
            });

            <?php if($errors->any()): ?>
                <?php if(old('_method') === 'PUT'): ?>
                    $('#timeoffDrawerTitle').text('Editar Solicitação');
                    $('#timeoff-form').attr('action', updateUrlTemplate.replace('__ID__', '<?php echo e(old('id')); ?>'));
                    $('#timeoff-formMethod').val('PUT');
                <?php else: ?>
                    $('#timeoffDrawerTitle').text('Solicitar Folga ou Férias');
                    $('#timeoff-form').attr('action', storeUrl);
                    $('#timeoff-formMethod').val('POST');
                <?php endif; ?>
                $('#timeoffDrawerBackdrop').addClass('show');
                $('#timeoffDrawer').addClass('open');
            <?php endif; ?>

            // Editar: abre o mesmo drawer preenchido, enviando como PUT
            $('.btn-edit-timeoff').on('click', function() {
                var d = $(this).data();
                var form = $('#timeoff-form');
                form.attr('action', updateUrlTemplate.replace('__ID__', d.id));
                $('#timeoff-formMethod').val('PUT');
                $('#timeoffDrawerTitle').text('Editar Solicitação');
                $('#timeoff_id').val(d.id);
                $('#user_id').val(d.user);
                $('#tipo').val(d.tipo);
                $('#data_inicio').val(d.inicio);
                $('#data_fim').val(d.fim);
                $('#observacao').val(d.obs || '');
                $('#timeoffDrawerBackdrop').addClass('show');
                $('#timeoffDrawer').addClass('open');
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\clockify\timeoff.blade.php ENDPATH**/ ?>