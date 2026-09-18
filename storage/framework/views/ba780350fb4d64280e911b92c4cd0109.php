<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'tourId',
    'steps',
    'autorun' => true,
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
    'tourId',
    'steps',
    'autorun' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<style>
.driver-popover{border-radius:16px!important;box-shadow:0 18px 50px rgba(10,22,40,.22)!important;min-width:320px!important;max-width:380px!important;padding:18px 18px 14px!important;border:1px solid rgba(226,232,240,.95)!important}
.driver-popover-title{font-family:inherit!important;font-weight:800!important;font-size:1rem!important;color:#0a1628!important;margin-bottom:6px!important}
.driver-popover-description{font-size:.85rem!important;line-height:1.6!important;color:#374151!important;margin-bottom:14px!important}
.driver-popover-footer{display:grid!important;grid-template-columns:auto 1fr auto!important;align-items:center!important;gap:10px!important;border-top:1px solid #f1f5f9!important;padding-top:12px!important;margin-top:0!important}
.driver-popover-progress-text{font-size:.73rem!important;color:#64748b!important;text-align:center!important;justify-self:center!important;margin:0!important}
.driver-popover-navigation-btns{display:flex!important;justify-content:flex-end!important;align-items:center!important;gap:8px!important;justify-self:end!important}
.driver-popover-footer button{border-radius:10px!important;font-weight:700!important;font-size:.82rem!important;padding:.48rem 1rem!important;white-space:nowrap!important;box-shadow:none!important}
.driver-popover-prev-btn,.driver-popover-next-btn,.driver-popover-done-btn{min-width:104px!important}
.driver-popover-prev-btn{background:#fff!important;color:#475569!important;border:1px solid #dbe3ef!important}
.driver-popover-next-btn,.driver-popover-done-btn{background:#0f172a!important;color:#fff!important;border:1px solid #0f172a!important}
.driver-popover-prev-btn[disabled]{opacity:.48!important;background:#f8fafc!important;color:#94a3b8!important}
.driver-popover-close-btn{top:12px!important;right:14px!important;font-size:1.1rem!important;color:#94a3b8!important}
.drv-skip-btn{background:#fff!important;border:1px solid #dbe3ef!important;color:#64748b!important;font-size:.78rem!important;font-weight:700!important;padding:.48rem .9rem!important;cursor:pointer!important;transition:all .15s!important}
.drv-skip-btn:hover{color:#ef4444!important;border-color:#fecaca!important;background:#fff5f5!important}
#driver-page-overlay{opacity:.4!important}
@media (max-width:575px){
    .driver-popover{min-width:280px!important;max-width:calc(100vw - 24px)!important}
    .driver-popover-footer{grid-template-columns:1fr!important}
    .driver-popover-progress-text,.driver-popover-navigation-btns{justify-self:stretch!important}
    .driver-popover-navigation-btns{justify-content:space-between!important}
    .driver-popover-prev-btn,.driver-popover-next-btn,.driver-popover-done-btn,.drv-skip-btn{min-width:auto!important;width:100%!important}
}
</style>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID    = <?php echo json_encode($tourId, 15, 512) ?>;
    const autorun    = <?php echo json_encode($autorun, 15, 512) ?>;
    const stepsData  = <?php echo json_encode($steps, 15, 512) ?>;
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        }).catch(() => {});
    }

    function getAvailableSteps() {
        return (stepsData || []).filter(function (step) {
            if (!step || !step.element) return false;

            return String(step.element)
                .split(',')
                .map(function (selector) { return selector.trim(); })
                .filter(Boolean)
                .some(function (selector) {
                    try {
                        return !!document.querySelector(selector);
                    } catch (error) {
                        return false;
                    }
                });
        });
    }

    function buildDriver() {
        if (typeof window.driver === 'undefined' || !window.driver.js) return null;

        var availableSteps = getAvailableSteps();
        if (!availableSteps.length) return null;

        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            progressText: 'Passo {{current}} de {{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { if (!_pulou) marcarFeito(); },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btnTour = document.createElement('button');
                btnTour.className = 'drv-skip-btn drv-skip-tour-btn';
                btnTour.type = 'button';
                btnTour.textContent = 'Pular tour';
                btnTour.addEventListener('click', function () {
                    _pulou = true;
                    marcarFeito();
                    driver.destroy();
                });
                var btnStep = document.createElement('button');
                btnStep.className = 'drv-skip-btn drv-skip-step-btn';
                btnStep.type = 'button';
                btnStep.textContent = 'Pular passo';
                btnStep.addEventListener('click', function () { driver.moveNext(); });
                var nav = popover.footer.querySelector('.driver-popover-navigation-btns');
                if (nav) { nav.prepend(btnStep); }
                popover.footer.prepend(btnTour);
            },
            steps: availableSteps,
        });
    }

    function startTour(attempt) {
        var currentAttempt = attempt || 0;
        var tour = buildDriver();
        if (!tour) {
            if (currentAttempt < 8) {
                setTimeout(function () { startTour(currentAttempt + 1); }, 250);
            }
            return;
        }
        setTimeout(function () { tour.drive(); }, 350);
    }

    window.restartPageTour = function () {
        localStorage.removeItem(CACHE_KEY);
        fetch(RESETAR_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        }).catch(() => {}).then(() => startTour());
    };

    function init() {
        const forceTour = new URLSearchParams(window.location.search).get('tour') === '1';
        if (!autorun && !forceTour) return;

        // Cache local evita race condition entre POST concluir e próximo carregamento
        fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.feito && !forceTour) {
                    localStorage.setItem(CACHE_KEY, '1');
                    return;
                }
                startTour();
            })
            .catch(() => {
                if (localStorage.getItem(CACHE_KEY) && !forceTour) return;
                startTour();
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 350);
    }
})();
</script>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views\components\page-tour.blade.php ENDPATH**/ ?>