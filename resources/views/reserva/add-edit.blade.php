@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
    <link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/css/bootstrap-select.min.css">

@stop

@section('content')
    @php
        $preRequisitosReserva = $preRequisitosReserva ?? [
            'qtd_estacoes' => 0,
            'qtd_grupos' => 0,
            'qtd_modelos' => 0,
            'qtd_veiculos_total' => 0,
            'qtd_estacoes_com_veiculo' => 0,
            'detalhes_veiculos_estacao' => [],
            'faltas' => [],
            'pode_reservar' => true,
        ];
        $moedaEmpresa = strtoupper($moedaEmpresa ?? 'BRL');
        $simboloMoedaEmpresa = $simboloMoedaEmpresa ?? ($moedaEmpresa === 'USD' ? '$' : ($moedaEmpresa === 'EUR' ? '€' : 'R$'));
        $qtdEstacoes = (int) ($preRequisitosReserva['qtd_estacoes'] ?? 0);
        $qtdGrupos = (int) ($preRequisitosReserva['qtd_grupos'] ?? 0);
        $qtdModelos = (int) ($preRequisitosReserva['qtd_modelos'] ?? 0);
        $qtdVeiculosTotal = (int) ($preRequisitosReserva['qtd_veiculos_total'] ?? 0);
        $qtdEstacoesComVeiculo = (int) ($preRequisitosReserva['qtd_estacoes_com_veiculo'] ?? 0);
        $detalhesVeiculosEstacao = $preRequisitosReserva['detalhes_veiculos_estacao'] ?? [];
        $linkCadastroEstacao = \Illuminate\Support\Facades\Route::has('estacao.create') ? route('estacao.create') : null;
        $linkCadastroGrupo = \Illuminate\Support\Facades\Route::has('grupo_frota.create') ? route('grupo_frota.create') : null;
        $linkCadastroModelo = \Illuminate\Support\Facades\Route::has('modelo.create') ? route('modelo.create') : null;
        $linkCadastroVeiculo = \Illuminate\Support\Facades\Route::has('veiculo.create') ? route('veiculo.create') : null;
        $dataInicioSelecionada = old('data_inicio', $reserva->data_inicio ?? '');
        $dataFimSelecionada = old('data_fim', $reserva->data_fim ?? '');
        $dataInicioDataExibicao = $dataInicioSelecionada ? \Carbon\Carbon::parse($dataInicioSelecionada)->format('d/m/Y') : '';
        $dataInicioHoraSelecionada = $dataInicioSelecionada ? \Carbon\Carbon::parse($dataInicioSelecionada)->format('H:i') : '';
        $dataFimExibicao = $dataFimSelecionada ? \Carbon\Carbon::parse($dataFimSelecionada)->format('d/m/Y H:i') : '';
    @endphp
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <span>{{ session('warning') }}</span>
        </div>
    @endif
    @if (!empty($resetWizardStep) && $resetWizardStep)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                try {
                    localStorage.setItem('reservaWizardStep', '1');
                    var btn = document.querySelector('[data-reserva-step-btn="1"]');
                    if (btn) {
                        btn.click();
                    }
                } catch (e) {
                    // ignore
                }
            });
        </script>
    @endif
    <div class="reserva-top-header mb-3">
        <div class="reserva-top-header__badges">
            <span class="reserva-chip">Estações: {{ $preRequisitosReserva['qtd_estacoes'] }}</span>
            <span class="reserva-chip">Grupos: {{ $preRequisitosReserva['qtd_grupos'] }}</span>
            <span class="reserva-chip">Modelos: {{ $preRequisitosReserva['qtd_modelos'] }}</span>
            <span class="reserva-chip">Veículos: {{ $qtdVeiculosTotal }}</span>
            <span class="reserva-chip reserva-chip--info">Moeda: {{ $moedaEmpresa }}</span>
        </div>
        @if (!$preRequisitosReserva['pode_reservar'])
            <div class="reserva-top-header__warning">
                <div class="reserva-top-header__warning-title">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Pré-requisitos pendentes para reservar
                </div>
                @foreach ($preRequisitosReserva['faltas'] as $falta)
                    <div class="reserva-top-header__warning-item">{{ $falta }}</div>
                @endforeach

                @if (!empty($estacoesBloqueadasReserva))
                    <div class="reserva-top-header__station-explanation">
                        <div class="reserva-top-header__station-explanation-title">
                            Você tem acesso ao perfil, mas estas estações não entram na reserva atual:
                        </div>
                        <div class="reserva-top-header__station-explanation-list">
                            @foreach ($estacoesBloqueadasReserva as $estacao)
                                <div class="reserva-top-header__station-explanation-item">
                                    <strong>{{ $estacao['nome'] }}</strong>
                                    <span>{{ $estacao['motivo'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="reserva-top-header__links">
                    @if ($qtdEstacoes === 0 && $linkCadastroEstacao)
                        <a href="{{ $linkCadastroEstacao }}" class="reserva-link-action" target="_blank" rel="noopener noreferrer">Cadastrar estação</a>
                    @endif
                    @if ($qtdGrupos === 0 && $linkCadastroGrupo)
                        <a href="{{ $linkCadastroGrupo }}" class="reserva-link-action" target="_blank" rel="noopener noreferrer">Cadastrar grupo de veículos</a>
                    @endif
                    @if ($qtdModelos === 0 && $linkCadastroModelo)
                        <a href="{{ $linkCadastroModelo }}" class="reserva-link-action" target="_blank" rel="noopener noreferrer">Cadastrar modelo</a>
                    @endif
                    @if ($qtdVeiculosTotal === 0 && $linkCadastroVeiculo)
                        <a href="{{ $linkCadastroVeiculo }}" class="reserva-link-action" target="_blank" rel="noopener noreferrer">Cadastrar veículo</a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- <x-pre-requisitos-alerta
        title="Disponibilidade de veículos por estação"
        :chips="[
            ['label' => 'Veículos nas estações', 'value' => $qtdVeiculosTotal],
            ['label' => 'Estações com veículos', 'value' => $qtdEstacoesComVeiculo],
        ]"
        :details="$detalhesVeiculosEstacao"
        :missing-items="[]"
        :action-links="[]"
    /> --}}

    <div class="reserva-page">
    <div class="d-flex justify-content-between flex-wrap reserva-layout">
        <div class="col-xl-9 col-lg-9 col-md-9 col-sm-12 col-xs-12 card p-2 reserva-main-card">
            <div class="card-body">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" id="formularioReserva" rowClass="row" novalidate>

                    <div class="col-12 reserva-wizard-top">
                        <div class="reserva-wizard-steps" role="tablist" aria-label="Passos da reserva">
                            <button type="button" class="reserva-wizard-step is-active" data-reserva-step-btn="1">
                                <span class="num">1</span><span class="txt">Datas</span>
                            </button>
                            <div class="div"></div>
                            <button type="button" class="reserva-wizard-step" data-reserva-step-btn="2">
                                <span class="num">2</span><span class="txt">Veículo</span>
                            </button>
                            <div class="div"></div>
                            <button type="button" class="reserva-wizard-step" data-reserva-step-btn="3">
                                <span class="num">3</span><span class="txt">Opcionais</span>
                            </button>
                            <div class="div"></div>
                            <button type="button" class="reserva-wizard-step" data-reserva-step-btn="4">
                                <span class="num">4</span><span class="txt">Cliente</span>
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Dica: avance pelos passos e finalize em "Cliente" para salvar.
                        </small>
                    </div>

                    <div class="col-12" data-reserva-step="1">
                        <x-section-division-header-form class="reserva-section-title">Estações e Datas</x-section-division-header-form>
                    </div>

                    <!-- Estações e Datas -->
                    <div class="col-md-6" data-reserva-step="1">
                        <div class="form-group">
                                <x-form-select id="estacao_retirada_id" name="estacao_retirada_id" label="Estação de Retirada"
                                :options="$estacoesRetirada" placeholder="Selecione uma estação" :selected="old(
                                    'estacao_retirada_id',
                                    isset($reserva->estacao_retirada_id) ? $reserva->estacao_retirada_id : null,
                                )"
                                class="handleGetGrupoVeiculos form-control form-control-sm js-selectpicker" data-live-search="true" data-size="8" required />
                            @if ($qtdEstacoes === 0)
                                <small class="text-danger d-block mt-1">Nenhuma estação disponível para seu acesso.</small>
                            @endif
                            <small id="estacao_veiculos_feedback" class="d-block mt-1 text-muted">
                                Veículos disponíveis na estação selecionada: --
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6" data-reserva-step="1">
                        <div class="form-group mb-0">
                            <label class="mb-1">Data e Hora de Retirada <span class="text-danger">*</span></label>
                            <div class="d-flex">
                                <div class="flex-grow-1 mr-2">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="text" id="data_inicio_flatpickr" class="form-control form-control-sm"
                                            placeholder="Selecione a estação" readonly
                                            value="{{ $dataInicioSelecionada ? \Carbon\Carbon::parse($dataInicioSelecionada)->format('d/m/Y') : '' }}">
                                    </div>
                                    <small id="data_inicio_dias_feedback" class="d-block text-muted mt-1" style="font-size:0.75rem">Selecione a estação para ver os dias.</small>
                                </div>
                                <div style="min-width:130px">
                                    <select id="data_inicio_hora" class="form-control form-control-sm" disabled>
                                        <option value="">Selecione a data</option>
                                    </select>
                                    <small id="data_inicio_hora_feedback" class="d-block text-muted mt-1" style="font-size:0.75rem">Escolha uma data.</small>
                                    <small id="data_inicio_taxa_aviso" class="d-none d-block mt-1" style="font-size:.75rem;background:#fef3c7;border:1px solid #f59e0b;border-radius:5px;padding:2px 7px;color:#92400e;font-weight:600"><i class="fas fa-exclamation-triangle mr-1"></i>Fora do expediente — taxa adicional aplicável.</small>
                                </div>
                            </div>
                            <input type="hidden" id="data_inicio_data" value="{{ $dataInicioSelecionada ? \Carbon\Carbon::parse($dataInicioSelecionada)->format('Y-m-d') : '' }}">
                            <input type="hidden" id="data_inicio" name="data_inicio" value="{{ $dataInicioSelecionada }}">
                        </div>
                    </div>
                    <div class="col-md-6" data-reserva-step="1">
                        <div class="form-group">
                            <x-form-select id="estacao_devolucao_id" name="estacao_devolucao_id"
                                label="Estação de Devolução" :options="$estacoesDevolucao" placeholder="Selecione uma estação"
                                :selected="old(
                                    'estacao_devolucao_id',
                                    isset($reserva->estacao_devolucao_id) ? $reserva->estacao_devolucao_id : null,
                                )" class="handleGetGrupoVeiculos form-control form-control-sm js-selectpicker" data-live-search="true" data-size="8" required />
                            @if ($qtdEstacoes === 0)
                                <small class="text-danger d-block mt-1">Cadastre/libere estação para continuar.</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6" data-reserva-step="1">
                        <div class="form-group mb-0">
                            <label class="mb-1">Data e Hora de Devolução <span class="text-danger">*</span></label>
                            <div class="d-flex">
                                <div class="flex-grow-1 mr-2">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="text" id="data_fim_flatpickr" class="form-control form-control-sm"
                                            placeholder="Selecione a estação" readonly
                                            value="{{ $dataFimSelecionada ? \Carbon\Carbon::parse($dataFimSelecionada)->format('d/m/Y') : '' }}">
                                    </div>
                                    <small id="data_fim_dias_feedback" class="d-block text-muted mt-1" style="font-size:0.75rem">Selecione a estação para ver os dias.</small>
                                </div>
                                <div style="min-width:130px">
                                    <select id="data_fim_hora_fp" class="form-control form-control-sm" disabled>
                                        <option value="">Selecione a data</option>
                                    </select>
                                    <small id="data_fim_hora_feedback" class="d-block text-muted mt-1" style="font-size:0.75rem">Escolha uma data.</small>
                                    <small id="data_fim_taxa_aviso" class="d-none d-block mt-1" style="font-size:.75rem;background:#fef3c7;border:1px solid #f59e0b;border-radius:5px;padding:2px 7px;color:#92400e;font-weight:600"><i class="fas fa-exclamation-triangle mr-1"></i>Fora do expediente — taxa adicional aplicável.</small>
                                </div>
                            </div>
                            <input type="hidden" id="data_fim_data" value="{{ $dataFimSelecionada ? \Carbon\Carbon::parse($dataFimSelecionada)->format('Y-m-d') : '' }}">
                            <input type="hidden" id="data_fim_hora" value="{{ $dataFimSelecionada ? \Carbon\Carbon::parse($dataFimSelecionada)->format('H:i') : '' }}">
                            {{-- mantido para compatibilidade com reserva.js --}}
                            <input type="hidden" id="data_fim_display" value="{{ $dataFimExibicao ?? '' }}">
                            <input type="hidden" id="data_fim" name="data_fim" value="{{ $dataFimSelecionada }}">
                        </div>
                    </div>


                    <div class="col-12" data-reserva-step="2">
                        <x-section-division-header-form class="reserva-section-title">Veículo</x-section-division-header-form>
                    </div>

                    <div class="col-md-8" data-reserva-step="2">
                        <div class="form-group">
                            <div class="form-group">
                                <label for="grupo_frota_id" class="d-flex align-items-center justify-content-between mb-1">
                                    <span>Grupo de Veículos</span>
                                    <button type="button" class="btn btn-xs btn-outline-secondary" id="btn_buscar_grupo"
                                        data-toggle="modal" data-target="#modalPesquisaGrupo" title="Buscar grupo de veículos">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                </label>
                                <select id="grupo_frota_id"
                                    class="form-control form-control-sm js-selectpicker"
                                    data-live-search="true"
                                    data-size="8"
                                    data-show-subtext="true"
                                    disabled>
                                    <option value="" disabled selected>Selecione pelo botão Buscar...</option>
                                    @foreach ($gruposVeiculo as $grupo)
                                        <option value="{{ $grupo->id }}" data-subtext="{{ $simboloMoedaEmpresa }} {{ number_format((float) $grupo->valor_diario, 2, ',', '.') }}"
                                            {{ old('grupo_frota_id', isset($reserva->grupo_frota_id) ? $reserva->grupo_frota_id : null) == $grupo->id ? 'selected' : '' }}>
                                            {{ $grupo->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="grupo_frota_id" id="grupo_frota_id_hidden" value="{{ old('grupo_frota_id', $reserva->grupo_frota_id ?? '') }}">
                                @if ($qtdGrupos === 0)
                                    <small class="text-danger d-block mt-1">Nenhum grupo de veículos ativo disponível.</small>
                                @endif
                            </div>

                        </div>
                    </div>
                     <div class="col-md-3" data-reserva-step="2">
                        <label for="cupom_desconto" class="mb-1">Cupom de Desconto</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="cupom_desconto" name="cupom_desconto" maxlength="7"
                                value="{{ old('cupom_desconto', $reserva->cupomDesconto->codigo ?? '') }}"
                                class="form-control form-control-sm text-uppercase"
                                placeholder="Ex: DESC10" autocomplete="off" />
                            <div class="input-group-append">
                                <button type="button" id="btn_verificar_cupom" class="btn btn-outline-secondary btn-sm" title="Verificar cupom">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <small id="cupom_feedback" class="d-block mt-1" style="font-size:.75rem;min-height:1rem"></small>
                    </div>
                    {{-- <div class="col-md-2 d-flex align-items-center">

                        <a class="btn btn-xs btn-info " data-toggle="modal" data-target="#modalPesquisa">
                            <i class="fa fa-search mr-1"></i>
                            Pesquisar
                        </a>
                    </div> --}}
                    @if(!empty($reservaSelecionarModelo))
                    <div class="col-md-4 pb-2" data-reserva-step="2">
                        @if (isset($reserva))
                            <x-form-select name="modelo_id" label="Modelo" :options="$modelos" :selected="old('modelo_id', isset($reserva->modelos) ? $reserva->modelos->id : null)"
                                id="modelo" onchange="getMarcaESegmento(this.value, $(this).find('option:selected').text())" placeholder="Selecione um modelo"
                                class="form-control form-control-sm js-selectpicker" data-live-search="true" data-size="8" required />
                        @else
                            <x-form-select name="modelo_id" label="Modelo" id="modelo" :options="[]"
                                :selected="old('modelo_id')"
                                onchange="getMarcaESegmento(this.value)" placeholder="Selecione primeiro o grupo de frotas"
                                class="form-control form-control-sm js-selectpicker" data-live-search="true" data-size="8" disabled required />
                        @endif
                        @if ($qtdModelos === 0)
                            <small class="text-danger d-block mt-1">Nenhum modelo ativo disponivel para reserva.</small>
                        @endif
                        <small id="modelo_disponibilidade_feedback" class="text-warning d-block mt-1 d-none"></small>
                    </div>
                    <div class="col-md-4" data-reserva-step="2">
                        <x-form-input type="text" class="form-control form-control-sm" disabled id="marca"
                            name="marca" label="Marca"
                            value="{{ old('marca', isset($marcas) && isset($marcas->nome) ? $marcas->nome : '') }}" />
                    </div>
                    <div class="col-md-4" data-reserva-step="2">
                        <x-form-input type="text" class="form-control form-control-sm" disabled id="segmento"
                            name="segmento" label="Segmento"
                            value="{{ old('segmento', isset($segmentos) && isset($segmentos->nome) ? $segmentos->nome : '') }} " />
                    </div>
                    @else
                    {{-- Sem seleção de modelo: campos ocultos para manter compatibilidade de submissão --}}
                    <input type="hidden" name="modelo_id" id="modelo" value="{{ old('modelo_id', isset($reserva) ? ($reserva->modelos?->id ?? '') : '') }}">
                    <input type="hidden" name="marca" id="marca" value="{{ old('marca', '') }}">
                    <input type="hidden" name="segmento" id="segmento" value="{{ old('segmento', '') }}">
                    <small id="modelo_disponibilidade_feedback" class="text-warning d-block mt-1 d-none col-12" style="padding:0 15px"></small>
                    @endif


                    <div class="col-12" data-reserva-step="3">
                        <x-section-division-header-form class="reserva-section-title">Opcionais</x-section-division-header-form>
                    </div>
                    <div id="opcionaisWrapper" data-reserva-step="3" class="w-100">
                        <div id="opcionais" class="" 
                        style="padding:1em; 
                                background: linear-gradient(
                                    90deg,
                                    color-mix(in srgb, var(--empresa-cor-primaria, #1e293b) 90%, #000 10%) 0%,
                                    color-mix(in srgb, var(--empresa-cor-terciaria, #334155) 90%, #000 10%) 100%
                                );
                                width:100%">
                            <div class="alert alert-light col-md-8 d-flex align-items-center text-xs py-1 mt-1">
                                <i class="fa-solid fa-circle-info me-2"></i>
                                <span>Selecione um grupo de veículos para visualizar os opcionais disponíveis.</span>
                            </div>
                        </div>
                    </div>
                    {{-- @if (count($tipoOpcional) > 0) --}}
                    {{-- @component('reserva.components.opcionais', ['tiposOpcionais' => $tipoOpcional])
                    @endcomponent --}}
                    {{-- @endif --}}
                    {{-- <div id="opcionais"class="d-flex justify-content-center flex-wrap w-100">
                        @if (!isset($reserva))
                            <div class="alert alert-light col-md-8 d-flex align-items-center text-xs py-1 mt-1">
                                <i class="fa-solid fa-circle-info me-2"></i>
                            <span>Selecione um grupo de veiculos e um modelo para visualizar os opcionais disponiveis.</span>
                            </div>
                        @endif
                        @if (isset($reserva))

                            @foreach ($tiposOpcionais as $tipoOpcional)
                                @if ($tipoOpcional->opcionais->count() > 1)
                                    <div class="card mt-1">
                                        <div class="card-body col-md-12">
                                            <p class="text-xs text-bold mb-0">{{ $tipoOpcional->nome }}</p>
                                            <hr class="my-1">
                                            <div class="">
                                                @foreach ($tipoOpcional->opcionais as $opcional)
                                                    <div class="btn btn-outline-secondary btn-xs mx-1">
                                                        <input type="checkbox" name="opcionais[]"
                                                            value="{{ $opcional->id }}" class="btn-check"
                                                            id="btn-check-{{ $opcional->id }}-outlined"
                                                            {{ isset($reserva->opcionais) && in_array($opcional->id, $opcionaisSelecionados) ? 'checked' : '' }}
                                                            autocomplete="off">
                                                        <label class="m-0"
                                                            for="btn-check-{{ $opcional->id }}-outlined">{{ $opcional->nome }}</label><br>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div> --}}
                    <br>


                    <div class="col-12" data-reserva-step="4">
                        <x-section-division-header-form class="reserva-section-title">Informações Pessoais</x-section-division-header-form>
                    </div>


                    <!-- Identificações -->
                    @php
                        $userIdSelecionado = old('user_id', $reserva->user_id ?? null);
                        $userNomeSelecionado = $userIdSelecionado && isset($users[$userIdSelecionado])
                            ? $users[$userIdSelecionado]
                            : ($reserva->usuario->nome ?? '');
                    @endphp
                    <div class="col-md-12" data-reserva-step="4">
                        <input type="hidden" name="user_id" id="user_id" value="{{ $userIdSelecionado }}" required>

                        {{-- Card de cliente não selecionado --}}
                        <div id="clienteEmptyState" class="{{ $userIdSelecionado ? 'd-none' : '' }}">
                            <div style="border:2px dashed #cbd5e1;border-radius:14px;padding:2rem 1.5rem;text-align:center;background:#f8fafc;">
                                <div style="width:56px;height:56px;border-radius:50%;background:#e2e8f0;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                                    <i class="fas fa-user" style="font-size:1.5rem;color:#94a3b8;"></i>
                                </div>
                                <p class="mb-1 font-weight-bold" style="color:#374151;font-size:.95rem;">Nenhum cliente selecionado</p>
                                <p class="mb-3" style="color:#94a3b8;font-size:.82rem;">Busque um cliente existente ou cadastre um novo</p>
                                <div class="d-flex justify-content-center" style="gap:.6rem;flex-wrap:wrap;">
                                    <button type="button" class="btn btn-primary btn-sm" id="btnBuscarUsuarioReserva">
                                        <i class="fas fa-search mr-1"></i>Buscar cliente
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" id="btnNovoUsuarioModal">
                                        <i class="fas fa-user-plus mr-1"></i>Novo cliente
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Card de cliente selecionado --}}
                        <div id="clienteSelectedCard" class="{{ $userIdSelecionado ? '' : 'd-none' }}" style="border:1.5px solid #bfdbfe;border-radius:14px;background:linear-gradient(135deg,#eff6ff 0%,#f0fdf4 100%);padding:1.25rem 1.5rem;">
                            <div class="d-flex align-items-center justify-content-between" style="gap:1rem;">
                                <div class="d-flex align-items-center" style="gap:.9rem;min-width:0;">
                                    <div id="clienteAvatar" style="width:46px;height:46px;border-radius:50%;background:#3b82f6;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem;font-weight:700;color:#fff;letter-spacing:.02em;">
                                        {{ strtoupper(substr($userNomeSelecionado ?: 'U', 0, 1)) }}
                                    </div>
                                    <div style="min-width:0;">
                                        <div id="clienteNomeDisplay" class="font-weight-bold" style="color:#1e3a5f;font-size:.97rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            {{ $userNomeSelecionado ?: '—' }}
                                        </div>
                                        <div style="color:#64748b;font-size:.78rem;margin-top:.15rem;">
                                            <i class="fas fa-id-badge mr-1"></i><span id="clienteIdDisplay">{{ $userIdSelecionado ? '#'.$userIdSelecionado : '' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center" style="gap:.5rem;flex-shrink:0;">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnBuscarUsuarioReserva2" title="Trocar cliente">
                                        <i class="fas fa-exchange-alt mr-1"></i>Trocar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnLimparUsuarioReserva" title="Remover cliente">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="text" id="user_display" class="d-none" value="{{ $userNomeSelecionado }}">
                    </div>

            </div>


            <!-- Status e Cancelamento -->
            {{-- <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <x-form-select name="status" label="Status" :options="$status" placeholder="Selecione um status"
                                :selected="isset($reserva->status) ? $reserva->status : null"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <x-form-input type="datetime-local" class="form-control form-control-sm" id="data_cancelamento" name="data_cancelamento"
                                label="Data de Cancelamento" value="{{ old('data_cancelamento', $reserva->data_cancelamento ?? '') }}" />
                        </div>
                    </div>
                </div> --}}

            <div class="col-12 reserva-wizard-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="reservaWizardPrev">
                    <i class="fas fa-arrow-left mr-1"></i>Voltar
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="reservaWizardNext">
                    Continuar <i class="fas fa-arrow-right ml-1"></i>
                </button>
                <button type="submit" id="btnSalvarReserva"
                    class="btn btn-success btn-sm reserva-submit-btn d-none"
                    {{ !$preRequisitosReserva['pode_reservar'] ? 'disabled' : '' }}>
                    <i class="fas fa-check mr-1"></i>Salvar reserva
                </button>
            </div>

            <input type="hidden" id="reservaHidden" name="reservaHidden">
            </x-form>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-xs-12 reserva-sidebar ">
            @component('reserva.components.card_valores')
            @endcomponent
        </div>
    </div>
    </div>
    <div class="modal fade" id="modalAgendaReserva" tabindex="-1" role="dialog" aria-labelledby="modalAgendaReservaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold" id="modalAgendaReservaLabel">
                        <i class="fas fa-calendar-alt mr-2"></i>Selecionar data e horário
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>Escolha um dia e depois um horário dentro do expediente configurado para a estação.</span>
                    </div>
                    <input type="hidden" id="agenda_tipo">
                    <input type="hidden" id="agenda_estacao_id">
                    <input type="hidden" id="agenda_valor_selecionado">
                    <div id="agenda_status" class="text-muted mb-3"></div>
                    <div class="row">
                        <div class="col-lg-5 mb-3 mb-lg-0" id="agenda_dias_card">
                            <div class="card h-100">
                                <div class="card-header bg-light py-2">
                                    <strong>Dias disponíveis</strong>
                                </div>
                                <div class="card-body">
                                    <div id="agenda_dias" class="list-group"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7" id="agenda_horarios_card">
                            <div class="card h-100">
                                <div class="card-header bg-light py-2">
                                    <strong>Horários disponíveis</strong>
                                </div>
                                <div class="card-body">
                                    <div id="agenda_horarios" class="d-flex flex-wrap"></div>
                                    <div id="agenda_horarios_vazio" class="text-muted d-none">
                                        Selecione um dia para visualizar os horários.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmarAgenda" disabled>Confirmar</button>
                </div>
            </div>
        </div>
    </div>
    @component('reserva.components.modal-pesquisa')
    @endcomponent

    {{-- Modal busca de usuario --}}
    <div class="modal fade" id="modalBuscarUsuarioReserva" tabindex="-1" role="dialog" aria-labelledby="modalBuscarUsuarioReservaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold" id="modalBuscarUsuarioReservaLabel">
                        <i class="fas fa-user-search mr-2"></i>Buscar Usuario
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    {{-- Filtros --}}
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header py-2">
                            <h6 class="card-title mb-0"><i class="fas fa-filter mr-1"></i> Filtros</h6>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body pb-1">
                            <div class="row">
                                <div class="col-md-3 form-group mb-2">
                                    <label class="small mb-1">Nome</label>
                                    <input type="text" id="fu_nome" class="form-control form-control-sm" placeholder="Nome do usuario">
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="small mb-1">E-mail</label>
                                    <input type="text" id="fu_email" class="form-control form-control-sm" placeholder="email@exemplo.com">
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="small mb-1">CPF / Documento</label>
                                    <input type="text" id="fu_documento" class="form-control form-control-sm" placeholder="000.000.000-00">
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="small mb-1">Telefone</label>
                                    <input type="text" id="fu_telefone" class="form-control form-control-sm" placeholder="(00) 00000-0000">
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="small mb-1">Perfil / Regra</label>
                                    <select id="fu_role_id" class="form-control form-control-sm">
                                        <option value="">Todos os perfis</option>
                                        @foreach($tiposUsuario as $id => $nome)
                                            <option value="{{ $id }}">{{ $nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 form-group mb-2">
                                    <label class="small mb-1">Nasc. de</label>
                                    <input type="date" id="fu_nascimento_de" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2 form-group mb-2">
                                    <label class="small mb-1">Nasc. atÃ©</label>
                                    <input type="date" id="fu_nascimento_ate" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2 form-group mb-2">
                                    <label class="small mb-1">Cadastro de</label>
                                    <input type="date" id="fu_cadastro_de" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2 form-group mb-2">
                                    <label class="small mb-1">Cadastro atÃ©</label>
                                    <input type="date" id="fu_cadastro_ate" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2 d-flex align-items-end form-group mb-2">
                                    <button type="button" class="btn btn-primary btn-sm btn-block" id="btnAplicarFiltroUsuario">
                                        <i class="fas fa-search mr-1"></i> Buscar
                                    </button>
                                </div>
                                <div class="col-md-2 d-flex align-items-end form-group mb-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimparFiltroUsuario">
                                        <i class="fas fa-eraser mr-1"></i> Limpar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="modalUsuarioLoading" class="text-center d-none py-3">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Buscando...
                    </div>
                    <div id="modalUsuarioResultados"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNovoUsuarioReserva" tabindex="-1" role="dialog" aria-labelledby="modalNovoUsuarioReservaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header border-0 pb-1">
                    <h5 class="modal-title font-weight-bold" id="modalNovoUsuarioReservaLabel">
                        <i class="fas fa-user-plus mr-1"></i>
                        Cadastrar usuario para reserva
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-2">
                    <div id="modalNovoUsuarioAlert" class="alert alert-danger d-none" role="alert"></div>
                    <div class="alert alert-info py-2 mb-3">
                        Perfil de acesso definido automaticamente como <strong>Cliente</strong>.
                    </div>

                    <form id="formNovoUsuarioReserva">
                        @csrf
                        <input type="hidden" name="cadastro_reserva" value="1">
                        <ul class="nav nav-pills reserva-user-modal__tabs" id="novoUsuarioReservaTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="tab-dados-pessoais-tab" data-toggle="pill" href="#tab-dados-pessoais" role="tab" aria-controls="tab-dados-pessoais" aria-selected="true">
                                    <i class="fas fa-user mr-1"></i> Dados Pessoais
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="tab-contato-endereco-tab" data-toggle="pill" href="#tab-contato-endereco" role="tab" aria-controls="tab-contato-endereco" aria-selected="false">
                                    <i class="fas fa-phone-alt mr-1"></i> Contato e Endereço
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="tab-carta-conducao-tab" data-toggle="pill" href="#tab-carta-conducao" role="tab" aria-controls="tab-carta-conducao" aria-selected="false">
                                    <i class="fas fa-id-card mr-1"></i> Carta de Condução
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content reserva-user-modal__tab-content" id="novoUsuarioReservaTabsContent">
                            <div class="tab-pane fade show active" id="tab-dados-pessoais" role="tabpanel" aria-labelledby="tab-dados-pessoais-tab">
                                <div class="reserva-user-modal__section-title">Informações Pessoais</div>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label for="novo_usuario_nome">Nome <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_nome" name="nome" value="{{ old('nome') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="novo_usuario_email">E-mail <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control form-control-sm" id="novo_usuario_email" name="email" value="{{ old('email') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="novo_usuario_sexo">Sexo <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm js-selectpicker" id="novo_usuario_sexo" name="sexo" data-size="6" required>
                                            <option value="">Selecione...</option>
                                            <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                            <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Feminino</option>
                                            <option value="O" {{ old('sexo') == 'O' ? 'selected' : '' }}>Outro</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="novo_usuario_data_nascimento">Data de Nascimento <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" id="novo_usuario_data_nascimento" name="data_nascimento" value="{{ old('data_nascimento') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="novo_usuario_local_nascimento">Local de Nascimento <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_local_nascimento" name="local_nascimento" value="{{ old('local_nascimento') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="novo_usuario_numero_identificacao">Número de Identificação <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_numero_identificacao" name="numero_identificacao" value="{{ old('numero_identificacao') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-contato-endereco" role="tabpanel" aria-labelledby="tab-contato-endereco-tab">
                                <div class="reserva-user-modal__section-title">Contato e Endereço</div>
                                <div class="form-row">
                                    <div class="col-md-3">
                                        <label for="novo_usuario_telefone">Telefone <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_telefone" name="telefone" value="{{ old('telefone') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="novo_usuario_celular">Celular <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_celular" name="celular" value="{{ old('celular') }}" placeholder="(99) 99999-9999" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="novo_usuario_pais_sigla">País <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm js-selectpicker" id="novo_usuario_pais_sigla" name="pais_sigla" data-live-search="true" data-size="8" required>
                                            <option value="">Selecione...</option>
                                            @foreach (($paises ?? []) as $siglaPais => $nomePais)
                                                <option value="{{ $siglaPais }}" {{ old('pais_sigla') == $siglaPais ? 'selected' : '' }}>{{ $nomePais }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="novo_usuario_zip_code">Zip Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_zip_code" name="zip_code" value="{{ old('zip_code') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="novo_usuario_cidade">Cidade <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_cidade" name="cidade" value="{{ old('cidade') }}" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="novo_usuario_endereco">Endereço <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_endereco" name="endereco" value="{{ old('endereco') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-carta-conducao" role="tabpanel" aria-labelledby="tab-carta-conducao-tab">
                                <div class="reserva-user-modal__section-title">Carta de Condução</div>
                                <div class="form-row">
                                    <div class="col-md-4">
                                        <label for="novo_usuario_numero_carta_conducao">Número da Carta de Condução <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_numero_carta_conducao" name="numero_carta_conducao" value="{{ old('numero_carta_conducao') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="novo_usuario_local_emissao_carta_conducao">Local de Emissão <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_local_emissao_carta_conducao" name="local_emissao_carta_conducao" value="{{ old('local_emissao_carta_conducao') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="novo_usuario_pais_emissao_carta_conducao">País de Emissão <span class="text-danger">*</span></label>
                                        <select class="form-control form-control-sm js-selectpicker" id="novo_usuario_pais_emissao_carta_conducao" name="pais_emissao_carta_conducao" data-live-search="true" data-size="8" required>
                                            <option value="">Selecione...</option>
                                            @foreach (($paises ?? []) as $siglaPais => $nomePais)
                                                <option value="{{ $siglaPais }}" {{ old('pais_emissao_carta_conducao') == $siglaPais ? 'selected' : '' }}>{{ $nomePais }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="novo_usuario_data_emissao_carta_conducao">Data de Emissão <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" id="novo_usuario_data_emissao_carta_conducao" name="data_emissao_carta_conducao" value="{{ old('data_emissao_carta_conducao') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="novo_usuario_data_validade_carta_conducao">Data de Validade</label>
                                        <input type="date" class="form-control form-control-sm" id="novo_usuario_data_validade_carta_conducao" name="data_validade_carta_conducao" value="{{ old('data_validade_carta_conducao') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarNovoUsuarioReserva">
                        <i class="fas fa-save mr-1"></i>Salvar usuário
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        /* Flatpickr overrides */
        .flatpickr-calendar { font-family: inherit; box-shadow: 0 4px 20px rgba(0,0,0,.15); border-radius: 8px; }
        .flatpickr-day.selected, .flatpickr-day.selected:hover { background: var(--empresa-cor-primaria, #0f172a); border-color: var(--empresa-cor-primaria, #0f172a); }
        .flatpickr-day.flatpickr-disabled { color: #ccc !important; background: #f8f9fa; text-decoration: line-through; }
        #data_inicio_flatpickr, #data_fim_flatpickr,
        #data_inicio_hora, #data_fim_hora_fp { background: #fff; cursor: pointer; }
        #data_inicio_hora[disabled], #data_fim_hora_fp[disabled] { cursor: not-allowed; background: #f4f6f9; }
        #data_inicio_hora.taxa-extra-sel, #data_fim_hora_fp.taxa-extra-sel {
            border-color: #f59e0b !important;
            border-width: 2px;
            background: #fffbeb;
            color: #92400e;
            font-weight: 600;
        }
        #data_inicio_flatpickr.is-invalid, #data_fim_flatpickr.is-invalid { border-color: #dc3545; }

        .reserva-page {
            --reserva-border: color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 35%, #dbe3ef 65%);
            --reserva-title: var(--empresa-cor-primaria, #0f172a);
        }

        .reserva-wizard-top {
            border: 1px solid var(--reserva-border);
            border-radius: 14px;
            padding: 12px 12px 10px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            margin-bottom: 10px;
        }

        .reserva-wizard-steps {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .reserva-wizard-steps .div {
            flex: 1 1 20px;
            height: 2px;
            background: color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 22%, #e5edf7 78%);
            border-radius: 999px;
            min-width: 12px;
        }
        .reserva-wizard-step {
            appearance: none;
            border: 1px solid color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 30%, #dbe3ef 70%);
            background: #fff;
            border-radius: 999px;
            padding: 8px 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            cursor: pointer;
            transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease, background .12s ease, color .12s ease;
        }
        .reserva-wizard-step .num {
            width: 22px;
            height: 22px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 12%, #ffffff 88%);
            color: color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 88%, #111827 12%);
            border: 1px solid color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 25%, #dbe3ef 75%);
        }
        .reserva-wizard-step.is-active {
            background: var(--empresa-cor-primaria, #2563eb);
            border-color: var(--empresa-cor-primaria, #2563eb);
            color: var(--empresa-cor-primaria-contraste, #fff);
            text-shadow: 0 1px 2px rgba(0,0,0,.25);
            box-shadow: 0 10px 22px color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 22%, transparent 78%);
        }
        .reserva-wizard-step.is-active .txt {
            color: var(--empresa-cor-primaria-contraste, #fff);
            text-shadow: 0 1px 2px rgba(0,0,0,.35);
            font-weight: 800;
        }
        .reserva-wizard-step.is-active .num {
            background: rgba(0,0,0,.18);
            color: #fff;
            border-color: rgba(0,0,0,.12);
            text-shadow: 0 1px 2px rgba(0,0,0,.4);
        }

        .reserva-wizard-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 10px 0 0;
            margin-top: 10px;
            border-top: 1px dashed color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 28%, #e5edf7 72%);
        }
        .reserva-wizard-footer .btn {
            border-radius: 999px;
            font-weight: 700;
        }

        /* ===== Minha Reserva (sidebar) ===== */
        .minha-reserva-card {
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 22%, #e5edf7 78%);
            background: #fff;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
        }
        .minha-reserva-card__header {
            padding: 16px 16px 14px;
            background: linear-gradient(
                135deg,
                color-mix(in srgb, var(--empresa-cor-primaria, #1e293b) 86%, #000 14%) 0%,
                color-mix(in srgb, var(--empresa-cor-terciaria, #334155) 86%, #000 14%) 100%
            );
            color: var(--empresa-cor-primaria-contraste, #fff);
            position: relative;
        }
        .minha-reserva-card__header::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.07) 1px, transparent 1px);
            background-size: 18px 18px;
            pointer-events: none;
        }
        .minha-reserva-card__title {
            display: flex;
            gap: 12px;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        .minha-reserva-card__icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.14);
            border: 1px solid rgba(255,255,255,.18);
            color: var(--empresa-cor-primaria-contraste, #fff);
            backdrop-filter: blur(8px);
        }
        .minha-reserva-card__eyebrow {
            font-size: 10px;
            letter-spacing: .18em;
            text-transform: uppercase;
            opacity: .85;
            font-weight: 800;
            margin-bottom: 2px;
        }
        .minha-reserva-card__name {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: -.02em;
            line-height: 1.15;
        }
        .minha-reserva-card__section {
            padding: 14px 16px 6px;
        }
        .minha-reserva-card__section + .minha-reserva-card__section {
            border-top: 1px solid #eef2f7;
        }
        .minha-reserva-card__section-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            letter-spacing: .14em;
            text-transform: uppercase;
            font-weight: 900;
            color: color-mix(in srgb, var(--empresa-cor-primaria, #0f172a) 82%, #0f172a 18%);
            margin-bottom: 10px;
        }
        .minha-reserva-card__section-title i {
            color: color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 70%, #0f172a 30%);
        }
        .minha-reserva-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 8px;
        }
        .minha-reserva-list__item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px solid #eef2f7;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }
        .minha-reserva-list__label {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
        }
        .minha-reserva-list__value {
            font-size: 12px;
            font-weight: 900;
            color: #0f172a;
        }
        .minha-reserva-list__empty {
            padding: 10px 12px;
            border-radius: 14px;
            border: 1px dashed #e2e8f0;
            color: #64748b;
            font-size: 12px;
            background: #f8fafc;
        }
        .minha-reserva-qty {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 8px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 900;
            border: 1px solid color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 18%, #e2e8f0 82%);
            background: color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 10%, #ffffff 90%);
            color: color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 82%, #0f172a 18%);
        }
        .minha-reserva-card__total {
            padding: 14px 16px 16px;
            border-top: 1px solid #eef2f7;
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: flex-end;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }
        .minha-reserva-card__total-label {
            font-size: 12px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: .12em;
        }
        .minha-reserva-card__total-hint {
            font-size: 12px;
            color: #64748b;
            font-weight: 700;
        }
        .minha-reserva-card__total-value {
            font-size: 22px;
            font-weight: 1000;
            letter-spacing: -.02em;
            color: #0f172a;
            text-align: right;
        }
        .minha-reserva-card__total-sub {
            margin-top: 4px;
            font-size: 12px;
            color: #334155;
            text-align: right;
            font-weight: 700;
        }
        .minha-reserva-card__total-sub .muted {
            color: #64748b;
            font-weight: 700;
        }
        .content-header h1 {
            background: linear-gradient(
                90deg,
                color-mix(in srgb, var(--empresa-cor-primaria, #1e293b) 90%, #000 10%) 0%,
                color-mix(in srgb, var(--empresa-cor-terciaria, #334155) 90%, #000 10%) 100%
            );
            color: var(--empresa-cor-primaria-contraste, #fff) !important;
            border-radius: 10px;
            padding: 8px 12px;
            display: inline-block;
            font-weight: 700;
        }
        .reserva-top-header {
            border: 1px solid var(--reserva-border);
            border-radius: 12px;
            padding: 10px 12px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        }
        .reserva-top-header__badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .reserva-chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid #d7e2ef;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            background: #fff;
        }
        .reserva-chip--info {
            color: #fff;
            background: var(--empresa-cor-primaria, #2563eb);
            border-color: var(--empresa-cor-primaria, #2563eb);
        }
        .reserva-top-header__warning {
            margin-top: 10px;
            border: 1px dashed #f3c56a;
            border-radius: 10px;
            background: #ffffff;
            padding: 10px;
        }
        .reserva-top-header__warning-title {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
            font-size: 13px;
        }
        .reserva-top-header__warning-item {
            font-size: 12px;
            color: #111827;
            margin-bottom: 4px;
        }
        .reserva-top-header__station-explanation {
            margin-top: 8px;
            border: 1px solid #dbe6f3;
            border-radius: 10px;
            background: #fff;
            padding: 10px;
        }
        .reserva-top-header__station-explanation-title {
            color: #0f172a;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .reserva-top-header__station-explanation-list {
            display: grid;
            gap: 6px;
        }
        .reserva-top-header__station-explanation-item {
            padding: 8px 10px;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #e5edf7;
        }
        .reserva-top-header__station-explanation-item strong {
            display: block;
            color: #0f172a;
            font-size: 12px;
            margin-bottom: 2px;
        }
        .reserva-top-header__station-explanation-item span {
            display: block;
            color: #111827;
            font-size: 12px;
            line-height: 1.35;
        }
        .reserva-top-header__links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        .reserva-link-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #f59e0b;
            color: #92400e;
            background: linear-gradient(180deg, #fffbeb 0%, #fef3c7 100%);
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none !important;
            box-shadow: 0 4px 10px rgba(245, 158, 11, .16);
            transition: all .15s ease;
        }
        .reserva-link-action:hover {
            color: #fff8eb;
            background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
            border-color: #d97706;
            box-shadow: 0 8px 18px rgba(217, 119, 6, .24);
            transform: translateY(-1px);
        }

        .reserva-page .reserva-main-card {
            border: 1px solid var(--reserva-border);
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
            background: #fff;
        }

        .reserva-page .reserva-section-title p {
            background: linear-gradient(
                90deg,
                color-mix(in srgb, var(--empresa-cor-primaria, #1e293b) 85%, #000 15%) 0%,
                color-mix(in srgb, var(--empresa-cor-terciaria, #334155) 85%, #000 15%) 100%
            ) !important;
            color: #fff !important;
            border-radius: 10px;
            font-size: 11px !important;
            text-transform: uppercase;
            letter-spacing: .05em;
            font-weight: 700;
            padding: 7px 10px;
            margin-top: 10px;
            margin-bottom: 10px !important;
        }

        .reserva-page .form-group label {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
        }

        .reserva-page .form-control {
            border: 1px solid var(--reserva-border);
            border-radius: 10px !important;
            min-height: 36px;
            box-shadow: none !important;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .reserva-page .form-control:focus {
            border-color: color-mix(in srgb, var(--empresa-cor-primaria, #3b82f6) 65%, #60a5fa 35%) !important;
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--empresa-cor-primaria, #3b82f6) 24%, transparent 76%) !important;
        }

        .reserva-page #opcionais .alert {
            border-radius: 12px;
            border: 1px dashed #bfd3ea;
            background: #f8fbff;
            color: #334155;
        }

        .reserva-user-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .reserva-user-actions .btn {
            border-radius: 999px;
            font-weight: 700;
            padding: 5px 11px;
            font-size: .76rem;
        }

        .reserva-user-modal__section-title {
            font-size: .86rem;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: .01em;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .reserva-user-modal__tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .reserva-user-modal__tabs .nav-link {
            border: 1px solid #cdd9e8;
            border-radius: 10px;
            color: #1e293b;
            background: #fff;
            font-size: .84rem;
            font-weight: 700;
            line-height: 1.15rem;
            padding: 6px 12px;
            transition: all .15s ease;
        }

        .reserva-user-modal__tabs .nav-link:hover {
            border-color: color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 45%, #cdd9e8 55%);
            color: color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 85%, #111827 15%);
        }

        .reserva-user-modal__tabs .nav-link.active {
            color: #fff;
            background: var(--empresa-cor-primaria, #2563eb);
            border-color: var(--empresa-cor-primaria, #2563eb);
            box-shadow: 0 8px 18px color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 22%, transparent 78%);
        }

        .reserva-user-modal__tab-content {
            min-height: 270px;
        }

        .reserva-user-modal__estacoes-wrap {
            border: 1px solid #dbe6f3;
            border-radius: 12px;
            padding: 10px;
            background: #f9fbfe;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 8px;
            max-height: 220px;
            overflow: auto;
        }

        .reserva-user-modal__estacao-check {
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #d7e3f1;
            border-radius: 10px;
            padding: 8px 10px;
            background: #fff;
            margin: 0;
            cursor: pointer;
            transition: border-color .15s ease, background-color .15s ease;
        }

        .reserva-user-modal__estacao-check:hover {
            border-color: #98b7de;
            background: #f7fbff;
        }

        .reserva-user-modal__estacao-check input[type="checkbox"] {
            accent-color: var(--empresa-cor-primaria, #2563eb);
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
        }

        .reserva-user-modal__estacao-check span {
            font-size: .84rem;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.2;
        }

        #modalNovoUsuarioReserva .modal-content {
            border: 1px solid #dbe5f1;
            border-radius: 14px;
        }

        #modalNovoUsuarioReserva .modal-header {
            background: linear-gradient(120deg, #f8fbff 0%, #eef3ff 100%);
            border-radius: 14px 14px 0 0;
        }

        #modalNovoUsuarioReserva .form-control {
            border-radius: 10px !important;
            min-height: 36px;
        }

        #modalNovoUsuarioReserva .bootstrap-select > .dropdown-toggle {
            min-height: 36px;
            border-radius: 10px !important;
        }

        #modalNovoUsuarioAlert ul {
            margin: 0;
            padding-left: 18px;
        }

        .reserva-page .reserva-submit-btn {
            border-radius: 10px;
            font-weight: 700;
            letter-spacing: .02em;
            padding: 9px 20px;
            background: #16a34a !important;
            border-color: #15803d !important;
            color: #fff !important;
            box-shadow: 0 8px 20px rgba(22, 163, 74, .35);
        }
        .reserva-page .reserva-submit-btn:hover {
            background: #15803d !important;
            border-color: #166534 !important;
        }

        .reserva-page .reserva-sidebar .card {
            border: 1px solid var(--reserva-border);
            border-radius: 14px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .08);
            position: sticky;
            top: 88px;
            overflow: hidden;
        }

        .reserva-page .reserva-sidebar .card .card-header {
            background: linear-gradient(
                120deg,
                color-mix(in srgb, var(--empresa-cor-primaria, #0f172a) 88%, #000 12%) 0%,
                color-mix(in srgb, var(--empresa-cor-terciaria, #1e293b) 88%, #000 12%) 100%
            );
            color: #fff;
            border-bottom: 0;
        }

        .reserva-page .reserva-sidebar .card .card-title {
            color: #fff;
            margin-bottom: 0;
            font-size: 15px;
        }

        @media (max-width: 1199px) {
            .reserva-page .reserva-sidebar .card {
                position: static;
                margin-top: 12px;
            }
        }

        .reserva-page .select2-container {
            width: 100% !important;
        }

        .reserva-page .select2-container--default .select2-selection--single {
            height: 36px;
            border: 1px solid var(--reserva-border);
            border-radius: 10px;
            padding-top: 4px;
        }

        .reserva-page .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .reserva-page .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #111827 !important;
            font-weight: 600;
        }
        .reserva-page .select2-results__option {
            color: #111827;
            font-weight: 500;
        }

        .reserva-page .select2-container--default.select2-container--focus .select2-selection--single,
        .reserva-page .select2-container--default.select2-container--open .select2-selection--single {
            border-color: color-mix(in srgb, var(--empresa-cor-primaria, #3b82f6) 65%, #60a5fa 35%);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--empresa-cor-primaria, #3b82f6) 20%, transparent 80%);
        }

        .select2-dropdown {
            border: 1px solid var(--reserva-border);
            border-radius: 10px;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--empresa-cor-primaria, #2563eb) !important;
            color: #fff !important;
        }

        .reserva-page .bootstrap-select,
        .reserva-page .bootstrap-select > .dropdown-toggle {
            width: 100% !important;
        }

        .reserva-page .bootstrap-select > .dropdown-toggle {
            height: 36px;
            border: 1px solid color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 35%, #9fb2c8 65%) !important;
            border-radius: 10px;
            background: #fff;
            color: #111827;
            font-weight: 600;
            padding: 6px 28px 6px 12px;
            display: flex;
            align-items: center;
            box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 10%, transparent 90%);
        }

        .reserva-page .bootstrap-select > .dropdown-toggle:focus,
        .reserva-page .bootstrap-select.show > .dropdown-toggle {
            border-color: color-mix(in srgb, var(--empresa-cor-primaria, #3b82f6) 65%, #60a5fa 35%);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--empresa-cor-primaria, #3b82f6) 20%, transparent 80%);
            outline: none !important;
        }

        .reserva-page .bootstrap-select > .dropdown-toggle.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, .12);
        }

        .reserva-page .bootstrap-select .dropdown-menu {
            border: 1px solid var(--reserva-border);
            border-radius: 10px;
            padding: 6px;
        }

        .reserva-page .bootstrap-select .dropdown-item {
            border-radius: 8px;
            color: #111827;
            font-weight: 500;
            font-size: .82rem;
        }

        .reserva-page .bootstrap-select .dropdown-item .text {
            font-size: .82rem;
        }

        .reserva-page .bootstrap-select .dropdown-item:hover {
            background-color: #1e293b;
            color: #fff;
        }
        .reserva-page .bootstrap-select .dropdown-item.active,
        .reserva-page .bootstrap-select .dropdown-item:active {
            background-color: #0f172a !important;
            color: #fff !important;
        }
        .reserva-page .bootstrap-select .dropdown-item.selected,
        .reserva-page .bootstrap-select .dropdown-item.selected.active {
            background-color: #0f172a !important;
            color: #fff !important;
            font-weight: 700;
        }

        .reserva-page .bootstrap-select .bs-searchbox input {
            border-radius: 8px;
            border: 1px solid var(--reserva-border);
        }

        @media (max-width: 991px) {
            .reserva-page .reserva-layout > [class*='col-'] {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .reserva-user-modal__tabs .nav-item {
                flex: 1 1 calc(50% - 8px);
            }

            .reserva-user-modal__tabs .nav-link {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 575px) {
            .reserva-user-modal__tabs .nav-item {
                flex: 1 1 100%;
            }
        }
    </style>
@stop
@section('js')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'reserva-add-edit';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.reserva-top-header', popover: { title: 'Pré-requisitos da reserva', description: 'Antes de criar uma reserva, o sistema verifica se você tem estações, grupos de frota, modelos e veículos cadastrados. Caso esteja faltando algo, um alerta aparecerá aqui.', side: 'bottom' } },
        { element: '.reserva-wizard-top', popover: { title: 'Formulário em etapas', description: 'O cadastro de reserva é dividido em etapas. Complete cada passo antes de avançar para o próximo.', side: 'bottom' } },
        { element: '[data-reserva-step-btn]', popover: { title: 'Navegação entre etapas', description: 'Use estes botões para navegar entre as etapas do formulário. Etapas concluídas ficam marcadas.', side: 'bottom' } },
        { element: '#cliente_id', popover: { title: 'Cliente', description: 'Selecione o cliente para esta reserva. O sistema buscará pelo nome ou e-mail.', side: 'right' } },
        { element: '#estacao_retirada_id', popover: { title: 'Estação de retirada', description: 'Escolha onde o cliente irá retirar o veículo.', side: 'right' } },
        { element: '#data_inicio', popover: { title: 'Data e hora de início', description: 'Informe a data e hora de retirada do veículo. O sistema calculará automaticamente a duração e o valor total.', side: 'right' } },
        { element: '.reserva-side-card', popover: { title: 'Resumo da reserva', description: 'Este painel lateral exibe o resumo em tempo real: veículo, período, valor diário e total calculado.', side: 'left' } },
    ];

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } }).catch(() => {});
    }

    function getAvailableSteps() {
        return steps.filter(function (step) {
            if (!step || !step.element) return false;

            return String(step.element)
                .split(',')
                .map(function (selector) { return selector.trim(); })
                .filter(Boolean)
                .some(function (selector) {
                    try {
                        return !!document.querySelector(selector);
                    } catch (error) {
                        return false;
                    }
                });
        });
    }

    function buildTour() {
        if (typeof window.driver === 'undefined' || !window.driver.js) return null;

        var availableSteps = getAvailableSteps();
        if (!availableSteps.length) return null;

        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            progressText: 'Passo @{{current}} de @{{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { if (!_pulou) marcarFeito(); },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btnTour = document.createElement('button');
                btnTour.className = 'drv-skip-btn drv-skip-tour-btn'; btnTour.type = 'button'; btnTour.textContent = 'Pular tour';
                btnTour.addEventListener('click', function () { _pulou = true; marcarFeito(); driver.destroy(); });
                var btnStep = document.createElement('button');
                btnStep.className = 'drv-skip-btn drv-skip-step-btn'; btnStep.type = 'button'; btnStep.textContent = 'Pular passo';
                btnStep.addEventListener('click', function () { driver.moveNext(); });
                var nav = popover.footer.querySelector('.driver-popover-navigation-btns');
                if (nav) { nav.prepend(btnStep); }
                popover.footer.prepend(btnTour);
            },
            steps: availableSteps,
        });
    }

    function startTour() {
        var tour = buildTour();
        if (!tour) return;
        setTimeout(function () { tour.drive(); }, 350);
    }

    window.restartPageTour = function () {
        localStorage.removeItem(CACHE_KEY);
        fetch(RESETAR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .catch(() => {})
            .then(() => startTour());
    };

    fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.feito && !forceTour) { localStorage.setItem(CACHE_KEY, '1'); return; }
            startTour();
        })
        .catch(() => {
            if (localStorage.getItem(CACHE_KEY) && !forceTour) return;
            startTour();
        });
})();
</script>

    <!-- Select2 / SelectPicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="{{ asset('js/sweetAlert.min.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/js/bootstrap-select.min.js"></script>
    <script src="{{ asset('js/reserva/reserva.js') }}"></script>
    <script>
        if (window.jQuery && jQuery.fn.selectpicker && jQuery.fn.selectpicker.Constructor) {
            jQuery.fn.selectpicker.Constructor.BootstrapVersion = '4';
        }

        @php
            $errosReservaHtml = '<ul class="text-left mb-0">';
            foreach ($errors->all() as $erro) {
                $errosReservaHtml .= '<li>' . e($erro) . '</li>';
            }
            $errosReservaHtml .= '</ul>';
        @endphp

        window.reservaCurrency = @json($moedaEmpresa);
        window.reservaCurrencySymbol = @json($simboloMoedaEmpresa);
        const podeReservar = @json((bool) ($preRequisitosReserva['pode_reservar'] ?? true));
        const isEdit = @json(isset($reserva) && $reserva !== null);
        const mensagemPreRequisitos = @json(
            !empty($preRequisitosReserva['faltas'] ?? [])
                ? 'Não é possível reservar ainda. ' . implode(' ', $preRequisitosReserva['faltas'])
                : 'Não é possível reservar ainda.',
        );
        const rotaCriarUsuario = @json(route('usuario.store'));
        const rotaUsuariosSelect = @json(route('usuario.selectOptions'));
        const mapaCamposAbaNovoUsuario = {
            nome: '#tab-dados-pessoais-tab',
            email: '#tab-dados-pessoais-tab',
            sexo: '#tab-dados-pessoais-tab',
            data_nascimento: '#tab-dados-pessoais-tab',
            local_nascimento: '#tab-dados-pessoais-tab',
            numero_identificacao: '#tab-dados-pessoais-tab',
            role_id: '#tab-dados-pessoais-tab',
            telefone: '#tab-contato-endereco-tab',
            celular: '#tab-contato-endereco-tab',
            pais_sigla: '#tab-contato-endereco-tab',
            zip_code: '#tab-contato-endereco-tab',
            cidade: '#tab-contato-endereco-tab',
            endereco: '#tab-contato-endereco-tab',
            numero_carta_conducao: '#tab-carta-conducao-tab',
            local_emissao_carta_conducao: '#tab-carta-conducao-tab',
            pais_emissao_carta_conducao: '#tab-carta-conducao-tab',
            data_emissao_carta_conducao: '#tab-carta-conducao-tab',
            data_validade_carta_conducao: '#tab-carta-conducao-tab',
        };

        function mostrarErroReserva(titulo, mensagem) {
            const tituloFinal = titulo || 'Atenção!';
            const mensagemFinal = mensagem || 'Não foi possível concluir a ação.';

            if (window.Swal && typeof Swal.fire === 'function') {
                Swal.fire({
                    icon: 'error',
                    title: tituloFinal,
                    html: mensagemFinal,
                    confirmButtonText: 'OK',
                });
                return;
            }

            const textoFallback = String(mensagemFinal).replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            alert(textoFallback ? `${tituloFinal}\n\n${textoFallback}` : tituloFinal);
        }

        @if ($errors->any())
            mostrarErroReserva('Erros de validacao', @json($errosReservaHtml));
        @endif

        @if (session('error'))
            mostrarErroReserva('Atenção!', @json(session('error')));
        @endif

        function abrirPrimeiraAbaNovoUsuario() {
            $('#tab-dados-pessoais-tab').tab('show');
        }

        function abrirAbaComPrimeiroErroNovoUsuario(erros = {}) {
            if (!erros || typeof erros !== 'object' || Object.keys(erros).length === 0) {
                return;
            }

            const primeiraChaveErro = Object.keys(erros)[0] || '';
            const chaveNormalizada = String(primeiraChaveErro).replace(/\.\d+$/, '');
            const abaSeletor = mapaCamposAbaNovoUsuario[chaveNormalizada];
            if (abaSeletor) {
                $(abaSeletor).tab('show');
            }
        }

        function exibirErrosNovoUsuario(erros = {}, mensagemPadrao = 'Não foi possível salvar o usuário.') {
            const alert = $('#modalNovoUsuarioAlert');
            let html = '';

            if (erros && typeof erros === 'object' && Object.keys(erros).length > 0) {
                html += '<ul>';
                Object.values(erros).forEach(function(lista) {
                    if (Array.isArray(lista)) {
                        lista.forEach(function(item) {
                            html += `<li>${item}</li>`;
                        });
                    } else if (typeof lista === 'string') {
                        html += `<li>${lista}</li>`;
                    }
                });
                html += '</ul>';
            } else {
                html = `<span>${mensagemPadrao}</span>`;
            }

            alert.html(html).removeClass('d-none');
            abrirAbaComPrimeiroErroNovoUsuario(erros);
        }

        function limparFormularioNovoUsuario() {
            const form = $('#formNovoUsuarioReserva');
            form[0].reset();

            $('#modalNovoUsuarioAlert').addClass('d-none').html('');

            form.find('.js-selectpicker').each(function() {
                $(this).val('');
            });
            initSelectPickers($('#modalNovoUsuarioReserva'));
            abrirPrimeiraAbaNovoUsuario();
        }



        function adicionarUsuarioSelectReserva(usuario) {
            if (!usuario || !usuario.id) {
                return;
            }

            const usuarioId = String(usuario.id);
            const nome = (usuario.nome || '').trim();
            const cpf  = (usuario.numero_identificacao || '').trim();
            const email = (usuario.email || '').trim();

            let label = nome || email || `Usuario #${usuarioId}`;
            if (cpf) {
                label = `${label} - ${cpf}`;
            } else if (email && label === nome) {
                label = `${label} - ${email}`;
            }

            // Atualiza input readonly + hidden
            const elUserId = document.getElementById('user_id');
            const elDisplay = document.getElementById('user_display');
            elUserId.value  = usuarioId;
            if (elDisplay) elDisplay.value = label;

            setUser(usuarioId, label);
            atualizarMinhaReserva();

            // Dispara change para o card de cliente reagir imediatamente
            elUserId.dispatchEvent(new Event('change'));
        }

        function atualizarUsuariosSelectReserva() {
            // mantido por compatibilidade â€” nao ha mais select de usuarios
            return $.Deferred().resolve().promise();
        }

        $('#formularioReserva').on('submit', function(e) {
            if (!podeReservar) {
                e.preventDefault();
                mostrarErroReserva('Atenção!', mensagemPreRequisitos);
                return false;
            }

            if (!handleGetGrupoVeiculos('submit')) {
                e.preventDefault();
                mostrarErroReserva('Atenção!', 'Preencha estações e datas válidas para continuar.');
                return false;
            }

            e.preventDefault();
            // quero que formate os opcionais para eu levar para o servidor em input hidden
            try {
                const reservaObj = getMinhaReserva() || {};
                // garantir que o user_id do campo hidden seja incluÃ­do na reservaHidden
                const userIdHidden = document.getElementById('user_id') ? document.getElementById('user_id').value : null;
                if (userIdHidden) {
                    reservaObj.user_id = Number(userIdHidden);
                }
                $('#reservaHidden').val(JSON.stringify(reservaObj));
            } catch (err) {
                console.error('Erro ao montar reservaHidden', err);
                $('#reservaHidden').val(JSON.stringify(getMinhaReserva()));
            }

            this.submit();
        });

        // Compatibilidade: sincroniza checkbox legados (opcionais[]) com o estado da reserva
        $(document).on('change', 'input[name="opcionais[]"]', function() {
            try {
                const id = Number($(this).val());
                if (!Number.isFinite(id) || id <= 0) return;

                if ($(this).is(':checked')) {
                    // adiciona com quantidade 1 por padrao
                    addOpcionalReserva(id, 1);
                } else {
                    removeOpcionalReserva(id);
                }

                if (typeof atualizarMinhaReserva === 'function') {
                    atualizarMinhaReserva();
                }
            } catch (e) {
                console.error('Erro ao sincronizar opcional checkbox:', e);
            }
        });

        function initSelectPickers(scope = document) {
            const $scope = $(scope);
            const $pickers = $scope.find('.js-selectpicker');

            $pickers.each(function() {
                const $el = $(this);
                if ($el.parent().hasClass('bootstrap-select')) {
                    $el.selectpicker('destroy');
                }

                $el.selectpicker({
                    liveSearch: $el.attr('data-live-search') === 'true',
                    noneSelectedText: 'Selecione...',
                    noneResultsText: 'Nenhum item encontrado para {0}',
                    selectAllText: 'Selecionar todos',
                    deselectAllText: 'Limpar',
                    size: Number($el.data('size')) || 8
                });
            });
        }

        $(document).ready(function() {

            // Wizard/Steps do cadastro de reserva
            (function initReservaWizard() {
                const maxStep = 4;
                const storageKey = 'reservaWizardStep';

                const prevBtn = document.getElementById('reservaWizardPrev');
                const nextBtn = document.getElementById('reservaWizardNext');
                const submitBtn = document.getElementById('btnSalvarReserva');

                function getStep() {
                    const saved = Number(localStorage.getItem(storageKey) || '1');
                    return Number.isFinite(saved) && saved >= 1 && saved <= maxStep ? saved : 1;
                }

                function setStep(step, persist = true) {
                    const s = Math.min(maxStep, Math.max(1, Number(step) || 1));

                    document.querySelectorAll('[data-reserva-step]').forEach(el => {
                        el.classList.toggle('d-none', Number(el.dataset.reservaStep) !== s);
                    });

                    // Garantia extra: opcionais/placeholder nunca aparecem fora do passo 3
                    const opcionaisWrapperEl = document.getElementById('opcionaisWrapper');
                    if (opcionaisWrapperEl) {
                        opcionaisWrapperEl.classList.toggle('d-none', s !== 3);
                    }
                    document.querySelectorAll('[data-reserva-step-btn]').forEach(btn => {
                        btn.classList.toggle('is-active', Number(btn.dataset.reservaStepBtn) === s);
                    });

                    if (prevBtn) prevBtn.disabled = s <= 1;
                    if (nextBtn) nextBtn.classList.toggle('d-none', s >= maxStep);
                    if (submitBtn) submitBtn.classList.toggle('d-none', !isEdit && s < maxStep);

                    if (persist) localStorage.setItem(storageKey, String(s));

                    // Evita glitch do bootstrap-select em elementos que estavam ocultos.
                    try {
                        $('.js-selectpicker').selectpicker('refresh');
                    } catch (e) {}
                }

                window.reservaWizardSetStep = setStep;

                function validateStep(step) {
                    const containers = Array.from(document.querySelectorAll(`[data-reserva-step="${step}"]`));
                    const fields = [];

                    containers.forEach(c => fields.push(...c.querySelectorAll('input, select, textarea')));

                    const uniqueFields = Array.from(new Set(fields)).filter(f => {
                        if (!f) return false;
                        if (f.disabled) return false;
                        if (f.type === 'hidden') return false;
                        return !!f.willValidate;
                    });

                    const invalid = uniqueFields.find(f => !f.checkValidity());
                    if (invalid) {
                        invalid.reportValidity();
                        return false;
                    }

                    if (Number(step) === 1) {
                        const dataInicio = document.getElementById('data_inicio');
                        const dataFim = document.getElementById('data_fim');
                        const fpRetirada = document.getElementById('data_inicio_flatpickr');
                        const fpDevolucao = document.getElementById('data_fim_flatpickr');

                        if (!dataInicio?.value) {
                            if (fpRetirada) fpRetirada.classList.add('is-invalid');
                            return false;
                        }
                        if (fpRetirada) fpRetirada.classList.remove('is-invalid');

                        if (!dataFim?.value) {
                            if (fpDevolucao) fpDevolucao.classList.add('is-invalid');
                            return false;
                        }
                        if (fpDevolucao) fpDevolucao.classList.remove('is-invalid');
                    }

                    return true;
                }

                prevBtn?.addEventListener('click', () => setStep(getStep() - 1));
                nextBtn?.addEventListener('click', () => {
                    const current = getStep();
                    if (!validateStep(current)) return;
                    setStep(current + 1);
                });

                document.querySelectorAll('[data-reserva-step-btn]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const target = Number(btn.dataset.reservaStepBtn);
                        const current = getStep();
                        if (target > current && !validateStep(current)) return;
                        setStep(target);
                    });
                });

                setStep(getStep(), false);
            })();

            // Se for edicao, definir os valores da variavel reserva
            const reserva = {!! $reserva ?? 'null' !!};
            if (reserva) { // se for diferente de nan ou null ou undefined

                const tiposOpcionais = {!! $tiposOpcionais ?? '[]' !!};
                opcionaisSelecionados = reserva.opcionais.map(opcional => {
                    return {
                        id: opcional.id,
                        nome: opcional.nome,
                        preco_por_dia: opcional.preco_por_dia,
                        preco_integral: opcional.preco_integral,
                        tipo_cobranca: opcional.tipo_cobranca,
                        quantidade: opcional.pivot.quantidade,
                        preco_total: opcional.pivot.preco_total
                    }
                });
                setDataInicio(reserva.data_inicio);
                setDataFim(reserva.data_fim);
                setTarifaBase(reserva.tarifa_base);
                setQuantidadeDias(reserva.quantidade_dias);
                setEstacaoRetirada(reserva.estacao_retirada_id, reserva.estacao_retirada?.nome);
                setEstacaoDevolucao(reserva.estacao_devolucao_id, reserva.estacao_devolucao?.nome);
                setGrupoFrota(reserva.grupo_frota_id, reserva.grupo_frota?.nome);
                $('#grupo_frota_id_hidden').val(reserva.grupo_frota_id || '');
                setModelo(reserva.modelo_id, reserva.modelos?.nome);

                const usuarioNome = (reserva.usuario?.nome || '').trim();
                const usuarioCpf = (reserva.usuario?.numero_identificacao || '').trim();
                const usuarioEmail = (reserva.usuario?.email || '').trim();
                let usuarioLabel = usuarioNome || usuarioEmail || `Usuario #${reserva.user_id}`;
                if (usuarioCpf) {
                    usuarioLabel = `${usuarioLabel} - ${usuarioCpf}`;
                } else if (usuarioEmail && usuarioLabel === usuarioNome) {
                    usuarioLabel = `${usuarioLabel} - ${usuarioEmail}`;
                }

                setUser(reserva.user_id, usuarioLabel);
                if(opcionaisSelecionados.length > 0){
                    setOpcionaisReserva(opcionaisSelecionados);
                }
                setCupomDesconto(reserva.cupom_desconto);

                // Inicializa taxas fora de expediente salvas na reserva
                if (typeof setTaxaColeta === 'function') {
                    setTaxaColeta(parseFloat(reserva.taxa_coleta || 0));
                }
                if (typeof setTaxaEntrega === 'function') {
                    setTaxaEntrega(parseFloat(reserva.taxa_entrega || 0));
                }

            $.ajax({
                url: "{{ route('reserva.obterOpcionais') }}",
                type: 'GET',
                data: {
                    grupo_frota_id: reserva.grupo_frota_id,
                    estacao_retirada_id: getEstacaoRetirada(),
                    data_inicio: getDataInicio(),
                    estacao_devolucao_id: getEstacaoDevolucao(),
                    data_fim: getDataFim(),
                    opcionais: opcionaisSelecionados
                },
                success: function(data) {
                    $('#opcionais').html(data);
                    if (typeof formatarPrecosOpcionais === 'function') {
                        formatarPrecosOpcionais();
                    }

                    if(tiposOpcionais.length > 0){
                        tiposOpcionais.forEach(tipoOpcional => {
                            if(tipoOpcional.opcionais.length > 0){
                                tipoOpcional.opcionais.forEach(opcional => {
                                addOpcional(opcional)
                                });
                            }
                        });
                    }
                },
                error: function(error) {
                    console.error('Error fetching data:', error);
                    }
                });

                atualizarMinhaReserva();
            }


            initSelectPickers(document);
            $('.js-selectpicker').selectpicker('refresh');

            if (typeof reserva !== 'undefined' && reserva && reserva.data_inicio) {
                const dataInicialRetirada = String(reserva.data_inicio).split(/[ T]/)[0] || '';
                const horaInicialRetirada = String(reserva.data_inicio).includes('T')
                    ? String(reserva.data_inicio).split('T')[1].slice(0, 5)
                    : (String(reserva.data_inicio).split(' ')[1] || '').slice(0, 5);

                // slots are loaded in inicializarDatePickers IIFE using schedule cache
            }

            $('#novo_usuario_telefone').mask('(00) 00000-0000');
            $('#novo_usuario_celular').mask('(00) 00000-0000');

            $('#btnNovoUsuarioModal').on('click', function() {
                limparFormularioNovoUsuario();
                initSelectPickers($('#modalNovoUsuarioReserva'));
                abrirPrimeiraAbaNovoUsuario();
                $('#modalNovoUsuarioReserva').modal('show');
            });

            // Novo usuario criado via modal: preenche input readonly
            $(document).on('novoUsuarioCriado', function(e, usuario) {
                if (usuario && usuario.id) {
                    document.getElementById('user_id').value      = usuario.id;
                    document.getElementById('user_display').value = usuario.nome || ('Usuario #' + usuario.id);
                    const btnLimpar = document.getElementById('btnLimparUsuarioReserva');
                    if (btnLimpar) btnLimpar.classList.remove('d-none');
                }
            });

            $('#btnSalvarNovoUsuarioReserva').on('click', function() {
                $('#formNovoUsuarioReserva').trigger('submit');
            });

            $('#formNovoUsuarioReserva').on('submit', function(event) {
                event.preventDefault();

                const form = $(this);
                const formElement = form.get(0);
                const botaoSalvar = $('#btnSalvarNovoUsuarioReserva');
                const textoOriginal = botaoSalvar.html();

                $('#modalNovoUsuarioAlert').addClass('d-none').html('');

                if (formElement && !formElement.checkValidity()) {
                    formElement.reportValidity();
                    abrirAbaComPrimeiroErroNovoUsuario({
                        nome: ['Preencha os campos obrigatorios para continuar.']
                    });
                    return;
                }

                botaoSalvar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Salvando');

                $.ajax({
                    url: rotaCriarUsuario,
                    method: 'POST',
                    data: form.serialize(),
                    headers: {
                        Accept: 'application/json'
                    }
                }).done(function(response) {
                    const usuario = response?.usuario || null;
                    adicionarUsuarioSelectReserva(usuario);
                    $('#modalNovoUsuarioReserva').modal('hide');
                    limparFormularioNovoUsuario();
                }).fail(function(xhr) {
                    if (xhr.status === 422) {
                        exibirErrosNovoUsuario(xhr.responseJSON?.errors || {}, xhr.responseJSON?.message);
                        return;
                    }

                    exibirErrosNovoUsuario({}, xhr.responseJSON?.message || 'Erro ao salvar usuario. Tente novamente.');
                }).always(function() {
                    botaoSalvar.prop('disabled', false).html(textoOriginal);
                });
            });

            $('#modalNovoUsuarioReserva').on('hidden.bs.modal', function() {
                limparFormularioNovoUsuario();
            });

            // Modal busca de usuario
            (function () {
                const URL_BUSCAR_USUARIO = @json(route('usuario.buscar'));

                function coletarFiltrosUsuario() {
                    const p = new URLSearchParams();
                    const add = (id, key) => { const v = document.getElementById(id)?.value?.trim(); if (v) p.set(key, v); };
                    add('fu_nome',           'nome');
                    add('fu_email',          'email');
                    add('fu_documento',      'documento');
                    add('fu_telefone',       'telefone');
                    add('fu_role_id',        'role_id');
                    add('fu_nascimento_de',  'nascimento_de');
                    add('fu_nascimento_ate', 'nascimento_ate');
                    add('fu_cadastro_de',    'cadastro_de');
                    add('fu_cadastro_ate',   'cadastro_ate');
                    return p.toString();
                }

                function executarBuscaUsuario() {
                    const loading    = document.getElementById('modalUsuarioLoading');
                    const resultados = document.getElementById('modalUsuarioResultados');
                    loading.classList.remove('d-none');
                    resultados.innerHTML = '';

                    fetch(`${URL_BUSCAR_USUARIO}?${coletarFiltrosUsuario()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(async (r) => {
                        let data = null;
                        try {
                            data = await r.json();
                        } catch (e) {
                            data = null;
                        }

                        if (!r.ok) {
                            throw new Error((data && data.message) ? data.message : 'Erro ao buscar usuarios.');
                        }

                        return data || {};
                    })
                    .then(data => {
                        loading.classList.add('d-none');
                        if (!data.usuarios || data.usuarios.length === 0) {
                            resultados.innerHTML = '<p class="text-muted text-center py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Nenhum usuario encontrado com os filtros informados.</p>';
                            return;
                        }
                        const badge = `<span class="badge badge-secondary mb-2">${data.usuarios.length} resultado(s)</span>`;
                        let html = `${badge}<div class="table-responsive"><table class="table table-sm table-hover table-bordered" style="font-size:.82rem">`;
                        html += `<thead class="thead-light">
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>CPF / Documento</th>
                                <th>Telefone</th>
                                <th>Perfil</th>
                                <th>Nascimento</th>
                                <th>Cadastro</th>
                                <th class="text-center">AÃ§Ã£o</th>
                            </tr>
                        </thead><tbody>`;
                        data.usuarios.forEach(u => {
                            html += `<tr>
                                <td><strong>${u.nome}</strong></td>
                                <td>${u.email || 'â€”'}</td>
                                <td>${u.documento || 'â€”'}</td>
                                <td>${u.telefone || 'â€”'}</td>
                                <td><span class="badge badge-info">${u.perfil || 'â€”'}</span></td>
                                <td>${u.nascimento || 'â€”'}</td>
                                <td>${u.cadastro || 'â€”'}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-success btn-selecionar-usuario"
                                        data-id="${u.id}" data-nome="${u.nome.replace(/"/g,'&quot;')}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>`;
                        });
                        html += '</tbody></table></div>';
                        resultados.innerHTML = html;

                        resultados.querySelectorAll('.btn-selecionar-usuario').forEach(btn => {
                            btn.addEventListener('click', function () {
                                const id   = parseInt(this.dataset.id);
                                const nome = this.dataset.nome;
                                adicionarUsuarioSelectReserva({ id, nome });
                                $('#modalBuscarUsuarioReserva').modal('hide');
                            });
                        });
                    })
                    .catch((error) => {
                        loading.classList.add('d-none');
                        resultados.innerHTML = `<p class="text-danger text-center py-3"><i class="fas fa-exclamation-triangle mr-1"></i>${(error && error.message) ? error.message : 'Erro ao buscar usuarios.'}</p>`;
                    });
                }

                document.getElementById('btnBuscarUsuarioReserva').addEventListener('click', function () {
                    document.getElementById('modalUsuarioResultados').innerHTML = '';
                    $('#modalBuscarUsuarioReserva').modal('show');
                    setTimeout(() => document.getElementById('fu_nome').focus(), 400);
                    executarBuscaUsuario();
                });

                document.getElementById('btnAplicarFiltroUsuario').addEventListener('click', executarBuscaUsuario);

                const btnLimparReservaUser = document.getElementById('btnLimparUsuarioReserva');
                if (btnLimparReservaUser) {
                    btnLimparReservaUser.addEventListener('click', function () {
                        document.getElementById('user_id').value      = '';
                        document.getElementById('user_display').value = '';
                        this.classList.add('d-none');
                    });
                }

                document.getElementById('btnLimparFiltroUsuario').addEventListener('click', function () {
                    ['fu_nome','fu_email','fu_documento','fu_telefone','fu_nascimento_de','fu_nascimento_ate','fu_cadastro_de','fu_cadastro_ate'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.value = '';
                    });
                    const roleEl = document.getElementById('fu_role_id');
                    if (roleEl) roleEl.selectedIndex = 0;
                    document.getElementById('modalUsuarioResultados').innerHTML = '';
                });

                // Enter em qualquer filtro de texto
                ['fu_nome','fu_email','fu_documento','fu_telefone'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); executarBuscaUsuario(); } });
                });
            })();


            // Inicializar Select2 quando o modal for aberto
            $('.select2').select2();
                // Inicializar Select2 quando o modal for aberto
            $('#modalPesquisa').on('shown.bs.modal', function() {
                $('.select2').select2({
                    dropdownParent: $('#modalPesquisa')
                });
            });

        });

        function verificarCupomDesconto() {
            const codigo = $('#cupom_desconto').val().trim().toUpperCase();
            const $fb = $('#cupom_feedback');
            const $btn = $('#btn_verificar_cupom');

            if (!codigo) {
                setCupomDesconto(null);
                $fb.text('').removeClass('text-success text-danger');
                atualizarMinhaReserva();
                return;
            }

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            $fb.text('Verificando...').removeClass('text-success text-danger').addClass('text-muted');

            $.ajax({
                url: "{{ route('cupom_desconto.verificarCupom') }}",
                method: 'POST',
                data: { cupom: codigo, _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.id) {
                        setCupomDesconto(response);
                        $fb.text('✓ Cupom válido — ' + response.valor + '% de desconto')
                            .removeClass('text-danger text-muted').addClass('text-success');
                    } else {
                        setCupomDesconto(null);
                        $fb.text('✗ Cupom inválido ou expirado.')
                            .removeClass('text-success text-muted').addClass('text-danger');
                    }
                    atualizarMinhaReserva();
                },
                error: function() {
                    setCupomDesconto(null);
                    $fb.text('Erro ao verificar o cupom.').removeClass('text-success text-muted').addClass('text-danger');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-search"></i>');
                }
            });
        }

        $('#btn_verificar_cupom').on('click', verificarCupomDesconto);
        $('#cupom_desconto').on('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); verificarCupomDesconto(); }
        });
        $('#cupom_desconto').on('input', function() {
            // Limpa feedback e desconto ao digitar (força revalidação)
            setCupomDesconto(null);
            $('#cupom_feedback').text('').removeClass('text-success text-danger text-muted');
            atualizarMinhaReserva();
        });

        function validarPeriodoReserva() {
            const inicioTexto = $('#data_inicio').val();
            const fimTexto = $('#data_fim').val();

            if (!inicioTexto || !fimTexto) {
                return;
            }

            const inicioObjReserva = new Date(inicioTexto);
            const fimObjReserva = new Date(fimTexto);
            if (fimObjReserva < inicioObjReserva) {
                mostrarErroReserva('Periodo invalido', 'A data de devolucao nao pode ser anterior a data de retirada.');
                $('#data_fim').val('');
                $('#data_fim').removeClass('is-invalid');
                return;
            }

            Promise.all([
                validarFuncionamento($('#estacao_retirada_id').val(), inicioTexto, 'retirada', '#data_inicio'),
                validarFuncionamento($('#estacao_devolucao_id').val(), fimTexto, 'devolucao', '#data_fim'),
            ]).then(function(resultados) {
                const mensagem = resultados.find(function(item) {
                    return !!item;
                });

                if (mensagem) {
                    mostrarErroReserva('Atencao!', mensagem);
                    $('#data_fim').val('');
                    $('#data_fim').removeClass('is-invalid');
                }
            }).catch(function() {});
        }

        $('#estacao_retirada_id, #estacao_devolucao_id, #data_inicio, #data_fim').on('change', function() {
            this.setCustomValidity('');
            if ($('#data_inicio').val() && $('#data_fim').val()) {
                validarPeriodoReserva();
            }
        });

        $('#user_id').on('change', function() {
            setUser($(this).val(),  $(this).find('option:selected').text().trim());
            atualizarMinhaReserva();
        });

        // Ao selecionar a estacao de retirada, pre-seleciona a estacao de devolucao (se o usuario ainda nao alterou manualmente).
        let estacaoDevolucaoAlterada = false;
        $('#estacao_devolucao_id').on('change', function() {
            estacaoDevolucaoAlterada = true;
            setEstacaoDevolucao($(this).val(), $(this).find('option:selected').text().trim());
            limparAgendaSelecionada('devolucao', false);
            if (typeof setTaxaEntrega === 'function') { setTaxaEntrega(0); atualizarMinhaReserva(); }
        });
        $('#estacao_retirada_id').on('change', function() {
            const retirada = $(this).val();
            const devolucaoAtual = $('#estacao_devolucao_id').val();
            setEstacaoRetirada(retirada, $(this).find('option:selected').text().trim());
            limparAgendaSelecionada('retirada', false);
            if (typeof setTaxaColeta === 'function') { setTaxaColeta(0); atualizarMinhaReserva(); }
            if (!retirada) return;

            if (estacaoPodeSerDevolucao(retirada) && (!estacaoDevolucaoAlterada || !devolucaoAtual)) {
                $('#estacao_devolucao_id').val(retirada);
                if ($('#estacao_devolucao_id').hasClass('js-selectpicker')) {
                    $('#estacao_devolucao_id').selectpicker('refresh');
                }
                $('#estacao_devolucao_id').trigger('change');
            }

        });

        // dispara no load (edit com valores preenchidos)
        setTimeout(function () {
            try {
                atualizarEstacoesDisponiveis();
            } catch (e) {}
        }, 0);

        @php
            $estacoesIds = array_keys($estacoesRetirada + $estacoesDevolucao);
            $estacoesTaxasMap = \App\Models\Estacao::whereIn('id', $estacoesIds)
                ->get(['id', 'cobrar_na_coleta', 'valor_taxa_coleta', 'cobrar_na_entrega', 'valor_taxa_entrega'])
                ->keyBy('id')
                ->map(fn($e) => [
                    'cobrar_na_coleta'   => $e->cobrar_na_coleta   ?? 'N',
                    'valor_taxa_coleta'  => (float) ($e->valor_taxa_coleta  ?? 0),
                    'cobrar_na_entrega'  => $e->cobrar_na_entrega  ?? 'N',
                    'valor_taxa_entrega' => (float) ($e->valor_taxa_entrega ?? 0),
                ]);
        @endphp
        const ESTACOES_TAXAS = @json($estacoesTaxasMap);

        const VALIDAR_FUNC_URL = @json(route('reserva.validarFuncionamentoEstacao'));
        const ESTACOES_DISP_URL = @json(route('reserva.estacoesDisponiveisNoPeriodo'));
        const ESTACOES_DISP_DIA_URL = @json(route('reserva.estacoesDisponiveisNaDataHora'));
        const DATAS_RETIRADA_URL = @json(route('reserva.datasRetiradaDisponiveisPorEstacao'));
        const AGENDA_DISP_URL = @json(route('reserva.agendaDisponivelPorEstacao'));

        let agendaReservaDados = [];
        let agendaReservaDiaSelecionado = null;
        let agendaReservaSlotSelecionado = null;
        let retiradaDataSelecionada = null;

        function obterTipoAgendaAtual() {
            return $('#agenda_tipo').val() || 'retirada';
        }

        function obterDataBaseAgenda(tipo) {
            const valorAtual = tipo === 'devolucao' ? $('#data_fim').val() : $('#data_inicio').val();
            if (valorAtual) {
                return String(valorAtual).slice(0, 10);
            }

            const agora = new Date();
            const ano = agora.getFullYear();
            const mes = String(agora.getMonth() + 1).padStart(2, '0');
            const dia = String(agora.getDate()).padStart(2, '0');
            return `${ano}-${mes}-${dia}`;
        }

        function definirAgendaSelecionada(tipo, valor, dispararChange = true) {
            const campoValor = tipo === 'devolucao' ? '#data_fim' : '#data_inicio';
            const campoDisplay = tipo === 'devolucao' ? '#data_fim_display' : '#data_inicio_display';

            if (tipo === 'devolucao') {
                setDataFim(valor || '');
            } else {
                setDataInicio(valor || '');
                if (valor) {
                    const partes = String(valor).split(/[ T]/);
                    const dataParte = partes[0] || '';
                    $('#data_inicio_data').val(dataParte);
                } else {
                    $('#data_inicio_data').val('');
                    $('#data_inicio_hora').val('');
                    $('#data_inicio_hora').prop('disabled', true);
                    if ($('#data_inicio_hora').hasClass('js-selectpicker')) {
                        $('#data_inicio_hora').selectpicker('refresh');
                    }
                }
            }

            $(campoValor).val(valor || '');
            $(campoDisplay).val(valor ? formatarDataHoraReserva(valor) : '');
            $(campoDisplay).toggleClass('is-invalid', !valor);

            if (dispararChange) {
                $(campoValor).trigger('change');
            }
        }

        function limparAgendaSelecionada(tipo, dispararChange = false) {
            definirAgendaSelecionada(tipo, '', dispararChange);
        }

        function obterEstacaoAgenda(tipo) {
            return tipo === 'devolucao' ? $('#estacao_devolucao_id').val() : $('#estacao_retirada_id').val();
        }

        function obterNomeEstacaoAgenda(tipo) {
            const seletor = tipo === 'devolucao' ? '#estacao_devolucao_id' : '#estacao_retirada_id';
            return $(seletor).find('option:selected').text().trim();
        }

        function atualizarDataInicioRetirada() {
            const data = $('#data_inicio_data').val() || '';
            const hora = $('#data_inicio_hora').val() || '';

            if (!data) {
                $('#data_inicio').val('');
                setDataInicio('');
                setQuantidadeDias(0);
                atualizarMinhaReserva();
                $('#data_inicio').trigger('change');
                return;
            }

            if (!hora) {
                $('#data_inicio').val('');
                setDataInicio(data);
                $('#data_inicio').trigger('change');
                return;
            }

            const valor = `${data}T${hora}`;
            setDataInicio(valor);
            recalcularDiasEAtualizar();
            $('#data_inicio').trigger('change');
        }

        const ptLocale = {
            weekdays: { shorthand: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'], longhand: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'] },
            months: { shorthand: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'], longhand: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'] },
            firstDayOfWeek: 0, ordinal: function() { return 'º'; }, rangeSeparator: ' até ', time_24hr: true
        };

        let _pickerDataRetirada = null;
        let _pickerDataDevolucao = null;

        // Gera todos os slots de 30 em 30 minutos do dia (00:00 – 23:30)
        function gerarSlotsDia() {
            const slots = [];
            for (let h = 0; h < 24; h++) {
                slots.push(String(h).padStart(2,'0') + ':00');
                slots.push(String(h).padStart(2,'0') + ':30');
            }
            return slots;
        }

        function normalizarSlotHorario(slot) {
            if (slot && typeof slot === 'object') {
                const value = String(slot.value || slot.hora || slot.time || '').trim();
                const label = String(slot.label || slot.text || value).trim();
                if (!value) {
                    return null;
                }
                return {
                    value: value,
                    label: label || value,
                    taxa_extra: Boolean(slot.taxa_extra || slot.status === 'taxa_extra'),
                    status: slot.status || (slot.taxa_extra ? 'taxa_extra' : 'normal'),
                };
            }

            const value = String(slot || '').trim();
            if (!value) {
                return null;
            }

            return {
                value: value,
                label: value,
                taxa_extra: false,
                status: 'normal',
            };
        }

        function preencherHorariosRetirada(slots, horaPreferida = null) {
            const $sel = $('#data_inicio_hora');
            const $fb  = $('#data_inicio_hora_feedback');
            const lista = Array.isArray(slots)
                ? slots.map(normalizarSlotHorario).filter(Boolean)
                : [];

            $sel.empty().prop('disabled', true);

            if (!lista.length) {
                $sel.append('<option value="">Sem horários</option>');
                $fb.text('Sem horários disponíveis para esta data.');
                $('#data_inicio_data').val('');
                setDataInicio('');
                return [];
            }

            $sel.append('<option value="">Selecione a hora</option>');
            lista.forEach(function(slot) {
                const optVal = slot.value.includes('T') ? slot.value.split('T')[1].slice(0, 5) : String(slot.value).slice(0, 5);
                const texto = slot.taxa_extra
                    ? `* ${slot.label}  —  fora do expediente`
                    : slot.label;
                const $opt = $(`<option value="${optVal}" data-taxa-extra="${slot.taxa_extra ? '1' : '0'}">${texto}</option>`);
                $sel.append($opt);
            });
            $sel.prop('disabled', false);

            const _toTime = function(v) { return String(v).includes('T') ? String(v).split('T')[1].slice(0, 5) : String(v).slice(0, 5); };
            const horaNorm = horaPreferida ? _toTime(horaPreferida) : (lista.length === 1 ? _toTime(lista[0].value) : '');
            if (horaNorm && $sel.find(`option[value="${horaNorm}"]`).length) {
                $sel.val(horaNorm);
            }
            const _initTaxaRetirada = $sel.find('option:selected').data('taxa-extra') == '1';
            $('#data_inicio_taxa_aviso').toggleClass('d-none', !_initTaxaRetirada);
            $sel.css({ 'border-color': _initTaxaRetirada ? '#f59e0b' : '', 'color': _initTaxaRetirada ? '#92400e' : '', 'font-weight': _initTaxaRetirada ? '600' : '' });
            $fb.text('Escolha a hora de retirada.');
            atualizarDataInicioRetirada();
            return lista;
        }

        function carregarHorariosRetiradaDisponiveis(dataBase, horaPreferida = null) {
            return carregarHorariosAgendaPorData('retirada', dataBase, horaPreferida);
        }

        function carregarHorariosDevolucaoDisponiveis(dataBase, horaPreferida = null) {
            return carregarHorariosAgendaPorData('devolucao', dataBase, horaPreferida);
        }

        function carregarHorariosAgendaPorData(tipo, dataBase, horaPreferida = null) {
            const tipoAgenda = tipo || 'retirada';
            const estacaoAtual = obterEstacaoAgenda(tipoAgenda);
            if (!estacaoAtual || !dataBase) {
                return Promise.resolve([]);
            }

            const params = new URLSearchParams({
                estacao_id: estacaoAtual,
                tipo: tipoAgenda,
                data_base: dataBase,
                limite: 1,
            });

            const reservaId = $('#reserva_id').val() || null;
            if (reservaId) {
                params.set('reserva_id', reservaId);
            }

            const seletorHora = tipoAgenda === 'devolucao' ? '#data_fim_hora_fp' : '#data_inicio_hora';
            const seletorFeedback = tipoAgenda === 'devolucao' ? '#data_fim_hora_feedback' : '#data_inicio_hora_feedback';

            $(seletorFeedback).text('Carregando horários disponíveis...');
            $(seletorHora).prop('disabled', true);

            return fetch(AGENDA_DISP_URL + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(d => {
                const agenda = Array.isArray(d.agenda) ? d.agenda : [];
                const dia = agenda.find(item => String(item.date) === String(dataBase)) || agenda[0] || null;
                const slots = Array.isArray(dia?.slots) ? dia.slots : [];

                if (!slots.length) {
                    if (tipoAgenda === 'devolucao') {
                        preencherHorariosDevolucao([], null);
                    } else {
                        preencherHorariosRetirada([], null);
                    }
                    mostrarErroReserva('Sem horario', tipoAgenda === 'devolucao'
                        ? 'Não há horários de devolução disponíveis para a data selecionada.'
                        : 'Não há horários de retirada disponíveis para a data selecionada.');
                    return [];
                }

                return tipoAgenda === 'devolucao'
                    ? preencherHorariosDevolucao(slots, horaPreferida)
                    : preencherHorariosRetirada(slots, horaPreferida);
            })
            .catch(() => {
                if (tipoAgenda === 'devolucao') {
                    preencherHorariosDevolucao([], null);
                } else {
                    preencherHorariosRetirada([], null);
                }
                mostrarErroReserva('Sem horario', tipoAgenda === 'devolucao'
                    ? 'Não foi possível carregar os horários da devolução.'
                    : 'Não foi possível carregar os horários da retirada.');
                return [];
            });
        }

        function buscarHorariosAoSelecionarData(tipo, dataBase, horaPreferida = null) {
            if (!dataBase) {
                return Promise.resolve([]);
            }

            if (tipo === 'devolucao') {
                $('#data_fim_data').val(dataBase);
                return carregarHorariosDevolucaoDisponiveis(dataBase, horaPreferida || $('#data_fim_hora_fp').val() || null);
            }

            $('#data_inicio_data').val(dataBase);
            return carregarHorariosRetiradaDisponiveis(dataBase, horaPreferida || $('#data_inicio_hora').val() || null);
        }

        function selecionarDataRetirada(dataSelecionada, horaPreferida = null) {
            if (!dataSelecionada) {
                return;
            }

            retiradaDataSelecionada = dataSelecionada;
            $('#agenda_dias').children().removeClass('active');
            $('#agenda_dias').children().filter(function() {
                return String($(this).attr('data-date') || '') === String(dataSelecionada);
            }).addClass('active');

            definirAgendaSelecionada('retirada', dataSelecionada, false);
            carregarHorariosRetiradaDisponiveis(dataSelecionada, horaPreferida || $('#data_inicio_hora').val()).then(function(slots) {
                if (Array.isArray(slots) && slots.length > 0) {
                    $('#modalAgendaReserva').modal('hide');
                }
            });
        }

        function renderizarAgendaDias() {
            const $dias = $('#agenda_dias');
            const $horarios = $('#agenda_horarios');
            const $vazio = $('#agenda_horarios_vazio');
            const tipo = obterTipoAgendaAtual();
            const valorAtual = tipo === 'devolucao' ? $('#data_fim').val() : $('#data_inicio').val();

            $dias.empty();
            $horarios.empty();
            $vazio.addClass('d-none');
            $('#btnConfirmarAgenda').prop('disabled', true);
            agendaReservaDiaSelecionado = null;
            agendaReservaSlotSelecionado = null;
            $('#agenda_valor_selecionado').val('');

            if (tipo === 'retirada') {
                $('#agenda_horarios_card').addClass('d-none');
                $('#btnConfirmarAgenda').addClass('d-none');
            } else {
                $('#agenda_horarios_card').removeClass('d-none');
                $('#btnConfirmarAgenda').removeClass('d-none');
            }

            if (!Array.isArray(agendaReservaDados) || agendaReservaDados.length === 0) {
                $('#agenda_status').html('<span class="text-danger">Nenhum dia com expediente disponível para a estação selecionada.</span>');
                $vazio.removeClass('d-none').text('Nenhum horário disponível.');
                return;
            }

            $('#modalAgendaReservaLabel').html(
                '<i class="fas fa-calendar-alt mr-2"></i>Selecionar data e horário de ' + (tipo === 'devolucao' ? 'devolução' : 'retirada')
            );

            $('#agenda_status').html(
                '<strong>Estação:</strong> ' + $('<div>').text(obterNomeEstacaoAgenda(tipo) || '---').html()
            );

            agendaReservaDados.forEach(function(dia, indice) {
                const qtdSlots = Array.isArray(dia.slots) ? dia.slots.length : 0;
                const temTaxaExtra = Array.isArray(dia.slots) && dia.slots.some(function(slot) {
                    return !!slot.taxa_extra;
                });
                const $btn = $('<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"></button>');
                $btn.append(`<span>${dia.label || ''}</span>`);
                const $badges = $('<span class="d-flex align-items-center"></span>');
                $badges.append(`<span class="badge badge-primary badge-pill mr-1">${qtdSlots}</span>`);
                if (temTaxaExtra) {
                    $badges.append('<span class="badge badge-warning text-dark badge-pill">Taxa extra</span>');
                }
                $btn.append($badges);
                $btn.on('click', function() {
                    if (tipo === 'retirada') {
                        selecionarDataRetirada(dia.date || '');
                        return;
                    }
                    selecionarAgendaDia(indice);
                });
                $btn.attr('data-date', dia.date || '');
                $dias.append($btn);
            });

            if (valorAtual) {
                const valorNormalizado = String(valorAtual);
                let indiceEncontrado = -1;
                let slotEncontrado = null;
                const horaAtual = valorNormalizado.includes('T')
                    ? valorNormalizado.split('T')[1].slice(0, 5)
                    : (valorNormalizado.split(' ')[1] || '').slice(0, 5);

                agendaReservaDados.forEach(function(dia, indice) {
                    (dia.slots || []).forEach(function(slot) {
                        if (String(slot.value) === valorNormalizado) {
                            indiceEncontrado = indice;
                            slotEncontrado = slot.value;
                        }
                    });
                });

                if (indiceEncontrado !== -1) {
                    if (tipo === 'retirada') {
                        $dias.children().removeClass('active');
                        $dias.children().eq(indiceEncontrado).addClass('active');
                        retiradaDataSelecionada = agendaReservaDados[indiceEncontrado]?.date || '';
                        return;
                    } else {
                        selecionarAgendaDia(indiceEncontrado, slotEncontrado);
                    }
                    return;
                }
            }

            if (tipo !== 'retirada') {
                selecionarAgendaDia(0);
            }
        }

        function selecionarAgendaDia(indice, slotPreferido = null) {
            const dia = agendaReservaDados[indice];
            const $horarios = $('#agenda_horarios');
            const $vazio = $('#agenda_horarios_vazio');
            const $dias = $('#agenda_dias');

            if (!dia) {
                $horarios.empty();
                $vazio.removeClass('d-none').text('Nenhum horário disponível.');
                $('#btnConfirmarAgenda').prop('disabled', true);
                agendaReservaDiaSelecionado = null;
                agendaReservaSlotSelecionado = null;
                $('#agenda_valor_selecionado').val('');
                return;
            }

            agendaReservaDiaSelecionado = indice;
            agendaReservaSlotSelecionado = null;
            $('#agenda_valor_selecionado').val('');
            $dias.children().removeClass('active');
            $dias.children().eq(indice).addClass('active');
            $horarios.empty();

            const slots = Array.isArray(dia.slots) ? dia.slots : [];
            if (!slots.length) {
                $vazio.removeClass('d-none').text('Nenhum horário disponível para este dia.');
                $('#btnConfirmarAgenda').prop('disabled', true);
                return;
            }

            $vazio.addClass('d-none');
            slots.forEach(function(slot) {
                const ativo = slotPreferido && String(slot.value) === String(slotPreferido);
                const $btn = $('<button type="button" class="btn btn-outline-primary btn-sm mr-2 mb-2"></button>');
                $btn.text(slot.label || slot.value);
                if (slot.taxa_extra) {
                    $btn.append(' ');
                    $btn.append('<span class="badge badge-warning text-dark ml-1">Taxa extra</span>');
                }
                if (ativo) {
                    $btn.addClass('active');
                    agendaReservaSlotSelecionado = slot.value;
                    $('#agenda_valor_selecionado').val(slot.value);
                    $('#btnConfirmarAgenda').prop('disabled', false);
                }
                $btn.on('click', function() {
                    $horarios.find('button').removeClass('active');
                    $(this).addClass('active');
                    agendaReservaSlotSelecionado = slot.value;
                    $('#agenda_valor_selecionado').val(slot.value);
                    $('#btnConfirmarAgenda').prop('disabled', false);
                });
                $horarios.append($btn);
            });

            if (!slotPreferido) {
                const primeiroSlot = slots[0];
                agendaReservaSlotSelecionado = primeiroSlot.value;
                $('#agenda_valor_selecionado').val(primeiroSlot.value);
                $horarios.find('button').first().addClass('active');
                $('#btnConfirmarAgenda').prop('disabled', false);
            }
        }

        function carregarAgendaEstacao(tipo) {
            const estacaoId = obterEstacaoAgenda(tipo);
            if (!estacaoId) {
                mostrarErroReserva('Atenção!', 'Selecione a estação antes de abrir a agenda.');
                return;
            }

            $('#agenda_tipo').val(tipo || 'retirada');
            $('#agenda_estacao_id').val(estacaoId);
            $('#agenda_status').text('Carregando agenda...');
            $('#agenda_dias').empty();
            $('#agenda_horarios').empty();
            $('#agenda_horarios_vazio').removeClass('d-none').text('Carregando horarios...');
            $('#btnConfirmarAgenda').prop('disabled', true);
            agendaReservaDados = [];
            agendaReservaDiaSelecionado = null;
            agendaReservaSlotSelecionado = null;
            $('#agenda_valor_selecionado').val('');
            $('#modalAgendaReserva').modal('show');

            if ((tipo || 'retirada') === 'retirada') {
                $('#agenda_horarios_card').addClass('d-none');
                $('#btnConfirmarAgenda').addClass('d-none');
            } else {
                $('#agenda_horarios_card').removeClass('d-none');
                $('#btnConfirmarAgenda').removeClass('d-none');
            }

            const params = new URLSearchParams({
                estacao_id: estacaoId,
                tipo: tipo || 'retirada',
                data_base: obterDataBaseAgenda(tipo || 'retirada'),
                limite: 21,
            });

            fetch(AGENDA_DISP_URL + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(d => {
                agendaReservaDados = Array.isArray(d.agenda) ? d.agenda : [];
                renderizarAgendaDias();
            })
            .catch(() => {
                agendaReservaDados = [];
                $('#agenda_status').html('<span class="text-danger">Não foi possível carregar a agenda desta estação.</span>');
                $('#agenda_horarios_vazio').removeClass('d-none').text('Não foi possível carregar os horários.');
            });
        }

        function confirmarAgendaSelecionada() {
            const tipo = $('#agenda_tipo').val() || 'retirada';
            if (tipo === 'retirada') {
                return;
            }
            const valor = $('#agenda_valor_selecionado').val();

            if (!valor) {
                mostrarErroReserva('Atenção!', 'Selecione um horário para continuar.');
                return;
            }

            definirAgendaSelecionada(tipo, valor, true);
            $('#modalAgendaReserva').modal('hide');
        }
        function validarFuncionamento(estacaoId, dataHora, tipo, campoSelector) {
            if (!estacaoId || !dataHora) return Promise.resolve(null);
            return fetch(VALIDAR_FUNC_URL + '?estacao_id=' + encodeURIComponent(estacaoId) + '&data_hora=' + encodeURIComponent(dataHora) + '&tipo=' + encodeURIComponent(tipo), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': @json(csrf_token()) }
            })
            .then(r => r.json())
            .then(d => {
                if (d && d.ok === false) {
                    return d.message || 'Data/horário inválido para a estação selecionada.';
                }
                return null;
            })
            .catch(() => null);
        }

        function aplicarEstacoesDisponiveis(ids, seletor = null) {
            const allowed = new Set((ids || []).map(String));
            const seletores = seletor ? [seletor] : ['#estacao_retirada_id', '#estacao_devolucao_id'];

            seletores.forEach(function (sel) {
                const $s = $(sel);
                const atual = $s.val();
                $s.find('option').each(function () {
                    const v = $(this).attr('value');
                    if (!v) return; // placeholder

                    if (allowed.size === 0) {
                        const manterSelecionada = atual && String(v) === String(atual);
                        $(this).prop('disabled', !manterSelecionada);
                        return;
                    }

                    const enable = allowed.has(String(v));
                    $(this).prop('disabled', !enable);
                });

                if (allowed.size > 0 && atual && !allowed.has(String(atual))) {
                    $s.val('');
                }

                if ($s.hasClass('js-selectpicker')) {
                    $s.selectpicker('refresh');
                }
            });
        }

        function estacaoPodeSerDevolucao(estacaoId) {
            if (!estacaoId) {
                return false;
            }

            return $('#estacao_devolucao_id option[value="' + estacaoId + '"]').length > 0;
        }

        function atualizarEstacoesDisponiveisPorDataHora(tipo, dataHora) {
            if (!dataHora) {
                return Promise.resolve([]);
            }

            const grupoId = $('#grupo_frota_id').val() || null;
            const modeloId = $('#modelo_id').val() || null;
            const reservaId = $('#reserva_id').val() || null;

            const params = new URLSearchParams({
                data_hora: dataHora,
                tipo: tipo || 'retirada',
            });
            if (grupoId) params.set('grupo_frota_id', grupoId);
            if (modeloId) params.set('modelo_id', modeloId);
            if (reservaId) params.set('reserva_id', reservaId);

            const seletor = tipo === 'devolucao' ? '#estacao_devolucao_id' : '#estacao_retirada_id';

            return fetch(ESTACOES_DISP_DIA_URL + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(d => {
                const ids = Array.isArray(d.estacao_ids) ? d.estacao_ids : [];
                aplicarEstacoesDisponiveis(ids, seletor);

                if (tipo === 'retirada' && !ids.length) {
                    $('#estacao_retirada_id').val('');
                    if ($('#estacao_retirada_id').hasClass('js-selectpicker')) {
                        $('#estacao_retirada_id').selectpicker('refresh');
                    }
                    mostrarErroReserva('Sem disponibilidade', 'Não há nenhuma estação com veículos disponíveis para essa data de retirada.');
                }

                return ids;
            })
            .catch(() => []);
        }

        function renderDatasRetiradaSugeridas(datas) {
            const $container = $('#datas_retirada_sugeridas');
            const $lista = $('#datas_retirada_sugeridas_lista');
            if (!$container.length || !$lista.length) {
                return;
            }

            $lista.empty();

            if (!Array.isArray(datas) || datas.length === 0) {
                $container.addClass('d-none');
                return;
            }

            datas.forEach(function(item) {
                if (!item || !item.value || !item.label) {
                    return;
                }

                const $btn = $('<button type="button" class="btn btn-outline-primary btn-sm mr-2 mb-2"></button>');
                $btn.text(item.label);
                $btn.on('click', function() {
                    $('#data_inicio').val(item.value).trigger('change');
                });
                $lista.append($btn);
            });

            $container.removeClass('d-none');
        }

        function carregarDatasRetiradaDisponiveis() {
            const estacaoId = $('#estacao_retirada_id').val();
            if (!estacaoId) {
                renderDatasRetiradaSugeridas([]);
                return;
            }

            const dataBase = $('#data_inicio').val() || null;
            const params = new URLSearchParams({
                estacao_id: estacaoId,
            });
            if (dataBase) {
                params.set('data_base', dataBase);
            }

            fetch(DATAS_RETIRADA_URL + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(d => renderDatasRetiradaSugeridas(d.datas || []))
            .catch(() => renderDatasRetiradaSugeridas([]));
        }

        function atualizarEstacoesDisponiveis() {
            const dataInicio = $('#data_inicio').val();
            const dataFim = $('#data_fim').val();
            if (!dataInicio || !dataFim) return;

            const grupoId = $('#grupo_frota_id').val() || null;
            const modeloId = $('#modelo_id').val() || null;
            const reservaId = $('#reserva_id').val() || null;

            const params = new URLSearchParams({
                data_inicio: dataInicio,
                data_fim: dataFim,
            });
            if (grupoId) params.set('grupo_frota_id', grupoId);
            if (modeloId) params.set('modelo_id', modeloId);
            if (reservaId) params.set('reserva_id', reservaId);

            fetch(ESTACOES_DISP_URL + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(d => aplicarEstacoesDisponiveis(d.estacao_ids || []))
            .catch(() => {});
        }

        $('#grupo_frota_id, #modelo_id').on('change', atualizarEstacoesDisponiveis);

        $('#btnConfirmarAgenda').on('click', function() {
            confirmarAgendaSelecionada();
        });

        $('#modalAgendaReserva').on('hidden.bs.modal', function() {
            $('#agenda_status').text('');
            $('#agenda_dias').empty();
            $('#agenda_horarios').empty();
            $('#agenda_horarios_vazio').removeClass('d-none').text('Selecione um dia para visualizar os horarios.');
            $('#agenda_valor_selecionado').val('');
            $('#btnConfirmarAgenda').prop('disabled', true);
            $('#agenda_horarios_card').removeClass('d-none');
            $('#btnConfirmarAgenda').removeClass('d-none');
            agendaReservaDados = [];
            agendaReservaDiaSelecionado = null;
            agendaReservaSlotSelecionado = null;
        });

        function validarDatas() {
            validarPeriodoReserva();
        }

        $('.handleGetGrupoVeiculos').on('change', function() {

            datasEstacoesValidas = handleGetGrupoVeiculos('change');

            estacao_retirada_id = $('#estacao_retirada_id').val();
            estacao_retirada_nome = $('#estacao_retirada_id').find('option:selected').text();
            data_inicio = $('#data_inicio').val();
            estacao_devolucao_id = $('#estacao_devolucao_id').val();
            estacao_devolucao_nome = $('#estacao_devolucao_id').find('option:selected').text();
            data_fim = $('#data_fim').val();

            setEstacaoRetirada(estacao_retirada_id,estacao_retirada_nome);
            setDataInicio(data_inicio);
            setEstacaoDevolucao(estacao_devolucao_id,estacao_devolucao_nome);
            setDataFim(data_fim);

            setQuantidadeDias(calculaQuantidadeDias(data_inicio, data_fim));

            atualizarMinhaReserva();

        });

        // ── Recalcula dias e atualiza resumo quando ambas as datas estão completas ──
        function recalcularDiasEAtualizar() {
            const ini = getDataInicio();
            const fim = getDataFim();
            if (ini && fim && ini.length > 10 && fim.length > 10) {
                const dias = calculaQuantidadeDias(ini, fim);
                setQuantidadeDias(Number.isFinite(dias) && dias > 0 ? dias : 0);
            } else {
                setQuantidadeDias(0);
            }
            atualizarMinhaReserva();
        }

        // ── Devolução: hora select ──────────────────────────────────────────────
        function atualizarDataFimDevolucao() {
            const data = $('#data_fim_data').val() || '';
            const hora = $('#data_fim_hora_fp').val() || '';
            if (!data) {
                $('#data_fim').val('');
                setDataFim('');
                setQuantidadeDias(0);
                atualizarMinhaReserva();
                $('#data_fim').trigger('change');
                return;
            }
            if (!hora) {
                $('#data_fim').val('');
                setDataFim(data);
                $('#data_fim').trigger('change');
                return;
            }
            const valor = `${data}T${hora}`;
            $('#data_fim').val(valor);
            setDataFim(valor);
            recalcularDiasEAtualizar();
            $('#data_fim').trigger('change');
        }

        function preencherHorariosDevolucao(slots, horaPreferida = null) {
            const $sel = $('#data_fim_hora_fp');
            const $fb  = $('#data_fim_hora_feedback');
            const lista = Array.isArray(slots)
                ? slots.map(normalizarSlotHorario).filter(Boolean)
                : [];

            $sel.empty().prop('disabled', true);

            if (!lista.length) {
                $sel.append('<option value="">Sem horários</option>');
                $fb.text('Sem horários disponíveis para esta data.');
                $('#data_fim_data').val('');
                setDataFim('');
                return [];
            }

            $sel.append('<option value="">Selecione a hora</option>');
            lista.forEach(function(slot) {
                const optVal = slot.value.includes('T') ? slot.value.split('T')[1].slice(0, 5) : String(slot.value).slice(0, 5);
                const texto = slot.taxa_extra
                    ? `* ${slot.label}  —  fora do expediente`
                    : slot.label;
                const $opt = $(`<option value="${optVal}" data-taxa-extra="${slot.taxa_extra ? '1' : '0'}">${texto}</option>`);
                $sel.append($opt);
            });
            $sel.prop('disabled', false);

            const _toTimeDev = function(v) { return String(v).includes('T') ? String(v).split('T')[1].slice(0, 5) : String(v).slice(0, 5); };
            const horaNorm = horaPreferida ? _toTimeDev(horaPreferida) : (lista.length === 1 ? _toTimeDev(lista[0].value) : '');
            if (horaNorm && $sel.find(`option[value="${horaNorm}"]`).length) {
                $sel.val(horaNorm);
            }
            const _initTaxaDevolucao = $sel.find('option:selected').data('taxa-extra') == '1';
            $('#data_fim_taxa_aviso').toggleClass('d-none', !_initTaxaDevolucao);
            $sel.css({ 'border-color': _initTaxaDevolucao ? '#f59e0b' : '', 'color': _initTaxaDevolucao ? '#92400e' : '', 'font-weight': _initTaxaDevolucao ? '600' : '' });
            $fb.text('Escolha a hora de devolução.');
            atualizarDataFimDevolucao();
            return lista;
        }

        $('#data_fim_hora_fp').on('change', function() {
            const temTaxa = $(this).find('option:selected').data('taxa-extra') == '1';
            $('#data_fim_taxa_aviso').toggleClass('d-none', !temTaxa);
            $(this).toggleClass('taxa-extra-sel', temTaxa).css({ 'border-color': '', 'color': '', 'font-weight': '' });
            if (typeof setTaxaEntrega === 'function') {
                const estacaoId = $('#estacao_devolucao_id').val();
                const info = ESTACOES_TAXAS[estacaoId];
                const valor = (temTaxa && info && info.cobrar_na_entrega === 'S') ? parseFloat(info.valor_taxa_entrega || 0) : 0;
                setTaxaEntrega(valor);
                atualizarMinhaReserva();
            }
            atualizarDataFimDevolucao();
        });
        $('#data_inicio_hora').on('change', function() {
            const temTaxa = $(this).find('option:selected').data('taxa-extra') == '1';
            $('#data_inicio_taxa_aviso').toggleClass('d-none', !temTaxa);
            $(this).toggleClass('taxa-extra-sel', temTaxa).css({ 'border-color': '', 'color': '', 'font-weight': '' });
            if (typeof setTaxaColeta === 'function') {
                const estacaoId = $('#estacao_retirada_id').val();
                const info = ESTACOES_TAXAS[estacaoId];
                const valor = (temTaxa && info && info.cobrar_na_coleta === 'S') ? parseFloat(info.valor_taxa_coleta || 0) : 0;
                setTaxaColeta(valor);
                atualizarMinhaReserva();
            }
            atualizarDataInicioRetirada();
        });

        // ── Date Flatpickr + hora select ───────────────────────────────────────
        (function inicializarDatePickers() {
            const DIAS_DISPONIVEIS_URL = @json(route('reserva.diasSemDisponibilidade'));
            const _disponiveisCache = {};

            function obterDataHojeIso() {
                const agora = new Date();
                const ano = agora.getFullYear();
                const mes = String(agora.getMonth() + 1).padStart(2, '0');
                const dia = String(agora.getDate()).padStart(2, '0');
                return `${ano}-${mes}-${dia}`;
            }

            function fetchDiasDisponiveis(estacaoId, tipo = 'retirada') {
                const tipoAgenda = tipo || 'retirada';
                const cacheKey = `${tipoAgenda}|${estacaoId}`;
                if (_disponiveisCache[cacheKey]) return Promise.resolve(_disponiveisCache[cacheKey]);

                return fetch(DIAS_DISPONIVEIS_URL + '?estacao_id=' + estacaoId + '&dias=90&tipo=' + tipoAgenda, { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(d => {
                        _disponiveisCache[cacheKey] = {
                            datas: d.dias_disponiveis || [],
                            mensagem: d.mensagem || '',
                        };
                        return _disponiveisCache[cacheKey];
                    })
                    .catch(() => ({ datas: [], mensagem: '' }));
            }

            function criarPicker(selectorInput, disponiveis, onDateSelected, minDate) {
                const disponiveisSet = new Set((disponiveis || []).map(function(item) { return item.data; }));
                return flatpickr(selectorInput, {
                    locale: ptLocale, dateFormat: 'd/m/Y',
                    minDate: minDate || 'today',
                    disableMobile: true,
                    disable: [function(date) {
                        const isoStr = date.getFullYear() + '-' + String(date.getMonth()+1).padStart(2,'0') + '-' + String(date.getDate()).padStart(2,'0');
                        return !disponiveisSet.has(isoStr);
                    }],
                    onChange: function(selectedDates) {
                        if (!selectedDates.length) return;
                        const d = selectedDates[0];
                        const isoStr = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
                        onDateSelected(isoStr, d.getDay());
                    }
                });
            }

            // Retorna a data de retirada selecionada como objeto Date ou null
            function getDataRetiradaSelecionada() {
                const v = $('#data_inicio_data').val();
                if (!v) return null;
                const d = new Date(v + 'T00:00:00');
                return isNaN(d.getTime()) ? null : d;
            }

            // Retorna a data mínima permitida para devolução (dia seguinte à retirada)
            function getMinDateDevolucao() {
                const ret = getDataRetiradaSelecionada();
                if (!ret) return null;
                const min = new Date(ret.getTime() + 86400000);
                return min;
            }

            function recarregarPickerRetirada(estacaoId) {
                if (_pickerDataRetirada) { _pickerDataRetirada.destroy(); _pickerDataRetirada = null; }
                $('#data_inicio_hora').empty().append('<option value="">Selecione a data</option>').prop('disabled', true);
                $('#data_inicio_hora_feedback').text('Escolha uma data.');
                $('#data_inicio_flatpickr').prop('disabled', true).val('');

                if (!estacaoId) {
                    $('#data_inicio_dias_feedback').text('Selecione a estação para ver os dias disponíveis.');
                    return;
                }
                $('#data_inicio_dias_feedback').text('Verificando disponibilidade...');

                fetchDiasDisponiveis(estacaoId, 'retirada').then(function(resultado) {
                    const disponiveis = Array.isArray(resultado?.datas) ? resultado.datas : [];
                    const mensagem = (resultado?.mensagem || '').trim();

                    if (!disponiveis.length) {
                        $('#data_inicio_dias_feedback')
                            .text(mensagem || 'Nenhuma data de retirada disponível para a estação selecionada.')
                            .removeClass('text-muted')
                            .addClass('text-danger');
                        return;
                    }

                    $('#data_inicio_dias_feedback')
                        .text('Selecione a data de retirada.')
                        .removeClass('text-danger')
                        .addClass('text-muted');
                    $('#data_inicio_flatpickr').prop('disabled', false).attr('placeholder', 'Clique para selecionar');
                    _pickerDataRetirada = criarPicker('#data_inicio_flatpickr', disponiveis, function(isoStr) {
                        buscarHorariosAoSelecionarData('retirada', isoStr);
                        // Atualiza minDate do picker de devolução ao selecionar retirada
                        atualizarMinDateDevolucao();
                    });
                }).catch(function() {
                    $('#data_inicio_dias_feedback').text('Não foi possível verificar a disponibilidade.');
                });
            }

            function recarregarPickerDevolucao(estacaoId, minDate) {
                if (_pickerDataDevolucao) { _pickerDataDevolucao.destroy(); _pickerDataDevolucao = null; }
                $('#data_fim_hora_fp').empty().append('<option value="">Selecione a data</option>').prop('disabled', true);
                $('#data_fim_hora_feedback').text('Escolha uma data.');
                $('#data_fim_flatpickr').prop('disabled', true).val('');

                if (!estacaoId) {
                    $('#data_fim_dias_feedback').text('Selecione a estação para ver os dias disponíveis.');
                    return;
                }

                // Exige data de retirada válida antes de habilitar devolução
                const minDev = minDate || getMinDateDevolucao();
                if (!minDev) {
                    $('#data_fim_dias_feedback').text('Selecione primeiro a data de retirada.');
                    $('#data_fim_flatpickr').prop('disabled', true).attr('placeholder', 'Selecione a data de retirada primeiro');
                    _ultimaEstacaoDevolucaoId = estacaoId; // guarda para recriar depois
                    return;
                }

                $('#data_fim_dias_feedback').text('Verificando disponibilidade...');

                fetchDiasDisponiveis(estacaoId, 'devolucao').then(function(resultado) {
                    const disponiveis = Array.isArray(resultado?.datas) ? resultado.datas : [];
                    const mensagem = (resultado?.mensagem || '').trim();

                    if (!disponiveis.length) {
                        $('#data_fim_dias_feedback')
                            .text(mensagem || 'Nenhuma data de devolução disponível para a estação selecionada.')
                            .removeClass('text-muted')
                            .addClass('text-danger');
                        return;
                    }

                    $('#data_fim_dias_feedback')
                        .text('Selecione a data de devolução.')
                        .removeClass('text-danger')
                        .addClass('text-muted');
                    $('#data_fim_flatpickr').prop('disabled', false).attr('placeholder', 'Clique para selecionar');
                    _pickerDataDevolucao = criarPicker('#data_fim_flatpickr', disponiveis, function(isoStr) {
                        buscarHorariosAoSelecionarData('devolucao', isoStr);
                    }, minDev);
                }).catch(function() {
                    $('#data_fim_dias_feedback').text('Não foi possível verificar a disponibilidade.');
                });
            }

            // Atualiza (ou cria) o picker de devolução com o novo minDate após retirada ser selecionada
            function atualizarMinDateDevolucao() {
                const minDev = getMinDateDevolucao();
                if (!minDev) return;

                const estacaoDevId = $('#estacao_devolucao_id').val() || _ultimaEstacaoDevolucaoId;
                if (!estacaoDevId) return;

                // Se já existe picker criado, só atualiza minDate e limpa se necessário
                if (_pickerDataDevolucao) {
                    _pickerDataDevolucao.set('minDate', minDev);
                    // Se data atual de devolução é antes do novo mínimo, limpar
                    const selDev = _pickerDataDevolucao.selectedDates[0];
                    if (selDev && selDev <= minDev) {
                        _pickerDataDevolucao.clear();
                        $('#data_fim_data').val('');
                        $('#data_fim_hora_fp').val('').prop('disabled', true);
                        $('#data_fim').val('').trigger('change');
                        $('#data_fim_dias_feedback').text('Data de devolução resetada. Selecione uma nova data.');
                    }
                    return;
                }

                // Picker ainda não criado (estação selecionada mas retirada não tinha sido escolhida)
                recarregarPickerDevolucao(estacaoDevId, minDev);
            }

            let _ultimaEstacaoDevolucaoId = null;

            $('#estacao_retirada_id').on('change.fpRetirada', function() {
                $('#data_inicio_data').val('');
                $('#data_inicio').val('').trigger('change');
                recarregarPickerRetirada($(this).val());
                // Resetar devolução ao trocar estação de retirada
                if (_pickerDataDevolucao) { _pickerDataDevolucao.clear(); }
                $('#data_fim_data').val('');
                $('#data_fim_hora_fp').val('').prop('disabled', true);
                $('#data_fim').val('').trigger('change');
                atualizarMinDateDevolucao();
            });

            $('#estacao_devolucao_id').on('change.fpDevolucao', function() {
                _ultimaEstacaoDevolucaoId = $(this).val();
                $('#data_fim_data').val('');
                $('#data_fim_hora_fp').val('').prop('disabled', true);
                $('#data_fim').val('').trigger('change');
                recarregarPickerDevolucao($(this).val());
            });

            const estacaoRetiradaInicial = $('#estacao_retirada_id').val();
            const estacaoDevolucaoInicial = $('#estacao_devolucao_id').val();
            _ultimaEstacaoDevolucaoId = estacaoDevolucaoInicial || null;
            if (estacaoRetiradaInicial) recarregarPickerRetirada(estacaoRetiradaInicial);
            else $('#data_inicio_flatpickr').prop('disabled', true);
            if (estacaoDevolucaoInicial) recarregarPickerDevolucao(estacaoDevolucaoInicial);
            else $('#data_fim_flatpickr').prop('disabled', true);

            // Modo edição: pré-selecionar hora
            const dataInicioEdit = $('#data_inicio_data').val();
            const horaInicioEdit = @json($dataInicioSelecionada ? \Carbon\Carbon::parse($dataInicioSelecionada)->format('H:i') : '');
            const dataFimEdit = $('#data_fim_data').val();
            const horaFimEdit = @json($dataFimSelecionada ? \Carbon\Carbon::parse($dataFimSelecionada)->format('H:i') : '');

            if (dataInicioEdit && estacaoRetiradaInicial) {
                buscarHorariosAoSelecionarData('retirada', dataInicioEdit, horaInicioEdit || null);
            }
            if (dataFimEdit && estacaoDevolucaoInicial) {
                buscarHorariosAoSelecionarData('devolucao', dataFimEdit, horaFimEdit || null);
            }
        }());

        function handleGetGrupoVeiculos(modo = 'submit') {
            const campos = ['#estacao_retirada_id', '#data_inicio', '#estacao_devolucao_id', '#data_fim'];
            const inicioValorGrupo = $('#data_inicio').val();
            const fimValorGrupo = $('#data_fim').val();
            const datasPreenchidas = !!inicioValorGrupo && !!fimValorGrupo;
            let isValid = true;

            campos.forEach(function(selector) {
                const $campo = $(selector);
                const valor = $campo.val();
                const vazio = valor === '' || valor == null || (typeof valor === 'string' && valor.trim() === '');
                const ehDataFim = selector === '#data_fim';
                const ignorarVazio = modo === 'change' && ehDataFim && !datasPreenchidas;

                $campo.toggleClass('is-invalid', vazio && !ignorarVazio);
                if ($campo.hasClass('js-selectpicker')) {
                    $campo.closest('.bootstrap-select').find('> .dropdown-toggle').toggleClass('is-invalid', vazio && !ignorarVazio);
                }

                if (vazio && !ignorarVazio) {
                    isValid = false;
                }
            });

            if (modo === 'change' && !datasPreenchidas) {
                $('#data_fim').removeClass('is-invalid');
                return true;
            }

            if (!isValid) {
                return false;
            }

            // Mesma estacao e permitida.
            // A unica regra de consistencia aqui e: data de devolucao >= data de retirada.
            const dataInicioPeriodo = new Date($('#data_inicio').val());
            const dataFimPeriodo = new Date($('#data_fim').val());
            const datasInvalidas = isNaN(dataInicioPeriodo.getTime()) || isNaN(dataFimPeriodo.getTime()) || dataFimPeriodo < dataInicioPeriodo;

            $('#data_fim').toggleClass('is-invalid', datasInvalidas);
            if (datasInvalidas) {
                isValid = false;
            }

            return isValid;
        }

        function getMarcaESegmento(id, nome) {


            $.ajax({
                url: "{{ route('modelo.getMarcaESegmento') }}",
                type: 'GET',
                data: {
                    modelo_id: id
                },
                success: function(data) {
                    $('#marca').val(data.marca);
                    $('#segmento').val(data.segmento);
                }
            });
            setModelo(id, nome);
            atualizarMinhaReserva();
        }


        function obterOpcionais(id) {

            $.ajax({
                url: "{{ route('reserva.obterOpcionais') }}",
                type: 'GET',
                data: {
                    grupo_frota_id: id,
                    estacao_retirada_id: getEstacaoRetirada(),
                    data_inicio: getDataInicio(),
                    estacao_devolucao_id: getEstacaoDevolucao(),
                    data_fim: getDataFim(),
                },
                success: function(data) {
                    $('#opcionais').html(data);
                    if (typeof formatarPrecosOpcionais === 'function') {
                        formatarPrecosOpcionais();
                    }
                },
                error: function(error) {
                    console.error('Error fetching data:', error);
                }
            });
        }

        function obterModelosComVeiculosDisponiveis(selectElement) {
            const id = $(selectElement).val();
            const nome = $(selectElement).find('option:selected').text();
            $('#modelo_disponibilidade_feedback').text('').addClass('d-none');
            $('#estacao_veiculos_feedback')
                .text('Veículos disponíveis na estação selecionada: --')
                .removeClass('text-danger text-success')
                .addClass('text-muted');

            if (faltaPreencherEstacoesEDatas()) {
                mostrarErroReserva('Atenção!', 'Por favor, preencha as estações e as datas antes de selecionar o grupo.');
                $('#grupo_frota_id').val('');
                if ($('#grupo_frota_id').hasClass('js-selectpicker')) {
                    $('#grupo_frota_id').selectpicker('refresh');
                }
                return;
            }

            setGrupoFrota(id, nome);
            $('#grupo_frota_id_hidden').val(id);

            $.ajax({
                url: "{{ route('reserva.veiculosDisponiveis') }}",
                type: 'GET',
                data: {
                    grupo_frota_id: id,
                    estacao_retirada_id: getEstacaoRetirada(),
                    data_inicio: getDataInicio(),
                    estacao_devolucao_id: getEstacaoDevolucao(),
                    data_fim: getDataFim(),
                },
                success: function(data) {
                    handleSuccess(data);
                    obterOpcionais(id);
                },
                error: function(error) {
                    console.error('Error fetching data:', error);
                }
            });

        }

        function getAllModelos(id) {
            $.ajax({
                url: "{{ route('grupo_frota.getModelosPorGrupoFrota') }}",
                type: 'GET',
                data: {
                    grupo_frota_id: id
                },
                success: handleSuccess,
                error: function(error) {
                    console.error('Error fetching data:', error);
                }
            });
        }

        function populeModelosSelect(modelos, selectId) {
            var modeloSelect = $('#' + selectId);
            const listaModelos = Array.isArray(modelos) ? modelos : [];

            // Se for hidden input (sem seleção de modelo), apenas auto-seleciona o primeiro disponível
            if (modeloSelect.attr('type') === 'hidden') {
                if (listaModelos.length > 0 && !modeloSelect.val()) {
                    modeloSelect.val(listaModelos[0].id);
                    setModelo(listaModelos[0].id, listaModelos[0].nome);
                }
                return;
            }

            modeloSelect.empty();
            modeloSelect.append('<option value="">Selecione um modelo</option>');

            $.each(listaModelos, function(index, modelo) {
                modeloSelect.append('<option value="' + modelo.id + '">' + modelo.nome + '</option>');
            });

            const possuiModelos = listaModelos.length > 0;
            if (possuiModelos) {
                $(modeloSelect).removeAttr('disabled');
                $(modeloSelect).removeAttr('readonly');
            } else {
                $(modeloSelect).attr('disabled', 'disabled');
            }

            if (modeloSelect.hasClass('js-selectpicker')) {
                modeloSelect.selectpicker('refresh');
            }
        }

        function exibeModalGrupoVeiculos() {
            $('#modalGrupoVeiculos').modal('show');
        }


        function handleSuccess(data) {
            // Populate the model select dropdown
            populeModelosSelect(data.modelo, 'modelo');
            const avisoModelos = (data?.aviso_modelos || '').trim();
            const feedbackModelos = $('#modelo_disponibilidade_feedback');
            const feedbackEstacao = $('#estacao_veiculos_feedback');
            const qtdVeiculosEstacao = Number(data?.qtd_veiculos_estacao ?? 0);
            if (avisoModelos) {
                feedbackModelos.text(avisoModelos).removeClass('d-none');
            } else {
                feedbackModelos.text('').addClass('d-none');
            }

            if (Number.isFinite(qtdVeiculosEstacao)) {
                const texto = qtdVeiculosEstacao === 1
                    ? 'Veículos disponíveis na estação selecionada: 1'
                    : `Veículos disponíveis na estação selecionada: ${qtdVeiculosEstacao}`;

                feedbackEstacao
                    .text(texto)
                    .removeClass('text-muted text-danger text-success')
                    .addClass(qtdVeiculosEstacao > 0 ? 'text-success' : 'text-danger');
            } else {
                feedbackEstacao
                    .text('Veículos disponíveis na estação selecionada: --')
                    .removeClass('text-danger text-success')
                    .addClass('text-muted');
            }


            // Clear the existing optional items
            // $("#opcionais").empty();
            $('#minhaReserva_opcionals_ul').html('<li class="minha-reserva-list__empty">Selecione opcionais para aparecerem aqui.</li>');
            $("#tarifa_base").empty();
            $("#segmento").val('');
            $("#marca").val('');

            setGrupoFrota(data.grupo_frota.id, data.grupo_frota.nome);

            // Iterate over each group of optional items and generate the HTML
            // Object.values(data.tipo_opcional).forEach(grupo => {
            //     if (grupo.opcionais.length > 0) {
            //         const cardHtml = generateGrupoHtml(grupo);
            //         $("#opcionais").append(cardHtml);
            //     }
            // });


            // Definindo uma variavel global para armazenar todos os opcionais
            // window.opcionaisGlobais = [];
            // console.group('Opcionais Globais');

            removeAllOpcionalReserva();

            removeAllOpcional();
            for (const key in data.tipo_opcional) {
                // console.log(`Verificando tipo_opcional ${key}`);

                // Garantindo que opcionais existe e e um array
                if (data.tipo_opcional[key].opcionais && Array.isArray(data.tipo_opcional[key].opcionais)) {
                    // console.log(`Adicionando opcionais do tipo_opcional ${key}`, data.tipo_opcional[key].opcionais);
                    // Concatenando os opcionais ao array global
                    data.tipo_opcional[key].opcionais.forEach(opcional => {
                        addOpcional(opcional);
                    });
                } else {
                    // console.log(`Nenhum opcional encontrado para tipo_opcional ${key}`);
                }
            }

            // Agora 'opcionaisGlobais' contem todos os opcionais
            // console.log("Todos os opcionais:", window.opcionaisGlobais);

            $('#tarifa_base').text(formatarMoeda(data.grupo_frota.valor_diario));


            setTarifaBase(data.grupo_frota.valor_diario);
            atualizarMinhaReserva();
            // $('#opcionais').html(data.html_opcionals);
        }

        function generateOpcionalHtml(opcional) {
            const isPrecoIntegral = opcional.preco_por_dia == null ? true : false;

            if (isPrecoIntegral) {
                return `
                    <div class="border p-2 mx-1">
                        <input type="checkbox" name="opcionais[]" value="${opcional.id}" class="btn-check"
                            id="btn-check-${opcional.id}-outlined" autocomplete="off">
                        <label class="m-0" for="btn-check-${opcional.id}-outlined">${opcional.nome}</label><br>
                        <span>${formatarMoeda(opcional.preco_integral)}</span>
                    </div>
                `;
            } else {
                return `
                    <div class="border p-2 mx-1">

                        <label class="m-0" for="btn-check-${opcional.id}-outlined">${opcional.nome}</label><br>
                                            <span>${formatarMoeda(opcional.preco_por_dia)}</span>
                                            <br>
                        <span><strong style="font-size:0.7rem">Quantidade:</strong><br> <button class='btn btn-xs btn-dark'>+</button> 0 <button class='btn btn-xs btn-dark'>-</button> </span>

                    </div>
                `;


            }

        }

        function generateGrupoHtml(grupo) {
            const tipo = grupo.tipo;
            const opcionaisHtml = grupo.opcionais.map(generateOpcionalHtml).join('');

            return `
            <div class="card mt-1">
                <div class="card-body col-md-12">
                    <p class="text-xs text-bold mb-0">${tipo.nome}</p>
                    <hr class="my-1">
                    <div class="d-flex flex-row">
                        ${opcionaisHtml}
                    </div>
                </div>
            </div>
        `;
        }


        function adicionarOpcionalNoHtmlDaMinhaReserva(nome,quantidade,valor) {
            let novaQuantidade = quantidade;
            if(quantidade == true)  {
                novaQuantidade = 1;
            }

            // Remove placeholder
            $('#minhaReserva_opcionals_ul .minha-reserva-list__empty').remove();

            const html = `
                <li class="minha-reserva-list__item">
                    <span class="minha-reserva-list__label">
                        ${nome}
                        <span class="minha-reserva-qty">${novaQuantidade ? novaQuantidade + 'x' : '0x'}</span>
                    </span>
                    <span class="minha-reserva-list__value">${formatarMoeda(valor)}</span>
                </li>
            `;

            $('#minhaReserva_opcionals_ul').append(html.trim());

        }

        // ── Modal Pesquisa de Grupo de Veículos ─────────────────────────────
        (function() {
            const PESQUISAR_URL = @json(route('reserva.pesquisarGruposVeiculos'));
            let _pgfPage = 1;

            function pgfParams(page) {
                page = page || 1;
                const params = {
                    grupo:     $('#pgf_grupo').val().trim(),
                    modelo:    $('#pgf_modelo').val().trim(),
                    marca:     $('#pgf_marca').val().trim(),
                    segmento:  $('#pgf_segmento').val().trim(),
                    valor_min: $('#pgf_valor_min').val().trim(),
                    valor_max: $('#pgf_valor_max').val().trim(),
                    por_pagina: 10,
                    page: page,
                };
                // Include period for availability check — read live from DOM
                const _estRet = $('#estacao_retirada_id').val();
                const _dtIni  = $('#data_inicio').val();
                const _dtFim  = $('#data_fim').val();
                if (_estRet) params.estacao_retirada_id = _estRet;
                if (_dtIni)  params.data_inicio = _dtIni;
                if (_dtFim)  params.data_fim    = _dtFim;
                return new URLSearchParams(params).toString();
            }

            function pgfFormatarMoeda(v) {
                return 'R$ ' + Number(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function pgfRenderPaginacao(meta) {
                const $p = $('#pgf_paginacao').empty();
                if (meta.last_page <= 1) return;

                const mkBtn = (label, page, disabled, active) => {
                    const $b = $(`<button type="button" class="btn btn-xs ${active ? 'btn-primary' : 'btn-outline-secondary'}">${label}</button>`);
                    if (disabled) $b.prop('disabled', true);
                    else $b.on('click', () => pgfBuscar(page));
                    return $b;
                };

                $p.append(mkBtn('<i class="fas fa-chevron-left"></i>', meta.current_page - 1, meta.current_page <= 1, false));
                const start = Math.max(1, meta.current_page - 2);
                const end   = Math.min(meta.last_page, meta.current_page + 2);
                for (let i = start; i <= end; i++) {
                    $p.append(mkBtn(i, i, false, i === meta.current_page));
                }
                $p.append(mkBtn('<i class="fas fa-chevron-right"></i>', meta.current_page + 1, meta.current_page >= meta.last_page, false));
            }

            function pgfRenderLinhas(grupos) {
                const $tbody = $('#pgf_tbody').empty();
                const temPeriodo = !!($('#estacao_retirada_id').val() && $('#data_inicio').val() && $('#data_fim').val());
                grupos.forEach(function(g) {
                    const img = g.imagem_url
                        ? `<img src="${g.imagem_url}" style="width:40px;height:28px;object-fit:cover;border-radius:4px">`
                        : `<span class="text-muted"><i class="fas fa-car fa-lg"></i></span>`;

                    const modelosArr = g.modelos || [];
                    const totalModelos = modelosArr.length;
                    const collapseId = 'pgf_modelos_' + g.id;
                    const modelosBadges = modelosArr.map(m => `<span class="badge badge-light border mr-1 mb-1">${m.nome}</span>`).join('');
                    const modelos = totalModelos === 0
                        ? '<span class="text-muted">—</span>'
                        : `<button type="button" class="btn btn-sm btn-outline-secondary pgf-toggle-modelos"
                                data-target="${collapseId}" style="font-size:.75rem;padding:2px 8px">
                                <i class="fas fa-tags mr-1"></i>${totalModelos} modelo(s)
                           </button>
                           <div id="${collapseId}" class="pgf-modelos-collapse mt-1" style="display:none">${modelosBadges}</div>`;
                    const marcas    = (g.marcas   || []).join(', ') || '—';
                    const segmentos = (g.segmentos || []).join(', ') || '—';

                    let dispBadge = '';
                    let btnSelecionar = '';
                    if (!temPeriodo) {
                        // Período não preenchido — exibe botão normalmente sem verificação
                        dispBadge = '<span class="badge badge-secondary">Período não informado</span>';
                        btnSelecionar = `<button type="button" class="btn btn-sm btn-primary pgf-selecionar"
                            data-id="${g.id}" data-nome="${g.nome}" data-valor="${g.valor_diario}">
                            <i class="fas fa-check mr-1"></i>Selecionar
                        </button>`;
                    } else if (g.disponivel === true) {
                        dispBadge = `<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>${g.qtd_modelos_disponiveis} modelo(s) disponível(is)</span>`;
                        btnSelecionar = `<button type="button" class="btn btn-sm btn-success pgf-selecionar"
                            data-id="${g.id}" data-nome="${g.nome}" data-valor="${g.valor_diario}">
                            <i class="fas fa-check mr-1"></i>Selecionar
                        </button>`;
                    } else {
                        dispBadge = '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Sem disponibilidade</span>';
                        btnSelecionar = `<button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Sem veículos disponíveis para o período">
                            <i class="fas fa-ban mr-1"></i>Indisponível
                        </button>`;
                    }

                    const $tr = $(`
                        <tr>
                            <td class="text-center align-middle">${img}</td>
                            <td class="align-middle">
                                <div class="font-weight-bold">${g.nome}</div>
                                <div class="mt-1">${dispBadge}</div>
                            </td>
                            <td class="align-middle" style="max-width:220px">${modelos}</td>
                            <td class="align-middle">${marcas}</td>
                            <td class="align-middle">${segmentos}</td>
                            <td class="align-middle text-right text-success font-weight-bold">${pgfFormatarMoeda(g.valor_diario)}</td>
                            <td class="align-middle text-center">${btnSelecionar}</td>
                        </tr>
                    `);
                    $tbody.append($tr);
                });

                // Toggle expandir/recolher modelos
                $tbody.off('click', '.pgf-toggle-modelos').on('click', '.pgf-toggle-modelos', function () {
                    const targetId = $(this).data('target');
                    const $list = $('#' + targetId);
                    const aberto = $list.is(':visible');
                    $list.slideToggle(150);
                    $(this).html(aberto
                        ? `<i class="fas fa-tags mr-1"></i>${$list.children().length} modelo(s)`
                        : `<i class="fas fa-chevron-up mr-1"></i>Recolher`
                    );
                });
            }

            function pgfBuscar(page) {
                _pgfPage = page || 1;
                $('#pgf_loading').removeClass('d-none');
                $('#pgf_empty').addClass('d-none');
                $('#pgf_results').addClass('d-none');

                fetch(PESQUISAR_URL + '?' + pgfParams(_pgfPage), { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .then(function(resp) {
                        $('#pgf_loading').addClass('d-none');
                        if (!resp.data || !resp.data.length) {
                            $('#pgf_empty').removeClass('d-none');
                            return;
                        }
                        $('#pgf_total_label').text(resp.total + ' grupo(s) encontrado(s)');
                        pgfRenderLinhas(resp.data);
                        pgfRenderPaginacao(resp);
                        $('#pgf_results').removeClass('d-none');
                    })
                    .catch(function() {
                        $('#pgf_loading').addClass('d-none');
                        $('#pgf_empty').removeClass('d-none').find('p').text('Erro ao buscar. Tente novamente.');
                    });
            }

            // Buscar ao abrir o modal
            $('#modalPesquisaGrupo').on('shown.bs.modal', function() {
                if (!$('#pgf_tbody').children().length) {
                    pgfBuscar(1);
                }
            });

            $('#pgf_btn_buscar').on('click', function() { pgfBuscar(1); });

            $('#pgf_grupo, #pgf_modelo, #pgf_marca, #pgf_segmento, #pgf_valor_min, #pgf_valor_max').on('keypress', function(e) {
                if (e.which === 13) pgfBuscar(1);
            });

            $('#pgf_btn_limpar').on('click', function() {
                $('#pgf_grupo, #pgf_modelo, #pgf_marca, #pgf_segmento, #pgf_valor_min, #pgf_valor_max').val('');
                pgfBuscar(1);
            });

            // Selecionar grupo
            $(document).on('click', '.pgf-selecionar', function() {
                const grupoId   = $(this).data('id');
                const grupoNome = $(this).data('nome');
                const grupoValor = $(this).data('valor');

                // Atualizar o select de grupo
                const $sel = $('#grupo_frota_id');
                if ($sel.find('option[value="' + grupoId + '"]').length) {
                    $sel.val(grupoId);
                } else {
                    $sel.append(new Option(grupoNome, grupoId, true, true));
                }

                if ($sel.hasClass('js-selectpicker')) {
                    $sel.selectpicker('refresh');
                }

                // Fechar modal e disparar change para carregar modelos/opcionais
                $('#modalPesquisaGrupo').modal('hide');

                setTarifaBase(grupoValor);
                setGrupoFrota(grupoId, grupoNome);
                atualizarMinhaReserva();

                // Sincroniza select visual (disabled) e hidden
                const $selGrupo = $('#grupo_frota_id');
                if ($selGrupo.find('option[value="' + grupoId + '"]').length) {
                    $selGrupo.val(grupoId);
                } else {
                    $selGrupo.append(new Option(grupoNome, grupoId, true, true));
                    $selGrupo.val(grupoId);
                }
                if ($selGrupo.hasClass('js-selectpicker')) $selGrupo.selectpicker('refresh');
                $('#grupo_frota_id_hidden').val(grupoId);

                // Carregar modelos e opcionais como se tivesse mudado o select
                obterModelosComVeiculosDisponiveis(document.getElementById('grupo_frota_id'));
            });
        }());

        // ── Card de cliente ──────────────────────────────────────────────────
        (function initClienteCard() {
            const hiddenId   = document.getElementById('user_id');
            const hiddenNome = document.getElementById('user_display');
            const emptyState = document.getElementById('clienteEmptyState');
            const card       = document.getElementById('clienteSelectedCard');
            const nomeEl     = document.getElementById('clienteNomeDisplay');
            const idEl       = document.getElementById('clienteIdDisplay');
            const avatarEl   = document.getElementById('clienteAvatar');
            const submitBtn  = document.getElementById('btnSalvarReserva');

            function primeiraLetra(str) {
                return (str || 'U').trim().charAt(0).toUpperCase();
            }

            function atualizarCardCliente(id, nome) {
                const temCliente = !!(id && String(id).trim() !== '');
                emptyState?.classList.toggle('d-none', temCliente);
                card?.classList.toggle('d-none', !temCliente);

                if (temCliente) {
                    if (nomeEl)   nomeEl.textContent   = nome || 'Cliente #' + id;
                    if (idEl)     idEl.textContent      = '#' + id;
                    if (avatarEl) avatarEl.textContent  = primeiraLetra(nome);
                    if (hiddenNome) hiddenNome.value    = nome || '';
                }

                // Botão salvar: só habilitado se houver cliente
                if (submitBtn) {
                    submitBtn.disabled = !temCliente;
                    submitBtn.title    = temCliente ? '' : 'Selecione um cliente para salvar a reserva';
                }
            }

            // Observa mudanças no campo hidden user_id via MutationObserver + evento customizado
            hiddenId?.addEventListener('change', function() {
                atualizarCardCliente(this.value, hiddenNome?.value || '');
            });
            // Polling leve para capturar atribuições diretas ao .value
            let _lastUserId = hiddenId?.value || '';
            setInterval(function() {
                const cur = hiddenId?.value || '';
                if (cur !== _lastUserId) {
                    _lastUserId = cur;
                    atualizarCardCliente(cur, hiddenNome?.value || '');
                }
            }, 300);

            // Botão "Trocar" no card aponta para o mesmo modal de busca
            document.getElementById('btnBuscarUsuarioReserva2')?.addEventListener('click', function() {
                $('#modalBuscarUsuarioReserva').modal('show');
            });

            // Limpar cliente
            document.getElementById('btnLimparUsuarioReserva')?.addEventListener('click', function() {
                hiddenId.value = '';
                if (hiddenNome) hiddenNome.value = '';
                setUser('', '');
                atualizarCardCliente('', '');
                atualizarMinhaReserva();
            });

            // Estado inicial
            atualizarCardCliente(hiddenId?.value, hiddenNome?.value);
        })();

        // ── Validação completa no submit ──────────────────────────────────────
        (function initValidacaoSubmit() {
            const form = document.getElementById('formularioReserva');
            if (!form) return;

            const camposObrigatorios = [
                { id: 'estacao_retirada_id', label: 'Estação de Retirada',  step: 1 },
                { id: 'estacao_devolucao_id', label: 'Estação de Devolução', step: 1 },
                { id: 'data_inicio',          label: 'Data de Retirada',     step: 1 },
                { id: 'data_fim',             label: 'Data de Devolução',    step: 1 },
                { id: 'grupo_frota_id_hidden',label: 'Grupo de Veículos',    step: 2 },
                { id: 'user_id',              label: 'Cliente',              step: 4 },
            ];

            const submitBtn = document.getElementById('btnSalvarReserva');
            if (!submitBtn) return;

            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const faltando = camposObrigatorios.filter(c => {
                    const el = document.getElementById(c.id);
                    return !el || !el.value || el.value.trim() === '';
                });

                if (faltando.length > 0) {
                    const lista = faltando.map(c =>
                        `<li><i class="fas fa-exclamation-circle text-danger mr-1"></i><strong>${c.label}</strong></li>`
                    ).join('');

                    const alertHtml = `
                        <div style="text-align:left">
                            <p class="mb-2" style="font-size:.92rem;">Preencha os campos obrigatórios antes de salvar:</p>
                            <ul class="mb-0 pl-3" style="font-size:.88rem;list-style:none;">${lista}</ul>
                        </div>`;

                    mostrarErroReserva('Campos obrigatórios', alertHtml);

                    // Ir para o step do primeiro campo faltando
                    const primeiroStep = faltando[0].step;
                    if (typeof window.reservaWizardSetStep === 'function') {
                        window.reservaWizardSetStep(primeiroStep);
                    }
                    return;
                }

                // Tudo ok — submete
                form.requestSubmit ? form.requestSubmit() : form.submit();
            }, true);
        })();

    </script>
@stop











