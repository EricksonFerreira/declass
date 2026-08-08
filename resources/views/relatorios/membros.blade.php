@extends('adminlte::page')

@section('title', 'Membros da Equipe')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Membros da Equipe'" />
@stop

@section('content')
    <div class="row">
        <!-- Listagem de Membros -->
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $users->total() }}</span></h3>
                    <div class="card-tools">
                        <form action="{{ route('relatorios.membros') }}" method="GET" class="input-group input-group-sm" style="width: 200px;">
                            <input type="text" name="search" class="form-control float-right" placeholder="Procurar..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped table-bordered table-sm text-nowrap">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Cargo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->cargo ?? 'Sem Cargo' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-warning" onclick="editUser({{ json_encode($user) }})">
                                            <i class="fa fa-edit"></i> Editar
                                        </button>
                                        <form action="{{ route('relatorios.membros.reset-password', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button" class="btn btn-xs btn-info" onclick="confirmResetPassword(this)">
                                                <i class="fa fa-key"></i> Resetar Senha
                                            </button>
                                        </form>
                                        <form action="{{ route('relatorios.membros.destroy', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelete(this)"><i class="fa fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Nenhum membro encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

        <!-- Formulário de Cadastro / Edição -->
        <div class="col-md-4">
            <div class="card card-outline card-success" id="form-card">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm" id="form-title">Cadastrar Novo Membro</h3>
                </div>
                <form id="user-form" action="{{ route('relatorios.membros.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="POST">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control form-control-sm" required placeholder="Ex: João Silva">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control form-control-sm" placeholder="email@exemplo.com">
                        </div>
                        <div class="form-group">
                            <label for="cargo">Cargo <span class="text-danger">*</span></label>
                            <input type="text" name="cargo" id="cargo" class="form-control form-control-sm" required placeholder="Ex: Desenvolvedor, Designer">
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-sm btn-success float-right" id="save-btn">Salvar</button>
                        <button type="button" class="btn btn-sm btn-default" id="cancel-btn" style="display: none;" onclick="resetForm()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">
@stop

@section('js')
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
    <script>
        // Alertar após ações com SweetAlert
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

        function editUser(user) {
            $('#form-title').text('Editar Membro: ' + user.name);
            $('#user-form').attr('action', '{{ url('admin/relatorios/membros') }}/' + user.id);
            $('#form-method').val('PUT');
            $('#name').val(user.name);
            $('#email').val(user.email);
            $('#cargo').val(user.cargo);
            $('#cancel-btn').show();
            $('#form-card').removeClass('card-success').addClass('card-warning');
            $('#save-btn').removeClass('btn-success').addClass('btn-warning').text('Atualizar');
        }

        function resetForm() {
            $('#form-title').text('Cadastrar Novo Membro');
            $('#user-form').attr('action', '{{ route('relatorios.membros.store') }}');
            $('#form-method').val('POST');
            $('#user-form')[0].reset();
            $('#cargo').val('');
            $('#cancel-btn').hide();
            $('#form-card').removeClass('card-warning').addClass('card-success');
            $('#save-btn').removeClass('btn-warning').addClass('btn-success').text('Salvar');
        }

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

        // Confirmação SweetAlert2 ao deletar
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
