@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $isEdit = isset($movimentacao) && $movimentacao;
    $prefill = $prefill ?? [];
    $dataRegistro        = $isEdit && $movimentacao->data_registro         ? \Carbon\Carbon::parse($movimentacao->data_registro)->format('Y-m-d\TH:i')         : now()->format('Y-m-d\TH:i');
    $dataSaida           = $isEdit && $movimentacao->data_saida            ? \Carbon\Carbon::parse($movimentacao->data_saida)->format('Y-m-d\TH:i')            : '';
    $dataChegadaPrevista = $isEdit && $movimentacao->data_chegada_prevista ? \Carbon\Carbon::parse($movimentacao->data_chegada_prevista)->format('Y-m-d\TH:i') : '';
    $dataChegadaReal     = $isEdit && $movimentacao->data_chegada_real     ? \Carbon\Carbon::parse($movimentacao->data_chegada_real)->format('Y-m-d\TH:i')     : '';

    $selTipo    = old('tipo_movimentacao_id',    $isEdit ? $movimentacao->tipo_movimentacao_id    : ($prefill['tipo_movimentacao_id']    ?? ''));
    $selStatus  = old('status_movimentacao_id',  $isEdit ? $movimentacao->status_movimentacao_id  : ($prefill['status_movimentacao_id']  ?? ''));
    $selVeiculo = old('veiculo_id',              $isEdit ? $movimentacao->veiculo_id              : ($prefill['veiculo_id']              ?? ''));
    $selOrigem  = old('local_origem_id',         $isEdit ? $movimentacao->local_origem_id         : ($prefill['local_origem_id']         ?? ''));
    $selDestino = old('local_destino_id',        $isEdit ? $movimentacao->local_destino_id        : ($prefill['local_destino_id']        ?? ''));
    $selUsuario = old('user_id',                 $isEdit ? $movimentacao->user_id                 : ($prefill['user_id']                 ?? auth()->id()));
    $selReserva = old('reserva_id',              $isEdit ? $movimentacao->reserva_id              : '');
    $selOs      = old('ordem_servico_id',        $isEdit ? $movimentacao->ordem_servico_id        : '');
    $kmSaida    = old('km_saida',                $isEdit ? $movimentacao->km_saida                : ($prefill['km_saida'] ?? ''));
    $kmChegada  = old('km_chegada',              $isEdit ? $movimentacao->km_chegada              : '');

    // Labels para display nos inputs readonly
    $veiculoLabel  = '';
    $origemLabel   = '';
    $destinoLabel  = '';
    $usuarioLabel  = '';

    if ($selVeiculo && isset($veiculos[$selVeiculo])) {
        $veiculoLabel = $veiculos[$selVeiculo];
    }
    foreach ($estacoes as $e) {
        $eid = is_object($e) ? $e->id : ($e['id'] ?? null);
        $enome = is_object($e) ? $e->nome : ($e['nome'] ?? '');
        if ($eid == $selOrigem) $origemLabel  = $enome;
        if ($eid == $selDestino) $destinoLabel = $enome;
    }
    if (isset($usuariosObj)) {
        foreach ($usuariosObj as $u) {
            if ($u->id == $selUsuario) { $usuarioLabel = $u->nome; break; }
        }
    } else {
        foreach ($usuarios as $uid => $unome) {
            if ($uid == $selUsuario) $usuarioLabel = $unome;
        }
    }
@endphp

