

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
<?php $component = App\View\Components\PageTitle::resolve(['title' => $titulo,'createUrl' => route('configuracao-checklist.create')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
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
    <?php if(isset($filters) && is_array($filters) && count($filters) > 0): ?>
        <?php if (isset($component)) { $__componentOriginalb68d555c13273f9d26aedfa8ff6c880d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb68d555c13273f9d26aedfa8ff6c880d = $attributes; } ?>
<?php $component = App\View\Components\Filter::resolve(['additionalFilters' => $additionalFilters,'filters' => $filters,'itensFiltered' => $itensFiltered] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Filter::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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
    <?php endif; ?>
    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-sm text-bold">REGISTROS <span
                        class="badge badge-success"><?php echo e($configuracaochecklists->total()); ?></span></h3>
                <div class="align-bottom d-flex flex-row justify-between">
                    <form action="<?php echo e(route('configuracao_checklist.setPerPage')); ?>" method="POST">
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
                    <div class="d-flex align-items-end">
                        <?php echo e($configuracaochecklists->links()); ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-0 pt-0 pb-1 table-responsive">

            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Tipo de Checklist</th>
                        <th>Item de Checklist</th>
                        <th>Status</th>
                        <th style="width: 180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $configuracaochecklists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $configuracaochecklist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($configuracaochecklist->tipoChecklistNome); ?></td>
                            <td><?php echo e($configuracaochecklist->itemChecklistNome); ?></td>
                            <td>
                                <span class="badge badge-xs bg-<?php echo e($configuracaochecklist->ativo == 'S' ? 'success' : 'danger'); ?>">
                                    <?php echo e($configuracaochecklist->ativo == 'S' ? 'ATIVO' : 'INATIVO'); ?>

                                </span>
                            </td>
                            <td style="width: 180px;">
                                <a href="<?php echo e(route('configuracao-checklist.edit', $configuracaochecklist->id)); ?>"
                                    class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                <?php if (isset($component)) { $__componentOriginal2550ac35d7599d92e03b1bde3934d26a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a = $attributes; } ?>
<?php $component = App\View\Components\DeleteButton::resolve(['action' => route('configuracao-checklist.destroy', $configuracaochecklist->id)] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('delete-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\DeleteButton::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2550ac35d7599d92e03b1bde3934d26a)): ?>
<?php $attributes = $__attributesOriginal2550ac35d7599d92e03b1bde3934d26a; ?>
<?php unset($__attributesOriginal2550ac35d7599d92e03b1bde3934d26a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2550ac35d7599d92e03b1bde3934d26a)): ?>
<?php $component = $__componentOriginal2550ac35d7599d92e03b1bde3934d26a; ?>
<?php unset($__componentOriginal2550ac35d7599d92e03b1bde3934d26a); ?>
<?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'configuracao-checklist-index';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
{ element: '.page-title', popover: { title: 'Lista de Configurações de Checklist', description: 'Aqui você gerencia as configurações de checklist: conjuntos de itens agrupados por tipo que definem o que deve ser verificado no check-in e checkout de cada reserva.' } },
        { element: '.btn-create-resource, a[href*="configuracao-checklist/create"], a[href*="configuracao_checklist/create"]', popover: { title: 'Adicionar configuração', description: 'Clique aqui para criar uma nova configuração de checklist, selecionando o tipo e os itens que devem ser verificados.', side: 'bottom' } },
        { element: 'table thead tr', popover: { title: 'Tabela de configurações', description: 'Lista as configurações existentes com nome, tipo de checklist, quantidade de itens e status (ativa/inativa).', side: 'bottom' } },
        { element: 'table tbody tr:first-child td:last-child', popover: { title: 'Ações', description: 'Use <b>Editar</b> para alterar os itens da configuração e <b>Excluir</b> para remover.', side: 'left' } },
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

<?php $__env->startSection('css'); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/configuracao_checklist/index.blade.php ENDPATH**/ ?>