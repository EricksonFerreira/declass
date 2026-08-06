<?php $__env->startSection('title', $titulo); ?>

<?php $__env->startSection('content_header'); ?>
    <?php if (isset($component)) { $__componentOriginal269900abaed345884ce342681cdc99f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal269900abaed345884ce342681cdc99f6 = $attributes; } ?>
<?php $component = App\View\Components\Breadcrumb::resolve(['breadcrumbs' => [['name' => 'Itens de Manutenção Preventiva', 'url' => route('manutencao.itens.index')], ['name' => 'Editar']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card manutencao-card shadow-sm border-0">
                <div class="card-header manutencao-card__header border-0">
                    <div class="d-flex align-items-start justify-content-between flex-wrap" style="gap:.75rem;">
                        <div>
                            <div class="manutencao-card__eyebrow">Itens de manutenção preventiva</div>
                            <h4 class="mb-1">Editar item</h4>
                            <p class="mb-0 text-muted">Atualize os dados sem perder o histórico dos planos vinculados.</p>
                        </div>
                        <div class="manutencao-card__badge">
                            <i class="fas fa-pen mr-1"></i> ID <?php echo e($item_manutencao->id); ?>

                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <form method="POST" action="<?php echo e(route('manutencao.itens.update', $item_manutencao->id)); ?>" class="p-3 p-md-4 js-form-item-manutencao">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="row">
                            <div class="col-12 col-lg-8">
                                <div class="form-group">
                                    <label for="nome">Nome</label>
                                    <input type="text" id="nome" name="nome" class="form-control form-control-lg" value="<?php echo e(old('nome', $item_manutencao->nome)); ?>" required>
                                    <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <small class="text-danger d-block mt-1"><?php echo e($message); ?></small>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="manutencao-card__tip">
                                    <strong>Contexto</strong>
                                    <span>Esse item é reutilizado nos planos e pode aparecer em várias grades de manutenção.</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="descricao">Descrição</label>
                            <textarea id="descricao" name="descricao" class="form-control" rows="5"><?php echo e(old('descricao', $item_manutencao->descricao)); ?></textarea>
                        </div>

                        <div class="d-flex flex-column flex-md-row justify-content-end" style="gap:.75rem;">
                            <a href="<?php echo e(route('manutencao.itens.index')); ?>" class="btn btn-outline-secondary px-4">Voltar</a>
                            <button type="submit" class="btn btn-primary px-4 js-submit-item">
                                <span class="js-submit-label">Salvar alterações</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.js-form-item-manutencao');
    if (!form) return;

    var button = form.querySelector('.js-submit-item');
    var submitting = false;

    form.addEventListener('submit', function () {
        if (submitting) return;
        submitting = true;

        if (button) {
            button.disabled = true;
            button.querySelector('.js-submit-label').textContent = 'Processando...';
            button.querySelector('i')?.classList.add('d-none');
        }
    });

    <?php if(session('success')): ?>
        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: <?php echo json_encode(session('success'), 15, 512) ?>,
                confirmButtonColor: '#0d6efd'
            });
        }
    <?php endif; ?>
});
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
    .manutencao-card { border-radius: 16px; overflow: hidden; }
    .manutencao-card__header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #f7fafc 0%, #eef4ff 55%, #f7fbff 100%);
        border-bottom: 1px solid #e8edf5;
    }
    .manutencao-card__eyebrow {
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #5b6b7f;
        margin-bottom: .25rem;
    }
    .manutencao-card__header h4 { font-weight: 800; color: #12263a; }
    .manutencao-card__badge,
    .manutencao-card__tip {
        border-radius: 14px;
        padding: .8rem .95rem;
        background: #fff;
        border: 1px solid #dfe7f1;
        box-shadow: 0 6px 20px rgba(18, 38, 58, .04);
    }
    .manutencao-card__badge { font-size: .9rem; font-weight: 700; color: #355070; white-space: nowrap; }
    .manutencao-card__tip {
        display: flex;
        flex-direction: column;
        gap: .25rem;
        color: #52657a;
        min-height: 100%;
    }
    .manutencao-card .form-control {
        border-radius: 12px;
        border-color: #d8e1ec;
    }
    .manutencao-card .form-control:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .15);
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/manutencao/itens/edit.blade.php ENDPATH**/ ?>