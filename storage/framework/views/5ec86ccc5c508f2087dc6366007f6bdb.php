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
    <div class="side-drawer-handle" aria-hidden="true"><span></span></div>
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

<?php if (! $__env->hasRenderedOnce('f10ad2d3-7f53-4026-8806-9920ad5155f5')): $__env->markAsRenderedOnce('f10ad2d3-7f53-4026-8806-9920ad5155f5'); ?>
<?php $__env->startPush('css'); ?>
    <style>
        /* =====================================================
           MOBILE FIRST — bottom sheet (desliza de baixo)
           ===================================================== */
        .side-drawer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 92vh;
            height: 92dvh;
            background: #ffffff;
            border-radius: 18px 18px 0 0;
            box-shadow: 0 -8px 30px rgba(15,23,42,0.25);
            z-index: 1050;
            transform: translateY(105%);
            transition: transform 0.32s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            will-change: transform;
        }
        .side-drawer.open {
            transform: translateY(0);
        }
        .side-drawer-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(2px);
            z-index: 1040;
            display: none;
        }
        .side-drawer-backdrop.show {
            display: block;
        }

        /* Alça de arrasto (visual + swipe-down) */
        .side-drawer-handle {
            display: flex;
            justify-content: center;
            padding: 10px 0 4px;
            cursor: grab;
            touch-action: none;
            flex-shrink: 0;
        }
        .side-drawer-handle span {
            width: 44px;
            height: 5px;
            border-radius: 99px;
            background: #cbd5e1;
        }

        .side-drawer-header {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            border-radius: 0;
        }
        .side-drawer-header .close {
            font-size: 1.6rem;
            padding: 0.35rem 0.55rem;
            margin: -0.35rem -0.4rem -0.35rem 0;
            min-width: 44px;
            min-height: 44px;
        }
        .side-drawer-body {
            flex-grow: 1;
            padding: 1rem;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }
        /* Inputs maiores no toque + font-size >= 16px evita o auto-zoom do iOS */
        .side-drawer .form-control,
        .side-drawer .custom-select,
        .side-drawer .select2-container .select2-selection {
            font-size: 16px;
            min-height: 44px;
            padding-top: 0.55rem;
            padding-bottom: 0.55rem;
        }
        .side-drawer .form-group > label { font-weight: 600; font-size: 0.9rem; }
        .side-drawer-footer {
            padding: 0.85rem 1rem;
            padding-bottom: calc(0.85rem + env(safe-area-inset-bottom, 0px));
            border-top: 1px solid rgba(0,0,0,0.06);
            background: #f8fafc;
            display: flex;
            gap: .5rem;
            flex-shrink: 0;
        }
        .side-drawer-footer .btn {
            flex: 1;
            margin-right: 0 !important;
            min-height: 48px;
            font-weight: 600;
        }

        /* Trava o scroll da página atrás do sheet */
        body.side-drawer-locked { overflow: hidden; }

        /* =====================================================
           DESKTOP — drawer lateral direito
           ===================================================== */
        @media (min-width: 576px) {
            .side-drawer {
                left: auto;
                right: 0;
                top: 0;
                bottom: 0;
                width: min(480px, 100vw);
                height: 100vh;
                border-radius: 0;
                box-shadow: -5px 0 25px rgba(0,0,0,0.15);
                transform: translateX(105%);
            }
            .side-drawer.open { transform: translateX(0); }
            .side-drawer-handle { display: none; }
            .side-drawer-header { padding: 1.25rem; }
            .side-drawer-body { padding: 1.5rem; }
            .side-drawer .form-control,
            .side-drawer .custom-select,
            .side-drawer .select2-container .select2-selection {
                font-size: inherit;
                min-height: 0;
                padding-top: 0.25rem;
                padding-bottom: 0.25rem;
            }
            .side-drawer .form-group > label { font-weight: 400; font-size: 1rem; }
            .side-drawer-footer {
                padding: 1.25rem;
                display: block;
                text-align: right;
            }
            .side-drawer-footer .btn {
                flex: none;
                min-height: 0;
                font-weight: 400;
            }
        }
    </style>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php $__env->startPush('js'); ?>
    <script>
        $(function() {
            var drawer = document.getElementById('<?php echo e($id); ?>');
            var backdrop = document.getElementById('<?php echo e($id); ?>Backdrop');

            function closeDrawer() {
                drawer.classList.remove('open');
                backdrop.classList.remove('show');
            }

            $('#close<?php echo e($id); ?>, #cancel<?php echo e($id); ?>, #<?php echo e($id); ?>Backdrop').on('click', closeDrawer);

            // Fecha com Esc
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && drawer.classList.contains('open')) {
                    closeDrawer();
                }
            });

            // Trava o scroll da página enquanto o drawer/sheet está aberto
            new MutationObserver(function() {
                document.body.classList.toggle('side-drawer-locked', drawer.classList.contains('open'));
            }).observe(drawer, { attributes: true, attributeFilter: ['class'] });

            // Swipe-down na alça/cabeçalho fecha o bottom sheet (mobile)
            var startY = null;
            $('.side-drawer-handle, #<?php echo e($id); ?> .side-drawer-header').on('touchstart', function(e) {
                startY = e.originalEvent.touches[0].clientY;
            }).on('touchmove', function(e) {
                if (startY === null) { return; }
                var delta = e.originalEvent.touches[0].clientY - startY;
                if (delta > 70) {
                    startY = null;
                    closeDrawer();
                }
            }).on('touchend touchcancel', function() {
                startY = null;
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\side-drawer.blade.php ENDPATH**/ ?>