@extends('adminlte::page')

@section('title', 'Atribuir Plano')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="card p-2">
        <div class="card-header px-0 pt-0 pb-1">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                <h3 class="card-title text-sm text-bold mb-0">
                    ATRIBUIR PLANO A EMPRESA
                </h3>
            </div>
        </div>

        <div class="card-body px-0 pt-0 pb-1">
            <div class="row">
                <div class="col-lg-10 col-xl-8">
                    <div class="card card-outline card-light mb-0">
                        <div class="card-body">
                            <form id="formAtribuir" action="{{ route('assinatura.atribuir.processar') }}" method="POST">
                                @csrf

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="mb-1">Empresa <span class="text-danger">*</span></label>
                                        <select name="empresa_id" id="empresa_id" class="form-control form-control-sm" required>
                                            <option value="">Selecione a empresa...</option>
                                            @foreach ($empresas as $emp)
                                                @php
                                                    $assinaturaAtual = $emp->planoAtivo;
                                                    $planoAtualNome = $assinaturaAtual?->plano?->nome;
                                                    $vinculada = (bool) $assinaturaAtual;
                                                @endphp
                                                <option
                                                    value="{{ $emp->id }}"
                                                    data-vinculada="{{ $vinculada ? '1' : '0' }}"
                                                    data-plano-atual="{{ $planoAtualNome ?? '' }}"
                                                    {{ request('empresa_id') == $emp->id ? 'selected' : '' }}
                                                >
                                                    {{ $emp->nome }}{{ $vinculada ? ' - Vinculada: ' . $planoAtualNome : ' - Sem plano' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted d-block mt-1">
                                            Empresas com plano ativo aparecem sinalizadas. O sistema impede uma segunda vinculação enquanto a atual estiver ativa.
                                        </small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="mb-1">Plano <span class="text-danger">*</span></label>
                                        <select name="plano_id" id="plano_id" class="form-control form-control-sm" required>
                                            <option value="">Selecione o plano...</option>
                                            @foreach ($planos as $p)
                                                <option value="{{ $p->id }}" data-mensal="{{ $p->valor_mensal }}" data-anual="{{ $p->valor_anual }}" data-storage="{{ $p->limite_armazenamento_mb ?? 0 }}">
                                                    {{ $p->nome }} - R$ {{ number_format($p->valor_mensal, 2, ',', '.') }}/mês
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="mb-1">Periodicidade <span class="text-danger">*</span></label>
                                        <select name="periodicidade" id="periodicidade" class="form-control form-control-sm" required>
                                            <option value="mensal">Mensal</option>
                                            <option value="anual">Anual (com desconto)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="mb-1">Valor a cobrar (R$) <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">R$</span>
                                            </div>
                                            <input type="text" id="valorExibido" class="form-control" readonly required>
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="mb-1">Data de Vencimento</label>
                                        <input type="date" name="data_vencimento" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+1 month')) }}">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="mb-1">Dia de Vencimento</label>
                                        <input type="number" name="dia_vencimento" class="form-control form-control-sm" min="1" max="28" placeholder="Ex.: 10">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="mb-1">Data de Início <span class="text-danger">*</span></label>
                                        <input type="date" name="data_inicio" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="mb-1">Armazenamento do Plano</label>
                                        <input type="text" class="form-control form-control-sm" id="planoStorage" readonly placeholder="Selecione um plano...">
                                    </div>

                                    <div class="col-12 mb-2">
                                        <label class="mb-1">Observação</label>
                                        <textarea name="observacao" class="form-control form-control-sm" rows="2" placeholder="Observação interna sobre esta atribuição..."></textarea>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap" style="gap:8px;">
                                    <button type="submit" id="btnSubmit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-check mr-1"></i> <span id="btnText">Lançar assinatura</span>
                                    </button>
                                    <a href="{{ route('assinatura.index') }}" class="btn btn-secondary btn-sm">Cancelar</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
    <script>
        (function() {
            const form = document.getElementById('formAtribuir');
            const btnSubmit = document.getElementById('btnSubmit');
            const btnText = document.getElementById('btnText');
            const empresaSelect = $('#empresa_id');

            function formatEmpresa(option) {
                if (!option.id) {
                    return option.text;
                }

                const vinculada = option.element?.dataset?.vinculada === '1';
                const planoAtual = option.element?.dataset?.planoAtual || '';
                const $option = $(
                    '<span style="display:flex;justify-content:space-between;gap:10px;align-items:center;width:100%;">' +
                        '<span>' + option.text + '</span>' +
                        (vinculada
                            ? '<span class="badge badge-warning">Vinculada' + (planoAtual ? ': ' + planoAtual : '') + '</span>'
                            : '<span class="badge badge-success">Livre</span>') +
                    '</span>'
                );

                return $option;
            }

            empresaSelect.select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Selecione a empresa...',
                allowClear: true,
                templateResult: formatEmpresa,
                templateSelection: formatEmpresa,
            });

            function atualizarPreview() {
                const plano = document.getElementById('plano_id').selectedOptions[0];
                const periodicidade = document.getElementById('periodicidade').value;
                const valorEl = document.getElementById('valorExibido');
                const storageEl = document.getElementById('planoStorage');

                if (plano && plano.value) {
                    const valor = periodicidade === 'anual'
                        ? parseFloat(plano.dataset.anual || 0)
                        : parseFloat(plano.dataset.mensal || 0);
                    valorEl.value = valor.toFixed(2).replace('.', ',');
                    const mb = parseInt(plano.dataset.storage || 0, 10);
                    storageEl.value = mb >= 1024 ? (mb / 1024).toFixed(1) + ' GB' : mb + ' MB';
                } else {
                    valorEl.value = '';
                    storageEl.value = '';
                }
            }

            document.getElementById('plano_id').addEventListener('change', atualizarPreview);
            document.getElementById('periodicidade').addEventListener('change', atualizarPreview);
            atualizarPreview();

            form.addEventListener('submit', function() {
                btnSubmit.disabled = true;
                btnText.textContent = 'Lançando...';
            });
        })();
    </script>
@stop
