@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="row">
        <!-- Listagem de Clientes -->
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $clientes->total() }}</span></h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 10px;">
                        <button type="button" class="btn btn-sm btn-primary" id="btn-create-cliente">
                            <i class="fas fa-plus mr-1"></i> Novo Cliente
                        </button>
                        
                        <form action="{{ route('clockify.clientes.index') }}" method="GET" class="input-group input-group-sm mb-0" style="width: 200px;">
                            <input type="text" name="search" class="form-control float-right" placeholder="Procurar..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped table-bordered table-sm text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Status</th>
                                <th style="width: 150px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clientes as $cliente)
                                <tr>
                                    <td class="align-middle font-weight-bold">{{ $cliente->nome }}</td>
                                    <td class="align-middle">{{ $cliente->email ?? '-' }}</td>
                                    <td class="align-middle">{{ $cliente->telefone ?? '-' }}</td>
                                    <td class="align-middle">
                                        <span class="badge badge-{{ $cliente->ativo ? 'success' : 'danger' }}">
                                            {{ $cliente->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button type="button" class="btn btn-xs btn-warning mr-1" 
                                            onclick="editCliente({{ json_encode($cliente) }})">
                                            <i class="fa fa-edit"></i> Editar
                                        </button>
                                        <form action="{{ route('clockify.clientes.destroy', $cliente->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este cliente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-user-slash fa-2x mb-2 d-block"></i>
                                        Nenhum cliente cadastrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($clientes->hasPages())
                    <div class="card-footer py-2 d-flex justify-content-end">
                        {{ $clientes->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Side Drawer Component -->
    <x-side-drawer id="clienteDrawer" title="Cadastrar Novo Cliente" formId="cliente-form" action="{{ route('clockify.clientes.store') }}">
        <div class="form-group">
            <label for="nome">Nome <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="nome" class="form-control form-control-sm" required placeholder="Ex: Google Inc.">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control form-control-sm" placeholder="cliente@email.com">
        </div>
        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="text" name="telefone" id="telefone" class="form-control form-control-sm" placeholder="(11) 99999-9999">
        </div>
        <div class="form-group">
            <label for="endereco">Endereço</label>
            <textarea name="endereco" id="endereco" rows="3" class="form-control form-control-sm" placeholder="Rua, Número, Bairro..."></textarea>
        </div>
        <div class="form-group" id="status-group" style="display: none;">
            <label for="ativo">Status</label>
            <select name="ativo" id="ativo" class="form-control form-control-sm">
                <option value="1">Ativo</option>
                <option value="0">Inativo</option>
            </select>
        </div>
    </x-side-drawer>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(function() {
            $('#telefone').mask('(00) 00000-0000');
            
            $('#btn-create-cliente').on('click', function() {
                $('#clienteDrawerTitle').text('Cadastrar Novo Cliente');
                $('#cliente-form').attr('action', '{{ route('clockify.clientes.store') }}');
                $('#cliente-formMethod').val('POST');
                $('#cliente-form')[0].reset();
                $('#status-group').hide();
                $('#clienteDrawerBackdrop').addClass('show');
                $('#clienteDrawer').addClass('open');
            });
        });

        function editCliente(cliente) {
            $('#clienteDrawerTitle').text('Editar Cliente: ' + cliente.nome);
            $('#cliente-form').attr('action', '/admin/clockify/clientes/' + cliente.id);
            $('#cliente-formMethod').val('PUT');
            $('#nome').val(cliente.nome);
            $('#email').val(cliente.email);
            $('#telefone').val(cliente.telefone);
            $('#endereco').val(cliente.endereco);
            $('#ativo').val(cliente.ativo ? "1" : "0");
            
            $('#status-group').show();
            $('#clienteDrawerBackdrop').addClass('show');
            $('#clienteDrawer').addClass('open');
        }
    </script>
@stop
