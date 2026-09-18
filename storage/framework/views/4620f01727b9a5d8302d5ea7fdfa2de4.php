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
    <style>
        .auto-filled {
            background-color: #e0f7fa;
        }
    </style>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    


    <div class="col-xl-8 col-lg-10 col-md-12 col-sm-12 col-xs-12 card p-4">
        <div class="">
            <?php if (isset($component)) { $__componentOriginal18ad2e0d264f9740dc73fff715357c28 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18ad2e0d264f9740dc73fff715357c28 = $attributes; } ?>
<?php $component = App\View\Components\Form::resolve(['method' => ''.e($metodo).'','rowClass' => 'row'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Form::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => ''.e($rota).'','id' => 'form']); ?>
                <!-- Informações Privadas -->
                <?php if (isset($component)) { $__componentOriginale94d6fe2ae5a1c4d57283b44b1e7afe4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale94d6fe2ae5a1c4d57283b44b1e7afe4 = $attributes; } ?>
<?php $component = App\View\Components\SectionDivisionHeaderForm::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('section-division-header-form'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\SectionDivisionHeaderForm::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>Informações Privadas <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale94d6fe2ae5a1c4d57283b44b1e7afe4)): ?>
<?php $attributes = $__attributesOriginale94d6fe2ae5a1c4d57283b44b1e7afe4; ?>
<?php unset($__attributesOriginale94d6fe2ae5a1c4d57283b44b1e7afe4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale94d6fe2ae5a1c4d57283b44b1e7afe4)): ?>
<?php $component = $__componentOriginale94d6fe2ae5a1c4d57283b44b1e7afe4; ?>
<?php unset($__componentOriginale94d6fe2ae5a1c4d57283b44b1e7afe4); ?>
<?php endif; ?>
                <div class="col-md-5">
                    <div class="form-group">
                        <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'password','name' => 'password','label' => 'Senha','value' => ''.e($usuario->senha ?? '').''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['required' => true,'class' => 'form-control form-control-sm','id' => 'senha']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $attributes = $__attributesOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__attributesOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $component = $__componentOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__componentOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'password','name' => 'confirmacao_senha','label' => 'Confirmação de Senha','value' => ''.e($usuario->confirmacao_senha ?? '').''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['required' => true,'class' => 'form-control form-control-sm','id' => 'confirmacao_senha']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $attributes = $__attributesOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__attributesOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal967bdc6f044358de111e8173f0c9c426)): ?>
<?php $component = $__componentOriginal967bdc6f044358de111e8173f0c9c426; ?>
<?php unset($__componentOriginal967bdc6f044358de111e8173f0c9c426); ?>
<?php endif; ?>
                    </div>
                </div>
                <div class="col-md-12">
                    
                    <div id="passwordValidation" class="alert alert-danger d-none" role="alert">
                        <ul>
                            <li id="length" class="d-none">A senha deve ter pelo menos 8 caracteres.</li>
                            <li id="uppercase" class="d-none">A senha deve conter pelo menos uma letra maiúscula.</li>
                            <li id="lowercase" class="d-none">A senha deve conter pelo menos uma letra minúscula.</li>
                            <li id="number" class="d-none">A senha deve conter pelo menos um número.</li>
                            <li id="special" class="d-none">A senha deve conter pelo menos um caractere especial.
                            </li>
                            <li id="match" class="d-none">As senhas devem ser iguais.</li>
                        </ul>
                    </div>
                </div>
                <input type="hidden" id="usuario" name="usuario" value="false">

                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-xs col-12">
                        Salvar
                    </button>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18ad2e0d264f9740dc73fff715357c28)): ?>
<?php $attributes = $__attributesOriginal18ad2e0d264f9740dc73fff715357c28; ?>
<?php unset($__attributesOriginal18ad2e0d264f9740dc73fff715357c28); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18ad2e0d264f9740dc73fff715357c28)): ?>
<?php $component = $__componentOriginal18ad2e0d264f9740dc73fff715357c28; ?>
<?php unset($__componentOriginal18ad2e0d264f9740dc73fff715357c28); ?>
<?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('js'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <script>

        function validatePassword() {
            var senha = $('#senha').val();
            var confirmacaoSenha = $('#confirmacao_senha').val();
            var valid = true;

            // Reset validation messages
            $('#passwordValidation li').addClass('d-none');

            // Check length
            if (senha.length < 8) {
                $('#length').removeClass('d-none');
                valid = false;
            }

            // Check for uppercase letter
            if (!/[A-Z]/.test(senha)) {
                $('#uppercase').removeClass('d-none');
                valid = false;
            }

            // Check for lowercase letter
            if (!/[a-z]/.test(senha)) {
                $('#lowercase').removeClass('d-none');
                valid = false;
            }

            // Check for number
            if (!/[0-9]/.test(senha)) {
                $('#number').removeClass('d-none');
                valid = false;
            }

            // Check for special character
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(senha)) {
                $('#special').removeClass('d-none');
                valid = false;
            }

            // Check if passwords match
            if (senha !== confirmacaoSenha) {
                $('#match').removeClass('d-none');
                valid = false;
            }

            // Show or hide validation message
            if (valid) {
                $('#passwordValidation').addClass('d-none');
            } else {
                $('#passwordValidation').removeClass('d-none');
            }

            return valid;
        }

        $('#senha, #confirmacao_senha').on('keyup', validatePassword);

        $(document).ready(function() {
            $('#form').on('submit', function(event) {
                if ($('#usuario').length && $('#usuario').val() === '') {
                    if (!validatePassword()) {
                        event.preventDefault(); // Prevent form submission
                    }
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\usuario\alterar-senha.blade.php ENDPATH**/ ?>