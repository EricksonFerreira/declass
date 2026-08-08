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
        <!-- Listagem de Tags -->
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary"><?php echo e($tags->total()); ?></span></h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 10px;">
                        <button type="button" class="btn btn-sm btn-primary" id="btn-create-tag">
                            <i class="fas fa-plus mr-1"></i> Novo Tag
                        </button>
                        
                        <form action="<?php echo e(route('clockify.tags.index')); ?>" method="GET" class="input-group input-group-sm mb-0" style="width: 200px;">
                            <input type="text" name="search" class="form-control float-right" placeholder="Procurar..." value="<?php echo e(request('search')); ?>">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped table-bordered table-sm text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Status</th>
                                <th style="width: 150px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="align-middle font-weight-bold"><?php echo e($tag->nome); ?></td>
                                    <td class="align-middle">
                                        <span class="badge badge-<?php echo e($tag->ativo ? 'success' : 'danger'); ?>">
                                            <?php echo e($tag->ativo ? 'Ativa' : 'Inativa'); ?>

                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button type="button" class="btn btn-xs btn-warning mr-1" 
                                            onclick="editTag(<?php echo e(json_encode($tag)); ?>)">
                                            <i class="fa fa-edit"></i> Editar
                                        </button>
                                        <form action="<?php echo e(route('clockify.tags.destroy', $tag->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir esta tag?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="fas fa-tags fa-2x mb-2 d-block"></i>
                                        Nenhuma tag cadastrada.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($tags->hasPages()): ?>
                    <div class="card-footer py-2 d-flex justify-content-end">
                        <?php echo e($tags->appends(request()->query())->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Side Drawer Component -->
    <?php if (isset($component)) { $__componentOriginalfcb525a84b79fe7e89c655d15af702a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfcb525a84b79fe7e89c655d15af702a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.side-drawer','data' => ['id' => 'tagDrawer','title' => 'Criar Nova Tag','formId' => 'tag-form','action' => ''.e(route('clockify.tags.store')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('side-drawer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'tagDrawer','title' => 'Criar Nova Tag','formId' => 'tag-form','action' => ''.e(route('clockify.tags.store')).'']); ?>
        <div class="form-group">
            <label for="nome">Nome da Tag <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="nome" class="form-control form-control-sm" required placeholder="Ex: Faturável, Em Análise...">
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
<?php if (isset($__attributesOriginalfcb525a84b79fe7e89c655d15af702a1)): ?>
<?php $attributes = $__attributesOriginalfcb525a84b79fe7e89c655d15af702a1; ?>
<?php unset($__attributesOriginalfcb525a84b79fe7e89c655d15af702a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfcb525a84b79fe7e89c655d15af702a1)): ?>
<?php $component = $__componentOriginalfcb525a84b79fe7e89c655d15af702a1; ?>
<?php unset($__componentOriginalfcb525a84b79fe7e89c655d15af702a1); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(function() {
            $('#btn-create-tag').on('click', function() {
                $('#tagDrawerTitle').text('Criar Nova Tag');
                $('#tag-form').attr('action', '<?php echo e(route('clockify.tags.store')); ?>');
                $('#tag-formMethod').val('POST');
                $('#tag-form')[0].reset();
                $('#status-group').hide();
                $('#tagDrawerBackdrop').addClass('show');
                $('#tagDrawer').addClass('open');
            });
        });

        function editTag(tag) {
            $('#tagDrawerTitle').text('Editar Tag: ' + tag.nome);
            $('#tag-form').attr('action', '/admin/clockify/tags/' + tag.id);
            $('#tag-formMethod').val('PUT');
            $('#nome').val(tag.nome);
            $('#ativo').val(tag.ativo ? "1" : "0");
            
            $('#status-group').show();
            $('#tagDrawerBackdrop').addClass('show');
            $('#tagDrawer').addClass('open');
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/clockify/tags.blade.php ENDPATH**/ ?>