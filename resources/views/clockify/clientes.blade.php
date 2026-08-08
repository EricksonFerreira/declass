@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="row">
        <!-- Listagem de Clientes -->
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $clientes->total() }}</span></h3>
                    <div class="card-tools">
                        <form action="{{ route('clockify.clientes.index') }}" method="GET" class="input-group input-group-sm" style="width: 200px;">
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
                                <th>Telefone</th>
                                <th>Status</th>
                                <th style="width: 150px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clientes as $cliente)
                                <tr>
                                    <td>{{ $cliente->nome }}</td>
                                    <td>{{ $cliente->email ?? '-' }}</td>
                                    <td>{{ $cliente->telefone ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $cliente->ativo ? 'success' : 'danger' }}">
                                            {{ $cliente->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Edit button triggers inline fill or modal -->
                                        <button type="button" class="btn btn-xs btn-warning" 
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
                                    <td colspan="5" class="text-center text-muted py-3">Nenhum cliente cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $clientes->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

        <!-- Formulário de Cadastro / Edição -->
        <div class="col-md-4">
            <div class="card card-outline card-success" id="form-card">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm" id="form-title">Cadastrar Novo Cliente</h3>
                </div>
                <form id="cliente-form" action="{{ route('clockify.clientes.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="POST">
                    <div class="card-body">
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

@section('js')
    <script>
        function editCliente(cliente) {
            $('#form-title').text('Editar Cliente: ' + cliente.nome);
            $('#cliente-form').attr('action', '/admin/clockify/clientes/' + cliente.id);
            $('#form-method').val('PUT');
            $('#nome').val(cliente.nome);
            $('#email').val(cliente.email);
            $('#telefone').val(cliente.telefone);
            $('#endereco').val(cliente.endereco);
            $('#ativo').val(cliente.ativo ? "1" : "0");
            
            $('#status-group').show();
            $('#cancel-btn').show();
            $('#form-card').removeClass('card-success').addClass('card-warning');
            $('#save-btn').removeClass('btn-success').addClass('btn-warning').text('Atualizar');
        }

        function resetForm() {
            $('#form-title').text('Cadastrar Novo Cliente');
            $('#cliente-form').attr('action', '{{ route('clockify.clientes.store') }}');
            $('#form-method').val('POST');
            $('#cliente-form')[0].reset();
            
            $('#status-group').hide();
            $('#cancel-btn').hide();
            $('#form-card').removeClass('card-warning').addClass('card-success');
            $('#save-btn').removeClass('btn-warning').addClass('btn-success').text('Salvar');
        }
    </script>
@stop
