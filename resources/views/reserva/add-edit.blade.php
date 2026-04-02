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
            'faltas' => [],
            'pode_reservar' => true,
        ];
        $moedaEmpresa = strtoupper($moedaEmpresa ?? 'BRL');
        $simboloMoedaEmpresa = $simboloMoedaEmpresa ?? ($moedaEmpresa === 'USD' ? '$' : ($moedaEmpresa === 'EUR' ? '€' : 'R$'));
        $qtdEstacoes = (int) ($preRequisitosReserva['qtd_estacoes'] ?? 0);
        $qtdGrupos = (int) ($preRequisitosReserva['qtd_grupos'] ?? 0);
        $qtdModelos = (int) ($preRequisitosReserva['qtd_modelos'] ?? 0);
        $linkCadastroEstacao = \Illuminate\Support\Facades\Route::has('estacao.create') ? route('estacao.create') : null;
        $linkCadastroGrupo = \Illuminate\Support\Facades\Route::has('grupo_frota.create') ? route('grupo_frota.create') : null;
        $linkCadastroModelo = \Illuminate\Support\Facades\Route::has('modelo.create') ? route('modelo.create') : null;
    @endphp
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Atencao!</h5>
            @foreach ($errors->all() as $error)
                <span>{{ $error }}</span>
            @endforeach
        </div>
    @endif
    <div class="reserva-top-header mb-3">
        <div class="reserva-top-header__badges">
            <span class="reserva-chip">Estacoes: {{ $preRequisitosReserva['qtd_estacoes'] }}</span>
            <span class="reserva-chip">Grupos: {{ $preRequisitosReserva['qtd_grupos'] }}</span>
            <span class="reserva-chip">Modelos: {{ $preRequisitosReserva['qtd_modelos'] }}</span>
            <span class="reserva-chip reserva-chip--info">Moeda: {{ $moedaEmpresa }}</span>
        </div>
        @if (!$preRequisitosReserva['pode_reservar'])
            <div class="reserva-top-header__warning">
                <div class="reserva-top-header__warning-title">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Pre-requisitos pendentes para reservar
                </div>
                @foreach ($preRequisitosReserva['faltas'] as $falta)
                    <div class="reserva-top-header__warning-item">{{ $falta }}</div>
                @endforeach
                <div class="reserva-top-header__links">
                    @if ($qtdEstacoes === 0 && $linkCadastroEstacao)
                        <a href="{{ $linkCadastroEstacao }}" class="reserva-link-action" target="_blank" rel="noopener noreferrer">Cadastrar estacao</a>
                    @endif
                    @if ($qtdGrupos === 0 && $linkCadastroGrupo)
                        <a href="{{ $linkCadastroGrupo }}" class="reserva-link-action" target="_blank" rel="noopener noreferrer">Cadastrar grupo de veiculos</a>
                    @endif
                    @if ($qtdModelos === 0 && $linkCadastroModelo)
                        <a href="{{ $linkCadastroModelo }}" class="reserva-link-action" target="_blank" rel="noopener noreferrer">Cadastrar modelo</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
    <div class="reserva-page">
    <div class="d-flex justify-content-between flex-wrap reserva-layout">
        <div class="col-xl-9 col-lg-9 col-md-9 col-sm-12 col-xs-12 card p-2 reserva-main-card">
            <div class="card-body">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" id="formularioReserva" rowClass="row" novalidate>

                    <x-section-division-header-form class="reserva-section-title">Estacoes e Datas</x-section-division-header-form>

                    <!-- Estações e Datas -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-select id="estacao_retirada_id" name="estacao_retirada_id" label="Estacao de Retirada"
                                :options="$estacoes" placeholder="Selecione uma estacao" :selected="old(
                                    'estacao_retirada_id',
                                    isset($reserva->estacao_retirada_id) ? $reserva->estacao_retirada_id : null,
                                )"
                                class="handleGetGrupoVeiculos form-control form-control-sm js-selectpicker" data-live-search="true" data-size="8" required />
                            @if ($qtdEstacoes === 0)
                                <small class="text-danger d-block mt-1">Nenhuma estacao disponivel para seu acesso.</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-input type="datetime-local" class="handleGetGrupoVeiculos form-control form-control-sm"
                                id="data_inicio" name="data_inicio" label="Data Retirada"
                                value="{{ $reserva->data_inicio ?? '' }}" required
                                onchange="validarDatas()"
                                min="{{ isset($reserva) ? '' : date('Y-m-d\TH:i') }}" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-select id="estacao_devolucao_id" name="estacao_devolucao_id"
                                label="Estacao de Devolucao" :options="$estacoes" placeholder="Selecione uma estacao"
                                :selected="old(
                                    'estacao_devolucao_id',
                                    isset($reserva->estacao_devolucao_id) ? $reserva->estacao_devolucao_id : null,
                                )" class="handleGetGrupoVeiculos form-control form-control-sm js-selectpicker" data-live-search="true" data-size="8" required />
                            @if ($qtdEstacoes === 0)
                                <small class="text-danger d-block mt-1">Cadastre/libere estacao para continuar.</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-input type="datetime-local" class="handleGetGrupoVeiculos form-control form-control-sm"
                                id="data_fim" name="data_fim" label="Data Devolucao" value="{{ $reserva->data_fim ?? '' }}"
                                required onchange="validarDatas()"
                                />
                        </div>
                    </div>


                    <x-section-division-header-form class="reserva-section-title">Veiculo</x-section-division-header-form>

                    <div class="col-md-8">
                        <div class="form-group">
                            <div class="form-group">
                                <label for="grupo_frota_id">Grupo de Veiculos</label>
                                <select name="grupo_frota_id" id="grupo_frota_id"
                                    class="form-control form-control-sm js-selectpicker"
                                    data-live-search="true"
                                    data-size="8"
                                    data-show-subtext="true"
                                    onchange="obterModelosComVeiculosDisponiveis(this)" required>
                                    <option value="" disabled selected>Selecione...</option>
                                    @foreach ($gruposVeiculo as $grupo)
                                        <option value="{{ $grupo->id }}" data-subtext="{{ $simboloMoedaEmpresa }} {{ number_format((float) $grupo->valor_diario, 2, ',', '.') }}"
                                            {{ old('grupo_frota_id', isset($reserva->grupo_frota_id) ? $reserva->grupo_frota_id : null) == $grupo->id ? 'selected' : '' }}>
                                            {{ $grupo->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($qtdGrupos === 0)
                                    <small class="text-danger d-block mt-1">Nenhum grupo de veiculos ativo disponivel.</small>
                                @endif
                            </div>

                        </div>
                    </div>
                     <div class="col-md-3">
                        <x-form-input maxlength="7" id="cupom_desconto" name="cupom_desconto" label="Cupom de Desconto"
                            value="{{ $reserva->cupomDesconto->codigo ?? '' }}" class="form-control form-control-sm" />
                    </div>
                    {{-- <div class="col-md-2 d-flex align-items-center">

                        <a class="btn btn-xs btn-info " data-toggle="modal" data-target="#modalPesquisa">
                            <i class="fa fa-search mr-1"></i>
                            Pesquisar
                        </a>
                    </div> --}}
                    <div class="col-md-4 pb-2">
                        @if (isset($reserva))
                            <x-form-select name="modelo_id" label="Modelo" :options="$modelos" :selected="old('modelo_id', isset($reserva->modelos) ? $reserva->modelos->id : null)"
                                id="modelo" onchange="getMarcaESegmento(this.value, $(this).find('option:selected').text())" placeholder="Selecione um modelo"
                                class="form-control form-control-sm js-selectpicker" data-live-search="true" data-size="8" required />
                        @else
                            <x-form-select name="modelo_id" label="Modelo" id="modelo" :options="[]"
                                onchange="getMarcaESegmento(this.value)" placeholder="Selecione primeiro o grupo de frotas"
                                class="form-control form-control-sm js-selectpicker" data-live-search="true" data-size="8" disabled required />
                        @endif
                        @if ($qtdModelos === 0)
                            <small class="text-danger d-block mt-1">Nenhum modelo ativo disponivel para reserva.</small>
                        @endif
                        <small id="modelo_disponibilidade_feedback" class="text-warning d-block mt-1 d-none"></small>
                    </div>
                    <div class="col-md-4">
                        <x-form-input type="text" class="form-control form-control-sm" disabled id="marca"
                            name="marca" label="Marca"
                            value="{{ isset($marcas) && isset($marcas->nome) ? $marcas->nome : '' }}" />
                    </div>
                    <div class="col-md-4">
                        <x-form-input type="text" class="form-control form-control-sm" disabled id="segmento"
                            name="segmento" label="Segmento"
                            value="{{ isset($segmentos) && isset($segmentos->nome) ? $segmentos->nome : '' }} " />
                    </div>


                    <x-section-division-header-form class="reserva-section-title">Opcionais</x-section-division-header-form>
                    <div id="opcionais"class="d-flex justify-content-center flex-wrap w-100" style="padding:1em;  background: linear-gradient(
                90deg,
                color-mix(in srgb, var(--empresa-cor-primaria, #1e293b) 90%, #000 10%) 0%,
                color-mix(in srgb, var(--empresa-cor-terciaria, #334155) 90%, #000 10%) 100%
            );">
                        <div class="alert alert-light col-md-8 d-flex align-items-center text-xs py-1 mt-1">
                            <i class="fa-solid fa-circle-info me-2"></i>
                            <span>Selecione um grupo de veiculos e um modelo para visualizar os opcionais disponiveis.</span>
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


                    <x-section-division-header-form class="reserva-section-title">Informacoes Pessoais</x-section-division-header-form>


                    <!-- Identificacoes -->
                    <div class="col-md-7">
                        <div class="form-group">
                            <x-form-select name="user_id" label="Usuario" :options="$users" id="user_id"
                                placeholder="Selecione um usuario" :selected="old('user_id', isset($reserva->user_id) ? $reserva->user_id : null)"
                                class="form-control form-control-sm js-selectpicker" data-live-search="true" data-size="8" required />

                            <div class="reserva-user-actions mt-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnNovoUsuarioModal">
                                    <i class="fas fa-user-plus mr-1"></i>Novo usuario
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnAtualizarUsuarios">
                                    <i class="fas fa-sync-alt mr-1"></i>Atualizar lista
                                </button>
                            </div>

                            @if (empty($users))
                                <small class="text-danger d-block mt-1">
                                    Nenhum usuario disponivel. Clique em "Novo usuario" para cadastrar sem sair da reserva.
                                </small>
                            @endif
                        </div>
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
                                label="Data de Cancelamento" value="{{ $reserva->data_cancelamento ?? '' }}" />
                        </div>
                    </div>
                </div> --}}

            <input type="hidden" id="reservaHidden" name="reservaHidden">
            <button type="submit" id="btnSalvarReserva"
                class="btn btn-primary btn-sm col-12 reserva-submit-btn"
                {{ !$preRequisitosReserva['pode_reservar'] ? 'disabled' : '' }}>
                Reservar
            </button>
            </x-form>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-xs-12 reserva-sidebar ">
            @component('reserva.components.card_valores')
            @endcomponent
        </div>
    </div>
    </div>
    @component('reserva.components.modal-pesquisa')
    @endcomponent

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

                    <form id="formNovoUsuarioReserva">
                        @csrf
                        <ul class="nav nav-pills reserva-user-modal__tabs" id="novoUsuarioReservaTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="tab-dados-pessoais-tab" data-toggle="pill" href="#tab-dados-pessoais" role="tab" aria-controls="tab-dados-pessoais" aria-selected="true">
                                    <i class="fas fa-user mr-1"></i> Dados Pessoais
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="tab-contato-endereco-tab" data-toggle="pill" href="#tab-contato-endereco" role="tab" aria-controls="tab-contato-endereco" aria-selected="false">
                                    <i class="fas fa-phone-alt mr-1"></i> Contato e Endereco
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="tab-perfil-acesso-tab" data-toggle="pill" href="#tab-perfil-acesso" role="tab" aria-controls="tab-perfil-acesso" aria-selected="false">
                                    <i class="fas fa-shield-alt mr-1"></i> Perfil e Acesso
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="tab-carta-conducao-tab" data-toggle="pill" href="#tab-carta-conducao" role="tab" aria-controls="tab-carta-conducao" aria-selected="false">
                                    <i class="fas fa-id-card mr-1"></i> Carta de Conducao
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="tab-seguranca-tab" data-toggle="pill" href="#tab-seguranca" role="tab" aria-controls="tab-seguranca" aria-selected="false">
                                    <i class="fas fa-lock mr-1"></i> Seguranca
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content reserva-user-modal__tab-content" id="novoUsuarioReservaTabsContent">
                            <div class="tab-pane fade show active" id="tab-dados-pessoais" role="tabpanel" aria-labelledby="tab-dados-pessoais-tab">
                                <div class="reserva-user-modal__section-title">Informacoes Pessoais</div>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label for="novo_usuario_nome">Nome</label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_nome" name="nome" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="novo_usuario_email">E-mail</label>
                                        <input type="email" class="form-control form-control-sm" id="novo_usuario_email" name="email" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="novo_usuario_sexo">Sexo</label>
                                        <select class="form-control form-control-sm js-selectpicker" id="novo_usuario_sexo" name="sexo" data-size="6" required>
                                            <option value="">Selecione...</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Feminino</option>
                                            <option value="O">Outro</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="novo_usuario_data_nascimento">Data de Nascimento</label>
                                        <input type="date" class="form-control form-control-sm" id="novo_usuario_data_nascimento" name="data_nascimento" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="novo_usuario_local_nascimento">Local de Nascimento</label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_local_nascimento" name="local_nascimento" required>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-contato-endereco" role="tabpanel" aria-labelledby="tab-contato-endereco-tab">
                                <div class="reserva-user-modal__section-title">Contato e Endereco</div>
                                <div class="form-row">
                                    <div class="col-md-3">
                                        <label for="novo_usuario_telefone">Telefone</label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_telefone" name="telefone" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="novo_usuario_celular">Celular</label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_celular" name="celular" placeholder="(99) 99999-9999" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="novo_usuario_pais_sigla">Pais</label>
                                        <select class="form-control form-control-sm js-selectpicker" id="novo_usuario_pais_sigla" name="pais_sigla" data-live-search="true" data-size="8" required>
                                            <option value="">Selecione...</option>
                                            @foreach (($paises ?? []) as $siglaPais => $nomePais)
                                                <option value="{{ $siglaPais }}">{{ $nomePais }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="novo_usuario_zip_code">Zip Code</label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_zip_code" name="zip_code" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="novo_usuario_cidade">Cidade</label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_cidade" name="cidade" required>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="novo_usuario_endereco">Endereco</label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_endereco" name="endereco" required>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-perfil-acesso" role="tabpanel" aria-labelledby="tab-perfil-acesso-tab">
                                <div class="reserva-user-modal__section-title">Perfil e Acesso</div>
                                <div class="form-row">
                                    <div class="col-md-4">
                                        <label for="novo_usuario_numero_identificacao">Numero de Identificacao</label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_numero_identificacao" name="numero_identificacao" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="novo_usuario_role_id">Regra de Usuario</label>
                                        <select class="form-control form-control-sm js-selectpicker" id="novo_usuario_role_id" name="role_id" data-live-search="true" data-size="8" required>
                                            <option value="">Selecione...</option>
                                            @foreach (($tiposUsuario ?? []) as $idTipoUsuario => $nomeTipoUsuario)
                                                <option value="{{ $idTipoUsuario }}">{{ $nomeTipoUsuario }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="d-block mb-1">Acesso por Estacao</label>
                                        <input type="hidden" name="acesso_todas_estacoes" value="0">
                                        <div class="custom-control custom-switch mt-1">
                                            <input type="checkbox" class="custom-control-input" id="novo_usuario_acesso_todas_estacoes" name="acesso_todas_estacoes" value="1" checked>
                                            <label class="custom-control-label" for="novo_usuario_acesso_todas_estacoes">
                                                Todas as estacoes
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 d-none" id="novo_usuario_estacoes_wrap">
                                        <label>Estacoes permitidas</label>
                                        <div class="reserva-user-modal__estacoes-wrap">
                                            @forelse (($estacoes ?? []) as $idEstacao => $nomeEstacao)
                                                <label class="reserva-user-modal__estacao-check">
                                                    <input
                                                        type="checkbox"
                                                        name="estacoes[]"
                                                        value="{{ $idEstacao }}"
                                                        class="novo-usuario-estacao-checkbox"
                                                    >
                                                    <span>{{ $nomeEstacao }}</span>
                                                </label>
                                            @empty
                                                <div class="text-muted small">Nenhuma estacao disponivel.</div>
                                            @endforelse
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            Quando desmarcado, o usuario so acessa dados das estacoes selecionadas.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-carta-conducao" role="tabpanel" aria-labelledby="tab-carta-conducao-tab">
                                <div class="reserva-user-modal__section-title">Carta de Conducao</div>
                                <div class="form-row">
                                    <div class="col-md-4">
                                        <label for="novo_usuario_numero_carta_conducao">Numero Carta de Conducao</label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_numero_carta_conducao" name="numero_carta_conducao" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="novo_usuario_local_emissao_carta_conducao">Local de Emissao</label>
                                        <input type="text" class="form-control form-control-sm" id="novo_usuario_local_emissao_carta_conducao" name="local_emissao_carta_conducao" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="novo_usuario_pais_emissao_carta_conducao">Pais de Emissao</label>
                                        <select class="form-control form-control-sm js-selectpicker" id="novo_usuario_pais_emissao_carta_conducao" name="pais_emissao_carta_conducao" data-live-search="true" data-size="8" required>
                                            <option value="">Selecione...</option>
                                            @foreach (($paises ?? []) as $siglaPais => $nomePais)
                                                <option value="{{ $siglaPais }}">{{ $nomePais }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="novo_usuario_data_emissao_carta_conducao">Data de Emissao</label>
                                        <input type="date" class="form-control form-control-sm" id="novo_usuario_data_emissao_carta_conducao" name="data_emissao_carta_conducao" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="novo_usuario_data_validade_carta_conducao">Data de Validade</label>
                                        <input type="date" class="form-control form-control-sm" id="novo_usuario_data_validade_carta_conducao" name="data_validade_carta_conducao">
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-seguranca" role="tabpanel" aria-labelledby="tab-seguranca-tab">
                                <div class="reserva-user-modal__section-title">Seguranca</div>
                                <div class="form-row">
                                    <div class="col-md-3">
                                        <label for="novo_usuario_password">Senha</label>
                                        <input type="password" class="form-control form-control-sm" id="novo_usuario_password" name="password" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="novo_usuario_confirmacao_senha">Confirmacao de Senha</label>
                                        <input type="password" class="form-control form-control-sm" id="novo_usuario_confirmacao_senha" name="confirmacao_senha" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarNovoUsuarioReserva">
                        <i class="fas fa-save mr-1"></i>Salvar usuario
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .reserva-page {
            --reserva-border: color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 35%, #dbe3ef 65%);
            --reserva-title: var(--empresa-cor-primaria, #0f172a);
        }
        .content-header h1 {
            background: linear-gradient(
                90deg,
                color-mix(in srgb, var(--empresa-cor-primaria, #1e293b) 90%, #000 10%) 0%,
                color-mix(in srgb, var(--empresa-cor-terciaria, #334155) 90%, #000 10%) 100%
            );
            color: #fff !important;
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
            background: #fffbeb;
            padding: 10px;
        }
        .reserva-top-header__warning-title {
            font-weight: 700;
            color: #92400e;
            margin-bottom: 6px;
            font-size: 13px;
        }
        .reserva-top-header__warning-item {
            font-size: 12px;
            color: #7c2d12;
            margin-bottom: 4px;
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
            border: 1px solid color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 65%, #ffffff 35%);
            color: var(--empresa-cor-primaria, #2563eb);
            background: #fff;
            border-radius: 999px;
            padding: 5px 11px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none !important;
            transition: all .15s ease;
        }
        .reserva-link-action:hover {
            color: #fff;
            background: var(--empresa-cor-primaria, #2563eb);
            border-color: var(--empresa-cor-primaria, #2563eb);
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
            padding: 9px 14px;
            margin-top: 8px;
            background: var(--empresa-cor-primaria, #2563eb) !important;
            border-color: var(--empresa-cor-primaria, #2563eb) !important;
            box-shadow: 0 8px 20px color-mix(in srgb, var(--empresa-cor-primaria, #2563eb) 30%, transparent 70%);
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

        .reserva-page .bootstrap-select .dropdown-item.active,
        .reserva-page .bootstrap-select .dropdown-item:active {
            background-color: var(--empresa-cor-primaria, #2563eb);
            color: #fff;
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

    <!-- Select2 / SelectPicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.18/dist/js/bootstrap-select.min.js"></script>
    <script src="{{ asset('js/reserva/reserva.js') }}"></script>
    <script>
        window.reservaCurrency = @json($moedaEmpresa);
        window.reservaCurrencySymbol = @json($simboloMoedaEmpresa);
        const podeReservar = @json((bool) ($preRequisitosReserva['pode_reservar'] ?? true));
        const mensagemPreRequisitos = @json(
            !empty($preRequisitosReserva['faltas'] ?? [])
                ? 'Nao e possivel reservar ainda. ' . implode(' ', $preRequisitosReserva['faltas'])
                : 'Nao e possivel reservar ainda.',
        );
        const rotaCriarUsuario = @json(route('usuario.store'));
        const rotaUsuariosSelect = @json(route('usuario.selectOptions'));
        const mapaCamposAbaNovoUsuario = {
            nome: '#tab-dados-pessoais-tab',
            email: '#tab-dados-pessoais-tab',
            sexo: '#tab-dados-pessoais-tab',
            data_nascimento: '#tab-dados-pessoais-tab',
            local_nascimento: '#tab-dados-pessoais-tab',
            telefone: '#tab-contato-endereco-tab',
            celular: '#tab-contato-endereco-tab',
            pais_sigla: '#tab-contato-endereco-tab',
            zip_code: '#tab-contato-endereco-tab',
            cidade: '#tab-contato-endereco-tab',
            endereco: '#tab-contato-endereco-tab',
            numero_identificacao: '#tab-perfil-acesso-tab',
            role_id: '#tab-perfil-acesso-tab',
            acesso_todas_estacoes: '#tab-perfil-acesso-tab',
            estacoes: '#tab-perfil-acesso-tab',
            numero_carta_conducao: '#tab-carta-conducao-tab',
            local_emissao_carta_conducao: '#tab-carta-conducao-tab',
            pais_emissao_carta_conducao: '#tab-carta-conducao-tab',
            data_emissao_carta_conducao: '#tab-carta-conducao-tab',
            data_validade_carta_conducao: '#tab-carta-conducao-tab',
            password: '#tab-seguranca-tab',
            confirmacao_senha: '#tab-seguranca-tab',
        };

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

        function exibirErrosNovoUsuario(erros = {}, mensagemPadrao = 'Nao foi possivel salvar o usuario.') {
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

            $('#novo_usuario_acesso_todas_estacoes').prop('checked', true);
            $('#novo_usuario_estacoes_wrap').addClass('d-none');
            $('#modalNovoUsuarioAlert').addClass('d-none').html('');

            form.find('.js-selectpicker').each(function() {
                $(this).val('');
            });
            initSelectPickers($('#modalNovoUsuarioReserva'));
            $('#novo_usuario_acesso_todas_estacoes').prop('checked', true);
            $('#novo_usuario_estacoes_wrap input[name="estacoes[]"]').prop('checked', false);
            abrirPrimeiraAbaNovoUsuario();
        }

        function toggleEstacoesNovoUsuario() {
            if ($('#novo_usuario_acesso_todas_estacoes').is(':checked')) {
                $('#novo_usuario_estacoes_wrap').addClass('d-none');
            } else {
                $('#novo_usuario_estacoes_wrap').removeClass('d-none');
            }
        }

        function adicionarUsuarioSelectReserva(usuario) {
            if (!usuario || !usuario.id) {
                return;
            }

            const select = $('#user_id');
            const usuarioId = String(usuario.id);
            let option = select.find(`option[value="${usuarioId}"]`);
            const label = usuario.nome || usuario.email || `Usuario #${usuarioId}`;

            if (option.length === 0) {
                select.append(`<option value="${usuarioId}">${label}</option>`);
                option = select.find(`option[value="${usuarioId}"]`);
            } else {
                option.text(label);
            }

            select.val(usuarioId);
            if (select.hasClass('js-selectpicker')) {
                select.selectpicker('refresh');
            }

            setUser(usuarioId, label);
            atualizarMinhaReserva();
        }

        function atualizarUsuariosSelectReserva(selecionarId = null) {
            const select = $('#user_id');
            const idAtual = selecionarId || select.val();

            return $.ajax({
                url: rotaUsuariosSelect,
                method: 'GET',
                dataType: 'json'
            }).done(function(response) {
                const usuarios = response?.usuarios || [];
                select.empty();
                select.append('<option value="" disabled>Selecione um usuario</option>');

                usuarios.forEach(function(usuario) {
                    select.append(`<option value="${usuario.id}">${usuario.nome}</option>`);
                });

                if (idAtual) {
                    select.val(String(idAtual));
                }

                if (!select.val()) {
                    select.prop('selectedIndex', 0);
                }

                if (select.hasClass('js-selectpicker')) {
                    select.selectpicker('refresh');
                }
            }).fail(function() {
                alert('Nao foi possivel atualizar a lista de usuarios.');
            });
        }

        $('#formularioReserva').on('submit', function(e) {
            if (!podeReservar) {
                e.preventDefault();
                alert(mensagemPreRequisitos);
                return false;
            }

            if (!handleGetGrupoVeiculos()) {
                e.preventDefault();
                alert('Preencha estacoes e datas validas para continuar.');
                return false;
            }

            e.preventDefault();
            // quero que formate os opcionais para eu levar para o servidor em input hidden
            $('#reservaHidden').val(JSON.stringify(getMinhaReserva()));

            this.submit();
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
                setModelo(reserva.modelo_id, reserva.modelos?.nome);
                setUser(reserva.user_id, reserva.usuario?.nome);
                if(opcionaisSelecionados.length > 0){
                    setOpcionaisReserva(opcionaisSelecionados);
                }
                setCupomDesconto(reserva.cupom_desconto);

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

            $('#novo_usuario_telefone').mask('(00) 00000-0000');
            $('#novo_usuario_celular').mask('(00) 00000-0000');

            $('#btnNovoUsuarioModal').on('click', function() {
                limparFormularioNovoUsuario();
                toggleEstacoesNovoUsuario();
                initSelectPickers($('#modalNovoUsuarioReserva'));
                abrirPrimeiraAbaNovoUsuario();
                $('#modalNovoUsuarioReserva').modal('show');
            });

            $('#novo_usuario_acesso_todas_estacoes').on('change', toggleEstacoesNovoUsuario);

            $('#btnAtualizarUsuarios').on('click', function() {
                const botao = $(this);
                const textoOriginal = botao.html();
                botao.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Atualizando');

                atualizarUsuariosSelectReserva()
                    .always(function() {
                        botao.prop('disabled', false).html(textoOriginal);
                    });
            });

            $('#btnSalvarNovoUsuarioReserva').on('click', function() {
                $('#formNovoUsuarioReserva').trigger('submit');
            });

            $('#formNovoUsuarioReserva').on('submit', function(event) {
                event.preventDefault();

                const form = $(this);
                const botaoSalvar = $('#btnSalvarNovoUsuarioReserva');
                const textoOriginal = botaoSalvar.html();

                $('#modalNovoUsuarioAlert').addClass('d-none').html('');
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

            // Inicializar Select2 quando o modal for aberto
            $('.select2').select2();
                // Inicializar Select2 quando o modal for aberto
            $('#modalPesquisa').on('shown.bs.modal', function() {
                $('.select2').select2({
                    dropdownParent: $('#modalPesquisa')
                });
            });

        });

        $('#cupom_desconto').on('input', function() {
            $.ajax({
                url: "{{ route('cupom_desconto.verificarCupom') }}",
                method: 'POST',
                data: {
                    cupom: $(this).val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.id) {
                        setCupomDesconto(response);
                    }else{
                        setCupomDesconto(null);
                    }
                    atualizarMinhaReserva();
                },
                error: function() {
                    alert('Erro ao verificar o cupom. Por favor, tente novamente.');
                }
            });
        });

        $('#data_inicio').on('change', function() {
            var dataInicio = new Date($(this).val());
            dataInicio.setDate(dataInicio.getDate() + 1); // Adiciona 1 dia a data de inicio
            var dataMinima = dataInicio.toISOString().split('T')[0] + 'T' + dataInicio.toTimeString().slice(0,5);
            $('#data_fim').attr('min', dataMinima);

            // Verifica se a data fim atual e menor que a nova data minima
            var dataFimAtual = new Date($('#data_fim').val());
            if (dataFimAtual < dataInicio) {
                $('#data_fim').val(dataMinima);
            }
        });

            $('#data_fim').on('change', function() {
                var dataFim = new Date($(this).val());
                var dataInicio = new Date($('#data_inicio').val());
                if (dataFim < dataInicio) {
                    alert('A data de devolucao nao pode ser anterior a data de retirada.');
                    $(this).val($('#data_inicio').val());
                }
            });

            $('#estacao_retirada_id, #estacao_devolucao_id, #data_inicio, #data_fim').on('change input', function() {
                this.setCustomValidity('');
            });

        $('#user_id').on('change', function() {
            setUser($(this).val(),  $(this).find('option:selected').text().trim());
            atualizarMinhaReserva();
        });

        function validarDatas() {
            let dataInicio = $('#data_inicio').val();
            let dataFim = $('#data_fim').val();

            if (dataInicio && dataFim) {
                let dataInicioObj = new Date(dataInicio);
                let dataFimObj = new Date(dataFim);

                if (dataInicioObj > dataFimObj) {
                    alert('A data de inicio nao pode ser maior que a data de fim.');
                    $('#data_fim').val('');
                }
            }
        }

        $('.handleGetGrupoVeiculos').on('change input', function() {

            datasEstacoesValidas = handleGetGrupoVeiculos();

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

            if (datasEstacoesValidas) {
                $.ajax({
                    url: "{{ route('grupo_frota.getGrupoFrotaDisponiveis') }}",
                    type: 'GET',
                    data: {
                        estacao_retirada_id: estacao_retirada_id,
                        data_inicio: data_inicio,
                        estacao_devolucao_id: estacao_devolucao_id,
                        data_fim: data_fim,
                    },
                    success: function(data) {
                        // console.log(1)
                        // $('#marca').val(data.marca);
                        // $('#segmento').val(data.segmento);
                    }
                });
            }
        });

        function handleGetGrupoVeiculos() {
            const campos = ['#estacao_retirada_id', '#data_inicio', '#estacao_devolucao_id', '#data_fim'];
            let isValid = true;

            campos.forEach(function(selector) {
                const $campo = $(selector);
                const valor = $campo.val();
                const vazio = valor === '' || valor == null || (typeof valor === 'string' && valor.trim() === '');

                $campo.toggleClass('is-invalid', vazio);
                if ($campo.hasClass('js-selectpicker')) {
                    $campo.closest('.bootstrap-select').find('> .dropdown-toggle').toggleClass('is-invalid', vazio);
                }

                if (vazio) {
                    isValid = false;
                }
            });

            if (!isValid) {
                return false;
            }

            // Mesma estacao e permitida.
            // A unica regra de consistencia aqui e: data de devolucao >= data de retirada.
            const dataInicio = new Date($('#data_inicio').val());
            const dataFim = new Date($('#data_fim').val());
            const datasInvalidas = isNaN(dataInicio.getTime()) || isNaN(dataFim.getTime()) || dataFim < dataInicio;

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

            if (faltaPreencherEstacoesEDatas()) {
                alert('Por favor, preencha as estacoes e as datas.');
                $('#grupo_frota_id').val('');
                if ($('#grupo_frota_id').hasClass('js-selectpicker')) {
                    $('#grupo_frota_id').selectpicker('refresh');
                }
            }

            setGrupoFrota(id, nome);

            $.ajax({
                url: "{{ route('reserva.veiculosDisponiveis') }}",
                type: 'GET',
                data: {
                    grupo_frota_id: id,
                    estacao_retirada_id: estacao_retirada_id,
                    data_inicio: data_inicio,
                    estacao_devolucao_id: estacao_devolucao_id,
                    data_fim: data_fim,
                },
                success: handleSuccess,
                error: function(error) {
                    console.error('Error fetching data:', error);
                }
            });

            obterOpcionais(id);

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
            modeloSelect.empty(); // Limpa as opcoes existentes
            modeloSelect.append('<option value="">Selecione um modelo</option>'); // Adiciona a opcao padrao

            // Itera sobre o array de modelos e adiciona cada um como uma opcao
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
            if (avisoModelos) {
                feedbackModelos.text(avisoModelos).removeClass('d-none');
            } else {
                feedbackModelos.text('').addClass('d-none');
            }


            // Clear the existing optional items
            // $("#opcionais").empty();
            $('#minhaReserva_opcionals_ul').html('')
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

            const html = ` <li class="d-flex px-3" style="border-bottom: 1px solid #9d9c9c4d;">
                <div class="col d-flex align-items-center" style="justify-content:left">
                    <strong>${nome}</strong> <span class="badge badge-secondary"
                        style="padding:3px;font-size:8px;background:#7f8c8d">${novaQuantidade ? novaQuantidade+'x' : '0x'}</span>
                </div>
                <div class="col-md-6 d-flex align-items-center" style="justify-content:right">
                    ${formatarMoeda(valor)}</div>

            </li>`;

            $('#minhaReserva_opcionals_ul').append(html);

        }
    </script>
@stop









