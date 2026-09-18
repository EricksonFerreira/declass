<?php $__env->startSection('title', 'Membros da Equipe'); ?>

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
<?php $component = App\View\Components\PageTitle::resolve(['title' => 'Membros da Equipe'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
    <div class="card">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-user-clock mr-1"></i> Registros
                <span class="badge badge-primary ml-1"><?php echo e($users->total()); ?></span>
            </h3>
            <div class="card-tools">
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <form action="<?php echo e(route('relatorios.membros')); ?>" method="GET" class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" name="search" class="form-control float-right" placeholder="Procurar..." value="<?php echo e(request('search')); ?>">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-novo-membro">
                        <i class="fas fa-plus mr-1"></i> Novo
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-sm text-nowrap mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-3">Nome</th>
                        <th>Email</th>
                        <th>Cargo</th>
                        <th class="pr-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="pl-3 font-weight-bold"><?php echo e($user->name); ?></td>
                            <td><?php echo e($user->email); ?></td>
                            <td><?php echo e($user->cargo ?? 'Sem Cargo'); ?></td>
                            <td class="pr-3">
                                <button type="button" class="btn btn-xs btn-warning btn-editar-membro"
                                        data-id="<?php echo e($user->id); ?>"
                                        data-name="<?php echo e($user->name); ?>"
                                        data-email="<?php echo e($user->email); ?>"
                                        data-cargo="<?php echo e($user->cargo); ?>">
                                    <i class="fa fa-edit"></i> Editar
                                </button>
                                <form action="<?php echo e(route('relatorios.membros.reset-password', $user->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="button" class="btn btn-xs btn-info" onclick="confirmResetPassword(this)">
                                        <i class="fa fa-key"></i> Resetar Senha
                                    </button>
                                </form>
                                <form action="<?php echo e(route('relatorios.membros.destroy', $user->id)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelete(this)"><i class="fa fa-trash"></i> Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-user-clock fa-2x mb-2 d-block"></i>
                                Nenhum membro encontrado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            <?php echo e($users->appends(request()->query())->links()); ?>

        </div>
    </div>

    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['id' => 'membroDrawer','icon' => 'fas fa-user-cog','title' => 'Cadastrar Membro','formId' => 'membroForm','action' => ''.e(route('relatorios.membros.store')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'membroDrawer','icon' => 'fas fa-user-cog','title' => 'Cadastrar Membro','formId' => 'membroForm','action' => ''.e(route('relatorios.membros.store')).'']); ?>
        <input type="hidden" name="id" id="membro_id" value="<?php echo e(old('id')); ?>">
        <div class="form-group">
            <label for="membro_name">Nome <span class="text-danger">*</span></label>
            <input type="text" name="name" id="membro_name" class="form-control form-control-sm <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required placeholder="Ex: João Silva" value="<?php echo e(old('name')); ?>">
            <?php $__errorArgs = ['name'];
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
            <label for="membro_email">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" id="membro_email" class="form-control form-control-sm <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required placeholder="email@exemplo.com" value="<?php echo e(old('email')); ?>">
            <?php $__errorArgs = ['email'];
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
        <div class="form-group mb-0">
            <label for="membro_cargo">Cargo <span class="text-danger">*</span></label>
            <input type="text" name="cargo" id="membro_cargo" class="form-control form-control-sm <?php $__errorArgs = ['cargo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required placeholder="Ex: Desenvolvedor, Designer" value="<?php echo e(old('cargo')); ?>">
            <?php $__errorArgs = ['cargo'];
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

<?php $__env->startSection('css'); ?>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
    <script>
        <?php if(session('success')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: "<?php echo e(session('success')); ?>",
                timer: 3000,
                showConfirmButton: false
            });
        <?php endif; ?>

        <?php if(session('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: "<?php echo e(session('error')); ?>",
                timer: 4000
            });
        <?php endif; ?>

        const $modal = $('#membroDrawer');
        const $backdrop = $('#membroDrawerBackdrop');
        const $form = $('#membroForm');
        const $method = $('#membroFormMethod');
        const $title = $('#membroDrawerTitle');

        function abrirModal(titulo, action, method, data = null) {
            $title.text(titulo);
            $form.attr('action', action);
            $method.val(method);

            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').remove();

            if (data) {
                $('#membro_id').val(data.id || '');
                $('#membro_name').val(data.name);
                $('#membro_email').val(data.email);
                $('#membro_cargo').val(data.cargo);
            } else {
                $form[0].reset();
                $('#membro_id').val('');
            }

            $backdrop.addClass('show');
            $modal.addClass('open');
        }

        function fecharModal() {
            $modal.removeClass('open');
            $backdrop.removeClass('show');
        }

        $('#btn-novo-membro').on('click', function () {
            abrirModal('Cadastrar Membro', "<?php echo e(route('relatorios.membros.store')); ?>", 'POST');
        });

        $(document).on('click', '.btn-editar-membro', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const id = $btn.data('id');
            abrirModal('Editar Membro', "<?php echo e(url('admin/relatorios/membros')); ?>/" + id, 'PUT', {
                id: id,
                name: $btn.data('name'),
                email: $btn.data('email'),
                cargo: $btn.data('cargo')
            });
        });

        $('#closemembroDrawer, #cancelmembroDrawer, #membroDrawerBackdrop').on('click', fecharModal);

        <?php if($errors->any()): ?>
            abrirModal(
                "<?php echo e(old('_method', 'POST') === 'PUT' ? 'Editar Membro' : 'Cadastrar Membro'); ?>",
                "<?php echo e(old('_method', 'POST') === 'PUT' ? url('admin/relatorios/membros') . '/' . old('id', 0) : route('relatorios.membros.store')); ?>",
                "<?php echo e(old('_method', 'POST') === 'PUT' ? 'PUT' : 'POST'); ?>",
                {
                    id: "<?php echo e(old('id')); ?>",
                    name: "<?php echo e(old('name')); ?>",
                    email: "<?php echo e(old('email')); ?>",
                    cargo: "<?php echo e(old('cargo')); ?>"
                }
            );
        <?php endif; ?>

        function confirmResetPassword(button) {
            const form = $(button).closest('form');
            Swal.fire({
                title: 'Resetar Senha?',
                text: "A senha do membro será resetada para 'Senh@123' e ele será forçado a alterá-la no próximo login.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0ea5e9',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sim, resetar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function confirmDelete(button) {
            const form = $(button).closest('form');
            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não poderá ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/relatorios/membros.blade.php ENDPATH**/ ?>