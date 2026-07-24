@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="fc-page">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card fc-card h-100">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h3 class="fc-title"><i class="fas fa-tags"></i><span>Categorias existentes<span class="fc-subtitle">Classificação das receitas e despesas</span></span></h3>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover fc-table">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3">Código</th>
                                        <th class="py-3">Nome / Hierarquia</th>
                                        <th class="py-3">Tipo</th>
                                        <th class="py-3 text-center">Permite Lançamento</th>
                                        <th class="py-3 text-center">Status</th>
                                        <th class="py-3 text-right px-4">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categorias as $categoria)
                                        <tr>
                                            <td class="px-4 py-3 font-weight-bold text-secondary">{{ $categoria->codigo }}</td>
                                            <td class="py-3">
                                                @if($categoria->categoriaPai)
                                                    <span class="text-xs text-muted">{{ $categoria->categoriaPai->nome }} &gt;</span><br>
                                                @endif
                                                <span class="font-weight-bold text-dark">{{ $categoria->nome }}</span>
                                                @if($categoria->descricao)
                                                    <small class="d-block text-muted">{{ $categoria->descricao }}</small>
                                                @endif
                                            </td>
                                            <td class="py-3">
                                                <span class="fc-badge {{ $categoria->tipo === 'receita' ? 'fc-badge--receita' : 'fc-badge--despesa' }}">
                                                    {{ $categoria->tipo === 'receita' ? 'Receita' : 'Despesa' }}
                                                </span>
                                            </td>
                                            <td class="py-3 text-center">
                                                @if($categoria->permite_lancamento)
                                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Sim</span>
                                                @else
                                                    <span class="badge badge-secondary"><i class="fas fa-ban mr-1"></i>Não</span>
                                                @endif
                                            </td>
                                            <td class="py-3 text-center">
                                                @if($categoria->status === 'ativo')
                                                    <span class="badge badge-success">Ativo</span>
                                                @else
                                                    <span class="badge badge-danger">Inativo</span>
                                                @endif
                                            </td>
                                            <td class="py-3 text-right px-4">
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-category-btn mr-1" 
                                                    data-id="{{ $categoria->id }}"
                                                    data-nome="{{ $categoria->nome }}"
                                                    data-codigo="{{ $categoria->codigo }}"
                                                    data-tipo="{{ $categoria->tipo }}"
                                                    data-categoria_pai_id="{{ $categoria->categoria_pai_id }}"
                                                    data-descricao="{{ $categoria->descricao }}"
                                                    data-permite_lancamento="{{ $categoria->permite_lancamento ? '1' : '0' }}"
                                                    data-status="{{ $categoria->status }}"
                                                    data-has_lancamentos="{{ $categoria->hasLancamentos ? '1' : '0' }}"
                                                    title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('financeiro.fluxo-caixa.categorias.destroy', $categoria->id) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir esta categoria?');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">Nenhuma categoria cadastrada.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card fc-card">
                    <div class="card-header py-3">
                        <div class="fc-head">
                            <h3 class="fc-title"><i class="fas fa-folder-plus"></i><span>Nova categoria<span class="fc-subtitle">Crie uma classificação financeira</span></span></h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('financeiro.fluxo-caixa.categorias.store') }}" method="POST" id="createCategoryForm">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="tipo" class="fc-label">Tipo</label>
                                <select name="tipo" id="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                                    <option value="" disabled selected>Selecione o tipo</option>
                                    <option value="receita" {{ old('tipo') === 'receita' ? 'selected' : '' }}>Receita (entrada)</option>
                                    <option value="despesa" {{ old('tipo') === 'despesa' ? 'selected' : '' }}>Despesa (saída)</option>
                                </select>
                                @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="categoria_pai_id" class="fc-label">Categoria Superior (Pai)</label>
                                <select name="categoria_pai_id" id="categoria_pai_id" class="form-control @error('categoria_pai_id') is-invalid @enderror">
                                    <option value="">Nenhuma (Categoria de nível superior)</option>
                                    @foreach($categoriasPais as $pai)
                                        <option value="{{ $pai->id }}" data-tipo="{{ $pai->tipo }}" {{ old('categoria_pai_id') == $pai->id ? 'selected' : '' }}>
                                            [{{ $pai->codigo }}] {{ $pai->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoria_pai_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="codigo" class="fc-label">Código da Categoria</label>
                                <input type="text" name="codigo" id="codigo" class="form-control @error('codigo') is-invalid @enderror" value="{{ old('codigo') }}" placeholder="Código sugerido automaticamente">
                                <small class="form-text text-muted">Ex: D.01 ou D.01.001. Deixe em branco para gerar automaticamente.</small>
                                @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="nome" class="fc-label">Nome da categoria</label>
                                <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome') }}" required placeholder="Ex: Combustível, Manutenção, Diárias">
                                @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="descricao" class="fc-label">Descrição</label>
                                <textarea name="descricao" id="descricao" rows="2" class="form-control @error('descricao') is-invalid @enderror" placeholder="Descrição opcional">{{ old('descricao') }}</textarea>
                                @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group mb-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="permite_lancamento" name="permite_lancamento" value="1" checked>
                                    <label class="custom-control-label font-weight-bold text-secondary" for="permite_lancamento">Permite lançamentos diretos</label>
                                </div>
                                <small class="form-text text-muted">Se desativado, servirá apenas como grupo agrupador de outras categorias.</small>
                            </div>

                            <button type="submit" class="btn btn-primary fc-btn w-100">
                                <i class="fas fa-save"></i> Salvar categoria
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <form id="editCategoryForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-primary text-white py-3">
                        <h5 class="modal-title font-weight-bold" id="editCategoryModalLabel"><i class="fas fa-edit mr-2"></i>Editar Categoria</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-warning d-none" id="editBlockedWarning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Como esta categoria possui lançamentos associados, o Tipo, a Categoria Superior e o Código não podem ser editados.
                        </div>

                        <div class="form-group mb-3">
                            <label for="edit_tipo" class="fc-label">Tipo</label>
                            <select name="tipo" id="edit_tipo" class="form-control" required>
                                <option value="receita">Receita (entrada)</option>
                                <option value="despesa">Despesa (saída)</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="edit_categoria_pai_id" class="fc-label">Categoria Superior (Pai)</label>
                            <select name="categoria_pai_id" id="edit_categoria_pai_id" class="form-control">
                                <option value="">Nenhuma (Categoria de nível superior)</option>
                                @foreach($categoriasPais as $pai)
                                    <option value="{{ $pai->id }}" data-tipo="{{ $pai->tipo }}">
                                        [{{ $pai->codigo }}] {{ $pai->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="edit_codigo" class="fc-label">Código da Categoria</label>
                            <input type="text" name="codigo" id="edit_codigo" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="edit_nome" class="fc-label">Nome da categoria</label>
                            <input type="text" name="nome" id="edit_nome" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="edit_descricao" class="fc-label">Descrição</label>
                            <textarea name="descricao" id="edit_descricao" rows="2" class="form-control"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="edit_status" class="fc-label">Status</label>
                                <select name="status" id="edit_status" class="form-control">
                                    <option value="ativo">Ativo</option>
                                    <option value="inativo">Inativo</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group mb-3 d-flex align-items-center pt-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="edit_permite_lancamento" name="permite_lancamento" value="1">
                                    <label class="custom-control-label font-weight-bold text-secondary" for="edit_permite_lancamento">Permite lançamentos</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-2"></i>Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('financeiro.fluxo_caixa._styles')
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Create Form Logic ---
        const createTipo = document.getElementById('tipo');
        const createPai = document.getElementById('categoria_pai_id');
        const createCodigo = document.getElementById('codigo');
        const parentOptions = Array.from(createPai.options);

        function updateParentCategories() {
            const selectedTipo = createTipo.value;
            createPai.innerHTML = '<option value="">Nenhuma (Categoria de nível superior)</option>';
            
            parentOptions.forEach(opt => {
                if (!opt.value) return;
                const optTipo = opt.getAttribute('data-tipo');
                if (optTipo === selectedTipo) {
                    createPai.appendChild(opt);
                }
            });
        }

        function fetchSuggestedCode() {
            const tipo = createTipo.value;
            const paiId = createPai.value;

            if (!tipo) {
                createCodigo.value = '';
                createCodigo.placeholder = 'Selecione o tipo primeiro';
                return;
            }

            createCodigo.placeholder = 'Carregando código sugerido...';

            fetch(`{{ route('financeiro.fluxo-caixa.categorias.sugerir-codigo') }}?tipo=${tipo}&categoria_pai_id=${paiId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.codigo) {
                        createCodigo.value = data.codigo;
                        createCodigo.placeholder = 'Código sugerido automaticamente';
                    }
                })
                .catch(err => {
                    console.error('Erro ao buscar código sugerido:', err);
                    createCodigo.placeholder = 'Falha ao carregar código';
                });
        }

        createTipo.addEventListener('change', function () {
            updateParentCategories();
            fetchSuggestedCode();
        });

        createPai.addEventListener('change', fetchSuggestedCode);

        // --- Edit Modal Logic ---
        const editModal = $('#editCategoryModal');
        const editForm = document.getElementById('editCategoryForm');
        const editTipo = document.getElementById('edit_tipo');
        const editPai = document.getElementById('edit_categoria_pai_id');
        const editCodigo = document.getElementById('edit_codigo');
        const editNome = document.getElementById('edit_nome');
        const editDescricao = document.getElementById('edit_descricao');
        const editStatus = document.getElementById('edit_status');
        const editPermite = document.getElementById('edit_permite_lancamento');
        const editBlockedWarning = document.getElementById('editBlockedWarning');

        const editParentOptions = Array.from(editPai.options);

        function filterEditParents(selectedTipo, excludeId) {
            editPai.innerHTML = '<option value="">Nenhuma (Categoria de nível superior)</option>';
            editParentOptions.forEach(opt => {
                if (!opt.value) return;
                // Don't show the category itself as a parent
                if (opt.value === String(excludeId)) return;
                
                const optTipo = opt.getAttribute('data-tipo');
                if (optTipo === selectedTipo) {
                    editPai.appendChild(opt);
                }
            });
        }

        $('.edit-category-btn').on('click', function () {
            const btn = $(this);
            const id = btn.data('id');
            const nome = btn.data('nome');
            const codigo = btn.data('codigo');
            const tipo = btn.data('tipo');
            const parentId = btn.data('categoria_pai_id');
            const descricao = btn.data('descricao');
            const permite = btn.data('permite_lancamento') === 1;
            const status = btn.data('status');
            const hasLancamentos = btn.data('has_lancamentos') === 1;

            // Set Form action
            editForm.action = `{{ route('financeiro.fluxo-caixa.categorias.index') }}/${id}`;

            // Set simple fields
            editNome.value = nome;
            editDescricao.value = descricao || '';
            editStatus.value = status;
            editPermite.checked = permite;

            // Filter parent category options
            filterEditParents(tipo, id);

            // Set select values
            editTipo.value = tipo;
            editPai.value = parentId || '';
            editCodigo.value = codigo || '';

            // Handle block warning and disabled fields
            if (hasLancamentos) {
                editBlockedWarning.classList.remove('d-none');
                editTipo.disabled = true;
                editPai.disabled = true;
                editCodigo.disabled = true;
            } else {
                editBlockedWarning.classList.add('d-none');
                editTipo.disabled = false;
                editPai.disabled = false;
                editCodigo.disabled = false;
            }

            editModal.modal('show');
        });

        // Handle edit form submit when disabled fields are used (Laravel needs values so we enable them just before submit if disabled)
        editForm.addEventListener('submit', function () {
            editTipo.disabled = false;
            editPai.disabled = false;
            editCodigo.disabled = false;
        });

        // Fetch suggested code for edit form if changed and not blocked
        editTipo.addEventListener('change', function () {
            const hasLanc = editBlockedWarning.classList.contains('d-none') === false;
            if (!hasLanc) {
                const id = editForm.action.split('/').pop();
                filterEditParents(editTipo.value, id);
                
                fetch(`{{ route('financeiro.fluxo-caixa.categorias.sugerir-codigo') }}?tipo=${editTipo.value}&categoria_pai_id=${editPai.value}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.codigo) {
                            editCodigo.value = data.codigo;
                        }
                    });
            }
        });

        editPai.addEventListener('change', function () {
            const hasLanc = editBlockedWarning.classList.contains('d-none') === false;
            if (!hasLanc) {
                fetch(`{{ route('financeiro.fluxo-caixa.categorias.sugerir-codigo') }}?tipo=${editTipo.value}&categoria_pai_id=${editPai.value}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.codigo) {
                            editCodigo.value = data.codigo;
                        }
                    });
            }
        });

        // Initialize create parent options filtering
        updateParentCategories();
    });
</script>
@stop
