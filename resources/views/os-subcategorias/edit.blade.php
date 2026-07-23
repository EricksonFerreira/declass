@extends('adminlte::page')

@section('title', 'Editar Subcategoria de OS')

@section('content_header')
    <x-breadcrumb :breadcrumbs="[['name' => 'Subcategorias de OS', 'url' => route('os-subcategorias.index')], ['name' => 'Editar']]" />
@stop

@section('content')
    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <div class="card card-opcional shadow-sm border-0">
            <div class="card-header card-header-opcional border-0">
                <div>
                    <h4 class="mb-1">Editar Subcategoria de OS</h4>
                    <p class="mb-0 text-muted">Configure os dados da subcategoria de OS.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('os-subcategorias.update', $osSubcategoria->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="opcional-section-title">Informações Básicas</div>
                    <div class="form-row">
                        <div class="col-lg-6">
                            <div class="form-group form-group-opcional">
                                <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                    label="Nome" value="{{ old('nome', $osSubcategoria->nome) }}" required />
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group form-group-opcional">
                                <x-form-select name="os_categoria_id" label="Categoria" :options="$categorias->pluck('nome', 'id')"
                                    placeholder="Selecione..." :selected="old('os_categoria_id', $osSubcategoria->os_categoria_id)" class="form-control form-control-sm" required />
                            </div>
                        </div>
                    </div>

                    <div class="opcional-section-title mt-4">Configurações de Exibição</div>
                    <div class="form-row">
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-opcional">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="show_sinistro" name="show_sinistro" value="1" {{ old('show_sinistro', $osSubcategoria->show_sinistro) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="show_sinistro">Exibe Sinistro</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-opcional">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="show_reserva" name="show_reserva" value="1" {{ old('show_reserva', $osSubcategoria->show_reserva) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="show_reserva">Exibe Reserva</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-opcional">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="show_avarias" name="show_avarias" value="1" {{ old('show_avarias', $osSubcategoria->show_avarias) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="show_avarias">Exibe Avarias</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-opcional">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="show_checklist_prevencao" name="show_checklist_prevencao" value="1" {{ old('show_checklist_prevencao', $osSubcategoria->show_checklist_prevencao) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="show_checklist_prevencao">Exibe Checklist Prev.</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mt-3">
                            <div class="form-group form-group-opcional">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $osSubcategoria->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Ativo</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="opcional-section-title mt-4">Campos Dinâmicos</div>
                    <div id="dynamic-fields-container">
                        @foreach($osSubcategoria->campos as $index => $campo)
                            <div class="card bg-light mb-3 dynamic-field-card" data-index="{{ $index }}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="mb-0 font-weight-bold">Campo Existente</h6>
                                        <button type="button" class="btn btn-sm btn-danger btn-remove-field"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="small">Nome do Campo</label>
                                            <input type="text" name="campos[{{ $index }}][nome]" class="form-control form-control-sm" value="{{ $campo->nome }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small">Tipo</label>
                                            <select name="campos[{{ $index }}][tipo]" class="form-control form-control-sm tipo-select" required>
                                                <option value="text" {{ $campo->tipo === 'text' ? 'selected' : '' }}>Texto</option>
                                                <option value="number" {{ $campo->tipo === 'number' ? 'selected' : '' }}>Número</option>
                                                <option value="date" {{ $campo->tipo === 'date' ? 'selected' : '' }}>Data</option>
                                                <option value="select" {{ $campo->tipo === 'select' ? 'selected' : '' }}>Seleção</option>
                                                <option value="checkbox" {{ $campo->tipo === 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-0 mt-4 pt-2">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="is_required_{{ $index }}" name="campos[{{ $index }}][is_required]" value="1" {{ $campo->is_required ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="is_required_{{ $index }}">Obrigatorio</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small">Ordem</label>
                                            <input type="number" name="campos[{{ $index }}][ordem]" class="form-control form-control-sm" value="{{ $campo->ordem ?? 0 }}">
                                        </div>
                                    </div>
                                    <div class="row mt-2 opcoes-container" style="{{ $campo->tipo === 'select' ? 'display:flex;' : 'display:none;' }}">
                                        <div class="col-12">
                                            <label class="small">Opções (separadas por vírgula)</label>
                                            <input type="text" name="campos[{{ $index }}][opcoes]" class="form-control form-control-sm" value="{{ $campo->opcoes ? implode(', ', $campo->opcoes) : '' }}" placeholder="Ex: Opção 1, Opção 2, Opção 3">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mb-4">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-field"><i class="fas fa-plus"></i> Adicionar Campo</button>
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-opcional col-12 mt-4">
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let fieldIndex = {{ $osSubcategoria->campos->count() }};
        const container = document.getElementById('dynamic-fields-container');
        const btnAdd = document.getElementById('btn-add-field');

        btnAdd.addEventListener('click', function() {
            const html = `
                <div class="card bg-light mb-3 dynamic-field-card" data-index="${fieldIndex}">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <h6 class="mb-0 font-weight-bold">Novo Campo</h6>
                            <button type="button" class="btn btn-sm btn-danger btn-remove-field"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="small">Nome do Campo</label>
                                <input type="text" name="campos[${fieldIndex}][nome]" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3">
                                <label class="small">Tipo</label>
                                <select name="campos[${fieldIndex}][tipo]" class="form-control form-control-sm tipo-select" required>
                                    <option value="text">Texto</option>
                                    <option value="number">Número</option>
                                    <option value="date">Data</option>
                                    <option value="select">Seleção</option>
                                    <option value="checkbox">Checkbox</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0 mt-4 pt-2">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_required_${fieldIndex}" name="campos[${fieldIndex}][is_required]" value="1">
                                        <label class="custom-control-label" for="is_required_${fieldIndex}">Obrigatorio</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="small">Ordem</label>
                                <input type="number" name="campos[${fieldIndex}][ordem]" class="form-control form-control-sm" value="0">
                            </div>
                        </div>
                        <div class="row mt-2 opcoes-container" style="display:none;">
                            <div class="col-12">
                                <label class="small">Opções (separadas por vírgula)</label>
                                <input type="text" name="campos[${fieldIndex}][opcoes]" class="form-control form-control-sm" placeholder="Ex: Opção 1, Opção 2, Opção 3">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            fieldIndex++;
        });

        container.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove-field')) {
                e.target.closest('.dynamic-field-card').remove();
            }
        });

        container.addEventListener('change', function(e) {
            if (e.target.classList.contains('tipo-select')) {
                const card = e.target.closest('.dynamic-field-card');
                const opcoesContainer = card.querySelector('.opcoes-container');
                if (e.target.value === 'select') {
                    opcoesContainer.style.display = 'flex';
                } else {
                    opcoesContainer.style.display = 'none';
                    card.querySelector('input[name*="[opcoes]"]').value = '';
                }
            }
        });
    });
</script>
@stop

@section('css')
<style>
    .card-opcional { border-radius: 14px; overflow: hidden; }
    .card-header-opcional { background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%); padding: 1.1rem 1.5rem; }
    .card-header-opcional h4 { color: #0f2740; font-weight: 700; }
    .opcional-section-title { font-size: .96rem; font-weight: 700; color: #21364f; letter-spacing: .02em; padding-bottom: 8px; border-bottom: 1px solid #e5eaf2; margin-bottom: 10px; }
    .form-group-opcional { margin-bottom: 1rem; }
    .card-opcional .form-control, .card-opcional .form-select { border-radius: 10px; border-color: #d8e1ec; min-height: 38px; }
    .card-opcional .form-control:focus, .card-opcional .form-select:focus { border-color: #4b80ff; box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18); }
    .btn-save-opcional { border-radius: 10px; font-weight: 600; padding-top: .58rem; padding-bottom: .58rem; }
</style>
@stop
