{{-- Toggle Cards/Tabela + persistência da visão por tela. Uso: @include('partials.ux-view-toggle', ['viewKey' => 'funcoes_index_view']) --}}
<div class="ux-view-toggle" data-store="{{ $viewKey ?? 'ux_index_view' }}" data-default="{{ $default ?? 'cards' }}">
    <button type="button" class="btn btn-xs btn-outline-secondary active" data-view="cards">
        <i class="fas fa-th-large mr-1"></i>Cards
    </button>
    <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
        <i class="fas fa-table mr-1"></i>Tabela
    </button>
</div>
@push('js')
<script>
    (function () {
        function initToggle(toggle) {
            if (toggle.__uxBound) return;
            toggle.__uxBound = true;

            var scope = toggle.closest('.card');
            var body = scope
                ? scope.querySelector('.ux-index-body')
                : document.querySelector('.ux-index-body');
            var btns = toggle.querySelectorAll('[data-view]');
            var store = toggle.dataset.store;
            var inicial = localStorage.getItem(store) || toggle.dataset.default || 'cards';

            function setView(view) {
                if (!body) return;
                body.dataset.view = view;
                btns.forEach(function (btn) { btn.classList.toggle('active', btn.dataset.view === view); });
                localStorage.setItem(store, view);
            }

            btns.forEach(function (btn) {
                btn.addEventListener('click', function () { setView(btn.dataset.view); });
            });
            setView(inicial);
        }

        function initAll() {
            document.querySelectorAll('.ux-view-toggle').forEach(initToggle);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAll);
        } else {
            initAll();
        }
    })();
</script>
@endpush
