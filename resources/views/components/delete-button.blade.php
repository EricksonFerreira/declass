<form action="{{ $action }}" method="POST" class="d-inline delete-form">
    @csrf
    @method('DELETE')

    <button
        type="button"
        class="btn btn-xs {{ $btnClass ?? 'btn-danger' }} delete-button"
        data-confirm-title="{{ $tituloMensagem ?? 'Você tem certeza?' }}"
        data-confirm-message="{{ $mensagem ?? 'Esta ação não pode ser desfeita!' }}"
        data-confirm-button="{{ $textoConfirmacao ?? 'Sim, excluir!' }}"
        data-confirm-color="{{ $corConfirmacao ?? '#d33' }}"
    >
        <i class="{{ $icone ?? 'fa fa-trash' }}"></i>
        {{ $titulo ?? 'Excluir' }}
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