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
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary"><?php echo e($funcoes->total()); ?></span></h3>
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
                        <?php echo $__env->make('partials.ux-view-toggle', ['viewKey' => 'funcoes_index_view'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <button type="button" class="btn btn-sm btn-primary" id="btn-create-funcao">
                            <i class="fas fa-plus mr-1"></i> Nova Função
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 ux-index-body" data-view="cards">
                    <div class="ux-cards">
                        <?php $__empty_1 = true; $__currentLoopData = $funcoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $funcao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <article class="ux-card">
                                <div class="ux-card__accent"></div>
                                <div class="ux-card__head">
                                    <div class="ux-card__avatar"><i class="fas fa-briefcase"></i></div>
                                    <div class="ux-card__head-info">
                                        <div class="ux-card__title"><?php echo e($funcao->nome); ?></div>
                                        <div class="ux-card__sub"><?php echo e($funcao->users_count); ?> colaborador(es) vinculado(s)</div>
                                    </div>
                                    <div class="ux-card__badges">
                                        <span class="ux-card__badge ux-card__badge--<?php echo e($funcao->ativo ? 'green' : 'red'); ?>">
                                            <?php echo e($funcao->ativo ? 'Ativa' : 'Inativa'); ?>

                                        </span>
                                    </div>
                                </div>
                                <div class="ux-card__meta">
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="fas fa-money-bill-wave mr-1"></i>Custo/Hora</span>
                                        <strong class="ux-card__meta-value">
                                            <?php echo e($funcao->custo_hora !== null ? 'R$ ' . number_format($funcao->custo_hora, 2, ',', '.') : '—'); ?>

                                        </strong>
                                    </div>
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="fas fa-users mr-1"></i>Colaboradores</span>
                                        <strong class="ux-card__meta-value"><?php echo e($funcao->users_count); ?></strong>
                                    </div>
                                </div>
                                <div class="ux-card__foot">
                                    <button type="button" class="btn btn-xs btn-warning"
                                        onclick="editFuncao(<?php echo e(json_encode($funcao)); ?>)">
                                        <i class="fa fa-edit mr-1"></i>Editar
                                    </button>
                                    <form action="<?php echo e(route('clockify.funcoes.destroy', $funcao->id)); ?>" method="POST" class="d-inline"
                                        onsubmit="return confirm('Deseja realmente excluir esta função?<?php echo e($funcao->users_count > 0 ? ' Os ' . $funcao->users_count . ' colaborador(es) vinculado(s) ficarão sem função.' : ''); ?>')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash mr-1"></i>Excluir</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center text-muted py-4 w-100">
                                <i class="fas fa-briefcase fa-2x mb-2 d-block"></i>
                                Nenhuma função cadastrada.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="ux-table table-responsive">
                        <table class="table table-hover table-striped table-bordered table-sm text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th style="width: 130px;" class="text-right">Custo/Hora</th>
                                <th style="width: 100px;" class="text-center">Colaboradores</th>
                                <th style="width: 90px;">Status</th>
                                <th style="width: 150px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $funcoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $funcao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="align-middle font-weight-bold"><?php echo e($funcao->nome); ?></td>
                                    <td class="align-middle text-right">
                                        <?php if($funcao->custo_hora !== null): ?>
                                            R$ <?php echo e(number_format($funcao->custo_hora, 2, ',', '.')); ?>

                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-secondary"><?php echo e($funcao->users_count); ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-<?php echo e($funcao->ativo ? 'success' : 'danger'); ?>">
                                            <?php echo e($funcao->ativo ? 'Ativa' : 'Inativa'); ?>

                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button type="button" class="btn btn-xs btn-warning mr-1"
                                            onclick="editFuncao(<?php echo e(json_encode($funcao)); ?>)">
                                            <i class="fa fa-edit"></i> Editar
                                        </button>
                                        <form action="<?php echo e(route('clockify.funcoes.destroy', $funcao->id)); ?>" method="POST" class="d-inline"
                                            onsubmit="return confirm('Deseja realmente excluir esta função?<?php echo e($funcao->users_count > 0 ? ' Os ' . $funcao->users_count . ' colaborador(es) vinculado(s) ficarão sem função.' : ''); ?>')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-briefcase fa-2x mb-2 d-block"></i>
                                        Nenhuma função cadastrada.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
                <?php if($funcoes->hasPages()): ?>
                    <div class="card-footer py-2 d-flex justify-content-end">
                        <?php echo e($funcoes->appends(request()->query())->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Side Drawer Component -->
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['id' => 'funcaoDrawer','icon' => 'fas fa-briefcase','title' => 'Nova Função','formId' => 'funcao-form','action' => ''.e(route('clockify.funcoes.store')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'funcaoDrawer','icon' => 'fas fa-briefcase','title' => 'Nova Função','formId' => 'funcao-form','action' => ''.e(route('clockify.funcoes.store')).'']); ?>
        <input type="hidden" name="id" id="funcao_id" value="<?php echo e(old('id')); ?>">
        <div class="form-group">
            <label for="nome">Nome da Função <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="nome" class="form-control form-control-sm <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('nome')); ?>" required placeholder="Ex: Técnico, Engenheiro Auxiliar, Coordenador...">
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
            <label for="custo_hora">Custo por hora (R$)</label>
            <input type="number" name="custo_hora" id="custo_hora" step="0.01" min="0" class="form-control form-control-sm <?php $__errorArgs = ['custo_hora'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('custo_hora')); ?>" placeholder="0,00">
            <?php $__errorArgs = ['custo_hora'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="invalid-feedback"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <small class="text-muted">Custo interno padrão da função. Pode ser sobrescrito por colaborador no cadastro de usuário.</small>
        </div>
        <div class="form-group" id="status-group" style="display: none;">
            <label for="ativo">Status</label>
            <select name="ativo" id="ativo" class="form-control form-control-sm">
                <option value="1">Ativo</option>
                <option value="0">Inativo</option>
            </select>
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
            $('#btn-create-funcao').on('click', function() {
                $('#funcaoDrawerTitle').text('Nova Função');
                $('#funcao-form').attr('action', '<?php echo e(route('clockify.funcoes.store')); ?>');
                $('#funcao-formMethod').val('POST');
                $('#funcao-form')[0].reset();
                $('#funcao_id').val('');
                $('#status-group').hide();
                $('#funcaoDrawerBackdrop').addClass('show');
                $('#funcaoDrawer').addClass('open');
            });

            <?php if($errors->any()): ?>
                <?php if(old('_method') === 'PUT'): ?>
                    $('#funcaoDrawerTitle').text('Editar Função');
                    $('#funcao-form').attr('action', '<?php echo e(url('admin/funcoes')); ?>/' + '<?php echo e(old('id')); ?>');
                    $('#funcao-formMethod').val('PUT');
                    $('#status-group').show();
                <?php else: ?>
                    $('#funcaoDrawerTitle').text('Nova Função');
                    $('#funcao-form').attr('action', '<?php echo e(route('clockify.funcoes.store')); ?>');
                    $('#funcao-formMethod').val('POST');
                <?php endif; ?>
                $('#funcaoDrawerBackdrop').addClass('show');
                $('#funcaoDrawer').addClass('open');
            <?php endif; ?>
        });

        function editFuncao(funcao) {
            $('#funcaoDrawerTitle').text('Editar Função: ' + funcao.nome);
            $('#funcao-form').attr('action', '<?php echo e(url('admin/funcoes')); ?>/' + funcao.id);
            $('#funcao-formMethod').val('PUT');
            $('#funcao_id').val(funcao.id);
            $('#nome').val(funcao.nome);
            $('#custo_hora').val(funcao.custo_hora);
            $('#ativo').val(funcao.ativo ? '1' : '0');

            $('#status-group').show();
            $('#funcaoDrawerBackdrop').addClass('show');
            $('#funcaoDrawer').addClass('open');
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\clockify\funcoes.blade.php ENDPATH**/ ?>