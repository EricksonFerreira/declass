@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
        <x-page-title :title="$titulo" subtitle="Gerencie as categorias e os vídeos exibidos na Central de Tutoriais" />
        <a href="{{ route('tutoriais.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Novo Tutorial
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Categorias -->
        <div class="col-lg-4 mb-3">
            <div class="card shadow-sm" style="border-radius:12px; border:1px solid #e2e8f0;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold m-0"><i class="fas fa-folder mr-2 text-primary"></i>Categorias</h6>
                    <span class="badge badge-secondary badge-pill">{{ $categorias->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <form action="{{ route('tutoriais.categorias.store') }}" method="POST" class="p-3 border-bottom bg-light">
                        @csrf
                        <label class="small text-muted mb-1">Nova categoria</label>
                        <div class="input-group">
                            <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                                   placeholder="Ex.: Primeiros passos" maxlength="255" required>
                            <input type="number" name="ordem" class="form-control" style="max-width:80px"
                                   placeholder="Ordem" min="0" value="0" title="Ordem de exibição">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary" title="Adicionar categoria">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        @error('nome') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </form>

                    @if ($categorias->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">Nenhuma categoria cadastrada.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($categorias as $categoria)
                                <li class="list-group-item d-flex align-items-center justify-content-between py-2">
                                    <div>
                                        <strong>{{ $categoria->nome }}</strong>
                                        <span class="badge badge-light border ml-1">{{ $categoria->tutoriais_count }}</span>
                                        <div class="small text-muted">
                                            Ordem {{ $categoria->ordem }}
                                            @unless ($categoria->ativo) · <span class="text-warning">Inativa</span> @endunless
                                        </div>
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-xs btn-outline-secondary" title="Editar"
                                                data-toggle="modal" data-target="#categoriaModal"
                                                data-id="{{ $categoria->id }}"
                                                data-nome="{{ $categoria->nome }}"
                                                data-ordem="{{ $categoria->ordem }}"
                                                data-ativo="{{ $categoria->ativo ? 1 : 0 }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <x-delete-button
                                            :action="route('tutoriais.categorias.destroy', $categoria)"
                                            titulo=""
                                            mensagem="Os tutoriais desta categoria ficarão sem categoria. Continuar?" />
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tutoriais -->
        <div class="col-lg-8 mb-3">
            <div class="card shadow-sm" style="border-radius:12px; border:1px solid #e2e8f0;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold m-0"><i class="fas fa-video mr-2 text-primary"></i>Tutoriais</h6>
                    <span class="badge badge-secondary badge-pill">{{ $tutoriais->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if ($tutoriais->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">Nenhum tutorial cadastrado.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:70px;">Capa</th>
                                        <th>Título</th>
                                        <th>Categoria</th>
                                        <th class="text-center">Ordem</th>
                                        <th>Visibilidade</th>
                                        <th>Status</th>
                                        <th class="text-right" style="width:110px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tutoriais as $tutorial)
                                        <tr>
                                            <td>
                                                @if ($tutorial->capa_url)
                                                    <img src="{{ $tutorial->capa_url }}" class="rounded" style="width:56px; height:36px; object-fit:cover;">
                                                @else
                                                    <span class="d-inline-block rounded bg-dark text-center text-white-50"
                                                          style="width:56px; height:36px; line-height:36px;">
                                                        <i class="fas fa-play"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $tutorial->titulo }}</strong>
                                                @if ($tutorial->principal)
                                                    <i class="fas fa-star text-warning ml-1" title="Tutorial principal"></i>
                                                @endif
                                                <div class="small text-muted">
                                                    @if ($tutorial->video_path)
                                                        <i class="fas fa-file-video mr-1"></i>Arquivo enviado
                                                    @elseif ($tutorial->video_url)
                                                        <i class="fas fa-link mr-1"></i>{{ Str::limit($tutorial->video_url, 40) }}
                                                    @else
                                                        <span class="text-warning">Sem vídeo</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $tutorial->categoria->nome ?? '—' }}</td>
                                            <td class="text-center">{{ $tutorial->ordem }}</td>
                                            <td>
                                                @if ($tutorial->visibilidade === 'superadmin')
                                                    <span class="badge badge-dark">Superadmin</span>
                                                @else
                                                    <span class="badge badge-info">Todos</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($tutorial->ativo)
                                                    <span class="badge badge-success">Ativo</span>
                                                @else
                                                    <span class="badge badge-secondary">Inativo</span>
                                                @endif
                                            </td>
                                            <td class="text-right text-nowrap">
                                                <a href="{{ route('tutoriais.edit', $tutorial) }}" class="btn btn-xs btn-outline-primary" title="Editar">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <x-delete-button
                                                    :action="route('tutoriais.destroy', $tutorial)"
                                                    titulo=""
                                                    mensagem="Excluir o tutorial '{{ $tutorial->titulo }}'?" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal editar categoria -->
    <div class="modal fade" id="categoriaModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:12px;">
                <form id="categoria-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h6 class="modal-title font-weight-bold">Editar categoria</h6>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" name="nome" id="cat-nome" class="form-control" maxlength="255" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label>Ordem</label>
                                <input type="number" name="ordem" id="cat-ordem" class="form-control" min="0">
                            </div>
                            <div class="form-group col-6">
                                <label>Status</label>
                                <select name="ativo" id="cat-ativo" class="form-control">
                                    <option value="1">Ativa</option>
                                    <option value="0">Inativa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#categoriaModal').on('show.bs.modal', function (e) {
                const btn = $(e.relatedTarget);
                const form = document.getElementById('categoria-form');
                form.action = '{{ route('tutoriais.categorias.update', ['categoria' => '__ID__']) }}'.replace('__ID__', btn.data('id'));
                document.getElementById('cat-nome').value = btn.data('nome');
                document.getElementById('cat-ordem').value = btn.data('ordem');
                document.getElementById('cat-ativo').value = btn.data('ativo') ? '1' : '0';
            });
        });
    </script>
@endpush
