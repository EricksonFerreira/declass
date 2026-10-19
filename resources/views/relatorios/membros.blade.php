@extends('adminlte::page')

@section('title', 'Membros da Equipe')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Membros da Equipe'" />
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-user-clock mr-1"></i> Registros
                <span class="badge badge-primary ml-1">{{ $users->total() }}</span>
            </h3>
            <div class="card-tools">
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <form action="{{ route('relatorios.membros') }}" method="GET" class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" name="search" class="form-control float-right" placeholder="Procurar..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                    @can('relatorios.membros.store')
                        <button type="button" class="btn btn-sm btn-primary" id="btn-novo-membro">
                            <i class="fas fa-plus mr-1"></i> Novo
                        </button>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-sm text-nowrap mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-3">Nome</th>
                        <th>Email</th>
                        <th class="pr-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="pl-3 font-weight-bold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="pr-3">
                                @can('relatorios.membros.update')
                                    <button type="button" class="btn btn-xs btn-warning btn-editar-membro"
                                            data-id="{{ $user->id }}"
                                            data-name="{{ $user->name }}"
                                            data-email="{{ $user->email }}">
                                        <i class="fa fa-edit"></i> Editar
                                    </button>
                                @endcan
                                @can('relatorios.membros.reset-password')
                                    <form action="{{ route('relatorios.membros.reset-password', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="button" class="btn btn-xs btn-info" onclick="confirmResetPassword(this)">
                                            <i class="fa fa-key"></i> Resetar Senha
                                        </button>
                                    </form>
                                @endcan
                                @can('relatorios.membros.destroy')
                                    <form action="{{ route('relatorios.membros.destroy', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelete(this)"><i class="fa fa-trash"></i> Excluir</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                <i class="fas fa-user-clock fa-2x mb-2 d-block"></i>
                                Nenhum membro encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>

    <x-modal id="membroDrawer" icon="fas fa-user-cog" title="Cadastrar Membro" formId="membroForm" action="{{ route('relatorios.membros.store') }}">
        <input type="hidden" name="id" id="membro_id" value="{{ old('id') }}">
        <div class="form-group">
            <label for="membro_name">Nome <span class="text-danger">*</span></label>
            <input type="text" name="name" id="membro_name" class="form-control form-control-sm @error('name') is-invalid @enderror" required placeholder="Ex: João Silva" value="{{ old('name') }}">
            @error('name')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="membro_email">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" id="membro_email" class="form-control form-control-sm @error('email') is-invalid @enderror" required placeholder="email@exemplo.com" value="{{ old('email') }}">
            @error('email')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
    </x-modal>
@stop

@section('css')
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">
@stop

@section('js')
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: "{{ session('success') }}",
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: "{{ session('error') }}",
                timer: 4000
            });
        @endif

        const $modal = $('#membroDrawer');
        const $backdrop = $('#membroDrawerBackdrop');
        const $form = $('#membroForm');
        const $method = $('#membroFormMethod');
        const $title = $('#membroDrawerTitle');

        function abrirModal(titulo, action, method, data = null) {
            $title.text(titulo);
            $form.attr('action', action);
            $method.val(method);

            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').remove();

            if (data) {
                $('#membro_id').val(data.id || '');
                $('#membro_name').val(data.name);
                $('#membro_email').val(data.email);
            } else {
                $form[0].reset();
                $('#membro_id').val('');
            }

            $backdrop.addClass('show');
            $modal.addClass('open');
        }

        function fecharModal() {
            $modal.removeClass('open');
            $backdrop.removeClass('show');
        }

        $('#btn-novo-membro').on('click', function () {
            abrirModal('Cadastrar Membro', "{{ route('relatorios.membros.store') }}", 'POST');
        });

        $(document).on('click', '.btn-editar-membro', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const id = $btn.data('id');
            abrirModal('Editar Membro', "{{ url('admin/relatorios/membros') }}/" + id, 'PUT', {
                id: id,
                name: $btn.data('name'),
                email: $btn.data('email')
            });
        });

        $('#closemembroDrawer, #cancelmembroDrawer, #membroDrawerBackdrop').on('click', fecharModal);

        @if ($errors->any())
            abrirModal(
                "{{ old('_method', 'POST') === 'PUT' ? 'Editar Membro' : 'Cadastrar Membro' }}",
                "{{ old('_method', 'POST') === 'PUT' ? url('admin/relatorios/membros') . '/' . old('id', 0) : route('relatorios.membros.store') }}",
                "{{ old('_method', 'POST') === 'PUT' ? 'PUT' : 'POST' }}",
                {
                    id: "{{ old('id') }}",
                    name: "{{ old('name') }}",
                    email: "{{ old('email') }}"
                }
            );
        @endif

        function confirmResetPassword(button) {
            const form = $(button).closest('form');
            Swal.fire({
                title: 'Resetar Senha?',
                text: "A senha do membro será resetada para 'Senh@123' e ele será forçado a alterá-la no próximo login.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0ea5e9',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sim, resetar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function confirmDelete(button) {
            const form = $(button).closest('form');
            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não poderá ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
@stop
