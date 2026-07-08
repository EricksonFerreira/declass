<form action="{{ $action }}" method="POST" class="d-inline delete-form">
    @csrf
    @method('DELETE')
    <button type="button" class="btn btn-xs btn-danger delete-button">
        <i class="fa fa-trash"></i> Excluir
    </button>
</form>

@once
    <script>
        document.addEventListener('click', function (event) {
            var button = event.target.closest('.delete-button');
            if (!button) {
                return;
            }

            event.preventDefault();

            var form = button.closest('form.delete-form');
            if (!form) {
                return;
            }

            var confirmDelete = function () {
                if (typeof window.Swal !== 'undefined' && typeof window.Swal.fire === 'function') {
                    return window.Swal.fire({
                        title: 'Você tem certeza?',
                        text: 'Esta ação não pode ser desfeita!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, excluir!',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        focusCancel: true,
                    }).then(function (result) {
                        if (result && result.isConfirmed) {
                            form.submit();
                        }
                    });
                }

                if (window.confirm('Você tem certeza? Esta ação não pode ser desfeita!')) {
                    form.submit();
                }
            };

            confirmDelete();
        });
    </script>
@endonce
