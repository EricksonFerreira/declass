@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="container-fluid px-0">

    {{-- ════ HERO ════ --}}
    <div class="module-card mb-4">
        <div class="module-card-header">
            <div class="module-header-left">
                @php
                    $iconeTipo = match($sinistro->tipo_sinistro) {
                        'furto_roubo' => 'fa-user-secret',
                        'incendio'    => 'fa-fire',
                        'vandalismo'  => 'fa-hammer',
                        'alagamento'  => 'fa-water',
                        default       => 'fa-car-crash',
                    };
                    $badgeStatus = match($sinistro->status) {
                        'encerrado','indenizado' => 'success',
                        'aprovado'               => 'primary',
                        'cancelado'              => 'secondary',
                        'aguardando_documentos'  => 'warning',
                        default                  => 'danger',
                    };
                @endphp
                <div class="module-header-icon" style="background:rgba(220,53,69,.12);color:#dc3545">
                    <i class="fas {{ $iconeTipo }}"></i>
                </div>
                <div>
                    <div class="module-header-eyebrow">Sinistros &bull; Detalhes</div>
                    <h1 class="module-header-title">Sinistro #{{ $sinistro->id }}</h1>
                    <p class="module-header-sub">
                        <span class="badge badge-{{ $badgeStatus }} mr-1">{{ $sinistro->label_status }}</span>
                        <span class="badge badge-light text-dark mr-1">{{ $sinistro->label_tipo }}</span>
                        @if($sinistro->placa_momento)
                            <span class="badge badge-secondary mr-1">{{ $sinistro->placa_momento }}</span>
                        @endif
                        @if($sinistro->data_hora_ocorrido)
                            <span style="opacity:.7;font-size:.82rem;">{{ $sinistro->data_hora_ocorrido->format('d/m/Y H:i') }}</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="module-header-right">
                @can('sinistro.edit')
                <a href="{{ route('sinistro.edit', $sinistro->id) }}" class="btn-xp btn-xp--sm mr-2">
                    <i class="fas fa-pen mr-1"></i>Editar
                </a>
                @endcan
                <a href="{{ route('sinistro.index') }}" class="btn-xp btn-xp--outline-light btn-xp--sm">
                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="row">

        {{-- ── Coluna esquerda ── --}}
        <div class="col-lg-8">

            {{-- Ocorrido --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Identificação do Ocorrido</strong>
                            <span>Tipo, data, local e detalhes do evento</span>
                        </div>
                    </div>
                    <div class="veiculo-grid">
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Tipo de Sinistro</span>
                            <span class="veiculo-field__value">{{ $sinistro->label_tipo }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Data / Hora do Ocorrido</span>
                            <span class="veiculo-field__value">{{ $sinistro->data_hora_ocorrido?->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Placa no Momento</span>
                            <span class="veiculo-field__value">{{ $sinistro->placa_momento ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Número do BO</span>
                            <span class="veiculo-field__value">{{ $sinistro->numero_bo ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Órgão (Delegacia / PRF)</span>
                            <span class="veiculo-field__value">{{ $sinistro->orgao_bo ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Terceiros Envolvidos</span>
                            <span class="veiculo-field__value">
                                @if($sinistro->terceiros_envolvidos)
                                    <span class="badge badge-warning">Sim</span>
                                    @if($sinistro->terceiro_nome) — {{ $sinistro->terceiro_nome }} @endif
                                    @if($sinistro->terceiro_telefone) — {{ $sinistro->terceiro_telefone }} @endif
                                    @if($sinistro->terceiro_placa) — Placa: {{ $sinistro->terceiro_placa }} @endif
                                @else
                                    <span class="badge badge-secondary">Não</span>
                                @endif
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Vítimas</span>
                            <span class="veiculo-field__value">
                                <span class="badge badge-{{ $sinistro->vitimas ? 'danger' : 'secondary' }}">
                                    {{ $sinistro->vitimas ? 'Sim' : 'Não' }}
                                </span>
                            </span>
                        </div>
                        <div class="veiculo-field" style="grid-column:1/-1">
                            <span class="veiculo-field__label">Local do Ocorrido</span>
                            <span class="veiculo-field__value">{{ $sinistro->local_ocorrido ?? '—' }}</span>
                        </div>
                        @if($sinistro->descricao)
                        <div class="veiculo-field" style="grid-column:1/-1">
                            <span class="veiculo-field__label">Descrição</span>
                            <span class="veiculo-field__value" style="white-space:pre-wrap;">{{ $sinistro->descricao }}</span>
                        </div>
                        @endif
                        @if($sinistro->observacoes)
                        <div class="veiculo-field" style="grid-column:1/-1">
                            <span class="veiculo-field__label">Observações</span>
                            <span class="veiculo-field__value" style="white-space:pre-wrap;">{{ $sinistro->observacoes }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Vínculos --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-link"></i>
                        <div>
                            <strong>Vínculos</strong>
                            <span>Reserva, veículo, cliente e condutor</span>
                        </div>
                    </div>
                    <div class="veiculo-grid">
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Veículo</span>
                            <span class="veiculo-field__value">
                                @if($sinistro->veiculo)
                                    <a href="{{ route('veiculo.show', $sinistro->veiculo_id) }}" class="xp-link">
                                        {{ $sinistro->veiculo->nome }}
                                        @if($sinistro->veiculo->marca || $sinistro->veiculo->modelo)
                                            — {{ $sinistro->veiculo->marca?->nome }} {{ $sinistro->veiculo->modelo?->nome }}
                                        @endif
                                    </a>
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Reserva</span>
                            <span class="veiculo-field__value">
                                @if($sinistro->reserva_id)
                                    <a href="{{ route('reserva.show', $sinistro->reserva_id) }}" class="xp-link">#{{ $sinistro->reserva_id }}</a>
                                    @if($sinistro->reserva?->usuario)
                                        — {{ $sinistro->reserva->usuario->nome }}
                                    @endif
                                @else —
                                @endif
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Cliente</span>
                            <span class="veiculo-field__value">{{ $sinistro->cliente?->nome ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Condutor</span>
                            <span class="veiculo-field__value">{{ $sinistro->condutor?->nome ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Responsável</span>
                            <span class="veiculo-field__value">{{ $sinistro->responsavel?->nome ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Registrado em</span>
                            <span class="veiculo-field__value">{{ $sinistro->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Seguro --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <strong>Seguro</strong>
                            <span>Dados da seguradora e valores da cobertura</span>
                        </div>
                    </div>
                    <div class="veiculo-grid">
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Seguradora</span>
                            <span class="veiculo-field__value">{{ $sinistro->seguradora ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Número da Apólice</span>
                            <span class="veiculo-field__value">{{ $sinistro->numero_apolice ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Nº Sinistro na Seguradora</span>
                            <span class="veiculo-field__value">{{ $sinistro->numero_sinistro_seguradora ?? '—' }}</span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Franquia</span>
                            <span class="veiculo-field__value">
                                {{ $sinistro->franquia !== null ? 'R$ ' . number_format((float)$sinistro->franquia, 2, ',', '.') : '—' }}
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Prejuízo Estimado</span>
                            <span class="veiculo-field__value text-danger font-weight-bold">
                                {{ $sinistro->valor_prejuizo_estimado !== null ? 'R$ ' . number_format((float)$sinistro->valor_prejuizo_estimado, 2, ',', '.') : '—' }}
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Aprovado pela Seguradora</span>
                            <span class="veiculo-field__value text-success font-weight-bold">
                                {{ $sinistro->valor_aprovado_seguradora !== null ? 'R$ ' . number_format((float)$sinistro->valor_aprovado_seguradora, 2, ',', '.') : '—' }}
                            </span>
                        </div>
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Resp. do Cliente</span>
                            <span class="veiculo-field__value text-warning font-weight-bold">
                                {{ $sinistro->valor_responsabilidade_cliente !== null ? 'R$ ' . number_format((float)$sinistro->valor_responsabilidade_cliente, 2, ',', '.') : '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Controle de Custos e Pagamentos --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-dollar-sign"></i>
                        <div>
                            <strong>Controle de Custos e Pagamentos</strong>
                            <span>Quem paga o custo residual e como foi/será pago</span>
                        </div>
                    </div>

                    <div class="alert alert-light border small py-2 px-3 mb-3">
                        <i class="fas fa-info-circle text-info mr-1"></i>
                        <strong>Como funciona:</strong>
                        O <strong>Custo Residual</strong> = Prejuízo Estimado − Aprovado pela Seguradora.
                        Esse valor pode ser absorvido pela <strong>Empresa</strong> (débito interno) ou cobrado do <strong>Cliente</strong>
                        (lançado como cobrança adicional na reserva). Quando ambos dividem, cada parte é registrada separadamente.
                    </div>

                    <div class="veiculo-grid mb-3">
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Destino do Custo</span>
                            <span class="veiculo-field__value">
                                @php $dc = $sinistro->destino_custo ?? 'empresa'; @endphp
                                @if($dc === 'cliente')
                                    <span class="badge badge-warning"><i class="fas fa-user mr-1"></i>Cliente</span>
                                @elseif($dc === 'ambos')
                                    <span class="badge badge-info"><i class="fas fa-users mr-1"></i>Empresa + Cliente</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fas fa-building mr-1"></i>Empresa</span>
                                @endif
                            </span>
                        </div>
                        @if($sinistro->valor_custo_empresa !== null)
                        <div class="veiculo-field">
                            <span class="veiculo-field__label">Custo para a Empresa</span>
                            <span class="veiculo-field__value font-weight-bold">
                                R$ {{ number_format((float)$sinistro->valor_custo_empresa, 2, ',', '.') }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <div class="row">
                        {{-- Bloco pagamento empresa --}}
                        @if(in_array($dc, ['empresa','ambos']))
                        <div class="col-md-6">
                            <h6 class="sn-pay-title"><i class="fas fa-building mr-1"></i>Pagamento da Empresa</h6>
                            @if($sinistro->pago_empresa_em)
                                <div class="alert alert-success py-2 px-3 small">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <strong>Pago em {{ $sinistro->pago_empresa_em->format('d/m/Y H:i') }}</strong><br>
                                    Forma: {{ \App\Models\Sinistro::FORMAS_PAGAMENTO[$sinistro->forma_pagamento_empresa] ?? $sinistro->forma_pagamento_empresa }}<br>
                                    @if($sinistro->referencia_pagamento_empresa) Ref: {{ $sinistro->referencia_pagamento_empresa }}<br> @endif
                                    @if($sinistro->pagoEmpresaPor) Confirmado por: {{ $sinistro->pagoEmpresaPor->nome ?? $sinistro->pagoEmpresaPor->name }} @endif
                                </div>
                            @else
                                <div class="alert alert-warning py-2 px-3 small mb-2">
                                    <i class="fas fa-clock mr-1"></i>Pagamento pendente
                                </div>
                                @can('sinistro.edit')
                                <button type="button" class="btn btn-sm btn-success w-100" id="btnPagarEmpresa">
                                    <i class="fas fa-check mr-1"></i>Registrar pagamento da empresa
                                </button>
                                @endcan
                            @endif
                        </div>
                        @endif

                        {{-- Bloco pagamento cliente --}}
                        @if(in_array($dc, ['cliente','ambos']))
                        <div class="col-md-6">
                            <h6 class="sn-pay-title"><i class="fas fa-user mr-1"></i>Pagamento do Cliente</h6>
                            @if($sinistro->pago_cliente_em)
                                <div class="alert alert-success py-2 px-3 small">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <strong>Pago em {{ $sinistro->pago_cliente_em->format('d/m/Y H:i') }}</strong><br>
                                    Forma: {{ \App\Models\Sinistro::FORMAS_PAGAMENTO[$sinistro->forma_pagamento_cliente] ?? $sinistro->forma_pagamento_cliente }}<br>
                                    @if($sinistro->referencia_pagamento_cliente) Ref: {{ $sinistro->referencia_pagamento_cliente }}<br> @endif
                                    @if($sinistro->pagoClientePor) Confirmado por: {{ $sinistro->pagoClientePor->nome ?? $sinistro->pagoClientePor->name }} @endif
                                </div>
                            @else
                                <div class="alert alert-warning py-2 px-3 small mb-2">
                                    <i class="fas fa-clock mr-1"></i>Pagamento pendente
                                    @if($sinistro->reserva_id)
                                        <br><small>Cobrado na reserva <a href="{{ route('reserva.show', $sinistro->reserva_id) }}">#{{ $sinistro->reserva_id }}</a></small>
                                    @endif
                                </div>
                                @can('sinistro.edit')
                                <button type="button" class="btn btn-sm btn-success w-100" id="btnPagarCliente">
                                    <i class="fas fa-check mr-1"></i>Registrar pagamento do cliente
                                </button>
                                @endcan
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Evidências --}}
            @php
                $anexos = array_filter([
                    'BO'     => $sinistro->anexo_bo_url,
                    'Foto 1' => $sinistro->anexo_foto1_url,
                    'Foto 2' => $sinistro->anexo_foto2_url,
                    'Foto 3' => $sinistro->anexo_foto3_url,
                    'Laudo'  => $sinistro->anexo_laudo_url,
                ]);
            @endphp
            @if(count($anexos))
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-paperclip"></i>
                        <div><strong>Evidências e Documentos</strong></div>
                    </div>
                    <div class="d-flex flex-wrap" style="gap:.5rem">
                        @foreach($anexos as $label => $url)
                            @php
                                $ext  = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                                $icon = $ext === 'pdf' ? 'fa-file-pdf text-danger' : 'fa-image text-primary';
                            @endphp
                            <a href="{{ $url }}" target="_blank" class="btn-xp btn-xp--sm" style="background:#f1f5f9;color:#334155;">
                                <i class="fas {{ $icon }}"></i>{{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- ── Coluna direita ── --}}
        <div class="col-lg-4">

            {{-- Resumo financeiro --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-chart-pie"></i>
                        <div>
                            <strong>Resumo Financeiro</strong>
                            <span>Valores consolidados do sinistro</span>
                        </div>
                    </div>
                    <div class="veiculo-summary">
                        <div class="veiculo-summary__row">
                            <span>Status</span>
                            <span class="badge badge-{{ $badgeStatus }}">{{ $sinistro->label_status }}</span>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Prejuízo estimado</span>
                            <strong class="text-danger">{{ $sinistro->valor_prejuizo_estimado !== null ? 'R$ '.number_format((float)$sinistro->valor_prejuizo_estimado,2,',','.') : '—' }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Cobertura seguradora</span>
                            <strong class="text-success">{{ $sinistro->valor_aprovado_seguradora !== null ? 'R$ '.number_format((float)$sinistro->valor_aprovado_seguradora,2,',','.') : '—' }}</strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Custo residual</span>
                            @php
                                $residual = max(0, (float)($sinistro->valor_prejuizo_estimado ?? 0) - (float)($sinistro->valor_aprovado_seguradora ?? 0));
                            @endphp
                            <strong class="{{ $residual > 0 ? 'text-warning' : 'text-muted' }}">
                                R$ {{ number_format($residual, 2, ',', '.') }}
                            </strong>
                        </div>
                        <div class="veiculo-summary__row">
                            <span>Destino do custo</span>
                            @if($dc === 'cliente') <span class="badge badge-warning">Cliente</span>
                            @elseif($dc === 'ambos') <span class="badge badge-info">Ambos</span>
                            @else <span class="badge badge-secondary">Empresa</span>
                            @endif
                        </div>
                        @if(in_array($dc, ['empresa','ambos']))
                        <div class="veiculo-summary__row">
                            <span>Pago (empresa)</span>
                            @if($sinistro->pago_empresa_em)
                                <span class="status-badge status-badge--green"><i class="fas fa-check mr-1"></i>Pago</span>
                            @else
                                <span class="status-badge status-badge--amber">Pendente</span>
                            @endif
                        </div>
                        @endif
                        @if(in_array($dc, ['cliente','ambos']))
                        <div class="veiculo-summary__row">
                            <span>Pago (cliente)</span>
                            @if($sinistro->pago_cliente_em)
                                <span class="status-badge status-badge--green"><i class="fas fa-check mr-1"></i>Pago</span>
                            @else
                                <span class="status-badge status-badge--amber">Pendente</span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Ações --}}
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-bolt"></i>
                        <div><strong>Ações</strong></div>
                    </div>
                    <div class="veiculo-acoes-grid">
                        @can('sinistro.edit')
                        <a href="{{ route('sinistro.edit', $sinistro->id) }}" class="veiculo-acao veiculo-acao--primary">
                            <i class="fas fa-pen"></i><span>Editar sinistro</span>
                        </a>
                        @endcan
                        @if($sinistro->reserva_id)
                        <a href="{{ route('reserva.show', $sinistro->reserva_id) }}" class="veiculo-acao">
                            <i class="fas fa-calendar-alt"></i><span>Ver reserva</span>
                        </a>
                        @endif
                        @if($sinistro->veiculo_id)
                        <a href="{{ route('veiculo.show', $sinistro->veiculo_id) }}" class="veiculo-acao">
                            <i class="fas fa-car"></i><span>Ver veículo</span>
                        </a>
                        @endif
                        <a href="{{ route('sinistro.index') }}" class="veiculo-acao">
                            <i class="fas fa-list"></i><span>Lista de sinistros</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Histórico --}}
    <div class="row">
        <div class="col-12">
            <div class="config-card mb-4">
                <div class="config-block">
                    <div class="config-block-label">
                        <i class="fas fa-history"></i>
                        <div>
                            <strong>Histórico de Alterações</strong>
                            <span>Todas as modificações registradas</span>
                        </div>
                    </div>
                    @if($sinistro->historico->isEmpty())
                        <p class="text-muted small mb-0">Nenhuma alteração registrada.</p>
                    @else
                    <div class="timeline timeline-inverse" style="max-height:320px;overflow-y:auto;">
                        @foreach($sinistro->historico as $hist)
                        <div class="time-label">
                            <span class="bg-secondary text-white" style="font-size:11px;padding:2px 8px;border-radius:3px;">
                                {{ $hist->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <div>
                            <i class="fas fa-circle bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-user mr-1"></i>{{ $hist->usuario?->nome ?? 'Sistema' }}</span>
                                <p class="timeline-body small">
                                    {{ $hist->descricao }}
                                    @if($hist->campo_alterado)
                                        <br><strong>{{ $hist->campo_alterado }}:</strong>
                                        <span class="text-danger">{{ $hist->valor_anterior ?? 'vazio' }}</span>
                                        &rarr;
                                        <span class="text-success">{{ $hist->valor_novo }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Modal pagamento empresa --}}
<div class="modal fade" id="modalPagarEmpresa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-building text-secondary mr-2"></i>Pagamento — Empresa</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small py-2 px-3 mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Este é o custo que a empresa absorve internamente (ex.: franquia, reparos, despesas não cobertas pelo seguro).
                    Registre como o valor foi quitado. Para pagamentos em <strong>dinheiro</strong> ou <strong>transferência</strong> feitos internamente,
                    selecione a forma correta — o sistema registra quem confirmou e quando.
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Forma de Pagamento <span class="text-danger">*</span></label>
                    <select id="pe_forma" class="form-control">
                        <option value="">Selecione...</option>
                        @foreach(\App\Models\Sinistro::FORMAS_PAGAMENTO as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!empty($metodosPagamento))
                <div class="form-group d-none" id="pc_cartao_group">
                    <label class="font-weight-bold">Cartão do Cliente (Reserva)</label>
                    <select id="pc_payment_method" class="form-control">
                        <option value="">Selecione...</option>
                        @foreach($metodosPagamento as $pm)
                            @php($card = $pm['card'] ?? null)
                            <option value="{{ $pm['id'] }}">
                                {{ strtoupper($card->brand ?? 'CARD') }} **** {{ $card->last4 ?? '' }}
                                ({{ $card->exp_month ?? '' }}/{{ $card->exp_year ?? '' }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Se a forma for cartão de crédito, o sistema cobra pelo Stripe usando o cartão do cliente.</small>
                </div>
                @endif
                <div class="form-group">
                    <label class="font-weight-bold">Referência / Comprovante</label>
                    <input type="text" id="pe_referencia" class="form-control" placeholder="Nº da transferência, comprovante, etc.">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btnSalvarPagarEmpresa">
                    <i class="fas fa-check mr-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal pagamento cliente --}}
<div class="modal fade" id="modalPagarCliente" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user text-warning mr-2"></i>Pagamento — Cliente</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small py-2 px-3 mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Este é o valor cobrado do <strong>cliente da reserva</strong> como responsabilidade pelo sinistro.
                    Confirme apenas quando o cliente efetuar o pagamento — pode ser em dinheiro presencialmente,
                    cartão ou PIX. O sistema registra a forma, o responsável pela confirmação e o horário.
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Forma de Pagamento <span class="text-danger">*</span></label>
                    <select id="pc_forma" class="form-control">
                        <option value="">Selecione...</option>
                        @foreach(\App\Models\Sinistro::FORMAS_PAGAMENTO as $key => $label)
                            @if($key !== 'transferencia')
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Referência / Comprovante</label>
                    <input type="text" id="pc_referencia" class="form-control" placeholder="NSU do cartão, código PIX, etc.">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btnSalvarPagarCliente">
                    <i class="fas fa-check mr-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
.module-card { background:linear-gradient(135deg,#1e293b 0%,#334155 100%);border-radius:16px;overflow:hidden; }
.module-card-header { display:flex;align-items:center;justify-content:space-between;padding:1.5rem;gap:1rem;flex-wrap:wrap; }
.module-header-left { display:flex;align-items:center;gap:1rem; }
.module-header-icon { width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0; }
.module-header-eyebrow { font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.55);margin-bottom:.2rem; }
.module-header-title { font-size:1.5rem;font-weight:800;color:#fff;margin:0 0 .2rem; }
.module-header-sub { font-size:.84rem;color:rgba(255,255,255,.7);margin:0; }
.module-header-right { display:flex;gap:.5rem;flex-wrap:wrap; }
.btn-xp { display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .9rem;border-radius:999px;font-size:.8rem;font-weight:700;cursor:pointer;text-decoration:none;border:none;background:rgba(255,255,255,.15);color:#fff;transition:background .15s; }
.btn-xp:hover { background:rgba(255,255,255,.25);color:#fff;text-decoration:none; }
.btn-xp--sm { padding:.25rem .7rem;font-size:.76rem; }
.btn-xp--outline-light { background:transparent;border:1px solid rgba(255,255,255,.4); }
.config-card { background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.07);overflow:hidden; }
.config-block { padding:1.25rem; }
.config-block + .config-block { border-top:1px solid #f0f4f8; }
.config-block-label { display:flex;align-items:flex-start;gap:.75rem;margin-bottom:1rem; }
.config-block-label > i { font-size:1rem;color:#64748b;margin-top:.15rem;flex-shrink:0; }
.config-block-label strong { display:block;font-size:.85rem;color:#1e293b;font-weight:700; }
.config-block-label span { display:block;font-size:.75rem;color:#94a3b8; }
.veiculo-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem 1.5rem; }
.veiculo-field { display:flex;flex-direction:column;gap:.15rem; }
.veiculo-field__label { font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8; }
.veiculo-field__value { font-size:.88rem;color:#1e293b;font-weight:500; }
.veiculo-summary { display:flex;flex-direction:column;gap:.5rem; }
.veiculo-summary__row { display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid #f1f5f9;font-size:.84rem; }
.veiculo-summary__row:last-child { border-bottom:none; }
.veiculo-acoes-grid { display:grid;grid-template-columns:1fr 1fr;gap:.5rem; }
.veiculo-acao { display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.35rem;padding:.75rem .5rem;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;text-align:center;font-size:.76rem;font-weight:700;color:#334155;cursor:pointer;text-decoration:none;transition:background .15s,border-color .15s; }
.veiculo-acao:hover { background:#e0f2fe;border-color:#38bdf8;color:#0369a1;text-decoration:none; }
.veiculo-acao i { font-size:1.1rem;color:#64748b; }
.veiculo-acao:hover i { color:#0369a1; }
.veiculo-acao--primary { background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%);border-color:#2563eb;color:#fff; }
.veiculo-acao--primary i { color:#fff; }
.veiculo-acao--primary:hover { background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%);color:#fff; }
.status-badge { display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .6rem;border-radius:999px;font-size:.75rem;font-weight:700; }
.status-badge--green { background:#dcfce7;color:#166534; }
.status-badge--amber { background:#fef3c7;color:#92400e; }
.xp-link { color:#2563eb;font-weight:600; }
.xp-link:hover { color:#1d4ed8; }
.sn-pay-title { font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:.5rem; }
</style>
@stop

@push('js')
<x-page-tour tour-id="sinistro-show" :steps="[
    [
        'element' => '.module-card',
        'popover' => [
            'title' => 'Cabeçalho do sinistro',
            'description' => 'Exibe o número do sinistro, ícone do tipo de evento (colisão, furto, incêndio…), status atual, tipo, placa e data/hora do ocorrido.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => '.module-header-sub .badge:first-child',
        'popover' => [
            'title' => 'Status atual',
            'description' => 'Indica a situação do sinistro: <b>Aberto</b>, <b>Aguardando Documentos</b>, <b>Aprovado</b>, <b>Encerrado</b>, <b>Indenizado</b> ou <b>Cancelado</b>. Atualizado conforme o processo avança.',
            'side' => 'bottom',
        ],
    ],
    [
        'element' => '.col-lg-8 .config-card:nth-child(1)',
        'popover' => [
            'title' => 'Identificação do Ocorrido',
            'description' => 'Dados completos do evento: tipo, data, local, placa, número do BO, órgão emissor, terceiros envolvidos, vítimas e descrição detalhada.',
            'side' => 'right',
        ],
    ],
    [
        'element' => '.col-lg-8 .config-card:nth-child(2)',
        'popover' => [
            'title' => 'Vínculos',
            'description' => 'Mostra o veículo, a reserva associada, o cliente, o condutor e o responsável pelo registro. Clique nos links para navegar direto para o veículo ou reserva.',
            'side' => 'right',
        ],
    ],
    [
        'element' => '.col-lg-8 .config-card:nth-child(3)',
        'popover' => [
            'title' => 'Seguro',
            'description' => 'Seguradora, número de apólice, franquia, prejuízo estimado, valor aprovado pela seguradora e responsabilidade do cliente. O custo residual é calculado automaticamente.',
            'side' => 'right',
        ],
    ],
    [
        'element' => '.col-lg-8 .config-card:nth-child(4)',
        'popover' => [
            'title' => 'Controle de Custos e Pagamentos',
            'description' => 'Define quem arca com o custo residual (empresa, cliente ou ambos) e registra os pagamentos. Você pode confirmar o pagamento da empresa ou lançar cobrança ao cliente via Stripe.',
            'side' => 'right',
        ],
    ],
    [
        'element' => '.col-lg-4',
        'popover' => [
            'title' => 'Painel lateral',
            'description' => 'Apresenta evidências anexadas (BO, fotos, laudo), o resumo financeiro consolidado, as ações disponíveis (editar, exportar) e o histórico de alterações do sinistro.',
            'side' => 'left',
        ],
    ],
]" />
<script>
(function () {
    function postPagamento(url, forma, referencia, paymentMethod, btnEl, successCb) {
        btnEl.disabled = true;
        btnEl.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Aguarde...';
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ forma_pagamento: forma, referencia_pagamento: referencia, payment_method: paymentMethod }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                btnEl.disabled = false;
                btnEl.innerHTML = '<i class="fas fa-check mr-1"></i>Confirmar';
                return;
            }
            successCb();
        })
        .catch(() => {
            alert('Erro ao registrar pagamento. Tente novamente.');
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="fas fa-check mr-1"></i>Confirmar';
        });
    }

    document.getElementById('btnPagarEmpresa')?.addEventListener('click', () => $('#modalPagarEmpresa').modal('show'));
    document.getElementById('btnPagarCliente')?.addEventListener('click', () => $('#modalPagarCliente').modal('show'));

    document.getElementById('btnSalvarPagarEmpresa')?.addEventListener('click', function () {
        const forma = document.getElementById('pe_forma').value;
        if (!forma) { alert('Selecione a forma de pagamento.'); return; }
        postPagamento(
            '{{ route("sinistro.pagarEmpresa", $sinistro->id) }}',
            forma,
            document.getElementById('pe_referencia').value,
            null,
            this,
            () => { $('#modalPagarEmpresa').modal('hide'); location.reload(); }
        );
    });

    document.getElementById('btnSalvarPagarCliente')?.addEventListener('click', function () {
        const forma = document.getElementById('pc_forma').value;
        if (!forma) { alert('Selecione a forma de pagamento.'); return; }
        const pmSel = document.getElementById('pc_payment_method');
        const paymentMethod = pmSel ? pmSel.value : null;
        if (forma === 'cartao_credito' && pmSel && !paymentMethod) {
            alert('Selecione um cartao do cliente para cobrar.');
            return;
        }
        postPagamento(
            '{{ route("sinistro.pagarCliente", $sinistro->id) }}',
            forma,
            document.getElementById('pc_referencia').value,
            paymentMethod,
            this,
            () => { $('#modalPagarCliente').modal('hide'); location.reload(); }
        );
    });

    document.getElementById('pc_forma')?.addEventListener('change', function () {
        const group = document.getElementById('pc_cartao_group');
        if (!group) return;
        if (this.value === 'cartao_credito') group.classList.remove('d-none');
        else group.classList.add('d-none');
    });
})();
</script>
@endpush
