@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" :createUrl="route('empresa.create')" />
@stop

@php
    $totalBytes = (int) \App\Models\ArquivoRastreado::query()->sum('size_bytes');
    if ($totalBytes >= 1073741824) {
        $storageFormatted = number_format($totalBytes / 1073741824, 2, ',', '.') . ' GB';
    } else {
        $storageFormatted = number_format($totalBytes / 1048576, 2, ',', '.') . ' MB';
    }
@endphp

@section('content')
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $storageFormatted }}</h3>
                    <p>Armazenamento total</p>
                </div>
                <div class="icon"><i class="fas fa-database"></i></div>
                <a href="#" class="small-box-footer" onclick="event.preventDefault();alert('Armazenamento total de todas as empresas somadas. O detalhamento por empresa aparece na tabela abaixo.');">
                    <i class="fas fa-chart-simple mr-1"></i> Detalhes por empresa na tabela
                </a>
            </div>
        </div>
    </div>

    @if (!empty($filters))
        <x-filter
            :filters="$filters"
            :additionalFilters="$additionalFilters"
            :itensFiltered="$itensFiltered"
            :nomeFiltro="$nomeFiltro"
        />
    @endif

    <div class="card">
        <div class="card-header px-3 py-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
                <h3 class="card-title mb-0 text-sm font-weight-bold">
                    Registros
                    <span class="badge badge-success ml-1">{{ $empresas->total() }}</span>
                </h3>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <form action="{{ route('empresa.setPerPage') }}" method="POST" class="mb-0">
                        @csrf
                        <x-per-page-selector :perPages="$perPages" :selectedPerPage="$perPage" />
                    </form>
                    {{ $empresas->links() }}
                </div>
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-sm mb-0 empresa-table" style="font-size:13px;">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-3">Empresa</th>
                        <th>Contato</th>
                        <th>Plano</th>
                        <th>Gateway / Moeda</th>
                        <th>Armazenamento</th>
                        <th>Cores</th>
                        <th>Status</th>
                        <th class="text-center" style="width:260px;">AÃ§Ãµes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($empresas as $empresa)
                        @php
                            $gateway = $empresa->gateway_pagamento ?? null;
                            $moeda = $empresa->moeda_pagamento ?? null;
                            $corPrimaria = $empresa->cor_primaria ?? null;
                            $corSecundaria = $empresa->cor_secundaria ?? null;
                            $corTerciaria = $empresa->cor_terciaria ?? null;
                        @endphp
                        <tr>
                            <td class="pl-3 align-middle">
                                <div class="d-flex align-items-center" style="gap: 10px;">
                                    @if(!empty($empresa->logo_url))
                                        <img
                                            src="{{ $empresa->logo_url }}"
                                            alt="Logo {{ $empresa->nome }}"
                                            class="empresa-logo-lista"
                                            style="background: {{ $empresa->cor_primaria ?? '#1F2937' }};"
                                        >
                                    @else
                                        <div class="empresa-logo-fallback">
                                            {{ strtoupper(mb_substr($empresa->nome ?? 'E', 0, 1)) }}
                                        </div>
                                    @endif

                                    <div>
                                        <div class="font-weight-bold">{{ $empresa->nome }}</div>
                                        <div class="text-muted text-xs">Slug: {{ $empresa->slug }}</div>
                                        @if(!empty($empresa->dominio))
                                            <div class="text-muted text-xs">{{ $empresa->dominio }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="align-middle">
                                <div class="text-xs">
                                    <div><strong>CNPJ:</strong> {{ $empresa->cnpj ?? '-' }}</div>
                                    <div><strong>E-mail:</strong> {{ $empresa->email ?? '-' }}</div>
                                    <div><strong>Telefone:</strong> {{ $empresa->telefone ?? '-' }}</div>
                                </div>
                            </td>

                            @php
                                $planoAtivo = $empresa->planoAtivo;
                            @endphp
                            <td class="align-middle text-xs">
                                @if($planoAtivo)
                                    <div><strong>{{ $planoAtivo->plano?->nome ?? '-' }}</strong></div>
                                    <div>
                                        <span class="badge badge-xs bg-{{ $planoAtivo->status === 'ativo' ? 'success' : ($planoAtivo->status === 'trial' ? 'info' : ($planoAtivo->status === 'inadimplente' ? 'danger' : 'warning')) }}">
                                            {{ $planoAtivo->status_badge['label'] ?? $planoAtivo->status }}
                                        </span>
                                    </div>
                                    <div class="text-muted" style="font-size:10px;">
                                        {{ $planoAtivo->periodicidade_texto }}
                                        {{ $planoAtivo->data_vencimento ? 'Â· atÃ© ' . $planoAtivo->data_vencimento->format('d/m/Y') : '' }}
                                    </div>
                                @else
                                    <div class="d-flex flex-column" style="gap:4px;">
                                        <span class="text-muted font-italic">Sem plano</span>
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-outline-primary btn-atribuir-assinatura"
                                            data-toggle="modal"
                                            data-target="#modalAtribuirAssinatura"
                                            data-empresa-id="{{ $empresa->id }}"
                                            data-empresa-nome="{{ $empresa->nome }}"
                                        >
                                            <i class="fas fa-link mr-1"></i> Atribuir assinatura
                                        </button>
                                    </div>
                                @endif
                            </td>

                            <td class="align-middle">
                                <div class="text-xs mb-1">
                                    <strong>Gateway:</strong>
                                    @if($gateway)
                                        {{ strtoupper(str_replace('_', ' ', $gateway)) }}
                                    @else
                                        <span class="text-muted font-italic">nao definido</span>
                                    @endif
                                </div>
                                <div class="text-xs mb-2">
                                    <strong>Moeda:</strong>
                                    @if($moeda)
                                        {{ $moeda }}
                                    @else
                                        <span class="text-muted font-italic">nao definida</span>
                                    @endif
                                </div>
                            </td>

                            <td class="align-middle text-xs">
                                @php
                                    $pct = $empresa->storage_percent;
                                    $barColor = $pct >= 90 ? '#dc2626' : ($pct >= 60 ? '#d97706' : ($pct >= 30 ? '#2563eb' : '#16a34a'));
                                    $labelColor = $pct >= 90 ? 'text-danger' : ($pct >= 60 ? 'text-warning' : 'text-success');
                                @endphp
                                <div class="{{ $labelColor }}" style="font-weight:600;font-size:11px;">
                                    <a href="{{ route('empresa.armazenamento', $empresa->id) }}" class="text-reset" style="text-decoration:none;">
                                        <i class="fas fa-database mr-1"></i> {{ $empresa->storage_used }}
                                    </a>
                                </div>
                                <div style="margin-top:3px;background:#e5e7eb;border-radius:4px;height:8px;width:100px;overflow:hidden;">
                                    <div style="width:{{ min($pct, 100) }}%;height:100%;background:{{ $barColor }};border-radius:4px;"></div>
                                </div>
                                <div class="text-muted" style="font-size:9px;margin-top:1px;">
                                    <a href="{{ route('empresa.armazenamento', $empresa->id) }}" class="text-muted" style="text-decoration:none;">
                                        {{ $pct }}% do limite ({{ $empresa->planoAtivo?->plano?->nome ?? 'Sem plano' }})
                                    </a>
                                </div>
                            </td>

                            <td class="align-middle">
                                @if($corPrimaria || $corSecundaria || $corTerciaria)
                                    <div class="d-flex flex-wrap align-items-center empresa-cor-group">
                                        @if($corPrimaria)
                                            <button type="button" class="empresa-cor-chip js-copy-color" data-color="{{ $corPrimaria }}" title="Clique para copiar {{ $corPrimaria }}">
                                                <span class="empresa-cor-dot" style="background: {{ $corPrimaria }}"></span>
                                            </button>
                                        @endif
                                        @if($corSecundaria)
                                            <button type="button" class="empresa-cor-chip js-copy-color" data-color="{{ $corSecundaria }}" title="Clique para copiar {{ $corSecundaria }}">
                                                <span class="empresa-cor-dot" style="background: {{ $corSecundaria }}"></span>
                                            </button>
                                        @endif
                                        @if($corTerciaria)
                                            <button type="button" class="empresa-cor-chip js-copy-color" data-color="{{ $corTerciaria }}" title="Clique para copiar {{ $corTerciaria }}">
                                                <span class="empresa-cor-dot" style="background: {{ $corTerciaria }}"></span>
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge badge-secondary">SEM CORES</span>
                                @endif
                            </td>

                            <td class="align-middle">
                                <span class="badge badge-{{ ($empresa->ativo ?? 'S') === 'S' ? 'success' : 'danger' }}">
                                    {{ ($empresa->ativo ?? 'S') === 'S' ? 'ATIVO' : 'INATIVO' }}
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                <div class="d-flex flex-wrap justify-content-center empresa-actions-group">
                                    <a href="{{ route('empresa.site-editor.edit', $empresa->id) }}" class="btn btn-xs btn-info" style="background:#0ea5e9;border-color:#0ea5e9;">
                                        <i class="fa fa-paint-brush"></i> Editor Site
                                    </a>
                                    <a href="{{ route('empresa.edit', $empresa->id) }}" class="btn btn-xs btn-warning">
                                        <i class="fa fa-edit"></i> Editar
                                    </a>
                                    <x-delete-button :action="route('empresa.destroy', $empresa->id)" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Nenhuma empresa encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($empresas->hasPages())
            <div class="card-footer py-2 d-flex justify-content-end">
                {{ $empresas->links() }}
            </div>
        @endif
    </div>
@stop

@section('css')
<style>
    .empresa-logo-lista {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        object-fit: contain;
        padding: 2px;
    }

    .empresa-logo-fallback {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        background: #1f2937;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }

    .empresa-table .text-xs {
        font-size: 11px;
        line-height: 1.35;
    }

    .empresa-cor-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        padding: 0;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #d7e0ea;
        color: #334155;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }

    .empresa-cor-chip:hover {
        transform: translateY(-1px);
        border-color: #94a3b8;
        box-shadow: 0 4px 10px rgba(15, 23, 42, .10);
    }

    .empresa-cor-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 1px solid rgba(15, 23, 42, .15);
        display: inline-block;
    }

    .empresa-cor-group {
        gap: 4px;
    }

    .empresa-cor-copied {
        outline: 2px solid rgba(37, 99, 235, .24);
        border-color: #2563eb !important;
    }

    .empresa-actions-group {
        gap: 4px;
    }

    .empresa-actions-group > * {
        margin: 0 !important;
    }

    .btn-atribuir-assinatura {
        width: fit-content;
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

    .form-control-sm {
        border-radius: 10px;
    }

    .modal-xl {
        max-width: 1180px;
    }
</style>
@stop

@section('js')
<script src="{{ asset('js/select2.min.js') }}"></script>
<script>
    (function () {
            var $modal = $('#modalAtribuirAssinatura');
            var $form = $('#formAtribuirAssinatura');
            var $empresaId = $form.find('[name="empresa_id"]');
            var $empresaNome = $form.find('#empresa_nome_modal');
            var $planSelect = $form.find('[name="plano_id"]');
            var $periodicidade = $form.find('[name="periodicidade"]');
            var $dataInicio = $form.find('[name="data_inicio"]');
            var $dataVencimento = $form.find('[name="data_vencimento"]');
            var $valorExibido = $form.find('#valorExibido');
            var $planoStorage = $form.find('#planoStorage');

            $modal.on('show.bs.modal', function (event) {
                var trigger = $(event.relatedTarget);
                var empresaId = trigger.data('empresa-id');
                var empresaNome = trigger.data('empresa-nome');

                if (empresaId) {
                    $empresaId.val(String(empresaId));
                }

                if (empresaNome) {
                    $empresaNome.val(empresaNome);
                    $modal.find('.modal-title').text('Atribuir assinatura para ' + empresaNome);
                }

                atualizarPreview();
            });

        function formatarMoeda(valor) {
            return Number(valor || 0).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function calcularVencimento(dataInicio, periodicidade) {
            if (!dataInicio) {
                return '';
            }

            var data = new Date(dataInicio + 'T00:00:00');
            if (Number.isNaN(data.getTime())) {
                return '';
            }

            if (periodicidade === 'anual') {
                data.setFullYear(data.getFullYear() + 1);
            } else {
                data.setMonth(data.getMonth() + 1);
            }

            return data.toISOString().slice(0, 10);
        }

        function atualizarPreview() {
            var $selected = $planSelect.find('option:selected');
            var mensal = parseFloat($selected.data('mensal') || 0);
            var anual = parseFloat($selected.data('anual') || 0);
            var storage = $selected.data('storage');
            var periodicidade = $periodicidade.val() || 'mensal';
            var valor = periodicidade === 'anual' ? anual : mensal;

            $valorExibido.val(formatarMoeda(valor));
            $planoStorage.val(storage ? storage + ' MB' : '');

            var vencimento = calcularVencimento($dataInicio.val(), periodicidade);
            if (vencimento) {
                $dataVencimento.val(vencimento);
            }
        }

        $planSelect.on('change', atualizarPreview);
        $periodicidade.on('change', atualizarPreview);
        $dataInicio.on('change', atualizarPreview);
        atualizarPreview();

        function copyColor(hex, trigger) {
            if (!hex) {
                return;
            }

            var text = String(hex).trim();
            var el = trigger instanceof HTMLElement ? trigger : null;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                var input = document.createElement('input');
                input.value = text;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
            }

            if (el) {
                el.classList.add('empresa-cor-copied');
                el.setAttribute('title', 'Copiado: ' + text);
                setTimeout(function () {
                    el.classList.remove('empresa-cor-copied');
                }, 700);
            }
        }

        document.querySelectorAll('.js-copy-color').forEach(function (button) {
            button.addEventListener('click', function () {
                copyColor(button.dataset.color || '', button);
            });
        });
    })();
</script>
@stop

<div class="modal fade" id="modalAtribuirAssinatura" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="formAtribuirAssinatura" action="{{ route('assinatura.atribuir.processar') }}" method="POST">
                @csrf
                <input type="hidden" name="empresa_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-link mr-1"></i> Atribuir assinatura</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card card-outline card-light mb-0">
                                <div class="card-body p-4">
                                    <div class="veiculo-section-title">
                                        <i class="fas fa-link mr-1"></i>Dados da assinatura
                                    </div>

                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="mb-1">Empresa <span class="text-danger">*</span></label>
                                            <input type="hidden" name="empresa_id" value="">
                                            <input type="text" class="form-control form-control-sm" id="empresa_nome_modal" readonly>
                                            <small class="veiculo-lookup-help">A empresa vem preenchida automaticamente a partir da linha selecionada.</small>
                                        </div>

                                        <div class="col-lg-6 col-md-6 mb-3">
                                            <label class="mb-1">Plano <span class="text-danger">*</span></label>
                                            <select name="plano_id" class="form-control form-control-sm" required>
                                                <option value="">Selecione o plano...</option>
                                                @foreach (\App\Models\Plano::orderBy('nome')->get() as $plano)
                                                    <option value="{{ $plano->id }}" data-mensal="{{ $plano->valor_mensal }}" data-anual="{{ $plano->valor_anual }}" data-storage="{{ $plano->limite_armazenamento_mb ?? 0 }}">
                                                        {{ $plano->nome }} - R$ {{ number_format($plano->valor_mensal, 2, ',', '.') }}/mês
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <label class="mb-1">Periodicidade <span class="text-danger">*</span></label>
                                            <select name="periodicidade" class="form-control form-control-sm" required>
                                                <option value="mensal">Mensal</option>
                                                <option value="anual">Anual (com desconto)</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <label class="mb-1">Dia de vencimento</label>
                                            <input type="number" name="dia_vencimento" class="form-control form-control-sm" min="1" max="28" placeholder="Ex.: 10">
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <label class="mb-1">Data de início <span class="text-danger">*</span></label>
                                            <input type="date" name="data_inicio" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required>
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <label class="mb-1">Data de vencimento</label>
                                            <input type="date" name="data_vencimento" class="form-control form-control-sm" value="{{ now()->addMonth()->toDateString() }}">
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <label class="mb-1">Valor a cobrar (R$) <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">R$</span>
                                                </div>
                                                <input type="text" id="valorExibido" class="form-control" readonly required>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <label class="mb-1">Armazenamento do plano</label>
                                            <input type="text" class="form-control form-control-sm" id="planoStorage" readonly placeholder="Selecione um plano...">
                                        </div>

                                        <div class="col-12 mb-2">
                                            <label class="mb-1">Observação</label>
                                            <textarea name="observacao" class="form-control form-control-sm" rows="2" placeholder="Observação interna sobre esta atribuição..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 mt-3 mt-lg-0">
                            <div class="card card-outline card-light h-100 mb-0">
                                <div class="card-body p-4">
                                    <div class="veiculo-section-title">
                                        <i class="fas fa-info-circle mr-1"></i>Resumo
                                    </div>
                                    <div class="veiculo-info-tip">
                                        Verifique o plano, a periodicidade e a data de vencimento antes de confirmar a atribuição.
                                    </div>
                                    <div class="veiculo-info-tip">
                                        O valor exibido muda conforme o plano selecionado e o tipo de cobrança.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Atribuir assinatura
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