@if ($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0 pl-3">
            @foreach(array_unique($errors->all()) as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mv-page">

    {{-- ── Guia de cenários ── --}}
    <div class="mv-guide mb-4">
        <div class="mv-guide-title">
            <i class="fas fa-lightbulb"></i>
            Como preencher? Escolha o cenário mais comum:
        </div>
        <div class="mv-guide-cards">
            <button type="button" class="mv-scenario" data-scenario="transferencia">
                <i class="fas fa-exchange-alt"></i>
                <strong>Transferência entre estações</strong>
                <span>Mover veículo de uma estação para outra</span>
            </button>
            <button type="button" class="mv-scenario" data-scenario="reserva">
                <i class="fas fa-calendar-check"></i>
                <strong>Saída para reserva</strong>
                <span>Veículo saindo para atender uma reserva</span>
            </button>
            <button type="button" class="mv-scenario" data-scenario="manutencao">
                <i class="fas fa-tools"></i>
                <strong>Manutenção / OS</strong>
                <span>Veículo indo para manutenção ou revisão</span>
            </button>
            <button type="button" class="mv-scenario" data-scenario="retorno">
                <i class="fas fa-undo-alt"></i>
                <strong>Retorno de reserva</strong>
                <span>Veículo retornando após entrega ao cliente</span>
            </button>
        </div>
        <div class="mv-guide-hint" id="mvGuideHint" style="display:none"></div>
    </div>

    {{-- ── Card principal ── --}}
    <div class="card shadow-sm border-0">
        <div class="card-header border-0 pb-0 pt-3 px-4">
            <div class="d-flex align-items-center" style="gap:.75rem">
                <div class="mv-header-icon">
                    <i class="fas fa-route"></i>
                </div>
                <div>
                    <h4 class="mb-0">{{ $titulo }}</h4>
                    <p class="text-muted small mb-0">Preencha os 4 passos abaixo para registrar a movimentação.</p>
                </div>
            </div>
        </div>

        <div class="card-body px-4 pb-4 pt-3">
            <form id="formMov" method="POST" action="{{ $rota }}">
                @csrf
                @if($metodo === 'PUT') @method('PUT') @endif

                {{-- Barra de steps --}}
                <div class="mv-wizard-top mb-3">
                    <div class="mv-wizard-steps">
                        <button type="button" class="mv-step is-active" data-mv-step-btn="1">
                            <span class="num">1</span><span class="txt">Tipo & Status</span>
                        </button>
                        <div class="mv-step-line"></div>
                        <button type="button" class="mv-step" data-mv-step-btn="2">
                            <span class="num">2</span><span class="txt">Veículo & Locais</span>
                        </button>
                        <div class="mv-step-line"></div>
                        <button type="button" class="mv-step" data-mv-step-btn="3">
                            <span class="num">3</span><span class="txt">Vínculos</span>
                        </button>
                        <div class="mv-step-line"></div>
                        <button type="button" class="mv-step" data-mv-step-btn="4">
                            <span class="num">4</span><span class="txt">Datas & Obs.</span>
                        </button>
                    </div>
                </div>

                {{-- ════ STEP 1: Tipo & Status ════ --}}
                <div class="mv-panel" data-mv-panel="1">
                    <div class="mv-section-title">Classificação da Movimentação</div>

                    <div class="form-row">
                        <div class="col-lg-5 form-group">
                            <label class="mv-label">
                                Tipo de Movimentação <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" id="btnAbrirModalTipo" title="Consultar tipos">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <select name="tipo_movimentacao_id" id="tipo_movimentacao_id" class="form-control form-control-sm" required>
                                    <option value="">Selecione um tipo…</option>
                                    @foreach($tiposMovimentacao as $id => $nome)
                                        <option value="{{ $id }}" {{ $selTipo == $id ? 'selected' : '' }}>{{ $nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Clique na lupa para ver a descrição de cada tipo</small>
                        </div>
                        <div class="col-lg-5 form-group">
                            <label class="mv-label">
                                Status da Movimentação <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" id="btnAbrirModalStatus" title="Consultar status">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <select name="status_movimentacao_id" id="status_movimentacao_id" class="form-control form-control-sm" required>
                                    <option value="">Selecione um status…</option>
                                    @foreach($statusMovimentacao as $id => $nome)
                                        <option value="{{ $id }}" {{ $selStatus == $id ? 'selected' : '' }}>{{ $nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Clique na lupa para ver a descrição de cada status</small>
                        </div>
                        <div class="col-lg-2 form-group">
                            <label class="mv-label">Status do registro</label>
                            <select name="ativo" class="form-control form-control-sm">
                                <option value="S" {{ old('ativo', $isEdit ? $movimentacao->ativo : 'S') === 'S' ? 'selected' : '' }}>Ativo</option>
                                <option value="N" {{ old('ativo', $isEdit ? $movimentacao->ativo : 'S') === 'N' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="mv-label">Nome / Descrição (opcional)</label>
                            <input type="text" name="nome" class="form-control form-control-sm"
                                   value="{{ old('nome', $isEdit ? $movimentacao->nome : '') }}"
                                   placeholder="Ex: Transferência AGO/2026">
                        </div>
                    </div>
                </div>

                {{-- ════ STEP 2: Veículo & Locais ════ --}}
                <div class="mv-panel d-none" data-mv-panel="2">
                    <div class="mv-section-title">Veículo e Quilometragem</div>
                    <div class="form-row">
                        <div class="col-lg-8 form-group">
                            <label class="mv-label">Veículo</label>
                            <input type="hidden" name="veiculo_id" id="mv_veiculo_id" value="{{ $selVeiculo }}">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" id="btnBuscarVeiculoMov" title="Buscar veículo">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="text" id="mv_veiculo_display" class="form-control form-control-sm"
                                       readonly placeholder="Clique na lupa para buscar…"
                                       value="{{ $veiculoLabel }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger btn-sm {{ $selVeiculo ? '' : 'd-none' }}" id="btnLimparVeiculoMov">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted" id="mv_veiculo_km_hint"></small>
                        </div>
                    </div>

                    <div class="mv-section-title mt-3">Locais de Origem e Destino</div>
                    <div class="form-row">
                        <div class="col-lg-6 form-group">
                            <label class="mv-label">Estação de Origem</label>
                            <input type="hidden" name="local_origem_id" id="mv_origem_id" value="{{ $selOrigem }}">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" data-buscar-estacao="origem" title="Buscar estação de origem">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="text" id="mv_origem_display" class="form-control form-control-sm"
                                       readonly placeholder="Clique na lupa para buscar estação…"
                                       value="{{ $origemLabel }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger btn-sm {{ $selOrigem ? '' : 'd-none' }}" id="btnLimparOrigem">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="mv-label">Estação de Destino</label>
                            <input type="hidden" name="local_destino_id" id="mv_destino_id" value="{{ $selDestino }}">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" data-buscar-estacao="destino" title="Buscar estação de destino">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="text" id="mv_destino_display" class="form-control form-control-sm"
                                       readonly placeholder="Clique na lupa para buscar estação…"
                                       value="{{ $destinoLabel }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger btn-sm {{ $selDestino ? '' : 'd-none' }}" id="btnLimparDestino">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mv-section-title mt-3">Quilometragem</div>
                    <div class="form-row">
                        <div class="col-lg-4 form-group">
                            <label class="mv-label">
                                KM de Saída
                                <span class="mv-km-badge" id="mv_km_veiculo_badge" style="display:none"></span>
                            </label>
                            <input type="number" name="km_saida" id="mv_km_saida" class="form-control form-control-sm"
                                   value="{{ $kmSaida }}" min="0" step="1" placeholder="Ex: 45230">
                            <small class="text-danger" id="mv_km_saida_erro" style="display:none">
                                KM de saída não pode ser menor que o último KM registrado do veículo.
                            </small>
                        </div>
                        <div class="col-lg-4 form-group">
                            <label class="mv-label">KM de Chegada</label>
                            <input type="number" name="km_chegada" id="mv_km_chegada" class="form-control form-control-sm"
                                   value="{{ $kmChegada }}" min="0" step="1" placeholder="Ex: 45890">
                            <small class="text-danger" id="mv_km_chegada_erro" style="display:none">
                                KM de chegada deve ser maior ou igual ao KM de saída.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- ════ STEP 3: Vínculos ════ --}}
                <div class="mv-panel d-none" data-mv-panel="3">
                    <div class="mv-section-title">Usuário Responsável</div>
                    <div class="form-row">
                        <div class="col-lg-6 form-group">
                            <label class="mv-label">Usuário <span class="text-danger">*</span></label>
                            <input type="hidden" name="user_id" id="mv_usuario_id" value="{{ $selUsuario }}" required>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" id="btnBuscarUsuarioMov" title="Buscar usuário">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="text" id="mv_usuario_display" class="form-control form-control-sm"
                                       readonly placeholder="Clique na lupa para buscar…"
                                       value="{{ $usuarioLabel }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger btn-sm {{ $selUsuario ? '' : 'd-none' }}" id="btnLimparUsuarioMov">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mv-section-title mt-3">Reserva e Ordem de Serviço (opcionais)</div>
                    <div class="form-row">
                        <div class="col-lg-6 form-group">
                            <label class="mv-label">Reserva</label>
                            <input type="hidden" name="reserva_id" id="mv_reserva_id" value="{{ $selReserva }}">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" id="btnBuscarReservaMov" title="Buscar reserva">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="text" id="mv_reserva_display" class="form-control form-control-sm"
                                       readonly placeholder="Clique na lupa para buscar…"
                                       value="{{ $selReserva ? 'Reserva #' . $selReserva : '' }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger btn-sm {{ $selReserva ? '' : 'd-none' }}" id="btnLimparReservaMov">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 form-group">
                            <label class="mv-label">Ordem de Serviço</label>
                            <input type="hidden" name="ordem_servico_id" id="mv_os_id" value="{{ $selOs }}">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary" id="btnBuscarOsMov" title="Buscar OS">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <input type="text" id="mv_os_display" class="form-control form-control-sm"
                                       readonly placeholder="Clique na lupa para buscar…"
                                       value="{{ $selOs ? 'OS #' . $selOs : '' }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-danger btn-sm {{ $selOs ? '' : 'd-none' }}" id="btnLimparOsMov">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ════ STEP 4: Datas & Observações ════ --}}
                <div class="mv-panel d-none" data-mv-panel="4">
                    <div class="mv-section-title">Datas</div>
                    <div class="form-row">
                        <div class="col-lg-3 form-group">
                            <label class="mv-label">Data de Registro <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="data_registro" id="mv_data_registro" class="form-control form-control-sm"
                                   value="{{ $dataRegistro }}" required>
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="mv-label">Data de Saída</label>
                            <input type="datetime-local" name="data_saida" id="mv_data_saida" class="form-control form-control-sm"
                                   value="{{ $dataSaida }}"
                                   oninput="validarDatas()">
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="mv-label">Chegada Prevista</label>
                            <input type="datetime-local" name="data_chegada_prevista" id="mv_data_chegada_prevista"
                                   class="form-control form-control-sm"
                                   value="{{ $dataChegadaPrevista }}"
                                   oninput="validarDatas()">
                            <small class="text-danger" id="mv_erro_chegada_prevista" style="display:none">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Deve ser posterior à data de saída.
                            </small>
                        </div>
                        <div class="col-lg-3 form-group">
                            <label class="mv-label">Chegada Real</label>
                            <input type="datetime-local" name="data_chegada_real" id="mv_data_chegada_real"
                                   class="form-control form-control-sm"
                                   value="{{ $dataChegadaReal }}"
                                   oninput="validarDatas()">
                            <small class="text-danger" id="mv_erro_chegada_real" style="display:none">
                                <i class="fas fa-exclamation-triangle mr-1"></i>Deve ser posterior à data de saída.
                            </small>
                        </div>
                    </div>

                    <div class="mv-section-title mt-2">Observações</div>
                    <div class="form-row">
                        <div class="col-12 form-group">
                            <textarea name="observacoes" class="form-control form-control-sm" rows="4"
                                      placeholder="Detalhes adicionais sobre a movimentação…">{{ old('observacoes', $isEdit ? $movimentacao->observacoes : '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Footer do wizard --}}
                <div class="mv-wizard-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" id="mvPrev" disabled>
                        <i class="fas fa-arrow-left mr-1"></i>Anterior
                    </button>
                    <div class="d-flex" style="gap:8px">
                        <button type="button" class="btn btn-primary btn-sm rounded-pill" id="mvNext">
                            Continuar <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                        <button type="submit" id="mvSubmit" class="btn btn-success btn-sm rounded-pill d-none">
                            <i class="fas fa-save mr-1"></i>Salvar movimentação
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Histórico (apenas em edição) ── --}}
    @if($isEdit && isset($movimentacao) && $movimentacao->relationLoaded('historico') && $movimentacao->historico->isNotEmpty())
    <div class="mv-historico-card mt-4">
        <div class="mv-historico-header">
            <div class="mv-historico-icon"><i class="fas fa-history"></i></div>
            <div>
                <strong>Histórico de Alterações</strong>
                <span>{{ $movimentacao->historico->count() }} registro(s)</span>
            </div>
        </div>
        <div class="mv-historico-timeline">
            @foreach($movimentacao->historico as $h)
            @php
                $acaoIcon = match($h->acao) {
                    'create'  => ['icon' => 'fa-plus-circle',    'color' => '#10b981', 'bg' => '#d1fae5'],
                    'concluir'=> ['icon' => 'fa-check-double',   'color' => '#10b981', 'bg' => '#d1fae5'],
                    'update'  => ['icon' => 'fa-pen',            'color' => '#3b82f6', 'bg' => '#dbeafe'],
                    default   => ['icon' => 'fa-circle',         'color' => '#94a3b8', 'bg' => '#f1f5f9'],
                };
                $acaoLabel = match($h->acao) {
                    'create'  => 'Criado',
                    'concluir'=> 'Concluído',
                    'update'  => 'Alterado',
                    default   => ucfirst($h->acao),
                };
            @endphp
            <div class="mv-historico-item">
                <div class="mv-historico-dot" style="background:{{ $acaoIcon['bg'] }};color:{{ $acaoIcon['color'] }}">
                    <i class="fas {{ $acaoIcon['icon'] }}"></i>
                </div>
                <div class="mv-historico-body">
                    <div class="mv-historico-meta">
                        <span class="mv-historico-acao" style="color:{{ $acaoIcon['color'] }}">{{ $acaoLabel }}</span>
                        <span class="mv-historico-user">
                            <i class="fas fa-user fa-xs mr-1"></i>{{ $h->usuario?->nome ?? 'Sistema' }}
                        </span>
                        <span class="mv-historico-data">
                            <i class="fas fa-clock fa-xs mr-1"></i>{{ $h->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    @if($h->observacao)
                    <div class="mv-historico-obs">{{ $h->observacao }}</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════════════ --}}
{{-- COMPONENTES DE MODAL                                    --}}
{{-- ══════════════════════════════════════════════════════ --}}

<x-movimentacao.modal-tipo
    :tipos="$tiposMovimentacaoObj ?? collect()"
    modal-id="modalTipoMov"
/>

<x-movimentacao.modal-status
    :statuses="$statusMovimentacaoObj ?? collect()"
    modal-id="modalStatusMov"
/>

<x-movimentacao.modal-estacao
    :estacoes="$estacoes"
    modal-id="modalEstacaoMov"
/>

<x-movimentacao.modal-usuario
    :usuarios="$usuariosObj ?? collect()"
    modal-id="modalUsuarioMov"
/>

<x-movimentacao.modal-reserva
    modal-id="modalReservaMov"
/>

<x-movimentacao.modal-os
    modal-id="modalOsMov"
/>

{{-- Componente de busca de veículo --}}
@php
    $filtroMarcas  = collect([]);
    $filtroGrupos  = collect([]);
    $filtroTipos   = collect([]);
    $filtroStatus  = collect([]);
@endphp
<x-veiculo.busca-avancada-modal
    prefix="mov"
    title="Buscar Veículo"
    trigger-id="btnBuscarVeiculoMov"
    hidden-input-id="mv_veiculo_id"
    display-input-id="mv_veiculo_display"
    clear-btn-id="btnLimparVeiculoMov"
    :filtro-marcas="$filtroMarcas"
    :filtro-grupos="$filtroGrupos"
    :filtro-tipos="$filtroTipos"
    :filtro-status="$filtroStatus"
/>

@stop

@section('css')
<style>
/* ── Page wrapper ── */
.mv-page {   margin: 0 auto; }

/* ── Guia de cenários ── */
.mv-guide {
    background: linear-gradient(135deg,#f0f4ff,#fafbff);
    border: 1px solid #dde6f5;
    border-radius: 14px;
    padding: 16px 20px;
}
.mv-guide-title {
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #475569;
    margin-bottom: 12px;
}
.mv-guide-title i { color: #f59e0b; margin-right: 6px; }
.mv-guide-cards { display: flex; gap: 10px; flex-wrap: wrap; }
.mv-scenario {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
    background: #fff;
    border: 1px solid #dde6f5;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .78rem;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s, transform .12s;
    text-align: left;
    flex: 1 1 180px;
}
.mv-scenario i { font-size: 1rem; color: var(--c-indigo, #4338ca); margin-bottom: 2px; }
.mv-scenario strong { font-size: .82rem; color: #1e293b; }
.mv-scenario span { color: #64748b; font-size: .72rem; }
.mv-scenario:hover, .mv-scenario.is-active {
    border-color: #4338ca;
    box-shadow: 0 2px 10px rgba(67,56,202,.12);
    transform: translateY(-1px);
}
.mv-scenario.is-active { background: #eef2fb; }
.mv-guide-hint {
    margin-top: 12px;
    padding: 10px 14px;
    background: #fff8e1;
    border-left: 3px solid #f59e0b;
    border-radius: 6px;
    font-size: .82rem;
    color: #78350f;
}

/* ── Header icon ── */
.mv-header-icon {
    width: 44px; height: 44px; border-radius: 10px;
    background: linear-gradient(135deg,#1a2848,#2e4080);
    color: #93c5fd;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}

/* ── Wizard steps ── */
.mv-wizard-top {
    background: #f8fafd;
    border: 1px solid #e2e9f4;
    border-radius: 12px;
    padding: 12px 16px;
}
.mv-wizard-steps {
    display: flex;
    align-items: center;
    gap: 0;
    flex-wrap: wrap;
}
.mv-step-line {
    flex: 1 1 20px;
    height: 2px;
    background: #dde6f5;
    border-radius: 99px;
    min-width: 12px;
}
.mv-step {
    appearance: none;
    border: 1.5px solid #dde6f5;
    background: #fff;
    border-radius: 999px;
    padding: 6px 12px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: .75rem;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}
.mv-step .num {
    width: 20px; height: 20px; border-radius: 50%;
    background: #e2e9f4; color: #64748b;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .72rem; font-weight: 800;
}
.mv-step.is-active {
    background: #1a2848;
    border-color: #1a2848;
    color: #fff;
    box-shadow: 0 4px 12px rgba(26,40,72,.2);
}
.mv-step.is-active .num { background: rgba(255,255,255,.2); color: #fff; }
.mv-step.is-done { border-color: #10b981; color: #10b981; }
.mv-step.is-done .num { background: #d1fae5; color: #10b981; }

/* ── Panels ── */
.mv-panel { padding-top: 6px; }
.mv-section-title {
    font-size: .68rem; font-weight: 700; letter-spacing: .09em;
    text-transform: uppercase; color: #94a3b8;
    border-bottom: 1px solid #eef2fb;
    padding-bottom: 6px; margin-bottom: 14px;
}
.mv-label {
    font-size: .82rem; font-weight: 600; color: #374151;
    margin-bottom: .25rem; display: flex; align-items: center; gap: 6px;
}

/* ── KM badge ── */
.mv-km-badge {
    background: #fef3c7; border: 1px solid #fde68a;
    color: #92400e; font-size: .68rem; font-weight: 700;
    border-radius: 999px; padding: 1px 8px; white-space: nowrap;
}

/* ── Footer ── */
.mv-wizard-footer {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    padding-top: 16px; margin-top: 16px;
    border-top: 1px dashed #e2e9f4;
}

/* ── Histórico ── */
.mv-historico-card {
    background: #fff;
    border: 1px solid #e2e9f4;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(12,20,40,.05);
}
.mv-historico-header {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 18px;
    background: linear-gradient(135deg,#f8fafd,#fff);
    border-bottom: 1px solid #eef2fb;
}
.mv-historico-header strong { font-size: .9rem; display: block; color: #1e293b; }
.mv-historico-header span { font-size: .72rem; color: #94a3b8; }
.mv-historico-icon {
    width: 36px; height: 36px; border-radius: 10px;
    background: #eef2ff; color: #4338ca;
    display: flex; align-items: center; justify-content: center; font-size: .85rem;
    flex-shrink: 0;
}
.mv-historico-timeline {
    padding: 14px 18px;
    display: flex; flex-direction: column; gap: 0;
}
.mv-historico-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 10px 0;
    border-bottom: 1px dashed #f0f4fc;
    position: relative;
}
.mv-historico-item:last-child { border-bottom: none; }
.mv-historico-dot {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .72rem; flex-shrink: 0; margin-top: 1px;
}
.mv-historico-body { flex: 1; min-width: 0; }
.mv-historico-meta {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 3px;
}
.mv-historico-acao { font-size: .78rem; font-weight: 700; }
.mv-historico-user { font-size: .74rem; color: #475569; }
.mv-historico-data { font-size: .72rem; color: #94a3b8; }
.mv-historico-obs { font-size: .78rem; color: #64748b; line-height: 1.4; }
</style>
@stop

@push('js')
<script>
(function() {
'use strict';

const URL_KM_VEICULO = '{{ route("movimentacao.kmVeiculo") }}';
let kmVeiculoAtual   = {{ $kmSaida ? (int)$kmSaida : 'null' }};

// ── Wizard ───────────────────────────────────────────────
const MAX = 4;
const SK  = 'mvWizardStep';

let currentStep = (() => {
    @if($errors->any())
    return 1;
    @endif
    const s = parseInt(localStorage.getItem(SK) || '1', 10);
    return (s >= 1 && s <= MAX) ? s : 1;
})();

function setStep(s, persist = true) {
    s = Math.max(1, Math.min(MAX, s));
    currentStep = s;

    document.querySelectorAll('[data-mv-panel]').forEach(p => {
        const n = parseInt(p.dataset.mvPanel, 10);
        p.classList.toggle('d-none', n !== s);
    });
    document.querySelectorAll('[data-mv-step-btn]').forEach(b => {
        const n = parseInt(b.dataset.mvStepBtn, 10);
        b.classList.toggle('is-active', n === s);
        b.classList.toggle('is-done', n < s);
    });

    const prev   = document.getElementById('mvPrev');
    const next   = document.getElementById('mvNext');
    const submit = document.getElementById('mvSubmit');
    if (prev)   prev.disabled    = s <= 1;
    if (next)   next.classList.toggle('d-none', s >= MAX);
    if (submit) submit.classList.toggle('d-none', s < MAX);

    if (persist) localStorage.setItem(SK, String(s));
}

function validateCurrentStep() {
    const panel = document.querySelector(`[data-mv-panel="${currentStep}"]`);
    if (!panel) return true;

    if (currentStep === 2) {
        const kmS = parseFloat(document.getElementById('mv_km_saida').value);
        const kmC = parseFloat(document.getElementById('mv_km_chegada').value);
        const erroS = document.getElementById('mv_km_saida_erro');
        const erroC = document.getElementById('mv_km_chegada_erro');
        let ok = true;

        if (kmVeiculoAtual !== null && !isNaN(kmS) && kmS < kmVeiculoAtual) {
            erroS.style.display = 'block'; ok = false;
        } else { erroS.style.display = 'none'; }

        if (!isNaN(kmS) && !isNaN(kmC) && kmC > 0 && kmC < kmS) {
            erroC.style.display = 'block'; ok = false;
        } else { erroC.style.display = 'none'; }

        if (!ok) return false;
    }

    if (currentStep === 3) {
        const uid = document.getElementById('mv_usuario_id').value;
        if (!uid) { alert('Selecione um usuário responsável.'); return false; }
    }

    const fields = Array.from(panel.querySelectorAll('input, select, textarea'))
        .filter(f => f.willValidate && !f.disabled && f.type !== 'hidden');
    const invalid = fields.find(f => !f.checkValidity());
    if (invalid) { invalid.reportValidity(); return false; }

    return true;
}

document.getElementById('mvPrev')?.addEventListener('click', () => setStep(currentStep - 1));
document.getElementById('mvNext')?.addEventListener('click', () => {
    if (validateCurrentStep()) setStep(currentStep + 1);
});
document.querySelectorAll('[data-mv-step-btn]').forEach(b => {
    b.addEventListener('click', () => {
        const target = parseInt(b.dataset.mvStepBtn, 10);
        if (target > currentStep && !validateCurrentStep()) return;
        setStep(target);
    });
});

document.getElementById('formMov')?.addEventListener('submit', e => {
    const kmS = parseFloat(document.getElementById('mv_km_saida').value);
    const kmC = parseFloat(document.getElementById('mv_km_chegada').value);
    if (kmVeiculoAtual !== null && !isNaN(kmS) && kmS < kmVeiculoAtual) {
        e.preventDefault(); setStep(2);
        document.getElementById('mv_km_saida_erro').style.display = 'block';
        return;
    }
    if (!isNaN(kmS) && !isNaN(kmC) && kmC > 0 && kmC < kmS) {
        e.preventDefault(); setStep(2);
        document.getElementById('mv_km_chegada_erro').style.display = 'block';
        return;
    }
    localStorage.removeItem(SK);
});

setStep(currentStep, false);

// ── Guia de cenários ─────────────────────────────────────
const hints = {
    transferencia: '📍 <strong>Transferência entre estações:</strong> Selecione o tipo de movimentação correspondente à transferência. Defina a estação de <strong>Origem</strong> (onde o veículo está) e o <strong>Destino</strong>. Informe os KMs de saída. Vincule ao usuário responsável pela operação.',
    reserva:       '📋 <strong>Saída para reserva:</strong> Selecione o tipo "Saída/Reserva". Vincule o veículo e a reserva correspondente no passo 3. Informe o KM de saída. A chegada pode ser preenchida posteriormente.',
    manutencao:    '🔧 <strong>Manutenção / OS:</strong> Selecione o tipo correspondente à manutenção. Vincule a Ordem de Serviço no passo 3. Registre o KM de saída para controle de quilometragem.',
    retorno:       '🔄 <strong>Retorno de reserva:</strong> Selecione o tipo "Retorno/Devolução". Informe o KM de chegada. Vincule a reserva de origem no passo 3 para fechar o ciclo.',
};
document.querySelectorAll('.mv-scenario').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.mv-scenario').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        const hint = document.getElementById('mvGuideHint');
        hint.innerHTML = hints[btn.dataset.scenario] || '';
        hint.style.display = 'block';
    });
});

// ── Modal: Tipo ──────────────────────────────────────────
document.getElementById('btnAbrirModalTipo')?.addEventListener('click', () => {
    filtrarModalTipos('modalTipoMov');
    $('#modalTipoMov').modal('show');
});

// ── Modal: Status ────────────────────────────────────────
document.getElementById('btnAbrirModalStatus')?.addEventListener('click', () => {
    filtrarModalStatus('modalStatusMov');
    $('#modalStatusMov').modal('show');
});

// ── Modal: Estação ────────────────────────────────────────
let estacaoAlvo = 'origem';

function abrirModalEstacao(alvo) {
    estacaoAlvo = alvo;
    var titulo = document.getElementById('modalEstacaoMov_titulo');
    var sub    = document.getElementById('modalEstacaoMov_subtitulo');
    if (titulo) titulo.textContent = 'Estação de ' + (alvo === 'origem' ? 'Origem' : 'Destino');
    if (sub)    sub.textContent    = 'Selecione a estação de ' + (alvo === 'origem' ? 'origem' : 'destino');
    var busca = document.getElementById('modalEstacaoMov_busca');
    if (busca) { busca.value = ''; filtrarModalEstacao('modalEstacaoMov'); }
    $('#modalEstacaoMov').modal('show');
    setTimeout(function() { if (busca) busca.focus(); }, 300);
}

document.querySelectorAll('[data-buscar-estacao]').forEach(function(b) {
    b.addEventListener('click', function() { abrirModalEstacao(this.dataset.buscarEstacao); });
});

document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-selecionar-estacao');
    if (!btn) return;
    var id = btn.dataset.id, nome = btn.dataset.nome;
    if (estacaoAlvo === 'origem') {
        document.getElementById('mv_origem_id').value      = id;
        document.getElementById('mv_origem_display').value = nome;
        document.getElementById('btnLimparOrigem').classList.remove('d-none');
    } else {
        document.getElementById('mv_destino_id').value      = id;
        document.getElementById('mv_destino_display').value = nome;
        document.getElementById('btnLimparDestino').classList.remove('d-none');
    }
    $('#modalEstacaoMov').modal('hide');
});

document.getElementById('btnLimparOrigem')?.addEventListener('click', function() {
    document.getElementById('mv_origem_id').value = '';
    document.getElementById('mv_origem_display').value = '';
    this.classList.add('d-none');
});
document.getElementById('btnLimparDestino')?.addEventListener('click', function() {
    document.getElementById('mv_destino_id').value = '';
    document.getElementById('mv_destino_display').value = '';
    this.classList.add('d-none');
});

// ── Veículo KM ───────────────────────────────────────────
const hiddenVeiculo = document.getElementById('mv_veiculo_id');
const kmHint        = document.getElementById('mv_veiculo_km_hint');
const kmBadge       = document.getElementById('mv_km_veiculo_badge');

function atualizarKmVeiculo(vid) {
    if (!vid) { if(kmHint) kmHint.textContent=''; if(kmBadge) kmBadge.style.display='none'; return; }
    fetch(URL_KM_VEICULO + '?veiculo_id=' + vid)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.kms !== null && data.kms !== undefined) {
                kmVeiculoAtual = parseFloat(data.kms);
                var fmt = new Intl.NumberFormat('pt-BR').format(kmVeiculoAtual);
                if(kmHint)  kmHint.textContent  = 'KM atual do veículo: ' + fmt + ' km';
                if(kmBadge) { kmBadge.textContent = 'KM atual: ' + fmt; kmBadge.style.display = ''; }
                var inp = document.getElementById('mv_km_saida');
                if (inp && !inp.value) inp.value = kmVeiculoAtual;
            } else {
                kmVeiculoAtual = null;
                if(kmHint)  kmHint.textContent = '';
                if(kmBadge) kmBadge.style.display = 'none';
            }
        }).catch(function() {});
}

if (hiddenVeiculo) {
    new MutationObserver(function() { atualizarKmVeiculo(hiddenVeiculo.value); })
        .observe(hiddenVeiculo, { attributes: true, attributeFilter: ['value'] });
    if (hiddenVeiculo.value) atualizarKmVeiculo(hiddenVeiculo.value);
}

document.getElementById('btnBuscarVeiculoMov')?.addEventListener('click', function() {
    $('#mov_modalBuscarVeiculo').modal('show');
});

document.getElementById('mv_km_saida')?.addEventListener('input', function() {
    var v = parseFloat(this.value);
    var erro = document.getElementById('mv_km_saida_erro');
    if(erro) erro.style.display = (kmVeiculoAtual !== null && !isNaN(v) && v < kmVeiculoAtual) ? 'block' : 'none';
});
document.getElementById('mv_km_chegada')?.addEventListener('input', function() {
    var chegada = parseFloat(this.value);
    var saida   = parseFloat(document.getElementById('mv_km_saida').value);
    var erro    = document.getElementById('mv_km_chegada_erro');
    if(erro) erro.style.display = (!isNaN(chegada) && !isNaN(saida) && chegada < saida) ? 'block' : 'none';
});

// ── Modal: Usuário ────────────────────────────────────────
document.getElementById('btnBuscarUsuarioMov')?.addEventListener('click', function() {
    ['_nome','_email','_id'].forEach(function(s){
        var el = document.getElementById('modalUsuarioMov'+s);
        if(el) el.value='';
    });
    filtrarModalUsuario('modalUsuarioMov');
    $('#modalUsuarioMov').modal('show');
    setTimeout(function() {
        var el = document.getElementById('modalUsuarioMov_nome');
        if(el) el.focus();
    }, 300);
});

document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-selecionar-usuario');
    if (!btn) return;
    document.getElementById('mv_usuario_id').value      = btn.dataset.id;
    document.getElementById('mv_usuario_display').value = btn.dataset.nome;
    var clr = document.getElementById('btnLimparUsuarioMov');
    if (clr) clr.classList.remove('d-none');
    $('#modalUsuarioMov').modal('hide');
});

document.getElementById('btnLimparUsuarioMov')?.addEventListener('click', function() {
    document.getElementById('mv_usuario_id').value = '';
    document.getElementById('mv_usuario_display').value = '';
    this.classList.add('d-none');
});

// ── Modal: Reserva ────────────────────────────────────────
document.getElementById('btnBuscarReservaMov')?.addEventListener('click', function() {
    $('#modalReservaMov').modal('show');
});

document.getElementById('btnLimparReservaMov')?.addEventListener('click', function() {
    document.getElementById('mv_reserva_id').value = '';
    document.getElementById('mv_reserva_display').value = '';
    this.classList.add('d-none');
});

// ── Modal: OS ─────────────────────────────────────────────
document.getElementById('btnBuscarOsMov')?.addEventListener('click', function() {
    $('#modalOsMov').modal('show');
});

document.getElementById('btnLimparOsMov')?.addEventListener('click', function() {
    document.getElementById('mv_os_id').value = '';
    document.getElementById('mv_os_display').value = '';
    this.classList.add('d-none');
});

// ── Validação de datas ────────────────────────────────────
window.validarDatas = function() {
    var saida    = document.getElementById('mv_data_saida')?.value;
    var prevista = document.getElementById('mv_data_chegada_prevista');
    var real     = document.getElementById('mv_data_chegada_real');
    var erroPrev = document.getElementById('mv_erro_chegada_prevista');
    var erroReal = document.getElementById('mv_erro_chegada_real');

    if (prevista && erroPrev) {
        var invalido = saida && prevista.value && prevista.value <= saida;
        erroPrev.style.display = invalido ? '' : 'none';
        prevista.classList.toggle('is-invalid', !!invalido);
    }
    if (real && erroReal) {
        var invalido2 = saida && real.value && real.value <= saida;
        erroReal.style.display = invalido2 ? '' : 'none';
        real.classList.toggle('is-invalid', !!invalido2);
    }
};

// Bloqueia submit se datas inválidas
document.getElementById('formMov')?.addEventListener('submit', function(e) {
    validarDatas();
    var prevista = document.getElementById('mv_data_chegada_prevista');
    var real     = document.getElementById('mv_data_chegada_real');
    if ((prevista && prevista.classList.contains('is-invalid')) ||
        (real     && real.classList.contains('is-invalid'))) {
        e.preventDefault();
        setStep(4);
        return;
    }
}, true); // capture para rodar antes do listener KM

// Validação inline no step 4
var _origValidate = validateCurrentStep;
// (já coberta pela validação de submit acima)

})();
</script>
@endpush
