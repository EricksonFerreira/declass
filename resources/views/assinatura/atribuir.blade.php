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

    <div class="card card-veiculo shadow-sm border-0">
        <div class="card-header card-header-veiculo border-0">
            <div class="d-flex align-items-start justify-content-between flex-wrap" style="gap: 16px;">
                <div>
                    <h4 class="mb-1">Atribuir assinatura</h4>
                    <p class="mb-0 text-muted">
                        Vincule um plano a uma empresa e mantenha o histórico de assinaturas organizado por período.
                    </p>
                </div>
                <div class="d-flex flex-wrap" style="gap:8px;">
                    <a href="{{ route('assinatura.index') }}" class="btn-xp btn-xp--outline btn-xp--sm">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row">
                <div class="col-xl-9 col-lg-10">
                    <x-campos-obrigatorios-pendentes
                        alert-class="mb-3"
                        summary-class="js-atribuir-resumo"
                        list-class="js-atribuir-lista"
                    />

                    <form id="formAtribuir" action="{{ route('assinatura.atribuir.processar') }}" method="POST">
                        @csrf

                        <div class="card card-outline card-light mb-0">
                            <div class="card-body p-4">
                                <div class="veiculo-section-title">
                                    <i class="fas fa-link mr-1"></i>Dados da assinatura
                                </div>

                                <div class="form-row">
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
                                                    data-veiculos="{{ $emp->qtd_veiculos_ativos ?? 0 }}"
                                                    data-estacoes="{{ $emp->qtd_estacoes ?? 0 }}"
                                                    data-storage="{{ $emp->armazenamento_usado_mb ?? 0 }}"
                                                    {{ request('empresa_id') == $emp->id ? 'selected' : '' }}
                                                >
                                                    {{ $emp->nome }}{{ $vinculada ? ' - Vinculada: ' . $planoAtualNome : ' - Sem plano' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="veiculo-lookup-help">
                                            Escolha a empresa. Se ela já tiver assinatura ativa, você verá a indicação ao lado.
                                        </small>
                                    </div>

                                    <div class="col-lg-6 col-md-6 mb-3">
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

                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <label class="mb-1">Periodicidade <span class="text-danger">*</span></label>
                                        <select name="periodicidade" id="periodicidade" class="form-control form-control-sm" required>
                                            <option value="mensal">Mensal</option>
                                            <option value="anual">Anual (com desconto)</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <label class="mb-1">Dia de vencimento</label>
                                        <input type="number" name="dia_vencimento" class="form-control form-control-sm" min="1" max="28" placeholder="Ex.: 10">
                                    </div>

                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <label class="mb-1">Data de início <span class="text-danger">*</span></label>
                                        <input type="date" name="data_inicio" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <label class="mb-1">Data de vencimento</label>
                                        <input type="date" name="data_vencimento" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+1 month')) }}">
                                    </div>

                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <label class="mb-1">Valor a cobrar (R$) <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">R$</span>
                                            </div>
                                            <input type="text" id="valorExibido" class="form-control" readonly required>
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="mb-1">Armazenamento do plano</label>
                                        <input type="text" class="form-control form-control-sm" id="planoStorage" readonly placeholder="Selecione um plano...">
                                    </div>

                                    <div class="col-12 mb-3">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0" id="tabelaAdicionais">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Adicional</th>
                                                        <th class="text-right" style="width: 140px;">Valor unitário</th>
                                                        <th class="text-center" style="width: 90px;">Qtd.</th>
                                                        <th class="text-right" style="width: 140px;">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Veículo adicional</td>
                                                        <td class="text-right" id="veiculoUnit">R$ 3,00</td>
                                                        <td class="text-center" id="veiculoQtd">0</td>
                                                        <td class="text-right" id="veiculoTotal">R$ 0,00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Filial adicional</td>
                                                        <td class="text-right" id="filialUnit">R$ 29,00</td>
                                                        <td class="text-center" id="filialQtd">0</td>
                                                        <td class="text-right" id="filialTotal">R$ 0,00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Armazenamento adicional</td>
                                                        <td class="text-right" id="storageUnit">R$ 5,00 por 5 GB</td>
                                                        <td class="text-center" id="storageQtd">0</td>
                                                        <td class="text-right" id="storageTotal">R$ 0,00</td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="3" class="text-right">Total geral</th>
                                                        <th class="text-right" id="totalGeral">R$ 0,00</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-2">
                                        <label class="mb-1">Observação</label>
                                        <textarea name="observacao" class="form-control form-control-sm" rows="2" placeholder="Observação interna sobre esta atribuição..."></textarea>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap" style="gap:8px;">
                                    <button type="submit" id="btnSubmit" class="btn-xp btn-xp--sm">
                                        <i class="fas fa-check mr-1"></i> <span id="btnText">Lançar assinatura</span>
                                    </button>
                                    <a href="{{ route('assinatura.index') }}" class="btn-xp btn-xp--outline btn-xp--sm">Cancelar</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-xl-3 col-lg-2 d-none d-xl-block">
                    <div class="card card-outline card-light h-100 mb-0">
                        <div class="card-body">
                            <div class="veiculo-section-title">
                                <i class="fas fa-info-circle mr-1"></i>Resumo
                            </div>
                            <div class="veiculo-info-tip js-atribuir-resumo">
                                Selecione uma empresa e um plano para visualizar o valor e o armazenamento antes de lançar.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-veiculo {
            border-radius: 14px;
            overflow: hidden;
        }

        .card-header-veiculo {
            background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
            padding: 1.1rem 1.5rem;
        }

        .card-header-veiculo h4 {
            color: #0f2740;
            font-weight: 700;
        }

        .veiculo-section-title {
            font-size: .96rem;
            font-weight: 700;
            color: #21364f;
            letter-spacing: .02em;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5eaf2;
            margin-bottom: 14px;
        }

        .veiculo-info-tip {
            border: 1px solid #dbe6f3;
            border-radius: 10px;
            background: #f8fbff;
            color: #334155;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 10px;
            margin-bottom: 10px;
        }

        .veiculo-lookup-help {
            display: block;
            margin-top: .35rem;
            font-size: .75rem;
            color: #6b7280;
        }

        .btn-xp {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .35rem .9rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 700;
            text-decoration: none;
            background: #2563eb;
            color: #fff;
            border: none;
        }

        .btn-xp:hover {
            opacity: .88;
            transform: translateY(-1px);
            color: #fff;
            text-decoration: none;
        }

        .btn-xp--sm {
            padding: 6px 12px;
            font-size: .78rem;
            border-radius: 8px;
        }

        .btn-xp--outline {
            background: #fff;
            color: #2563eb;
            border: 1px solid #cbd5e1;
        }

        .btn-xp--outline:hover {
            background: #eef2ff;
            color: #1d4ed8;
        }

        .form-control-sm {
            border-radius: 10px;
        }

        .select2-container--bootstrap4 .select2-selection {
            border-radius: 10px;
            min-height: 31px;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 30px;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 30px;
        }

        @media (max-width: 991.98px) {
            .card-header-veiculo {
                padding: 1rem 1.1rem;
            }

            .card-header-veiculo h4 {
                font-size: 1.1rem;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
    <script>
        (function() {
            const form = document.getElementById('formAtribuir');
            const btnSubmit = document.getElementById('btnSubmit');
            const btnText = document.getElementById('btnText');
            const empresaSelect = $('#empresa_id');

            empresaSelect.select2({
                theme: 'bootstrap4',
                width: 'resolve',
                placeholder: 'Selecione a empresa...',
                allowClear: true,
                minimumResultsForSearch: 0,
                dropdownAutoWidth: true,
                dropdownParent: $(document.body),
            });

            function atualizarPreview() {
                const empresa = document.getElementById('empresa_id').selectedOptions[0];
                const plano = document.getElementById('plano_id').selectedOptions[0];
                const periodicidade = document.getElementById('periodicidade').value;
                const valorEl = document.getElementById('valorExibido');
                const storageEl = document.getElementById('planoStorage');
                const veiculoUnit = 3;
                const filialUnit = 29;
                const storageUnit = 5;
                const storageBlockGb = 5;

                const empresaVeiculos = empresa ? parseInt(empresa.dataset.veiculos || 0, 10) : 0;
                const empresaEstacoes = empresa ? parseInt(empresa.dataset.estacoes || 0, 10) : 0;
                const empresaStorage = empresa ? parseFloat(empresa.dataset.storage || 0) : 0;

                const planoVeiculos = plano ? parseInt(plano.dataset.veiculos || 0, 10) : 0;
                const planoEstacoes = plano ? parseInt(plano.dataset.estacoes || 0, 10) : 0;
                const planoStorageMb = plano ? parseFloat(plano.dataset.storage || 0) : 0;

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

                const veiculosExcedentes = Math.max(0, empresaVeiculos - planoVeiculos);
                const filiaisExcedentes = Math.max(0, empresaEstacoes - planoEstacoes);
                const storageExcedenteMb = Math.max(0, empresaStorage - planoStorageMb);
                const storageQtd = storageExcedenteMb > 0 ? Math.ceil(storageExcedenteMb / (storageBlockGb * 1024)) : 0;

                const veiculoTotal = veiculosExcedentes * veiculoUnit;
                const filialTotal = filiaisExcedentes * filialUnit;
                const storageTotal = storageQtd * storageUnit;
                const totalGeral = veiculoTotal + filialTotal + storageTotal;

                document.getElementById('veiculoUnit').textContent = 'R$ ' + veiculoUnit.toFixed(2).replace('.', ',');
                document.getElementById('veiculoQtd').textContent = String(veiculosExcedentes);
                document.getElementById('veiculoTotal').textContent = 'R$ ' + veiculoTotal.toFixed(2).replace('.', ',');

                document.getElementById('filialUnit').textContent = 'R$ ' + filialUnit.toFixed(2).replace('.', ',');
                document.getElementById('filialQtd').textContent = String(filiaisExcedentes);
                document.getElementById('filialTotal').textContent = 'R$ ' + filialTotal.toFixed(2).replace('.', ',');

                document.getElementById('storageUnit').textContent = 'R$ ' + storageUnit.toFixed(2).replace('.', ',') + ' por 5 GB';
                document.getElementById('storageQtd').textContent = String(storageQtd);
                document.getElementById('storageTotal').textContent = 'R$ ' + storageTotal.toFixed(2).replace('.', ',');

                document.getElementById('totalGeral').textContent = 'R$ ' + totalGeral.toFixed(2).replace('.', ',');
            }

            document.getElementById('plano_id').addEventListener('change', atualizarPreview);
            document.getElementById('periodicidade').addEventListener('change', atualizarPreview);
            document.getElementById('empresa_id').addEventListener('change', atualizarPreview);
            atualizarPreview();

            form.addEventListener('submit', function() {
                btnSubmit.disabled = true;
                btnText.textContent = 'Lançando...';
            });
        })();
    </script>
@stop

