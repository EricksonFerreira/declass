@props([
    'tourId',   // string único por tela, ex: 'estacao-index'
    'steps',    // array de steps Driver.js
    'autorun'   => true,  // dispara automaticamente na primeira visita
])

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<style>
.driver-popover { border-radius: 14px !important; box-shadow: 0 8px 40px rgba(10,22,40,.2) !important; }
.driver-popover-title { font-family: inherit !important; font-weight: 800 !important; font-size: .95rem !important; color: #0a1628 !important; }
.driver-popover-description { font-size: .84rem !important; line-height: 1.6 !important; color: #374151 !important; }
.driver-popover-progress-text { font-size: .72rem !important; color: #94a3b8 !important; }
.driver-popover-footer button {
    border-radius: 8px !important; font-weight: 700 !important; font-size: .8rem !important;
    padding: .35rem .85rem !important;
}
#driver-page-overlay { opacity: .4 !important; }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID    = @json($tourId);
    const SKIP_KEY   = 'page_tour_done__' + TOUR_ID;
    const autorun    = @json($autorun);
    const stepsData  = @json($steps);

    function buildDriver(onDone) {
        return window.driver.js.driver({
            animate:      true,
            smoothScroll: true,
            showProgress: true,
            progressText: 'Passo @{{current}} de @{{total}}',
            nextBtnText:  'Próximo →',
            prevBtnText:  '← Anterior',
            doneBtnText:  'Entendido ✓',
            onDestroyStarted: (el, step, opts) => {
                // Marca como visto tanto ao concluir quanto ao pressionar Esc/fechar
                localStorage.setItem(SKIP_KEY, '1');
                if (onDone) onDone();
            },
            steps: stepsData,
        });
    }

    // Botão de re-abrir o tour (injetado no DOM se existir elemento #tour-restart-btn)
    window.restartPageTour = function () {
        localStorage.removeItem(SKIP_KEY);
        buildDriver().drive();
    };

    window.addEventListener('DOMContentLoaded', function () {
        const jaViu    = localStorage.getItem(SKIP_KEY);
        const forceTour = new URLSearchParams(window.location.search).get('tour') === '1';

        if (!autorun && !forceTour) return;
        if (jaViu && !forceTour) return;

        setTimeout(() => buildDriver().drive(), 350);
    });
})();
</script>
@endpush
