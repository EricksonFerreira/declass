@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="row">
        <!-- Listagem de Tags -->
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $tags->total() }}</span></h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 10px;">
                        <button type="button" class="btn btn-sm btn-primary" id="btn-create-tag">
                            <i class="fas fa-plus mr-1"></i> Novo Tag
                        </button>
                        
                        <form action="{{ route('clockify.tags.index') }}" method="GET" class="input-group input-group-sm mb-0" style="width: 200px;">
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
                                <th>Status</th>
                                <th style="width: 150px;" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tags as $tag)
                                <tr>
                                    <td class="align-middle font-weight-bold">{{ $tag->nome }}</td>
                                    <td class="align-middle">
                                        <span class="badge badge-{{ $tag->ativo ? 'success' : 'danger' }}">
                                            {{ $tag->ativo ? 'Ativa' : 'Inativa' }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <button type="button" class="btn btn-xs btn-warning mr-1" 
                                            onclick="editTag({{ json_encode($tag) }})">
                                            <i class="fa fa-edit"></i> Editar
                                        </button>
                                        <form action="{{ route('clockify.tags.destroy', $tag->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir esta tag?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="fas fa-tags fa-2x mb-2 d-block"></i>
                                        Nenhuma tag cadastrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($tags->hasPages())
                    <div class="card-footer py-2 d-flex justify-content-end">
                        {{ $tags->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Side Drawer Component -->
    <x-side-drawer id="tagDrawer" title="Criar Nova Tag" formId="tag-form" action="{{ route('clockify.tags.store') }}">
        <div class="form-group">
            <label for="nome">Nome da Tag <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="nome" class="form-control form-control-sm" required placeholder="Ex: Faturável, Em Análise...">
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
    <script>
        $(function() {
            $('#btn-create-tag').on('click', function() {
                $('#tagDrawerTitle').text('Criar Nova Tag');
                $('#tag-form').attr('action', '{{ route('clockify.tags.store') }}');
                $('#tag-formMethod').val('POST');
                $('#tag-form')[0].reset();
                $('#status-group').hide();
                $('#tagDrawerBackdrop').addClass('show');
                $('#tagDrawer').addClass('open');
            });
        });

        function editTag(tag) {
            $('#tagDrawerTitle').text('Editar Tag: ' + tag.nome);
            $('#tag-form').attr('action', '/admin/clockify/tags/' + tag.id);
            $('#tag-formMethod').val('PUT');
            $('#nome').val(tag.nome);
            $('#ativo').val(tag.ativo ? "1" : "0");
            
            $('#status-group').show();
            $('#tagDrawerBackdrop').addClass('show');
            $('#tagDrawer').addClass('open');
        }
    </script>
@stop
