
<div class="ux-view-toggle">
    <button type="button" class="btn btn-xs btn-outline-secondary active" data-view="cards">
        <i class="fas fa-th-large mr-1"></i>Cards
    </button>
    <button type="button" class="btn btn-xs btn-outline-secondary" data-view="table">
        <i class="fas fa-table mr-1"></i>Tabela
    </button>
</div>
<?php $__env->startPush('js'); ?>
<script>
    $(function () {
        const body = document.querySelector('.ux-index-body');
        const btns = document.querySelectorAll('.ux-view-toggle [data-view]');
        const store = <?php echo json_encode($viewKey ?? 'ux_index_view', 15, 512) ?>;

        function setView(view) {
            if (!body) return;
            body.dataset.view = view;
            btns.forEach(btn => btn.classList.toggle('active', btn.dataset.view === view));
            localStorage.setItem(store, view);
        }

        btns.forEach(btn => btn.addEventListener('click', function () {
            setView(btn.dataset.view);
        }));

        setView(localStorage.getItem(store) || <?php echo json_encode($default ?? 'cards', 15, 512) ?>);
    });
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/partials/ux-view-toggle.blade.php ENDPATH**/ ?>