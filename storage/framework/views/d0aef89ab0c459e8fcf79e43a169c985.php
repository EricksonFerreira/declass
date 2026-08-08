<form action="<?php echo e($action); ?>" method="POST" class="d-inline delete-form">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>

    <button
        type="button"
        class="btn btn-xs <?php echo e($btnClass ?? 'btn-danger'); ?> delete-button"
        data-confirm-title="<?php echo e($tituloMensagem ?? 'Você tem certeza?'); ?>"
        data-confirm-message="<?php echo e($mensagem ?? 'Esta ação não pode ser desfeita!'); ?>"
        data-confirm-button="<?php echo e($textoConfirmacao ?? 'Sim, excluir!'); ?>"
        data-confirm-color="<?php echo e($corConfirmacao ?? '#d33'); ?>"
    >
        <i class="<?php echo e($icone ?? 'fa fa-trash'); ?>"></i>
        <?php echo e($titulo ?? 'Excluir'); ?>

    </button>
</form>

<?php if (! $__env->hasRenderedOnce('6d246295-52a7-426f-9c9c-b17051128426')): $__env->markAsRenderedOnce('6d246295-52a7-426f-9c9c-b17051128426'); ?>
    <script>
        document.addEventListener('click', function (event) {
            const button = event.target.closest('.delete-button');

            if (!button) {
                return;
            }

            event.preventDefault();

            const form = button.closest('form.delete-form');

            if (!form) {
                return;
            }

            const title = button.dataset.confirmTitle || 'Você tem certeza?';
            const message = button.dataset.confirmMessage || 'Esta ação não pode ser desfeita!';
            const confirmButtonText = button.dataset.confirmButton || 'Sim, excluir!';
            const confirmButtonColor = button.dataset.confirmColor || '#d33';

            if (
                typeof window.Swal !== 'undefined'
                && typeof window.Swal.fire === 'function'
            ) {
                window.Swal.fire({
                    title: title,
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: confirmButtonColor,
                    cancelButtonColor: '#6c757d',
                    focusCancel: true,
                }).then(function (result) {
                    if (result && result.isConfirmed) {
                        form.submit();
                    }
                });

                return;
            }

            if (window.confirm(title + '\n\n' + message)) {
                form.submit();
            }
        });
    </script>
<?php endif; ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/components/delete-button.blade.php ENDPATH**/ ?>