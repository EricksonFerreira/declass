<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id' => 'formModal',
    'title' => '',
    'formId' => 'modalForm',
    'action' => '',
    'method' => 'POST',
    'icon' => 'fas fa-pen'
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
    'id' => 'formModal',
    'title' => '',
    'formId' => 'modalForm',
    'action' => '',
    'method' => 'POST',
    'icon' => 'fas fa-pen'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>


<div id="<?php echo e($id); ?>Backdrop" class="ux-modal-backdrop"></div>


<div id="<?php echo e($id); ?>" class="ux-modal" role="dialog" aria-modal="true" aria-labelledby="<?php echo e($id); ?>Title">
    <div class="ux-modal-card">
        <div class="ux-modal-header">
            <span class="ux-modal-icon" aria-hidden="true"><i class="<?php echo e($icon); ?>"></i></span>
            <h5 id="<?php echo e($id); ?>Title" class="ux-modal-title"><?php echo e($title); ?></h5>
            <button type="button" class="ux-modal-close" id="close<?php echo e($id); ?>" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <form id="<?php echo e($formId); ?>" action="<?php echo e($action); ?>" method="POST" class="d-flex flex-column flex-grow-1 mb-0" style="min-height:0;">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_method" id="<?php echo e($formId); ?>Method" value="<?php echo e($method); ?>">

            <div class="ux-modal-body">
                <?php echo e($slot); ?>

            </div>

            <div class="ux-modal-footer">
                <button type="button" class="btn btn-light" id="cancel<?php echo e($id); ?>">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('ebaf7912-5989-4412-b554-0eee2cb17a87')): $__env->markAsRenderedOnce('ebaf7912-5989-4412-b554-0eee2cb17a87'); ?>
<?php $__env->startPush('css'); ?>
    <style>
        .ux-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: opacity .22s ease, visibility .22s ease;
        }
        .ux-modal-backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        .ux-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            width: min(560px, calc(100vw - 32px));
            max-height: 90vh;
            max-height: 90dvh;
            z-index: 1050;
            display: flex;
            opacity: 0;
            visibility: hidden;
            transform: translate(-50%, -46%) scale(.96);
            transition: transform .28s cubic-bezier(0.16, 1, 0.3, 1), opacity .2s ease, visibility .2s ease;
        }
        .ux-modal.open {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, -50%) scale(1);
        }

        .ux-modal-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 24px 64px rgba(15, 23, 42, .28);
            display: flex;
            flex-direction: column;
            width: 100%;
            max-height: 90vh;
            max-height: 90dvh;
            overflow: hidden;
        }

        .ux-modal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-bottom: 1px solid #eef2f7;
            flex-shrink: 0;
        }
        .ux-modal-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--empresa-cor-primaria, #0f172a);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .ux-modal-title {
            margin: 0;
            font-size: 1.02rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
        }
        .ux-modal-close {
            margin-left: auto;
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 8px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 1.3rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
            flex-shrink: 0;
        }
        .ux-modal-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .ux-modal-body {
            padding: 20px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            flex: 1 1 auto;
            min-height: 0;
        }
        .ux-modal .form-group > label {
            font-weight: 600;
            font-size: .85rem;
            color: #334155;
        }

        .ux-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 14px 20px;
            border-top: 1px solid #eef2f7;
            background: #f8fafc;
            flex-shrink: 0;
        }
        .ux-modal-footer .btn {
            min-width: 96px;
            font-weight: 600;
        }

        body.ux-modal-locked { overflow: hidden; }

        @media (max-width: 575.98px) {
            .ux-modal {
                width: calc(100vw - 24px);
                max-height: 92vh;
                max-height: 92dvh;
            }
            .ux-modal-body { padding: 16px; }
            /* Inputs >= 16px evitam auto-zoom no iOS */
            .ux-modal .form-control,
            .ux-modal .custom-select,
            .ux-modal .select2-container .select2-selection {
                font-size: 16px;
                min-height: 44px;
            }
            .ux-modal-footer .btn {
                flex: 1;
                min-height: 46px;
            }
        }
    </style>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php $__env->startPush('js'); ?>
    <script>
        $(function() {
            var modal = document.getElementById('<?php echo e($id); ?>');
            var backdrop = document.getElementById('<?php echo e($id); ?>Backdrop');

            function closeModal() {
                modal.classList.remove('open');
                backdrop.classList.remove('show');
            }

            $('#close<?php echo e($id); ?>, #cancel<?php echo e($id); ?>, #<?php echo e($id); ?>Backdrop').on('click', closeModal);

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('open')) {
                    closeModal();
                }
            });

            new MutationObserver(function() {
                document.body.classList.toggle('ux-modal-locked', modal.classList.contains('open'));
            }).observe(modal, { attributes: true, attributeFilter: ['class'] });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\modal.blade.php ENDPATH**/ ?>