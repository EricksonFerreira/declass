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
        <!-- Listagem de Clientes -->
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $clientes->total() }}</span></h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 10px;">
                        <form action="{{ route($nomeFiltro . '.setPerPage') }}" method="POST" class="mb-0">
                            @csrf
                            <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                        </form>
                        @include('partials.ux-view-toggle', ['viewKey' => 'clientes_index_view'])
                        <button type="button" class="btn btn-sm btn-primary" id="btn-create-cliente">
                            <i class="fas fa-plus mr-1"></i> Novo Cliente
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 ux-index-body" data-view="cards">
                    <div class="ux-cards">
                        @forelse ($clientes as $cliente)
                            @php
                                $nomeCliente = trim((string) ($cliente->nome ?? 'Cliente'));
                                $partes = preg_split('/\s+/', $nomeCliente) ?: [];
                                $iniciais = collect($partes)->filter()->take(2)
                                    ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: 'CL';
                            @endphp
                            <article class="ux-card">
                                <div class="ux-card__accent"></div>
                                <div class="ux-card__head">
                                    <div class="ux-card__avatar">{{ $iniciais }}</div>
                                    <div class="ux-card__head-info">
                                        <div class="ux-card__title">{{ $cliente->nome }}</div>
                                        <div class="ux-card__sub">{{ $cliente->email ?: 'Sem e-mail' }}</div>
                                    </div>
                                    <div class="ux-card__badges">
                                        <span class="ux-card__badge ux-card__badge--{{ $cliente->ativo ? 'green' : 'red' }}">
                                            {{ $cliente->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ux-card__meta">
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="fas fa-phone-alt mr-1"></i>Telefone</span>
                                        <strong class="ux-card__meta-value">{{ $cliente->telefone ?: '—' }}</strong>
                                    </div>
                                    <div class="ux-card__meta-row">
                                        <span class="ux-card__meta-label"><i class="fas fa-envelope mr-1"></i>E-mail</span>
                                        <strong class="ux-card__meta-value">{{ $cliente->email ?: '—' }}</strong>
                                    </div>
                                </div>
                                <div class="ux-card__foot">
                                    <button type="button" class="btn btn-xs btn-warning"
                                        onclick="editCliente({{ json_encode($cliente) }})">
                                        <i class="fa fa-edit mr-1"></i>Editar
                                    </button>
                                    <form action="{{ route('clockify.clientes.destroy', $cliente->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este cliente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash mr-1"></i>Excluir</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="text-center text-muted py-4 w-100">
                                <i class="fas fa-user-slash fa-2x mb-2 d-block"></i>
                                Nenhum cliente cadastrado.
                            </div>
                        @endforelse
                    </div>
                    <div class="ux-table table-responsive">
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
    <x-modal id="clienteDrawer" icon="fas fa-user-friends" title="Cadastrar Novo Cliente" formId="cliente-form" action="{{ route('clockify.clientes.store') }}">
        <input type="hidden" name="id" id="cliente_id" value="{{ old('id') }}">
        <div class="form-group">
            <label for="nome">Nome <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="nome" class="form-control form-control-sm @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required placeholder="Ex: Google Inc.">
            @error('nome')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control form-control-sm" value="{{ old('email') }}" placeholder="cliente@email.com">
        </div>
        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="text" name="telefone" id="telefone" class="form-control form-control-sm" value="{{ old('telefone') }}" placeholder="(11) 99999-9999">
        </div>
        <div class="form-group">
            <label for="endereco">Endereço</label>
            <textarea name="endereco" id="endereco" rows="3" class="form-control form-control-sm" placeholder="Rua, Número, Bairro...">{{ old('endereco') }}</textarea>
        </div>
        <div class="form-group" id="status-group" style="display: none;">
            <label for="ativo">Status</label>
            <select name="ativo" id="ativo" class="form-control form-control-sm">
                <option value="1">Ativo</option>
                <option value="0">Inativo</option>
            </select>
        </div>
    </x-modal>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(function() {
            $('#cliente-form #telefone').mask('(00) 00000-0000');
            
            $('#btn-create-cliente').on('click', function() {
                $('#clienteDrawerTitle').text('Cadastrar Novo Cliente');
                $('#cliente-form').attr('action', '{{ route('clockify.clientes.store') }}');
                $('#cliente-formMethod').val('POST');
                $('#cliente-form')[0].reset();
                $('#cliente_id').val('');
                $('#status-group').hide();
                $('#clienteDrawerBackdrop').addClass('show');
                $('#clienteDrawer').addClass('open');
            });

            @if ($errors->any())
                @if (old('_method') === 'PUT')
                    $('#clienteDrawerTitle').text('Editar Cliente');
                    $('#cliente-form').attr('action', '{{ url('admin/clientes') }}/' + '{{ old('id') }}');
                    $('#cliente-formMethod').val('PUT');
                    $('#status-group').show();
                @else
                    $('#clienteDrawerTitle').text('Cadastrar Novo Cliente');
                    $('#cliente-form').attr('action', '{{ route('clockify.clientes.store') }}');
                    $('#cliente-formMethod').val('POST');
                @endif
                $('#clienteDrawerBackdrop').addClass('show');
                $('#clienteDrawer').addClass('open');
            @endif
        });

        function editCliente(cliente) {
            $('#clienteDrawerTitle').text('Editar Cliente: ' + cliente.nome);
            $('#cliente-form').attr('action', '{{ url('admin/clientes') }}/' + cliente.id);
            $('#cliente-formMethod').val('PUT');
            $('#cliente_id').val(cliente.id);
            $('#cliente-form #nome').val(cliente.nome);
            $('#cliente-form #email').val(cliente.email);
            $('#cliente-form #telefone').val(cliente.telefone);
            $('#cliente-form #endereco').val(cliente.endereco);
            $('#cliente-form #ativo').val(cliente.ativo ? "1" : "0");
            
            $('#status-group').show();
            $('#clienteDrawerBackdrop').addClass('show');
            $('#clienteDrawer').addClass('open');
        }
    </script>
@stop
