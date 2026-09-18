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
        <!-- Listagem de Tags -->
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm">Registros <span class="badge badge-primary">{{ $tags->total() }}</span></h3>
                    <div class="card-tools d-flex align-items-center" style="gap: 10px;">
                        <form action="{{ route($nomeFiltro . '.setPerPage') }}" method="POST" class="mb-0">
                            @csrf
                            <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                        </form>
                        @include('partials.ux-view-toggle', ['viewKey' => 'tags_index_view', 'default' => 'table'])
                        <button type="button" class="btn btn-sm btn-primary" id="btn-create-tag">
                            <i class="fas fa-plus mr-1"></i> Novo Tag
                        </button>
                    </div>
                </div>
                <div class="card-body p-0 ux-index-body" data-view="table">
                    <div class="ux-cards">
                        @forelse ($tags as $tag)
                            <article class="ux-card">
                                <div class="ux-card__accent"></div>
                                <div class="ux-card__head">
                                    <div class="ux-card__avatar"><i class="fas fa-tag"></i></div>
                                    <div class="ux-card__head-info">
                                        <div class="ux-card__title">{{ $tag->nome }}</div>
                                        <div class="ux-card__sub">Etiqueta de classificação</div>
                                    </div>
                                    <div class="ux-card__badges">
                                        <span class="ux-card__badge ux-card__badge--{{ $tag->ativo ? 'green' : 'red' }}">
                                            {{ $tag->ativo ? 'Ativa' : 'Inativa' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ux-card__foot">
                                    <button type="button" class="btn btn-xs btn-warning"
                                        onclick="editTag({{ json_encode($tag) }})">
                                        <i class="fa fa-edit mr-1"></i>Editar
                                    </button>
                                    <form action="{{ route('clockify.tags.destroy', $tag->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente excluir esta tag?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash mr-1"></i>Excluir</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="text-center text-muted py-4 w-100">
                                <i class="fas fa-tags fa-2x mb-2 d-block"></i>
                                Nenhuma tag cadastrada.
                            </div>
                        @endforelse
                    </div>
                    <div class="ux-table table-responsive">
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
    <x-modal id="tagDrawer" icon="fas fa-tags" title="Criar Nova Tag" formId="tag-form" action="{{ route('clockify.tags.store') }}">
        <input type="hidden" name="id" id="tag_id" value="{{ old('id') }}">
        <div class="form-group">
            <label for="nome">Nome da Tag <span class="text-danger">*</span></label>
            <input type="text" name="nome" id="nome" class="form-control form-control-sm @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required placeholder="Ex: Faturável, Em Análise...">
            @error('nome')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
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
    <script>
        $(function() {
            $('#btn-create-tag').on('click', function() {
                $('#tagDrawerTitle').text('Criar Nova Tag');
                $('#tag-form').attr('action', '{{ route('clockify.tags.store') }}');
                $('#tag-formMethod').val('POST');
                $('#tag-form')[0].reset();
                $('#tag_id').val('');
                $('#status-group').hide();
                $('#tagDrawerBackdrop').addClass('show');
                $('#tagDrawer').addClass('open');
            });

            @if ($errors->any())
                @if (old('_method') === 'PUT')
                    $('#tagDrawerTitle').text('Editar Tag');
                    $('#tag-form').attr('action', '{{ url('admin/tags') }}/' + '{{ old('id') }}');
                    $('#tag-formMethod').val('PUT');
                    $('#status-group').show();
                @else
                    $('#tagDrawerTitle').text('Criar Nova Tag');
                    $('#tag-form').attr('action', '{{ route('clockify.tags.store') }}');
                    $('#tag-formMethod').val('POST');
                @endif
                $('#tagDrawerBackdrop').addClass('show');
                $('#tagDrawer').addClass('open');
            @endif
        });

        function editTag(tag) {
            $('#tagDrawerTitle').text('Editar Tag: ' + tag.nome);
            $('#tag-form').attr('action', '{{ url('admin/tags') }}/' + tag.id);
            $('#tag-formMethod').val('PUT');
            $('#tag_id').val(tag.id);
            $('#nome').val(tag.nome);
            $('#ativo').val(tag.ativo ? "1" : "0");
            
            $('#status-group').show();
            $('#tagDrawerBackdrop').addClass('show');
            $('#tagDrawer').addClass('open');
        }
    </script>
@stop
