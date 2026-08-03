<div class="card card-soft mb-3">
    <div class="card-header">Resumo financeiro</div>
    <div class="card-body">
        <div class="mb-3">
            <div class="text-muted small mb-1">Cobranças</div>
            <div class="d-flex align-items-center justify-content-between">
                <strong class="text-dark">Total das cobranças</strong>
                <strong class="text-success">R$ <?php echo e(number_format($resumoFinanceiro['total_cobrancas'] ?? 0, 2, ',', '.')); ?></strong>
            </div>
            <div class="mt-2 small text-muted">
                Somente danos com decisãoo de cobrar o cliente.
            </div>
        </div>

        <div class="border-top pt-3">
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted">Caução recebida</span>
                <span class="text-dark">R$ <?php echo e(number_format($resumoFinanceiro['valor_caucao'] ?? 0, 2, ',', '.')); ?></span>
            </div>
            <div class="d-flex align-items-center justify-content-between mt-2">
                <span class="text-muted">Valor a devolver</span>
                <span class="text-primary font-weight-bold">R$ <?php echo e(number_format($resumoFinanceiro['valor_devolver'] ?? 0, 2, ',', '.')); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="card card-soft">
    <div class="card-header">Pendências para finalizar</div>
    <div class="card-body">
        <div class="side-list">
            <div class="side-item">
                <strong><i class="fas fa-check-circle text-success mr-1"></i>Revisar danos</strong>
                
            
                <strong><i class="fas fa-exclamation-circle text-warning mr-1"></i>Definir caução</strong>
                
            
                <strong><i class="fas fa-check-circle text-success mr-1"></i>Concluir revisão</strong>
                
            </div>
        </div>

        <div class="mt-3">
            <form method="POST" action="<?php echo e(route('reserva.checkout.revisao.concluir', $codigoReserva)); ?>" class="js-form-concluir-revisao">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="sem_pendencias" value="1">
                <button type="submit" class="btn btn-success btn-block font-weight-bold py-3 js-btn-concluir-revisao">
                    <i class="fas fa-check-circle mr-1"></i>Finalizar revisão e concluir reserva
                </button>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('js'); ?>
    <script>
        (function () {
            const form = document.querySelector('.js-form-concluir-revisao');
            const button = document.querySelector('.js-btn-concluir-revisao');

            if (!form || !button) {
                return;
            }

            form.addEventListener('submit', function (event) {
                if (form.dataset.confirmed === '1') {
                    return;
                }

                event.preventDefault();

                if (typeof window.Swal !== 'undefined' && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        title: 'Finalizar revisão?',
                        text: 'Ao confirmar, a reserva será concluída e essa ação não poderá ser desfeita.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, finalizar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = '1';
                            form.submit();
                        }
                    });
                    return;
                }

                if (confirm('Finalizar revisão e concluir a reserva?')) {
                    form.dataset.confirmed = '1';
                    form.submit();
                }
            });
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('js'); ?>
    <script>
        (function () {
            const form = document.querySelector('.js-form-concluir-revisao');
            const button = document.querySelector('.js-btn-concluir-revisao');

            if (!form || typeof window.Swal === 'undefined' || typeof window.Swal.fire !== 'function') {
                return;
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                if (button) button.blur();

                window.Swal.fire({
                    icon: 'warning',
                    title: 'Concluir revisão?',
                    text: 'Depois de concluir a revisão, não sera mais possivel fazer modificacoes nesta reserva.',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, concluir',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        })();
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/reserva/partials/revisao_checkout_sidebar.blade.php ENDPATH**/ ?>