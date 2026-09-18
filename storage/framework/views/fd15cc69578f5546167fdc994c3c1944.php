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
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary"><?php echo e($times->total()); ?></span></h3>
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
                        <?php echo $__env->make('partials.ux-view-toggle', ['viewKey' => 'times_index_view'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clockify.times.store')): ?>
                            <button type="button" class="btn btn-sm btn-primary" id="btn-create-time">
                                <i class="fas fa-plus mr-1"></i> Nova Equipe
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-0 ux-index-body" data-view="cards">
                    <div class="ux-cards">
                        <?php $__empty_1 = true; $__currentLoopData = $times; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $time): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <article class="ux-card">
                                <div class="ux-card__accent"></div>
                                <div class="ux-card__head">
                                    <div class="ux-card__avatar"><i class="fas fa-users"></i></div>
                                    <div class="ux-card__head-info">
                                        <div class="ux-card__title"><?php echo e($time->nome); ?></div>
                                        <div class="ux-card__sub"><?php echo e($time->descricao ?: 'Sem descrição'); ?></div>
                                    </div>
                                    <div class="ux-card__badges">
                                        <span class="ux-card__badge ux-card__badge--blue"><?php echo e($time->users->count()); ?> membro(s)</span>
                                        <?php if($time->lider): ?>
                                            <span class="ux-card__badge ux-card__badge--blue" title="Líder da equipe"><i class="fas fa-star mr-1"></i><?php echo e($time->lider->nome); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="ux-card__meta">
                                    <div class="ux-card__meta-row ux-card__meta-row--full">
                                        <span class="ux-card__meta-label"><i class="fas fa-user-friends mr-1"></i>Membros</span>
                                        <strong class="ux-card__meta-value" style="white-space: normal;">
                                            <?php $__empty_2 = true; $__currentLoopData = $time->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                <span class="badge badge-secondary mb-1" title="<?php echo e($u->email); ?>"><?php echo e($u->nome); ?></span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                <span class="text-muted">Sem membros</span>
                                            <?php endif; ?>
                                        </strong>
                                    </div>
                                </div>
                                <div class="ux-card__foot">
                                    <?php if($time->podeSerGerenciadoPor(auth()->user())): ?>
                                        <button type="button" class="btn btn-xs btn-warning"
                                            onclick="editTime(<?php echo e(json_encode($time->only(['id', 'nome', 'descricao', 'lider_id']))); ?>, <?php echo e(json_encode($time->users->pluck('id')->values())); ?>)">
                                            <i class="fa fa-edit mr-1"></i>Editar
                                        </button>
                                        <form action="<?php echo e(route('clockify.times.destroy', $time->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este time?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash mr-1"></i>Excluir</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center text-muted py-4 w-100">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                Nenhum time cadastrado.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="ux-table table-responsive">
                        <table class="table table-hover table-striped table-bordered table-sm text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Nome do Time</th>
                                <th>Líder</th>
                                <th>Descrição</th>
                                <th>Membros</th>
                                <th style="width: 150px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $times; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $time): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="align-middle font-weight-bold"><?php echo e($time->nome); ?></td>
                                    <td class="align-middle">
                                        <?php if($time->lider): ?>
                                            <span class="badge badge-warning" title="Líder da equipe"><i class="fas fa-star mr-1"></i><?php echo e($time->lider->nome); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted text-xs">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?php echo e($time->descricao ?? '-'); ?></td>
                                    <td class="align-middle">
                                        <?php $__currentLoopData = $time->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="badge badge-secondary" title="<?php echo e($u->email); ?>"><?php echo e($u->nome); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($time->users->isEmpty()): ?>
                                            <span class="text-muted text-xs">Sem membros</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if($time->podeSerGerenciadoPor(auth()->user())): ?>
                                            <button type="button" class="btn btn-xs btn-warning mr-1"
                                                onclick="editTime(<?php echo e(json_encode($time->only(['id', 'nome', 'descricao', 'lider_id']))); ?>, <?php echo e(json_encode($time->users->pluck('id')->values())); ?>)">
                                                <i class="fa fa-edit"></i> Editar
                                            </button>
                                            <form action="<?php echo e(route('clockify.times.destroy', $time->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este time?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Excluir</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted text-xs">Somente leitura</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                        Nenhum time cadastrado.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <?php if($times->hasPages()): ?>
                    <div class="card-footer py-2 d-flex justify-content-end">
                        <?php echo e($times->appends(request()->query())->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Side Drawer Component -->
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['id' => 'timeDrawer','icon' => 'fas fa-users','title' => 'Nova Equipe','formId' => 'time-form','action' => ''.e(route('clockify.times.store')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'timeDrawer','icon' => 'fas fa-users','title' => 'Nova Equipe','formId' => 'time-form','action' => ''.e(route('clockify.times.store')).'']); ?>
        <input type="hidden" name="id" id="time_id" value="<?php echo e(old('id')); ?>">
        <div class="form-group">
            <label for="nome">Nome do Time <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="nome" class="form-control form-control-sm <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('nome')); ?>" required placeholder="Ex: Time de Design">
            <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="invalid-feedback"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea name="descricao" id="descricao" rows="2" class="form-control form-control-sm" placeholder="Opcional..."><?php echo e(old('descricao')); ?></textarea>
        </div>
        <?php if(auth()->user()->isAdministrador()): ?>
            <div class="form-group">
                <label for="lider_id">Líder da Equipe</label>
                <select name="lider_id" id="lider_id" class="form-control form-control-sm">
                    <option value="">Sem líder definido</option>
                    <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($usuario->id); ?>" <?php echo e((string) old('lider_id') === (string) $usuario->id ? 'selected' : ''); ?>><?php echo e($usuario->nome); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <small class="text-muted">O líder aprova folgas e gerencia os membros desta equipe.</small>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label>Selecionar Membros</label>
            <div style="max-height: 240px; overflow-y: auto; border: 1px solid #ced4da; padding: 10px; border-radius: 4px;">
                <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="custom-control custom-checkbox mb-1">
                        <input class="custom-control-input user-checkbox" type="checkbox" name="users[]" id="user-<?php echo e($usuario->id); ?>" value="<?php echo e($usuario->id); ?>"
                            <?php echo e(in_array($usuario->id, (array) old('users', [])) ? 'checked' : ''); ?>>
                        <label for="user-<?php echo e($usuario->id); ?>" class="custom-control-label font-weight-normal text-sm"><?php echo e($usuario->nome); ?></label>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
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
            $('#btn-create-time').on('click', function() {
                $('#timeDrawerTitle').text('Nova Equipe');
                $('#time-form').attr('action', '<?php echo e(route('clockify.times.store')); ?>');
                $('#time-formMethod').val('POST');
                $('#time-form')[0].reset();
                $('#time_id').val('');
                $('.user-checkbox').prop('checked', false);
                $('#timeDrawerBackdrop').addClass('show');
                $('#timeDrawer').addClass('open');
            });

            <?php if($errors->any()): ?>
                <?php if(old('_method') === 'PUT'): ?>
                    $('#timeDrawerTitle').text('Editar Equipe');
                    $('#time-form').attr('action', '<?php echo e(url('admin/times')); ?>/' + '<?php echo e(old('id')); ?>');
                    $('#time-formMethod').val('PUT');
                <?php else: ?>
                    $('#timeDrawerTitle').text('Nova Equipe');
                    $('#time-form').attr('action', '<?php echo e(route('clockify.times.store')); ?>');
                    $('#time-formMethod').val('POST');
                <?php endif; ?>
                $('#timeDrawerBackdrop').addClass('show');
                $('#timeDrawer').addClass('open');
            <?php endif; ?>
        });

        function editTime(time, userIds) {
            $('#timeDrawerTitle').text('Editar Equipe: ' + time.nome);
            $('#time-form').attr('action', '<?php echo e(url('admin/times')); ?>/' + time.id);
            $('#time-formMethod').val('PUT');
            $('#time_id').val(time.id);
            $('#time-form #nome').val(time.nome);
            $('#time-form #descricao').val(time.descricao);
            $('#time-form #lider_id').val(time.lider_id || '');

            $('.user-checkbox').prop('checked', false);
            userIds.forEach(function(id) {
                $('#user-' + id).prop('checked', true);
            });

            $('#timeDrawerBackdrop').addClass('show');
            $('#timeDrawer').addClass('open');
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/clockify/times.blade.php ENDPATH**/ ?>