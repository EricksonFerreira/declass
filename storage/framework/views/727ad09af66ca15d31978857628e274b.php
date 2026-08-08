<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => 'sideDrawer',
    'title' => '',
    'formId' => 'drawerForm',
    'action' => '',
    'method' => 'POST'
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'id' => 'sideDrawer',
    'title' => '',
    'formId' => 'drawerForm',
    'action' => '',
    'method' => 'POST'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>


<div id="<?php echo e($id); ?>Backdrop" class="side-drawer-backdrop"></div>


<div id="<?php echo e($id); ?>" class="side-drawer">
    <div class="side-drawer-header d-flex justify-content-between align-items-center bg-primary text-white">
        <h5 id="<?php echo e($id); ?>Title" class="mb-0 font-weight-bold"><?php echo e($title); ?></h5>
        <button type="button" class="close text-white" id="close<?php echo e($id); ?>" aria-label="Close" style="outline: none;">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    
    <form id="<?php echo e($formId); ?>" action="<?php echo e($action); ?>" method="POST" class="flex-grow-1 d-flex flex-column h-100 mb-0">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="_method" id="<?php echo e($formId); ?>Method" value="<?php echo e($method); ?>">
        
        <div class="side-drawer-body">
            <?php echo e($slot); ?>

        </div>

        <div class="side-drawer-footer text-right">
            <button type="button" class="btn btn-secondary mr-2" id="cancel<?php echo e($id); ?>">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div>

<?php if (! $__env->hasRenderedOnce('06cbe917-a30e-482a-8e5f-6e6b3f8f59f4')): $__env->markAsRenderedOnce('06cbe917-a30e-482a-8e5f-6e6b3f8f59f4'); ?>
<?php $__env->startPush('css'); ?>
    <style>
        .side-drawer {
            position: fixed;
            top: 0;
            right: -480px;
            width: 480px;
            height: 100vh;
            background: #ffffff;
            box-shadow: -5px 0 25px rgba(0,0,0,0.15);
            z-index: 1050;
            transition: right 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
        }
        .side-drawer.open {
            right: 0;
        }
        .side-drawer-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(2px);
            z-index: 1040;
            display: none;
        }
        .side-drawer-backdrop.show {
            display: block;
        }
        .side-drawer-header {
            padding: 1.25rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .side-drawer-body {
            flex-grow: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }
        .side-drawer-footer {
            padding: 1.25rem;
            border-top: 1px solid rgba(0,0,0,0.05);
            background: #f8fafc;
        }
    </style>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php $__env->startPush('js'); ?>
    <script>
        $(function() {
            // Setup events for this specific drawer
            $('#close<?php echo e($id); ?>, #cancel<?php echo e($id); ?>, #<?php echo e($id); ?>Backdrop').on('click', function() {
                $('#<?php echo e($id); ?>').removeClass('open');
                $('#<?php echo e($id); ?>Backdrop').removeClass('show');
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/components/side-drawer.blade.php ENDPATH**/ ?>