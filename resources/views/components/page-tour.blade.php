@props([
    'tourId',
    'steps',
    'autorun' => true,
])
{{--
    Componente reutilizável de tour guiado (Driver.js).
    Persiste estado por usuário no banco via TourController.
    Uso: <x-page-tour tour-id="minha-tela" :steps="[...]" />
    Coloque dentro do @section('css') / @section('js') da view.
--}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<style>
.driver-popover{border-radius:14px!important;box-shadow:0 8px 40px rgba(10,22,40,.22)!important;min-width:280px!important;max-width:340px!important;padding:20px 20px 16px!important}
.driver-popover-title{font-family:inherit!important;font-weight:800!important;font-size:1rem!important;color:#0a1628!important;margin-bottom:6px!important}
.driver-popover-description{font-size:.85rem!important;line-height:1.6!important;color:#374151!important;margin-bottom:14px!important}
.driver-popover-footer{display:flex!important;flex-direction:column!important;gap:10px!important;border-top:1px solid #f1f5f9!important;padding-top:12px!important;margin-top:0!important}
.driver-popover-progress-text{font-size:.72rem!important;color:#94a3b8!important;text-align:center!important;order:-1!important}
.driver-popover-navigation-btns{display:flex!important;justify-content:flex-end!important;gap:8px!important}
.driver-popover-footer button{border-radius:8px!important;font-weight:700!important;font-size:.82rem!important;padding:.4rem 1rem!important;white-space:nowrap!important}
.driver-popover-next-btn,.driver-popover-done-btn{min-width:110px!important}
.driver-popover-close-btn{top:12px!important;right:14px!important;font-size:1.1rem!important;color:#94a3b8!important}
.drv-skip-btn{background:none!important;border:none!important;color:#94a3b8!important;font-size:.78rem!important;font-weight:600!important;padding:.3rem .5rem!important;cursor:pointer!important;margin-right:auto!important;transition:color .15s!important}
.drv-skip-btn:hover{color:#ef4444!important}
#driver-page-overlay{opacity:.4!important}
</style>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID   = @json($tourId);
    const autorun   = @json($autorun);
    const stepsData = @json($steps);
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function marcarFeito() {
        fetch(CONCLUIR_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
    }

    function buildDriver() {
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            progressText: 'Passo @{{current}} de @{{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { marcarFeito(); },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btn = document.createElement('button');
                btn.className = 'drv-skip-btn';
                btn.type = 'button';
                btn.textContent = 'Pular tour';
                btn.addEventListener('click', function () {
                    marcarFeito();
                    driver.destroy();
                });
                popover.footer.prepend(btn);
            },
            steps: stepsData,
        });
    }

    window.restartPageTour = function () {
        fetch('/admin/tour/resetar/' + TOUR_ID, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        }).then(() => buildDriver().drive());
    };

    function init() {
        const forceTour = new URLSearchParams(window.location.search).get('tour') === '1';
        if (!autorun && !forceTour) return;

        fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.feito && !forceTour) return;
                setTimeout(() => buildDriver().drive(), 350);
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 350);
    }
})();
</script>
