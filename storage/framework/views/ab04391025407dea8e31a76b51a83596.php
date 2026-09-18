<?php
    $etapaAtiva = 'onboarding.empresa';
    $etapasConc = [];
?>


<?php $__env->startSection('page_title', 'Dados da Empresa'); ?>

<?php $__env->startSection('content'); ?>
<div class="onb-card">
    <h1 class="onb-card-title">Bem-vindo ao DECLOCK! 👋</h1>
    <p class="onb-card-sub">
        Vamos configurar sua conta em poucos passos. Comece informando os dados básicos da sua empresa.
    </p>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <strong><i class="fas fa-exclamation-circle mr-1"></i> Corrija os erros abaixo:</strong>
            <ul style="margin:.5rem 0 0 1.2rem;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('onboarding.empresa.salvar')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="form-row" style="margin-bottom:1.25rem;">
            <div>
                <label class="form-label">Nome da Empresa <span style="color:#ef4444;">*</span></label>
                <input type="text" name="nome" class="form-control <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       value="<?php echo e(old('nome', auth()->user()->empresa?->nome ?? '')); ?>"
                       placeholder="Ex.: Agência Horizonte" required maxlength="200" autofocus>
                <?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="form-label">CNPJ</label>
                <input type="text" name="cnpj" class="form-control <?php $__errorArgs = ['cnpj'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       value="<?php echo e(old('cnpj', auth()->user()->empresa?->cnpj ?? '')); ?>"
                       placeholder="00.000.000/0000-00" maxlength="20" id="cnpjInput">
                <?php $__errorArgs = ['cnpj'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <div class="form-row" style="margin-bottom:2rem;">
            <div>
                <label class="form-label">E-mail da Empresa</label>
                <input type="email" name="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       value="<?php echo e(old('email', auth()->user()->empresa?->email ?? '')); ?>"
                       placeholder="contato@minhaempresa.com.br" maxlength="200">
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" class="form-control <?php $__errorArgs = ['telefone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       value="<?php echo e(old('telefone', auth()->user()->empresa?->telefone ?? '')); ?>"
                       placeholder="(00) 00000-0000" maxlength="30" id="telefoneInput">
                <?php $__errorArgs = ['telefone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <div class="alert alert-info" style="font-size:.83rem;">
            <i class="fas fa-info-circle mr-1"></i>
            Você poderá completar endereço, logo e outras configurações depois, diretamente no painel.
        </div>

        <div style="display:flex; justify-content:flex-end; margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary btn-lg">
                Próximo: Ativar teste grátis <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Máscara CNPJ
document.getElementById('cnpjInput')?.addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').slice(0, 14);
    if (v.length > 12) v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2}).*/, '$1.$2.$3/$4-$5');
    else if (v.length > 8) v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d+)/, '$1.$2.$3/$4');
    else if (v.length > 5) v = v.replace(/^(\d{2})(\d{3})(\d+)/, '$1.$2.$3');
    else if (v.length > 2) v = v.replace(/^(\d{2})(\d+)/, '$1.$2');
    this.value = v;
});
// Máscara telefone
document.getElementById('telefoneInput')?.addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 10) v = v.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4})(\d+)/, '($1) $2-$3');
    else if (v.length > 2) v = v.replace(/^(\d{2})(\d+)/, '($1) $2');
    this.value = v;
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('onboarding._layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\onboarding\empresa.blade.php ENDPATH**/ ?>