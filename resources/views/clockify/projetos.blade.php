@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="row">
        <!-- Listagem de Projetos -->
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $projetos->total() }}</span></h3>
                    <div class="card-tools">
                        <form action="{{ route('clockify.projetos.index') }}" method="GET" class="input-group input-group-sm" style="width: 200px;">
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
                                <th style="width: 50px;" class="text-center">Cor</th>
                                <th>Nome</th>
                                <th>Cliente</th>
                                <th>Descrição</th>
                                <th>Visibilidade</th>
                                <th>Status</th>
                                <th style="width: 150px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projetos as $projeto)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge text-white" style="background-color: {{ $projeto->cor ?? '#6c757d' }}; width: 24px; height: 24px; display: inline-block; border-radius: 50%; border: 1px solid #dee2e6;">&nbsp;</span>
                                    </td>
                                    <td class="font-weight-bold">{{ $projeto->nome }}</td>
                                    <td>{{ $projeto->cliente->nome ?? '-' }}</td>
                                    <td>
                                        <span class="text-muted" title="{{ $projeto->descricao }}">
                                            {{ Str::limit($projeto->descricao ?? '-', 30) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $projeto->publico ? 'info' : 'secondary' }}">
                                            {{ $projeto->publico ? 'Público' : 'Privado' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $projeto->ativo ? 'success' : 'danger' }}">
                                            {{ $projeto->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-warning" 
                                            onclick="editProjeto({{ json_encode($projeto) }})">
                                            <i class="fa fa-edit"></i> Editar
                                        </button>
                                        <form action="{{ route('clockify.projetos.destroy', $projeto->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este projeto?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">Nenhum projeto cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $projetos->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

        <!-- Formulário de Cadastro / Edição -->
        <div class="col-md-4">
            <div class="card card-outline card-success" id="form-card">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm" id="form-title">Cadastrar Novo Projeto</h3>
                </div>
                <form id="projeto-form" action="{{ route('clockify.projetos.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="POST">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nome">Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" id="nome" class="form-control form-control-sm" required placeholder="Ex: Novo Site XPAND">
                        </div>
                        <div class="form-group">
                            <label for="clockify_cliente_id">Cliente</label>
                            <select name="clockify_cliente_id" id="clockify_cliente_id" class="form-control form-control-sm select2">
                                <option value="">Nenhum Cliente</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <textarea name="descricao" id="descricao" rows="3" class="form-control form-control-sm" placeholder="Resumo do projeto..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="cor">Cor do Projeto</label>
                            <div class="input-group input-group-sm">
                                <input type="color" name="cor" id="cor" class="form-control form-control-sm" style="padding: 2px; height: 31px;" value="#0284c7">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="publico">Visibilidade</label>
                            <select name="publico" id="publico" class="form-control form-control-sm">
                                <option value="1">Público</option>
                                <option value="0">Privado</option>
                            </select>
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
        $(document).ready(function() {
            // Inicializar Select2 se disponível
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }
        });

        function editProjeto(projeto) {
            $('#form-title').text('Editar Projeto: ' + projeto.nome);
            $('#projeto-form').attr('action', '/admin/clockify/projetos/' + projeto.id);
            $('#form-method').val('PUT');
            $('#nome').val(projeto.nome);
            
            // Set values and trigger change for Select2
            $('#clockify_cliente_id').val(projeto.clockify_cliente_id).trigger('change');
            
            $('#descricao').val(projeto.descricao);
            $('#cor').val(projeto.cor ?? '#0284c7');
            $('#publico').val(projeto.publico ? "1" : "0");
            $('#ativo').val(projeto.ativo ? "1" : "0");
            
            $('#status-group').show();
            $('#cancel-btn').show();
            $('#form-card').removeClass('card-success').addClass('card-warning');
            $('#save-btn').removeClass('btn-success').addClass('btn-warning').text('Atualizar');
        }

        function resetForm() {
            $('#form-title').text('Cadastrar Novo Projeto');
            $('#projeto-form').attr('action', '{{ route('clockify.projetos.store') }}');
            $('#form-method').val('POST');
            $('#projeto-form')[0].reset();
            
            // Reset Select2
            $('#clockify_cliente_id').val('').trigger('change');
            
            $('#status-group').hide();
            $('#cancel-btn').hide();
            $('#form-card').removeClass('card-warning').addClass('card-success');
            $('#save-btn').removeClass('btn-warning').addClass('btn-success').text('Salvar');
        }
    </script>
@stop
