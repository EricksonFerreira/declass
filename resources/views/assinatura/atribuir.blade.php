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
                                            @foreach ($empresas as $e)
                                                <option value="{{ $e->id }}" {{ (int) $empresaSelecionadaId === (int) $e->id ? 'selected' : '' }}>
                                                    {{ $e->nome }}{{ $e->planoAtivo?->plano ? ' — atual: ' . $e->planoAtivo->plano->nome : ' — sem plano' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="veiculo-lookup-help">
                                            Ao vir do detalhe da empresa, ela já vem selecionada — mas pode ser trocada aqui.
                                        </small>
                                    </div>

                                    <div class="col-lg-6 col-md-6 mb-3">
                                        <label class="mb-1">Plano <span class="text-danger">*</span></label>
                                        <select name="plano_id" id="plano_id" class="form-control form-control-sm" required>
                                            <option value="">Selecione o plano...</option>
                                            @foreach ($planos as $p)
                                                <option
                                                    value="{{ $p->id }}"
                                                    data-mensal="{{ $p->valor_mensal }}"
                                                    data-anual="{{ $p->valor_anual }}"
                                                >
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
                                            <input type="number" step="0.01" min="0" id="valorExibido" name="valor" class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="trialCheck" name="trial" value="1">
                                            <label class="custom-control-label" for="trialCheck">
                                                <strong>Trial de 7 dias</strong> — acesso liberado na hora e <strong>desativado sozinho</strong> ao fim do período (sem cobrança).
                                            </label>
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
                                Selecione um plano para visualizar o valor antes de lançar. Marque <strong>Trial de 7 dias</strong> para liberar acesso temporário sem cobrança — desativa sozinho ao fim.
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
    <script src="/js/select2.min.js"></script>
    <script>
        (function() {
            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#empresa_id, #plano_id').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: 'Selecione...',
                    allowClear: false,
                });
            }

            const form = document.getElementById('formAtribuir');
            const btnSubmit = document.getElementById('btnSubmit');
            const btnText = document.getElementById('btnText');
            const trialCheck = document.getElementById('trialCheck');
            const camposCobranca = ['periodicidade', 'data_vencimento', 'valorExibido', 'dia_vencimento'];

            function atualizarPreview() {
                const plano = document.getElementById('plano_id').selectedOptions[0];
                const periodicidade = document.getElementById('periodicidade').value;
                const valorEl = document.getElementById('valorExibido');

                if (plano && plano.value && !trialCheck.checked) {
                    const valor = periodicidade === 'anual'
                        ? parseFloat(plano.dataset.anual || 0)
                        : parseFloat(plano.dataset.mensal || 0);
                    if (!valorEl.value || Number.isNaN(parseFloat(valorEl.value))) {
                        valorEl.value = valor.toFixed(2);
                    }
                } else if (!trialCheck.checked) {
                    valorEl.value = '';
                }
            }

            function aplicarTrial() {
                const emTrial = trialCheck.checked;
                camposCobranca.forEach(function(id) {
                    const el = document.getElementById(id);
                    if (!el) return;
                    el.disabled = emTrial;
                    el.required = !emTrial && (id === 'periodicidade' || id === 'valorExibido');
                });
                if (emTrial) {
                    document.getElementById('valorExibido').value = '0.00';
                } else {
                    atualizarPreview();
                }
            }

            document.getElementById('plano_id').addEventListener('change', atualizarPreview);
            document.getElementById('periodicidade').addEventListener('change', atualizarPreview);
            trialCheck.addEventListener('change', aplicarTrial);
            atualizarPreview();

            form.addEventListener('submit', function() {
                btnSubmit.disabled = true;
                btnText.textContent = 'Lançando...';
            });
        })();
    </script>
@stop

