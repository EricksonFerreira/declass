{{-- Partial reutilizado em create e edit --}}
<div class="row">
    <div class="col-md-8">
        {{-- Dados básicos --}}
        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title">Dados do Plano</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nome <span class="text-danger">*</span></label>
                            <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                                   value="{{ old('nome', $plano->nome ?? '') }}" required maxlength="80">
                            @error('nome')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ordem</label>
                            <input type="number" name="ordem" class="form-control"
                                   value="{{ old('ordem', $plano->ordem ?? 0) }}" min="0">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end pb-3">
                        <div class="d-flex flex-column" style="gap:8px;">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="ativo" value="0">
                                <input type="checkbox" class="custom-control-input" id="ativo" name="ativo" value="1"
                                       {{ old('ativo', ($plano->ativo ?? true) ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="ativo">Ativo</label>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="destaque" value="0">
                                <input type="checkbox" class="custom-control-input" id="destaque" name="destaque" value="1"
                                       {{ old('destaque', ($plano->destaque ?? false) ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="destaque">Destaque</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="descricao" class="form-control" rows="2">{{ old('descricao', $plano->descricao ?? '') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Valor Mensal (R$) <span class="text-danger">*</span></label>
                            <input type="number" name="valor_mensal" step="0.01" min="0"
                                   class="form-control @error('valor_mensal') is-invalid @enderror"
                                   value="{{ old('valor_mensal', $plano->valor_mensal ?? '') }}" required>
                            @error('valor_mensal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Valor Anual (R$/mês) <span class="text-danger">*</span></label>
                            <input type="number" name="valor_anual" step="0.01" min="0"
                                   class="form-control @error('valor_anual') is-invalid @enderror"
                                   value="{{ old('valor_anual', $plano->valor_anual ?? '') }}" required>
                            <small class="form-text text-muted">Valor mensal cobrado no plano anual (com desconto).</small>
                            @error('valor_anual')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Limite de Veículos</label>
                            <input type="number" name="limite_veiculos" min="1"
                                   class="form-control @error('limite_veiculos') is-invalid @enderror"
                                   value="{{ old('limite_veiculos', $plano->limite_veiculos ?? '') }}"
                                   placeholder="Vazio = ilimitado">
                            @error('limite_veiculos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Limite de Armazenamento (MB)</label>
                            <input type="number" name="limite_armazenamento_mb" min="1" step="1"
                                   class="form-control @error('limite_armazenamento_mb') is-invalid @enderror"
                                   value="{{ old('limite_armazenamento_mb', $plano->limite_armazenamento_mb ?? '') }}"
                                   placeholder="Ex.: 5120 para 5 GB">
                            @error('limite_armazenamento_mb')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Valor veículo adicional (R$)</label>
                            <input type="number" name="valor_veiculo_adicional" step="0.01" min="0"
                                   class="form-control @error('valor_veiculo_adicional') is-invalid @enderror"
                                   value="{{ old('valor_veiculo_adicional', $plano->valor_veiculo_adicional ?? '') }}"
                                   placeholder="Ex.: 3,00">
                            @error('valor_veiculo_adicional')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Quantidade de filiais</label>
                            <input type="number" name="quantidade_filiais" step="1" min="0"
                                   class="form-control @error('quantidade_filiais') is-invalid @enderror"
                                   value="{{ old('quantidade_filiais', $plano->quantidade_filiais ?? '') }}"
                                   placeholder="Ex.: 1">
                            @error('quantidade_filiais')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Valor filial adicional (R$)</label>
                            <input type="number" name="valor_filial_adicional" step="0.01" min="0"
                                   class="form-control @error('valor_filial_adicional') is-invalid @enderror"
                                   value="{{ old('valor_filial_adicional', $plano->valor_filial_adicional ?? '') }}"
                                   placeholder="Ex.: 29,00">
                            @error('valor_filial_adicional')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Valor armazenamento adicional (R$)</label>
                            <input type="number" name="valor_armazenamento_adicional" step="0.01" min="0"
                                   class="form-control @error('valor_armazenamento_adicional') is-invalid @enderror"
                                   value="{{ old('valor_armazenamento_adicional', $plano->valor_armazenamento_adicional ?? '') }}"
                                   placeholder="Ex.: 5,00">
                            @error('valor_armazenamento_adicional')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Quantidade do armazenamento adicional</label>
                            <input type="number" name="quantidade_armazenamento_adicional" step="1" min="0"
                                   class="form-control @error('quantidade_armazenamento_adicional') is-invalid @enderror"
                                   value="{{ old('quantidade_armazenamento_adicional', $plano->quantidade_armazenamento_adicional ?? '') }}"
                                   placeholder="Ex.: 5">
                            @error('quantidade_armazenamento_adicional')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    @foreach ([
                        ['site_vendas',   'globe',        'Site de Vendas Online'],
                        ['modulo_multas', 'file-invoice', 'Módulo de Multas'],
                        ['modulo_nfse',   'receipt',      'NFS-e Automática'],
                        ['multi_estacao', 'map-marker',   'Multi-Estação / Filiais'],
                        ['api_integracao','plug',         'API de Integração'],
                    ] as [$campo, $icon, $label])
                        <div class="col-md-4 mb-2">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="{{ $campo }}" value="0">
                                <input type="checkbox" class="custom-control-input" id="{{ $campo }}"
                                       name="{{ $campo }}" value="1"
                                       {{ old($campo, ($plano->$campo ?? false) ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="{{ $campo }}">
                                    <i class="fas fa-{{ $icon }} mr-1 text-muted"></i> {{ $label }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Itens do plano --}}
        <div class="card card-outline card-secondary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Itens do Plano</h3>
                <button type="button" class="btn btn-xs btn-outline-primary" id="addItem">
                    <i class="fas fa-plus mr-1"></i> Adicionar
                </button>
            </div>
            <div class="card-body p-2" id="itensContainer">
                @php $itensExistentes = old('itens', isset($plano) ? $plano->itens->toArray() : []); @endphp
                @foreach ($itensExistentes as $i => $item)
                    @php
                        $itemValor = old("itens.$i.valor", $item['valor'] ?? '');
                    @endphp
                    <div class="item-row mb-2" data-index="{{ $i }}">
                        <input type="hidden" name="itens[{{ $i }}][id]" value="{{ $item['id'] ?? '' }}">
                        <div class="d-flex align-items-start" style="gap:8px;">
                            <div class="pt-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="itens[{{ $i }}][incluso]" value="1"
                                           class="custom-control-input incluso-check"
                                           id="item_incluso_{{ $i }}"
                                           {{ (bool)($item['incluso'] ?? true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="item_incluso_{{ $i }}" title="Incluso"></label>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <input type="text" name="itens[{{ $i }}][descricao]"
                                       class="form-control form-control-sm mb-1"
                                       value="{{ $item['descricao'] ?? '' }}"
                                       placeholder="Nome do item" required>
                                <input type="number" name="itens[{{ $i }}][valor]"
                                       class="form-control form-control-sm"
                                       value="{{ $itemValor }}"
                                       placeholder="Preço (R$)"
                                       step="0.01"
                                       min="0">
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-item mt-1" title="Remover">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="card-footer py-1">
                <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Informe o nome e o preço do item. Marque o checkbox para indicar que ele está incluso no plano.</small>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
let itemIndex = {{ count($itensExistentes ?? []) }};

document.getElementById('addItem').addEventListener('click', function () {
    const container = document.getElementById('itensContainer');
    const div = document.createElement('div');
    div.className = 'item-row mb-2';
    div.dataset.index = itemIndex;
    div.innerHTML = `
        <input type="hidden" name="itens[${itemIndex}][id]" value="">
        <div class="d-flex align-items-start" style="gap:8px;">
            <div class="pt-2">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="itens[${itemIndex}][incluso]" value="1" checked
                           class="custom-control-input incluso-check" id="item_incluso_${itemIndex}">
                    <label class="custom-control-label" for="item_incluso_${itemIndex}" title="Incluso"></label>
                </div>
            </div>
            <div class="flex-grow-1">
                <input type="text" name="itens[${itemIndex}][descricao]"
                       class="form-control form-control-sm mb-1" placeholder="Nome do item" required>
                <input type="number" name="itens[${itemIndex}][valor]"
                       class="form-control form-control-sm" placeholder="Preço (R$)" step="0.01" min="0">
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-item mt-1" title="Remover">
                <i class="fas fa-times"></i>
            </button>
        </div>
        `;
    container.appendChild(div);
    div.querySelector('input[type=text]').focus();
    itemIndex++;
});

document.getElementById('itensContainer').addEventListener('click', function (e) {
    const btn = e.target.closest('.remove-item');
    if (btn) {
        btn.closest('.item-row').remove();
        reindexarItens();
    }
});

function reindexarItens() {
    document.querySelectorAll('#itensContainer .item-row').forEach((row, i) => {
        row.dataset.index = i;
        row.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/itens\[\d+\]/, `itens[${i}]`);
        });
    });
    itemIndex = document.querySelectorAll('#itensContainer .item-row').length;
}
</script>
@endpush
