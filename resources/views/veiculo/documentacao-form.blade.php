@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[
        ['name' => 'Documentacao', 'url' => route('veiculo.documentacao')],
        ['name' => isset($documentacao) ? 'Editar' : 'Criar']
    ]" />
@stop

@section('content')
    @php
        $veiculoSelecionadoNome = $veiculoSelecionado?->nome ?? '';
        $veiculoSelecionadoPlaca = $veiculoSelecionado?->placa_licenca ?? '';
        $veiculoBloqueado = !empty($veiculoId);
        $formAction = isset($documentacao)
            ? route('veiculo.documentacao.update', $documentacao)
            : route('veiculo.documentacao.store');
    @endphp

    <div class="col-xl-11 px-0">
        <div class="card card-opcional shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ $formAction }}" id="documentacao-form">
                    @csrf
                    @if(isset($documentacao))
                        @method('PUT')
                    @endif

                    <div class="form-row align-items-end">
                        <div class="col-md-5 form-group">
                            <label>Veiculo <span class="text-danger">*</span> 
                                @if($veiculoBloqueado)
                                <small class="text-muted">Veiculo definido pela URL e bloqueado para edicao manual.</small>
                            @else
                                <small class="text-muted">Clique na lupa para pesquisar o veiculo.</small>
                            @endif</label>
                            <div class="input-group">
                                <input type="hidden" name="veiculo_id" id="veiculo_id" value="{{ old('veiculo_id', $veiculoId ?? '') }}" required>
                                <input
                                    type="text"
                                    id="veiculo_display"
                                    class="form-control"
                                    value="{{ old('veiculo_display', $veiculoSelecionadoNome ? ($veiculoSelecionadoNome . ' - ' . $veiculoSelecionadoPlaca) : '') }}"
                                    placeholder="Selecione um veiculo"
                                    readonly
                                >
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="btnBuscarVeiculoDocumento" title="Buscar veiculo" {{ $veiculoBloqueado ? 'disabled' : '' }}>
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        
                        </div>

                        <div class="col-md-3 form-group">
                            <label>Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-control" required>
                                <option value="">Selecione</option>
                                <option value="ipva" {{ old('tipo', $documentacao->tipo ?? '') === 'ipva' ? 'selected' : '' }}>IPVA</option>
                                <option value="licenciamento" {{ old('tipo', $documentacao->tipo ?? '') === 'licenciamento' ? 'selected' : '' }}>Licenciamento</option>
                                <option value="seguro" {{ old('tipo', $documentacao->tipo ?? '') === 'seguro' ? 'selected' : '' }}>Seguro</option>
                            </select>
                        </div>

                        <div class="col-md-4 form-group">
                            <label>Descrição</label>
                            <input type="text" name="descricao" class="form-control" value="{{ old('descricao', $documentacao->descricao ?? '') }}" placeholder="Opcional">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-3 form-group">
                            <label>Data do primeiro vencimento <span class="text-danger">*</span></label>
                            <input type="date" name="data_primeiro_vencimento" class="form-control" id="data_primeiro_vencimento" value="{{ old('data_primeiro_vencimento') }}" required>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Parcelas <span class="text-danger">*</span></label>
                            <input type="number" name="parcelas_total" class="form-control" id="parcelas_total" min="1" value="{{ old('parcelas_total', $documentacao->parcelas_total ?? 1) }}" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Valor por parcela <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="valor_parcela" class="form-control" id="valor_parcela" min="0" value="{{ old('valor_parcela', $documentacao->valor_parcela ?? '') }}" required>
                        </div>
                        <div class="col-md-2 form-group d-flex align-items-end">
                            <button type="button" class="btn btn-outline-primary btn-block" id="gerar_parcelas">
                                <i class="fas fa-sync-alt mr-1"></i>Gerar parcelas
                            </button>
                        </div>
                        <div class="col-md-2 form-group d-flex align-items-end">
                            <button class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i>Salvar
                            </button>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <strong>Preview das parcelas</strong>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0" id="parcelas_preview">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Vencimento</th>
                                            <th>Valor</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Informe a data inicial e a quantidade de parcelas.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-veiculo.busca-entidade-modal
        modal-id="modalBuscarVeiculoDocumento"
        title="Buscar veiculo"
        buscar-url="{{ route('veiculo.buscar') }}"
        trigger-id="btnBuscarVeiculoDocumento"
        target-select-id="veiculo_id"
        target-display-id="veiculo_display"
        placeholder="Digite nome, placa, chassi, marca ou grupo"
    />

    <script>
        (function () {
            const parcelasInput = document.getElementById('parcelas_total');
            const dataInput = document.getElementById('data_primeiro_vencimento');
            const valorInput = document.getElementById('valor_parcela');
            const preview = document.querySelector('#parcelas_preview tbody');
            const gerarBtn = document.getElementById('gerar_parcelas');

            function toInputDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function addMonths(date, months) {
                const d = new Date(date.getTime());
                const day = d.getDate();
                d.setMonth(d.getMonth() + months);
                if (d.getDate() !== day) {
                    d.setDate(0);
                }
                return d;
            }

            function renderPreview() {
                const total = parseInt(parcelasInput.value || '0', 10);
                const dataBase = dataInput.value ? new Date(dataInput.value + 'T00:00:00') : null;
                const valor = parseFloat(valorInput.value || '0');

                if (!total || !dataBase) {
                    preview.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Informe a data inicial e a quantidade de parcelas.</td></tr>';
                    return;
                }

                const rows = [];
                for (let i = 0; i < total; i++) {
                    const vencimento = addMonths(dataBase, i);
                    rows.push(`
                        <tr>
                            <td>${i + 1}/${total}</td>
                            <td>
                                <input type="date" name="parcelas[${i}][data_vencimento]" class="form-control form-control-sm" value="${toInputDate(vencimento)}">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" value="R$ ${valor.toFixed(2).replace('.', ',')}" readonly>
                            </td>
                            <td>
                                <select name="parcelas[${i}][status]" class="form-control form-control-sm">
                                    <option value="pendente" selected>Pendente</option>
                                    <option value="pago">Pago</option>
                                </select>
                            </td>
                        </tr>
                    `);
                }

                preview.innerHTML = rows.join('');
            }

            gerarBtn.addEventListener('click', renderPreview);
            parcelasInput.addEventListener('input', renderPreview);
            dataInput.addEventListener('change', renderPreview);
            valorInput.addEventListener('input', renderPreview);
            renderPreview();
        })();
    </script>
@stop
