<form action="{{ $action }}" method="POST" class="d-inline delete-form">
    @csrf
    @method('DELETE')

    <button
        type="button"
        class="btn btn-xs {{ $btnClass ?? 'btn-danger' }} delete-button"
        title="{{ html_entity_decode($titulo ?? 'Excluir', ENT_QUOTES, 'UTF-8') }}"
        data-confirm-title="{{ html_entity_decode($tituloMensagem ?? 'Você tem certeza?', ENT_QUOTES, 'UTF-8') }}"
        data-confirm-message="{{ html_entity_decode($mensagem ?? 'Esta ação não pode ser desfeita!', ENT_QUOTES, 'UTF-8') }}"
        data-confirm-button="{{ html_entity_decode($textoConfirmacao ?? 'Sim, excluir!', ENT_QUOTES, 'UTF-8') }}"
        data-confirm-color="{{ $corConfirmacao ?? '#d33' }}"
    >
        <i class="{{ $icone ?? 'fa fa-trash' }}"></i>
    </button>
</form>

@once
    <script>
        document.addEventListener('click', function (event) {
            const button = event.target.closest('.delete-button');

            if (!button) {
                return;
            }

            event.preventDefault();

            const form = button.closest('form.delete-form');

            if (!form) {
                return;
            }

            const title = button.dataset.confirmTitle || 'Você tem certeza?';
            const message = button.dataset.confirmMessage || 'Esta ação não pode ser desfeita!';
            const confirmButtonText = button.dataset.confirmButton || 'Sim, excluir!';
            const confirmButtonColor = button.dataset.confirmColor || '#d33';

            if (
                typeof window.Swal !== 'undefined'
                && typeof window.Swal.fire === 'function'
            ) {
                window.Swal.fire({
                    title: title,
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: confirmButtonColor,
                    cancelButtonColor: '#6c757d',
                    focusCancel: true,
                }).then(function (result) {
                    if (result && result.isConfirmed) {
                        form.submit();
                    }
                });

                return;
            }

            if (window.confirm(title + '\n\n' + message)) {
                form.submit();
            }
        });
    </script>
@endonce