@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="row">
        <!-- Listagem de Equipes -->
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $times->total() }}</span></h3>
                    <div class="card-tools">
                        <form action="{{ route('clockify.times.index') }}" method="GET" class="input-group input-group-sm" style="width: 200px;">
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
                                <th>Nome do Time</th>
                                <th>Descrição</th>
                                <th>Membros</th>
                                <th style="width: 150px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($times as $time)
                                <tr>
                                    <td class="text-bold">{{ $time->nome }}</td>
                                    <td>{{ $time->descricao ?? '-' }}</td>
                                    <td>
                                        @foreach ($time->users as $u)
                                            <span class="badge badge-secondary" title="{{ $u->email }}">{{ $u->nome }}</span>
                                        @endforeach
                                        @if ($time->users->isEmpty())
                                            <span class="text-muted text-xs">Sem membros</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-xs btn-warning" 
                                            onclick="editTime({{ json_encode($time) }}, {{ json_encode($time->users->pluck('id')) }})">
                                            <i class="fa fa-edit"></i> Editar
                                        </button>
                                        <form action="{{ route('clockify.times.destroy', $time->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este time?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Nenhum time cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $times->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

        <!-- Formulário de Cadastro / Edição -->
        <div class="col-md-4">
            <div class="card card-outline card-success" id="form-card">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm" id="form-title">Criar Novo Time</h3>
                </div>
                <form id="time-form" action="{{ route('clockify.times.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="POST">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nome">Nome do Time <span class="text-danger">*</span></label>
                            <input type="text" name="nome" id="nome" class="form-control form-control-sm" required placeholder="Ex: Time de Design">
                        </div>
                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <textarea name="descricao" id="descricao" rows="2" class="form-control form-control-sm" placeholder="Opcional..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Selecionar Membros</label>
                            <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ced4da; padding: 10px; border-radius: 4px;">
                                @foreach ($usuarios as $usuario)
                                    <div class="custom-control custom-checkbox mb-1">
                                        <input class="custom-control-input user-checkbox" type="checkbox" name="users[]" id="user-{{ $usuario->id }}" value="{{ $usuario->id }}">
                                        <label for="user-{{ $usuario->id }}" class="custom-control-label font-weight-normal text-sm">{{ $usuario->nome }}</label>
                                    </div>
                                @endforeach
                            </div>
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
        function editTime(time, userIds) {
            $('#form-title').text('Editar Time: ' + time.nome);
            $('#time-form').attr('action', '/admin/clockify/times/' + time.id);
            $('#form-method').val('PUT');
            $('#nome').val(time.nome);
            $('#descricao').val(time.descricao);
            
            // Uncheck all, then check the associated users
            $('.user-checkbox').prop('checked', false);
            userIds.forEach(function(id) {
                $('#user-' + id).prop('checked', true);
            });
            
            $('#cancel-btn').show();
            $('#form-card').removeClass('card-success').addClass('card-warning');
            $('#save-btn').removeClass('btn-success').addClass('btn-warning').text('Atualizar');
        }

        function resetForm() {
            $('#form-title').text('Criar Novo Time');
            $('#time-form').attr('action', '{{ route('clockify.times.store') }}');
            $('#form-method').val('POST');
            $('#time-form')[0].reset();
            $('.user-checkbox').prop('checked', false);
            
            $('#cancel-btn').hide();
            $('#form-card').removeClass('card-warning').addClass('card-success');
            $('#save-btn').removeClass('btn-warning').addClass('btn-success').text('Salvar');
        }
    </script>
@stop
