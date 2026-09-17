@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $times->total() }}</span></h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 10px;">
                        <button type="button" class="btn btn-sm btn-primary" id="btn-create-time">
                            <i class="fas fa-plus mr-1"></i> Nova Equipe
                        </button>

                        <form action="{{ route('clockify.times.index') }}" method="GET" class="input-group input-group-sm mb-0" style="width: 200px;">
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
                                <th>Nome do Time</th>
                                <th>Descrição</th>
                                <th>Membros</th>
                                <th style="width: 150px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($times as $time)
                                <tr>
                                    <td class="align-middle font-weight-bold">{{ $time->nome }}</td>
                                    <td class="align-middle">{{ $time->descricao ?? '-' }}</td>
                                    <td class="align-middle">
                                        @foreach ($time->users as $u)
                                            <span class="badge badge-secondary" title="{{ $u->email }}">{{ $u->nome }}</span>
                                        @endforeach
                                        @if ($time->users->isEmpty())
                                            <span class="text-muted text-xs">Sem membros</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <button type="button" class="btn btn-xs btn-warning mr-1"
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
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                        Nenhum time cadastrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($times->hasPages())
                    <div class="card-footer py-2 d-flex justify-content-end">
                        {{ $times->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Side Drawer Component -->
    <x-side-drawer id="timeDrawer" title="Nova Equipe" formId="time-form" action="{{ route('clockify.times.store') }}">
        <input type="hidden" name="id" id="time_id" value="{{ old('id') }}">
        <div class="form-group">
            <label for="nome">Nome do Time <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="nome" class="form-control form-control-sm @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required placeholder="Ex: Time de Design">
            @error('nome')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea name="descricao" id="descricao" rows="2" class="form-control form-control-sm" placeholder="Opcional...">{{ old('descricao') }}</textarea>
        </div>
        <div class="form-group">
            <label>Selecionar Membros</label>
            <div style="max-height: 240px; overflow-y: auto; border: 1px solid #ced4da; padding: 10px; border-radius: 4px;">
                @foreach ($usuarios as $usuario)
                    <div class="custom-control custom-checkbox mb-1">
                        <input class="custom-control-input user-checkbox" type="checkbox" name="users[]" id="user-{{ $usuario->id }}" value="{{ $usuario->id }}"
                            {{ in_array($usuario->id, (array) old('users', [])) ? 'checked' : '' }}>
                        <label for="user-{{ $usuario->id }}" class="custom-control-label font-weight-normal text-sm">{{ $usuario->nome }}</label>
                    </div>
                @endforeach
            </div>
        </div>
    </x-side-drawer>
@stop

@section('js')
    <script>
        $(function() {
            $('#btn-create-time').on('click', function() {
                $('#timeDrawerTitle').text('Nova Equipe');
                $('#time-form').attr('action', '{{ route('clockify.times.store') }}');
                $('#time-formMethod').val('POST');
                $('#time-form')[0].reset();
                $('#time_id').val('');
                $('.user-checkbox').prop('checked', false);
                $('#timeDrawerBackdrop').addClass('show');
                $('#timeDrawer').addClass('open');
            });

            @if ($errors->any())
                @if (old('_method') === 'PUT')
                    $('#timeDrawerTitle').text('Editar Equipe');
                    $('#time-form').attr('action', '{{ url('admin/times') }}/' + '{{ old('id') }}');
                    $('#time-formMethod').val('PUT');
                @else
                    $('#timeDrawerTitle').text('Nova Equipe');
                    $('#time-form').attr('action', '{{ route('clockify.times.store') }}');
                    $('#time-formMethod').val('POST');
                @endif
                $('#timeDrawerBackdrop').addClass('show');
                $('#timeDrawer').addClass('open');
            @endif
        });

        function editTime(time, userIds) {
            $('#timeDrawerTitle').text('Editar Equipe: ' + time.nome);
            $('#time-form').attr('action', '{{ url('admin/times') }}/' + time.id);
            $('#time-formMethod').val('PUT');
            $('#time_id').val(time.id);
            $('#nome').val(time.nome);
            $('#descricao').val(time.descricao);

            $('.user-checkbox').prop('checked', false);
            userIds.forEach(function(id) {
                $('#user-' + id).prop('checked', true);
            });

            $('#timeDrawerBackdrop').addClass('show');
            $('#timeDrawer').addClass('open');
        }
    </script>
@stop
