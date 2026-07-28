@php
    $isEdit = isset($lancamento);
    $selectedTipo = old('tipo', $lancamento->tipo ?? '');
    $selectedCategoria = old('categoria_financeira_id', $lancamento->categoria_financeira_id ?? '');
    $selectedVeiculo = old('veiculo_id', $lancamento->veiculo_id ?? '');
    $selectedReserva = old('reserva_id', $lancamento->reserva_id ?? '');
@endphp

<div class="fc-page">
    <div class="card fc-card fc-form-card">
        <div class="card-header py-3">
            <div class="fc-head">
                <h4 class="fc-title">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span style='font-size:1.5rem'>{{ $isEdit ? 'Editar lançamento' : 'Novo lançamento' }}<span class="fc-subtitle">Dados financeiros, vencimento, status e vínculo operacional</span></span>
                </h4>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ $action }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="tipo" class="fc-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                            <option value="" disabled {{ $selectedTipo === '' ? 'selected' : '' }}>Selecione o tipo</option>
                            <option value="receita" {{ $selectedTipo === 'receita' ? 'selected' : '' }}>Receita (entrada)</option>
                            <option value="despesa" {{ $selectedTipo === 'despesa' ? 'selected' : '' }}>Despesa (saída)</option>
                        </select>
                        @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 form-group mb-3">
                        <label for="categoria_financeira_id" class="fc-label">Categoria</label>
                        <select name="categoria_financeira_id" id="categoria_financeira_id" class="form-control @error('categoria_financeira_id') is-invalid @enderror" required>
                            <option value="" disabled {{ $selectedCategoria === '' ? 'selected' : '' }}>Selecione a categoria</option>
                            @foreach($categorias as $cat)
                                @if($cat->status === 'ativo')
                                    <option value="{{ $cat->id }}" data-tipo="{{ $cat->tipo }}" {{ !$cat->permite_lancamento ? 'disabled' : '' }} {{ (string) $selectedCategoria === (string) $cat->id ? 'selected' : '' }}>
                                        {{ $cat->codigo }} — {{ $cat->categoriaPai ? $cat->categoriaPai->nome . ' > ' : '' }}{{ $cat->nome }}{{ !$cat->permite_lancamento ? ' (Grupo)' : '' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('categoria_financeira_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="descricao" class="fc-label">Descrição / histórico</label>
                    <input type="text" name="descricao" id="descricao" class="form-control @error('descricao') is-invalid @enderror" value="{{ old('descricao', $lancamento->descricao ?? '') }}" required placeholder="Ex: mensalidade, manutenção, recebimento de reserva">
                    @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="valor" class="fc-label">Valor</label>
                        <input type="number" step="0.01" name="valor" id="valor" class="form-control @error('valor') is-invalid @enderror" value="{{ old('valor', $lancamento->valor ?? '') }}" required placeholder="0,00">
                        @error('valor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 form-group mb-3">
                        <label for="metodo_pagamento" class="fc-label">Método de pagamento</label>
                        <select name="metodo_pagamento" id="metodo_pagamento" class="form-control @error('metodo_pagamento') is-invalid @enderror">
                            @php($metodoAtual = old('metodo_pagamento', $lancamento->metodo_pagamento ?? ''))
                            <option value="" {{ $metodoAtual === '' ? 'selected' : '' }}>Não definido</option>
                            <option value="pix" {{ $metodoAtual === 'pix' ? 'selected' : '' }}>Pix</option>
                            <option value="dinheiro" {{ $metodoAtual === 'dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                            <option value="cartao_credito" {{ $metodoAtual === 'cartao_credito' ? 'selected' : '' }}>Cartão de crédito</option>
                            <option value="cartao_debito" {{ $metodoAtual === 'cartao_debito' ? 'selected' : '' }}>Cartão de débito</option>
                            <option value="boleto" {{ $metodoAtual === 'boleto' ? 'selected' : '' }}>Boleto</option>
                            <option value="transferencia" {{ $metodoAtual === 'transferencia' ? 'selected' : '' }}>Transferência bancária</option>
                        </select>
                        @error('metodo_pagamento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="data_vencimento" class="fc-label">Data de vencimento</label>
                        <input type="date" name="data_vencimento" id="data_vencimento" class="form-control @error('data_vencimento') is-invalid @enderror" value="{{ old('data_vencimento', isset($lancamento) && $lancamento->data_vencimento ? $lancamento->data_vencimento->format('Y-m-d') : date('Y-m-d')) }}" required>
                        @error('data_vencimento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 form-group mb-3">
                        <label for="data_lancamento" class="fc-label">Data do lançamento</label>
                        <input type="datetime-local" name="data_lancamento" id="data_lancamento" class="form-control @error('data_lancamento') is-invalid @enderror" value="{{ old('data_lancamento', isset($lancamento) && $lancamento->data_lancamento ? $lancamento->data_lancamento->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                        @error('data_lancamento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="data_pagamento" class="fc-label">Data de pagamento</label>
                        <input type="date" name="data_pagamento" id="data_pagamento" class="form-control @error('data_pagamento') is-invalid @enderror" value="{{ old('data_pagamento', isset($lancamento) && $lancamento->data_pagamento ? $lancamento->data_pagamento->format('Y-m-d') : '') }}">
                        @error('data_pagamento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label for="status" class="fc-label">Status</label>
                        @php($statusAtual = old('status', $lancamento->status ?? 'pendente'))
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="pendente" {{ $statusAtual === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="pago" {{ $statusAtual === 'pago' ? 'selected' : '' }}>Pago</option>
                            <option value="cancelado" {{ $statusAtual === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 form-group mb-3" id="veiculo_container">
                        <label for="veiculo_id" class="fc-label">Vincular a veículo</label>
                        <select name="veiculo_id" id="veiculo_id" class="form-control @error('veiculo_id') is-invalid @enderror">
                            <option value="">Nenhum veículo</option>
                            @foreach($veiculos as $v)
                                <option value="{{ $v->id }}" {{ (string) $selectedVeiculo === (string) $v->id ? 'selected' : '' }}>
                                    {{ $v->modelo->nome ?? '' }} - {{ $v->placa_licenca }}
                                </option>
                            @endforeach
                        </select>
                        @error('veiculo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 form-group mb-3" id="reserva_container">
                        <label for="reserva_id" class="fc-label">Vincular a reserva</label>
                        <select name="reserva_id" id="reserva_id" class="form-control @error('reserva_id') is-invalid @enderror">
                            <option value="">Nenhuma reserva</option>
                            @foreach($reservas as $res)
                                @php($codigoReserva = $res->codigoReserva?->codigo ?? $res->id)
                                <option value="{{ $res->id }}" {{ (string) $selectedReserva === (string) $res->id ? 'selected' : '' }}>
                                    Reserva #{{ $codigoReserva }} - {{ $res->user->nome ?? 'Cliente' }}
                                </option>
                            @endforeach
                        </select>
                        @error('reserva_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                @if(!$isEdit)
                    <div class="card p-3 mb-4 bg-light border-0" style="border-radius: 8px;">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="recorrente" name="recorrente" value="1" {{ old('recorrente') ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold text-secondary" for="recorrente">Este é um lançamento recorrente / parcelado</label>
                        </div>
                        
                        <div class="row d-none" id="recorrencia_campos">
                            <div class="col-md-6 form-group">
                                <label for="recorrencia" class="fc-label">Frequência</label>
                                <select name="recorrencia" id="recorrencia" class="form-control form-control-sm">
                                    <option value="mensal" {{ old('recorrencia') === 'mensal' ? 'selected' : '' }}>Mensal</option>
                                    <option value="semanal" {{ old('recorrencia') === 'semanal' ? 'selected' : '' }}>Semanal</option>
                                    <option value="anual" {{ old('recorrencia') === 'anual' ? 'selected' : '' }}>Anual</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="parcelas_totais" class="fc-label">Número de Parcelas / Ocorrências</label>
                                <input type="number" name="parcelas_totais" id="parcelas_totais" class="form-control form-control-sm" min="2" max="120" value="{{ old('parcelas_totais', 12) }}">
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const chk = document.getElementById('recorrente');
                            const div = document.getElementById('recorrencia_campos');
                            if (chk && div) {
                                const toggle = () => {
                                    if (chk.checked) {
                                        div.classList.remove('d-none');
                                    } else {
                                        div.classList.add('d-none');
                                    }
                                };
                                chk.addEventListener('change', toggle);
                                toggle(); // init
                            }
                        });
                    </script>
                @endif

                <div class="form-group mb-4">
                    <label for="observacoes" class="fc-label">Observações</label>
                    <textarea name="observacoes" id="observacoes" rows="3" class="form-control @error('observacoes') is-invalid @enderror" placeholder="Informações adicionais">{{ old('observacoes', $lancamento->observacoes ?? '') }}</textarea>
                    @error('observacoes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="fc-actions">
                    <a href="{{ route('financeiro.fluxo-caixa.index') }}" class="btn btn-outline-secondary fc-btn px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary fc-btn px-4">
                        <i class="fas fa-save"></i> {{ $isEdit ? 'Salvar alterações' : 'Salvar lançamento' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoSelect = document.getElementById('tipo');
        const categoriaSelect = document.getElementById('categoria_financeira_id');
        const veiculoContainer = document.getElementById('veiculo_container');
        const reservaContainer = document.getElementById('reserva_container');
        const categoriesOptions = Array.from(categoriaSelect.options);
        const selectedCategoria = @json((string) $selectedCategoria);

        function updateFormLayout(resetCategoria) {
            const tipoVal = tipoSelect.value;
            categoriaSelect.innerHTML = '<option value="" disabled>Selecione a categoria</option>';

            categoriesOptions.forEach(opt => {
                if (!opt.value || opt.getAttribute('data-tipo') === tipoVal) {
                    if (!resetCategoria && opt.value === selectedCategoria) opt.selected = true;
                    categoriaSelect.appendChild(opt);
                }
            });

            veiculoContainer.style.display = tipoVal === 'despesa' ? 'block' : 'none';
            reservaContainer.style.display = tipoVal === 'receita' ? 'block' : 'none';

            if (tipoVal === 'despesa') document.getElementById('reserva_id').value = '';
            if (tipoVal === 'receita') document.getElementById('veiculo_id').value = '';
        }

        tipoSelect.addEventListener('change', function () {
            categoriaSelect.value = '';
            updateFormLayout(true);
        });

        updateFormLayout(false);
    });
</script>

@include('financeiro.fluxo_caixa._styles')
