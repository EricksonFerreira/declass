

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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-danger col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12">
            <ul class="mb-0 pl-3">
                <?php $__currentLoopData = array_unique($errors->all()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <div class="card card-item-checklist shadow-sm border-0">
            <div class="card-header card-header-item-checklist border-0">
                <div>
                    <h4 class="mb-1"><?php echo e($titulo); ?></h4>
                    <p class="mb-0 text-muted">Configure os dados principais do item de checklist.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario-item-checklist" method="POST" action="<?php echo e($rota); ?>">
                    <?php if(strtoupper($metodo ?? '') === 'PUT'): ?>
                        <?php echo method_field('PUT'); ?>
                    <?php endif; ?>
                    <?php echo csrf_field(); ?>

                    <div class="item-checklist-section-title">Informações Básicas</div>
                    <div class="form-row">
                        <div class="col-lg-8 col-md-7">
                            <div class="form-group form-group-item-checklist">
                                <?php if (isset($component)) { $__componentOriginal967bdc6f044358de111e8173f0c9c426 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal967bdc6f044358de111e8173f0c9c426 = $attributes; } ?>
<?php $component = App\View\Components\FormInput::resolve(['type' => 'text','name' => 'nome','label' => 'Nome','value' => ''.e(old('nome', $item_checklist->nome ?? '')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormInput::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','id' => 'nome','required' => true]); ?>
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
                        <div class="col-lg-4 col-md-5">
                            <div class="form-group form-group-item-checklist">
                                <?php if (isset($component)) { $__componentOriginal4b64953296fad43ab18567788f55889a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4b64953296fad43ab18567788f55889a = $attributes; } ?>
<?php $component = App\View\Components\FormSelect::resolve(['name' => 'ativo','label' => 'Status','options' => $status,'placeholder' => 'Selecione um valor','selected' => old('ativo', isset($item_checklist->ativo) && $item_checklist->ativo ? $item_checklist->ativo : null)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\FormSelect::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'form-control form-control-sm','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4b64953296fad43ab18567788f55889a)): ?>
<?php $attributes = $__attributesOriginal4b64953296fad43ab18567788f55889a; ?>
<?php unset($__attributesOriginal4b64953296fad43ab18567788f55889a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4b64953296fad43ab18567788f55889a)): ?>
<?php $component = $__componentOriginal4b64953296fad43ab18567788f55889a; ?>
<?php unset($__componentOriginal4b64953296fad43ab18567788f55889a); ?>
<?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-item-checklist col-12 mt-2">
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<style>
    .card-item-checklist {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-item-checklist {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-item-checklist h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .item-checklist-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-item-checklist {
        margin-bottom: 1rem;
    }

    .card-item-checklist .form-control,
    .card-item-checklist .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-item-checklist .form-control:focus,
    .card-item-checklist .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-item-checklist {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-item-checklist {
            padding: .95rem 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }
    }

    @media (max-width: 767.98px) {
        .col-xl-10.col-lg-11.col-md-12.col-sm-12.col-xs-12.px-0 {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .card-header-item-checklist h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-item-checklist p {
            font-size: .85rem;
        }

        .item-checklist-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-item-checklist {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-item-checklist {
            margin-bottom: .8rem;
        }

        .card-item-checklist .form-control,
        .card-item-checklist .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'item-checklist-add-edit';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.card-item-checklist', popover: { title: 'Cadastro de Item de Checklist', description: 'Aqui você cria ou edita um item do catálogo de inspeção. Itens ativos ficam disponíveis para seleção nas configurações de checklist.' } },
        { element: '#nome', popover: { title: 'Nome do item', description: 'Informe o nome do ponto de inspeção de forma clara e objetiva, como "Pneu dianteiro esquerdo", "Extintor de incêndio" ou "Documento CRLV".', side: 'bottom' } },
        { element: '#ativo', popover: { title: 'Status', description: 'Ativo: o item aparece nas configurações de checklist. Inativo: oculto das opções de seleção, mas mantém o histórico de usos anteriores.', side: 'bottom' } },
        { element: '.btn-save-item-checklist', popover: { title: 'Salvar', description: 'Clique para gravar o item. Ele ficará disponível imediatamente nas configurações de checklist.', side: 'top' } },
    ];

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    }

    function buildTour() {
        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            allowClose: true,
            progressText: 'Passo {{current}} de {{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { if (!_pulou) marcarFeito(); },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btnTour = document.createElement('button');
                btnTour.className = 'drv-skip-btn drv-skip-tour-btn'; btnTour.type = 'button'; btnTour.textContent = 'Pular tour';
                btnTour.addEventListener('click', function () { _pulou = true; marcarFeito(); driver.destroy(); });
                var btnStep = document.createElement('button');
                btnStep.className = 'drv-skip-btn drv-skip-step-btn'; btnStep.type = 'button'; btnStep.textContent = 'Pular passo';
                btnStep.addEventListener('click', function () { driver.moveNext(); });
                var nav = popover.footer.querySelector('.driver-popover-navigation-btns');
                if (nav) { nav.prepend(btnStep); }
                popover.footer.prepend(btnTour);
            },
            steps: steps,
        });
    }

    window.restartPageTour = function () {
        localStorage.removeItem(CACHE_KEY);
        fetch(RESETAR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .then(() => buildTour().drive());
    };

    if (localStorage.getItem(CACHE_KEY) && !forceTour) return;
    fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.feito && !forceTour) { localStorage.setItem(CACHE_KEY, '1'); return; }
            setTimeout(() => buildTour().drive(), 350);
        });
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/item_checklist/add-edit.blade.php ENDPATH**/ ?>