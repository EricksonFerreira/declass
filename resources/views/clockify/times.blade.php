@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@push('css')
    @include('partials.ux-index-styles')
@endpush

@section('content')
    @if (!empty($filters))
        <div class="veiculo-filter-sticky">
            <x-filter
                :filters="$filters"
                :additionalFilters="$additionalFilters"
                :itensFiltered="$itensFiltered"
                :nomeFiltro="$nomeFiltro"
            />
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $times->total() }}</span></h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 10px;">
                        <form action="{{ route($nomeFiltro . '.setPerPage') }}" method="POST" class="mb-0">
                            @csrf
                            <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                        </form>
                        @include('partials.ux-view-toggle', ['viewKey' => 'times_index_view'])
                        @can('clockify.times.store')
                            <button type="button" class="btn btn-sm btn-primary" id="btn-create-time">
                                <i class="fas fa-plus mr-1"></i> Nova Equipe
                            </button>
                        @endcan
                    </div>
                </div>
                <div class="card-body p-0 ux-index-body" data-view="cards">
                    <div class="ux-cards">
                        @forelse ($times as $time)
                            <article class="ux-card">
                                <div class="ux-card__accent"></div>
                                <div class="ux-card__head">
                                    <div class="ux-card__avatar"><i class="fas fa-users"></i></div>
                                    <div class="ux-card__head-info">
                                        <div class="ux-card__title">{{ $time->nome }}</div>
                                        <div class="ux-card__sub">{{ $time->descricao ?: 'Sem descrição' }}</div>
                                    </div>
                                    <div class="ux-card__badges">
                                        <span class="ux-card__badge ux-card__badge--blue">{{ $time->users->count() }} membro(s)</span>
                                        @if ($time->lider)
                                            <span class="ux-card__badge ux-card__badge--blue" title="Líder da equipe"><i class="fas fa-star mr-1"></i>{{ $time->lider->nome }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="ux-card__meta">
                                    <div class="ux-card__meta-row ux-card__meta-row--full">
                                        <span class="ux-card__meta-label"><i class="fas fa-user-friends mr-1"></i>Membros</span>
                                        <strong class="ux-card__meta-value" style="white-space: normal;">
                                            @forelse ($time->users as $u)
                                                <span class="badge badge-secondary mb-1" title="{{ $u->email }}">{{ $u->nome }}</span>
                                            @empty
                                                <span class="text-muted">Sem membros</span>
                                            @endforelse
                                        </strong>
                                    </div>
                                </div>
                                <div class="ux-card__foot">
                                    @if ($time->podeSerGerenciadoPor(auth()->user()))
                                        <button type="button" class="btn btn-xs btn-warning"
                                            onclick="editTime({{ json_encode($time) }}, {{ json_encode($time->users->pluck('id')) }})">
                                            <i class="fa fa-edit mr-1"></i>Editar
                                        </button>
                                        <form action="{{ route('clockify.times.destroy', $time->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este time?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash mr-1"></i>Excluir</button>
                                        </form>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="text-center text-muted py-4 w-100">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                Nenhum time cadastrado.
                            </div>
                        @endforelse
                    </div>
                    <div class="ux-table table-responsive">
                        <table class="table table-hover table-striped table-bordered table-sm text-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Nome do Time</th>
                                <th>Líder</th>
                                <th>Descrição</th>
                                <th>Membros</th>
                                <th style="width: 150px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($times as $time)
                                <tr>
                                    <td class="align-middle font-weight-bold">{{ $time->nome }}</td>
                                    <td class="align-middle">
                                        @if ($time->lider)
                                            <span class="badge badge-warning" title="Líder da equipe"><i class="fas fa-star mr-1"></i>{{ $time->lider->nome }}</span>
                                        @else
                                            <span class="text-muted text-xs">—</span>
                                        @endif
                                    </td>
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
                                        @if ($time->podeSerGerenciadoPor(auth()->user()))
                                            <button type="button" class="btn btn-xs btn-warning mr-1"
                                                onclick="editTime({{ json_encode($time) }}, {{ json_encode($time->users->pluck('id')) }})">
                                                <i class="fa fa-edit"></i> Editar
                                            </button>
                                            <form action="{{ route('clockify.times.destroy', $time->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este time?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Excluir</button>
                                            </form>
                                        @else
                                            <span class="text-muted text-xs">Somente leitura</span>
                                        @endif
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
    <x-modal id="timeDrawer" icon="fas fa-users" title="Nova Equipe" formId="time-form" action="{{ route('clockify.times.store') }}">
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
        @if (auth()->user()->isAdministrador())
            <div class="form-group">
                <label for="lider_id">Líder da Equipe</label>
                <select name="lider_id" id="lider_id" class="form-control form-control-sm">
                    <option value="">Sem líder definido</option>
                    @foreach ($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" {{ (string) old('lider_id') === (string) $usuario->id ? 'selected' : '' }}>{{ $usuario->nome }}</option>
                    @endforeach
                </select>
                <small class="text-muted">O líder aprova folgas e gerencia os membros desta equipe.</small>
            </div>
        @endif
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
    </x-modal>
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
            $('#lider_id').val(time.lider_id || '');

            $('.user-checkbox').prop('checked', false);
            userIds.forEach(function(id) {
                $('#user-' + id).prop('checked', true);
            });

            $('#timeDrawerBackdrop').addClass('show');
            $('#timeDrawer').addClass('open');
        }
    </script>
@stop
