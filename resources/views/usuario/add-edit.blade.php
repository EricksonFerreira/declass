@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $isAllStations = (bool) old('acesso_todas_estacoes', $acessoTodasEstacoes ?? true);
        $selectedStations = array_map('strval', (array) old('estacoes', $estacoesSelecionadas ?? []));
        $isCreate = !isset($usuario);

        $step2Fields = ['telefone', 'celular', 'pais_sigla', 'zip_code', 'cidade', 'endereco'];
        $step3Fields = ['role_id', 'acesso_todas_estacoes', 'estacoes'];
        $step4Fields = ['numero_carta_conducao', 'local_emissao_carta_conducao', 'pais_emissao_carta_conducao', 'data_emissao_carta_conducao', 'data_validade_carta_conducao'];
        $step5Fields = ['password', 'confirmacao_senha'];

        $startStep = 1;
        if ($errors->any()) {
            foreach ($step2Fields as $f) { if ($errors->has($f) || $errors->has($f.'.*')) { $startStep = 2; break; } }
            if ($startStep === 1) { foreach ($step3Fields as $f) { if ($errors->has($f) || $errors->has($f.'.*')) { $startStep = 3; break; } } }
            if ($startStep === 1) { foreach ($step4Fields as $f) { if ($errors->has($f) || $errors->has($f.'.*')) { $startStep = 4; break; } } }
            if ($startStep === 1 && $isCreate) { foreach ($step5Fields as $f) { if ($errors->has($f) || $errors->has($f.'.*')) { $startStep = 5; break; } } }
        }
    @endphp

    @if ($errors->any())
        <div class="alert alert-danger col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12">
            <ul class="mb-0 pl-3">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <div class="card card-usuario shadow-sm border-0">
            <div class="card-header card-header-usuario border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Dividimos o cadastro em etapas para facilitar o preenchimento.</p>
                </div>
            </div>

            <div class="card-body p-4">
                {{-- Wizard semelhante ao cadastro de estações --}}
                <div class="ec-wizard-steps mb-4" id="wizardSteps" data-start-step="{{ $startStep }}">
                    @php
                        $steps = [
                            1 => ['icon' => 'fa-user',        'label' => 'Dados'],
                            2 => ['icon' => 'fa-phone',       'label' => 'Contato'],
                            3 => ['icon' => 'fa-shield-alt',  'label' => 'Acesso'],
                            4 => ['icon' => 'fa-id-card',     'label' => 'Carta'],
                        ];
                        if ($isCreate) {
                            $steps[5] = ['icon' => 'fa-lock', 'label' => 'Segurança'];
                        }
                    @endphp
                    @foreach($steps as $n => $s)
                        <button type="button" class="ec-step-btn" data-step="{{ $n }}">
                            <span class="ec-step-circle"><i class="fas {{ $s['icon'] }}"></i></span>
                            <span class="ec-step-label">{{ $s['label'] }}</span>
                        </button>
                        @if($n < count($steps))
                            <div class="ec-step-line"></div>
                        @endif
                    @endforeach
                </div>

                <x-campos-obrigatorios-pendentes
                    alert-class="js-usuario-validacao-alerta mb-3"
                    action-class="js-usuario-ir-passo-base"
                    summary-class="js-usuario-validacao-resumo"
                    list-class="js-usuario-validacao-lista"
                />

                <x-form method="{{ $metodo }}" action="{{ $rota }}" id="form" rowClass="row" novalidate>
                    <div class="col-12">
                        <ul class="nav nav-pills nav-usuario mb-3 d-none" id="usuario-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-geral-link" data-toggle="pill" href="#tab-geral" role="tab" aria-controls="tab-geral" aria-selected="true">
                                    <i class="fa fa-user mr-1"></i> Dados Pessoais
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-contato-link" data-toggle="pill" href="#tab-contato" role="tab" aria-controls="tab-contato" aria-selected="false">
                                    <i class="fa fa-phone mr-1"></i> Contato e Endereço
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-acesso-link" data-toggle="pill" href="#tab-acesso" role="tab" aria-controls="tab-acesso" aria-selected="false">
                                    <i class="fa fa-shield-alt mr-1"></i> Perfil e Acesso
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-carta-link" data-toggle="pill" href="#tab-carta" role="tab" aria-controls="tab-carta" aria-selected="false">
                                    <i class="fa fa-id-card mr-1"></i> Carta de Condução
                                </a>
                            </li>
                            @if ($isCreate)
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-seguranca-link" data-toggle="pill" href="#tab-seguranca" role="tab" aria-controls="tab-seguranca" aria-selected="false">
                                        <i class="fa fa-lock mr-1"></i> Segurança
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="col-12">
                        <div class="tab-content" id="usuario-tab-content">
                            <div class="tab-pane fade show active" id="tab-geral" role="tabpanel" aria-labelledby="tab-geral-link">
                                <div class="usuario-section-title">Informações Pessoais</div>
                                <div class="form-row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input type="text" class="form-control-sm" id="nome" name="nome"
                                                label="Nome" value="{{ old('nome', $usuario->nome ?? '') }}" required />
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input type="email" class="form-control-sm" id="email" name="email"
                                                label="E-mail" value="{{ old('email', $usuario->email ?? '') }}" required />
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-select name="sexo" label="Sexo" :options="$sexo" placeholder="Selecione ..."
                                                :selected="old('sexo', isset($usuario->sexo) ? $usuario->sexo : null)"
                                                class="form-control form-control-sm" required />
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="date" class="form-control-sm" id="data_nascimento"
                                                name="data_nascimento" label="Data de Nascimento"
                                                value="{{ old('data_nascimento', $usuario->data_nascimento ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="local_nascimento"
                                                name="local_nascimento" label="Local de Nascimento"
                                                value="{{ old('local_nascimento', $usuario->local_nascimento ?? '') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-contato" role="tabpanel" aria-labelledby="tab-contato-link">
                                <div class="usuario-section-title">Contato e Endereço</div>
                                <div class="form-row">
                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input type="text" class="form-control-sm" id="telefone" name="telefone"
                                                label="Telefone" value="{{ old('telefone', $usuario->telefone ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="celular" name="celular"
                                                placeholder="(99) 99999-9999" label="Celular"
                                                value="{{ old('celular', $usuario->celular ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group form-group-usuario">
                                            <x-form-select name="pais_sigla" label="País" :options="$paises" placeholder="Selecione um valor"
                                                :selected="old('pais_sigla', isset($usuario->pais_sigla) ? $usuario->pais_sigla : 'BR')"
                                                id="pais_sigla" class="form-control form-control-sm" required />
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-4">
                                        <div class="form-group form-group-usuario">
                                            <label class="mb-1" style="font-size:.85rem;font-weight:600">CPF <small class="text-muted font-weight-normal">(zip code)</small> <span class="text-danger">*</span></label>
                                            <input required type="text" class="form-control form-control-sm" id="cep" name="zip_code"
                                                value="{{ old('zip_code', $usuario->zip_code ?? '') }}"
                                                placeholder="000.000.000-00" maxlength="14" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-8">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="cidade" name="cidade"
                                                label="Cidade" value="{{ old('cidade', $usuario->cidade ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-5 col-md-12">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="endereco" name="endereco"
                                                label="Endereço" value="{{ old('endereco', $usuario->endereco ?? '') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-acesso" role="tabpanel" aria-labelledby="tab-acesso-link">
                                <div class="usuario-section-title">Perfil e Acesso</div>
                                <div class="form-row">
                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="numero_identificacao"
                                                name="numero_identificacao" label="Número de Identificação"
                                                value="{{ old('numero_identificacao', $usuario->numero_identificacao ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-select name="role_id" label="Regra de Usuário" :options="$tiposUsuario"
                                                placeholder="Selecione um valor"
                                                :selected="old('role_id', isset($usuario->role_id) ? $usuario->role_id : null)"
                                                class="form-control form-control-sm" required />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group form-group-usuario mb-1">
                                            <label class="mb-2">Acesso por Estação</label>
                                            <input type="hidden" name="acesso_todas_estacoes" value="0">

                                            <div class="custom-control custom-switch mb-2">
                                                <input type="checkbox" class="custom-control-input" id="acesso_todas_estacoes"
                                                    name="acesso_todas_estacoes" value="1" {{ $isAllStations ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="acesso_todas_estacoes">
                                                    Acesso a todas as estações
                                                </label>
                                            </div>
                                            <small class="text-muted d-block" id="estacoes-limit-hint"></small>
                                        </div>
                                    </div>

                                    <div class="col-12" id="estacoes-permitidas-wrap" style="{{ $isAllStations ? 'display:none;' : '' }}">
                                        <div class="estacoes-wrap">
                                            <label class="mb-1 d-block">Estações permitidas</label>
                                            <div class="estacoes-checkbox-grid">
                                                @forelse ($estacoes ?? [] as $estacaoId => $estacaoNome)
                                                    <label class="estacao-checkbox-item">
                                                        <input
                                                            type="checkbox"
                                                            name="estacoes[]"
                                                            value="{{ $estacaoId }}"
                                                            {{ in_array((string) $estacaoId, $selectedStations, true) ? 'checked' : '' }}
                                                        >
                                                        <span>{{ $estacaoNome }}</span>
                                                    </label>
                                                @empty
                                                    <div class="text-muted small">Nenhuma estação disponível.</div>
                                                @endforelse
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                Quando desmarcado, o usuário só acessa dados das estações selecionadas.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-carta" role="tabpanel" aria-labelledby="tab-carta-link">
                                <div class="usuario-section-title">Carta de Condução <small class="text-muted font-weight-normal" style="font-size:.8rem">(opcional)</small></div>
                                <div class="form-row">
                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input type="text" class="form-control-sm" id="numero_carta_conducao"
                                                name="numero_carta_conducao" label="Número da Carta de Condução"
                                                value="{{ old('numero_carta_conducao', $usuario->numero_carta_conducao ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input type="text" class="form-control-sm" id="local_emissao_carta_conducao"
                                                name="local_emissao_carta_conducao" label="Local de Emissão"
                                                value="{{ old('local_emissao_carta_conducao', $usuario->local_emissao_carta_conducao ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group form-group-usuario">
                                            <x-form-select name="pais_emissao_carta_conducao" label="País de Emissão" :options="$paises"
                                                placeholder="Selecione um valor"
                                                :selected="old(
                                                    'pais_emissao_carta_conducao',
                                                    isset($usuario->pais_emissao_carta_conducao) ? $usuario->pais_emissao_carta_conducao : 'BR',
                                                )"
                                                class="form-control form-control-sm" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input type="date" class="form-control-sm" id="data_emissao_carta_conducao"
                                                name="data_emissao_carta_conducao" label="Data de Emissão"
                                                value="{{ old('data_emissao_carta_conducao', $usuario->data_emissao_carta_conducao ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input type="date" class="form-control-sm" id="data_validade_carta_conducao"
                                                name="data_validade_carta_conducao" label="Data de Validade"
                                                value="{{ old('data_validade_carta_conducao', $usuario->data_validade_carta_conducao ?? '') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($isCreate)
                                <div class="tab-pane fade" id="tab-seguranca" role="tabpanel" aria-labelledby="tab-seguranca-link">
                                    <div class="usuario-section-title">Informações Privadas</div>
                                    <div class="form-row">
                                        <div class="col-lg-5 col-md-6">
                                            <div class="form-group form-group-usuario">
                                                <x-form-input required type="password" class="form-control-sm" id="senha"
                                                    name="password" label="Senha" value="{{ old('password', '') }}" />
                                            </div>
                                        </div>

                                        <div class="col-lg-5 col-md-6">
                                            <div class="form-group form-group-usuario">
                                                <x-form-input required type="password" class="form-control-sm" id="confirmacao_senha"
                                                    name="confirmacao_senha" label="Confirmacao de Senha"
                                                    value="{{ old('confirmacao_senha', '') }}" />
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div id="passwordValidation" class="alert alert-danger d-none" role="alert">
                                                <ul class="mb-0 pl-3">
                                                    <li id="length" class="d-none">A senha deve ter pelo menos 8 caracteres.</li>
                                                    <li id="uppercase" class="d-none">A senha deve conter pelo menos uma letra maiúscula.</li>
                                                    <li id="lowercase" class="d-none">A senha deve conter pelo menos uma letra minúscula.</li>
                                                    <li id="number" class="d-none">A senha deve conter pelo menos um número.</li>
                                                    <li id="special" class="d-none">A senha deve conter pelo menos um caractere especial.</li>
                                                    <li id="match" class="d-none">As senhas devem ser iguais.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 usuario-wizard-footer mt-4 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-sm ec-nav-btn ec-nav-btn--back d-none" id="btnPrev">
                            <i class="fas fa-chevron-left mr-1"></i> Voltar
                        </button>

                        <div class="usuario-wizard-footer__actions ml-auto d-flex align-items-center" style="gap:8px;">
                            <button type="button" class="btn btn-sm ec-nav-btn ec-nav-btn--next" id="btnNext">
                                <i class="fas fa-chevron-right mr-1"></i> Próximo
                            </button>
                            <button type="submit" class="btn btn-sm ec-nav-btn ec-nav-btn--save d-none" id="btnSalvarUsuario">
                                <i class="fas fa-save mr-1"></i> Salvar alterações
                            </button>
                        </div>
                    </div>
                </x-form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .auto-filled {
        background-color: #e0f7fa;
    }

    .card-usuario {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-usuario {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-usuario h4 {
        color: #0f2740;
        font-weight: 700;
    }

    /* Wizard Steps (mesmo padrão de Estações) */
    .ec-wizard-steps {
        display: flex;
        align-items: center;
        gap: 0;
        overflow-x: auto;
        padding-bottom: 4px;
    }
    .ec-step-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        background: none;
        border: none;
        padding: 0 8px;
        cursor: pointer;
        flex-shrink: 0;
    }
    .ec-step-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 2px solid #d0daea;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        color: #94a3b8;
        transition: all .2s;
    }
    .ec-step-btn.active .ec-step-circle {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }
    .ec-step-btn.done .ec-step-circle {
        background: #ecfdf5;
        border-color: #10b981;
        color: #10b981;
    }
    .ec-step-label {
        font-size: .72rem;
        font-weight: 600;
        color: #94a3b8;
        white-space: nowrap;
    }
    .ec-step-btn.active .ec-step-label { color: #2563eb; }
    .ec-step-btn.done .ec-step-label { color: #10b981; }
    .ec-step-line {
        flex: 1;
        height: 2px;
        background: #e5eaf2;
        min-width: 16px;
        margin-bottom: 18px;
    }

    .usuario-wizard-footer {
        padding-top: 14px;
        border-top: 1px solid #e5eaf2;
    }

    .ec-nav-btn {
        min-width: 140px;
        border-radius: 999px;
        font-weight: 700;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
        transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
    }

    .ec-nav-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(15, 23, 42, .12);
    }

    .ec-nav-btn--back {
        background: #fff;
        border: 1px solid #d4dceb;
        color: #334155;
    }

    .ec-nav-btn--next {
        background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
        border: 1px solid #2563eb;
        color: #fff;
    }

    .ec-nav-btn--save {
        background: linear-gradient(135deg, #16a34a 0%, #0f766e 100%);
        border: 1px solid #16a34a;
        color: #fff;
    }

    .ec-nav-btn--back:hover,
    .ec-nav-btn--next:hover,
    .ec-nav-btn--save:hover {
        color: #fff;
    }

    .nav-usuario .nav-link {
        border-radius: 8px;
        font-weight: 600;
        color: #334155;
        border: 1px solid #dbe3ef;
        margin-right: .45rem;
        margin-bottom: .45rem;
        background: #fff;
        font-size: .9rem;
    }

    .nav-usuario .nav-link.active {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
    }

    .usuario-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-usuario {
        margin-bottom: 1rem;
    }

    .card-usuario .form-control,
    .card-usuario .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-usuario .form-control:focus,
    .card-usuario .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .estacoes-wrap {
        border: 1px solid #dbe6f3;
        border-radius: 12px;
        padding: 12px;
        background: #f9fbfe;
        margin-bottom: 6px;
    }

    .estacoes-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 8px;
        max-height: 220px;
        overflow: auto;
        padding: 4px 2px;
    }

    .estacao-checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #d7e3f1;
        border-radius: 10px;
        padding: 8px 10px;
        background: #fff;
        cursor: pointer;
        margin: 0;
        transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
    }

    .estacao-checkbox-item:hover {
        border-color: #98b7de;
        background: #f7fbff;
    }

    .estacao-checkbox-item input[type="checkbox"] {
        accent-color: #2563eb;
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
    }

    .estacao-checkbox-item span {
        font-size: .86rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.2;
    }

    .btn-save-usuario {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-usuario {
            padding: .95rem 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }
    }

    @media (max-width: 767.98px) {
        .col-xl-10.col-lg-11.col-md-12.col-sm-12.col-xs-12.px-0 {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .card-header-usuario h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-usuario p {
            font-size: .85rem;
        }

        .usuario-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-usuario {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-usuario {
            margin-bottom: .8rem;
        }

        .card-usuario .form-control,
        .card-usuario .form-select {
            min-height: 36px;
            font-size: 13px;
        }

        .usuario-wizard-footer {
            flex-direction: column;
            align-items: stretch !important;
            gap: 10px;
        }

        .usuario-wizard-footer__actions {
            width: 100%;
            margin-left: 0 !important;
            justify-content: space-between;
        }

        .ec-nav-btn {
            min-width: 0;
            flex: 1 1 0;
        }
    }
</style>
@stop

@section('js')
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="{{ asset('js/sweetAlert.min.js') }}"></script>
    <script src="{{ asset('js/alertConfirmPequena.js') }}"></script>
    <script>
        const isCreateUsuario = @json($isCreate);
        const usuarioWizardStartStep = Number(document.getElementById('wizardSteps')?.dataset?.startStep || '1');
        const $usuarioStepBtns = $('.ec-step-btn');
        const $usuarioForm = $('#form');
        const $usuarioBtnPrev = $('#btnPrev');
        const $usuarioBtnNext = $('#btnNext');
        const $usuarioBtnSave = $('#btnSalvarUsuario');
        const $usuarioAlertaPendencias = $('.js-usuario-validacao-alerta');
        const $usuarioListaPendencias = $('.js-usuario-validacao-lista');
        const $usuarioResumoPendencias = $('.js-usuario-validacao-resumo');
        const $usuarioBotaoIrPassoBase = $('.js-usuario-ir-passo-base');
        const usuarioTotalSteps = $usuarioStepBtns.length || 1;
        const USUARIO_STEP_TABS = {
            1: '#tab-geral-link',
            2: '#tab-contato-link',
            3: '#tab-acesso-link',
            4: '#tab-carta-link',
            5: '#tab-seguranca-link',
        };
        const USUARIO_STEP_LABELS = {};
        $usuarioStepBtns.each(function () {
            const step = Number($(this).data('step') || 1);
            USUARIO_STEP_LABELS[step] = $(this).find('.ec-step-label').first().text().trim() || ('Etapa ' + step);
        });
        let usuarioCurrentStep = Math.min(Math.max(usuarioWizardStartStep, 1), usuarioTotalSteps);
        let usuarioUltimoPassoPendente = usuarioCurrentStep;

        function usuarioPane(step) {
            const linkSel = USUARIO_STEP_TABS[step] || USUARIO_STEP_TABS[1];
            return linkSel ? $(linkSel).closest('.tab-pane') : $();
        }

        function usuarioStepLabel(step) {
            return USUARIO_STEP_LABELS[step] || ('Etapa ' + step);
        }

        function escaparHtml(texto) {
            return String(texto ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function obterRotuloCampo($campo) {
            const rotulo = String($campo.data('field-label') ?? '').trim();
            if (rotulo) {
                return rotulo;
            }

            const $group = $campo.closest('.form-group');
            const textoLabel = String($group.find('label').first().text() ?? '').trim();
            if (textoLabel) {
                return textoLabel;
            }

            return String($campo.attr('name') ?? 'Campo').replace(/\[\]$/, '');
        }

        function obterPassoCampo($campo) {
            const tabId = String($campo.closest('.tab-pane').attr('id') || '');
            const stepMap = {
                'tab-geral': 1,
                'tab-contato': 2,
                'tab-acesso': 3,
                'tab-carta': 4,
                'tab-seguranca': 5,
            };

            return stepMap[tabId] || 1;
        }

        function obterElementoFoco($campo) {
            const mapa = {
                zip_code: '#cep',
                role_id: 'select[name="role_id"]',
                acesso_todas_estacoes: '#acesso_todas_estacoes',
                estacoes: 'input[name="estacoes[]"]',
                password: '#senha',
                confirmacao_senha: '#confirmacao_senha',
            };

            const id = String($campo.attr('id') || '');
            const name = String($campo.attr('name') || '').replace(/\[\]$/, '');
            const seletor = mapa[id] || mapa[name];

            if (seletor && $(seletor).length) {
                return $(seletor).first();
            }

            return $campo;
        }

        function mensagemObrigatoria($campo) {
            const rotulo = obterRotuloCampo($campo);
            return rotulo ? ('O campo ' + rotulo + ' é obrigatório.') : 'Este campo é obrigatório.';
        }

        function obterAjudaCampo($campo) {
            const id = String($campo.attr('id') || '');
            const ajuda = {
                senha: 'A senha deve ter pelo menos 8 caracteres, uma letra maiúscula, uma minúscula, um número e um caractere especial.',
                confirmacao_senha: 'As senhas devem ser iguais.',
                data_emissao_carta_conducao: 'Use uma data válida de emissão.',
                data_validade_carta_conducao: 'Deve ser maior que a data de emissão.',
            };

            return ajuda[id] || '';
        }

        function limparPendencias() {
            $usuarioAlertaPendencias.addClass('d-none').removeClass('is-live');
            $usuarioListaPendencias.empty();
            $usuarioResumoPendencias.empty();
            $('.ec-step-btn').removeClass('warning');
            $usuarioForm.find('.js-validacao-manual')
                .removeClass('is-invalid js-validacao-manual')
                .removeAttr('aria-invalid')
                .removeAttr('title')
                .removeAttr('data-toggle')
                .removeAttr('data-placement');
        }

        function montarLinhaPendencia(item) {
            const stepLabel = usuarioStepLabel(item.step);
            return [
                '<div class="pre-requisitos-alerta__detail-item">',
                '<span class="pre-requisitos-alerta__detail-label">' + escaparHtml(item.label) + '</span>',
                '<span class="badge badge-warning text-dark">' + escaparHtml(stepLabel) + '</span>',
                item.message ? '<div class="pre-requisitos-alerta__detail-message">' + escaparHtml(item.message) + '</div>' : '',
                '</div>',
            ].join('');
        }

        function mostrarPendencias(pendencias) {
            limparPendencias();

            if (!pendencias.length) {
                return true;
            }

            const passosPendentes = [...new Set(pendencias.map(function (item) {
                return item.step;
            }))];
            usuarioUltimoPassoPendente = Math.min(...passosPendentes);

            passosPendentes.forEach(function (step) {
                $('.ec-step-btn[data-step="' + step + '"]').addClass('warning');
            });

            $usuarioResumoPendencias.text('Encontramos ' + pendencias.length + ' item(ns) obrigatório(s) pendente(s).');
            $usuarioBotaoIrPassoBase.text('Ir para o passo ' + usuarioStepLabel(usuarioUltimoPassoPendente));
            $usuarioListaPendencias.html(pendencias.map(montarLinhaPendencia).join(''));
            $usuarioAlertaPendencias.removeClass('d-none').addClass('is-live');

            if (usuarioCurrentStep !== usuarioUltimoPassoPendente) {
                setWizardStep(usuarioUltimoPassoPendente);
            }

            window.requestAnimationFrame(function () {
                if ($usuarioAlertaPendencias.length) {
                    $usuarioAlertaPendencias[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                }
            });

            const $primeiroCampoInvalido = pendencias[0]?.focusTarget || pendencias[0]?.field;
            if ($primeiroCampoInvalido && $primeiroCampoInvalido.length) {
                window.setTimeout(function () {
                    $primeiroCampoInvalido.trigger('focus');
                }, 150);
            }

            return false;
        }

        function coletarPendencias($escopo) {
            const pendencias = [];

            $escopo.find(':invalid').each(function () {
                const $campo = $(this);

                if ($campo.is(':disabled')) {
                    return;
                }

                $campo.addClass('is-invalid js-validacao-manual').attr('aria-invalid', 'true');

                const field = $campo[0];
                let mensagem = '';

                if (field?.validity?.valueMissing) {
                    mensagem = mensagemObrigatoria($campo);
                } else {
                    mensagem = String(field?.validationMessage || '').trim();
                }

                if (!mensagem) {
                    mensagem = obterAjudaCampo($campo);
                }

                if (mensagem) {
                    $campo.attr('title', mensagem);
                }

                pendencias.push({
                    step: obterPassoCampo($campo),
                    label: obterRotuloCampo($campo),
                    message: mensagem,
                    field: $campo,
                    focusTarget: obterElementoFoco($campo),
                });
            });

            return pendencias;
        }

        function setWizardStep(step) {
            const targetStep = Math.max(1, Math.min(Number(step || 1), usuarioTotalSteps));
            usuarioCurrentStep = targetStep;

            const linkSel = USUARIO_STEP_TABS[targetStep] || USUARIO_STEP_TABS[1];

            try {
                $(linkSel).tab('show');
            } catch (e) {}

            $('.ec-step-btn').each(function () {
                const n = Number($(this).data('step') || 1);
                $(this).toggleClass('active', n === targetStep);
                $(this).toggleClass('done', n < targetStep);
            });

            $usuarioBtnPrev.toggleClass('d-none', targetStep <= 1);
            $usuarioBtnNext.toggleClass('d-none', targetStep >= usuarioTotalSteps);
            $usuarioBtnSave.toggleClass('d-none', targetStep < usuarioTotalSteps);
        }

        function applyRoleStationUiRules() {
            const roleSel = document.querySelector('select[name="role_id"]');
            const hint = document.getElementById('estacoes-limit-hint');
            if (!roleSel) return;

            const roleName = (roleSel.options[roleSel.selectedIndex]?.text || '').trim().toLowerCase();
            const switchEl = document.getElementById('acesso_todas_estacoes');
            const boxes = Array.from(document.querySelectorAll('input[name="estacoes[]"]'));

            let max = null;
            let forceAll = false;
            let forceNone = false;

            if (roleName === 'gerente') {
                forceAll = true;
            } else if (roleName === 'colaborador administrativo' || roleName === 'colaborador_administrativo') {
                max = 1;
            } else if (roleName === 'colaborador chefe' || roleName === 'colaborador_chefe') {
                max = 3;
            } else if (roleName === 'cliente') {
                forceNone = true;
                max = 0;
            }

            if (switchEl) {
                if (forceAll) switchEl.checked = true;
                if (forceNone) switchEl.checked = false;
            }

            toggleEstacoesPermitidas();

            const selected = boxes.filter(b => b.checked).length;
            if (hint) {
                if (forceAll) hint.textContent = 'Perfil Gerente: acesso a todas as estações.';
                else if (forceNone) hint.textContent = 'Perfil Cliente: sem acesso por estação.';
                else if (max !== null) hint.textContent = `Limite de estações: ${max}. Selecionadas: ${selected}.`;
                else hint.textContent = '';
            }


            if (max !== null) {
                boxes.forEach(function (b) {
                    if (b.checked) return;
                    b.disabled = selected >= max;
                });
            } else {
                boxes.forEach(b => b.disabled = false);
            }
        }

        function toggleEstacoesPermitidas() {
            if ($('#acesso_todas_estacoes').is(':checked')) {
                $('#estacoes-permitidas-wrap').hide();
            } else {
                $('#estacoes-permitidas-wrap').show();
            }
        }

        function validatePassword() {
            const senhaEl = document.getElementById('senha');
            const confirmacaoEl = document.getElementById('confirmacao_senha');
            if (!senhaEl || !confirmacaoEl) {
                return true;
            }

            const senha = String(senhaEl.value || '');
            const confirmacaoSenha = String(confirmacaoEl.value || '');
            let valid = true;

            $('#passwordValidation li').addClass('d-none');

            senhaEl.setCustomValidity('');
            confirmacaoEl.setCustomValidity('');

            if (senha.length < 8) {
                $('#length').removeClass('d-none');
                valid = false;
            }

            if (!/[A-Z]/.test(senha)) {
                $('#uppercase').removeClass('d-none');
                valid = false;
            }

            if (!/[a-z]/.test(senha)) {
                $('#lowercase').removeClass('d-none');
                valid = false;
            }

            if (!/[0-9]/.test(senha)) {
                $('#number').removeClass('d-none');
                valid = false;
            }

            if (!/[!@#$%^&*(),.?":{}|<>]/.test(senha)) {
                $('#special').removeClass('d-none');
                valid = false;
            }

            if (senha !== confirmacaoSenha) {
                $('#match').removeClass('d-none');
                valid = false;
            }

            if (valid) {
                $('#passwordValidation').addClass('d-none');
            } else {
                $('#passwordValidation').removeClass('d-none');
            }

            senhaEl.setCustomValidity(valid ? '' : 'A senha não atende aos requisitos mínimos.');
            confirmacaoEl.setCustomValidity(senha === confirmacaoSenha ? '' : 'As senhas devem ser iguais.');

            return valid;
        }

        // Wizard + regras de estação por perfil
        $(document).ready(function () {
            setWizardStep(Number.isFinite(usuarioWizardStartStep) && usuarioWizardStartStep > 0 ? usuarioWizardStartStep : 1);
            $('.ec-step-btn').on('click', function () {
                const target = Number($(this).data('step') || 1);
                if (target <= usuarioCurrentStep) {
                    setWizardStep(target);
                    return;
                }

                const pendencias = coletarPendencias(usuarioPane(usuarioCurrentStep));
                if (pendencias.length > 0) {
                    mostrarPendencias(pendencias);
                    return;
                }

                setWizardStep(target);
            });

            $('#btnNext').on('click', function () {
                const pendencias = coletarPendencias(usuarioPane(usuarioCurrentStep));
                if (pendencias.length > 0) {
                    mostrarPendencias(pendencias);
                    return;
                }

                setWizardStep(usuarioCurrentStep + 1);
            });

            $('#btnPrev').on('click', function () {
                setWizardStep(usuarioCurrentStep - 1);
            });

            $usuarioBotaoIrPassoBase.on('click', function () {
                setWizardStep(usuarioUltimoPassoPendente);
            });

            $('#acesso_todas_estacoes').on('change', function () {
                toggleEstacoesPermitidas();
                applyRoleStationUiRules();
            });

            $(document).on('change', 'select[name="role_id"]', function () {
                applyRoleStationUiRules();
            });

            $(document).on('change', 'input[name="estacoes[]"]', function () {
                applyRoleStationUiRules();
            });

            applyRoleStationUiRules();
        });

        function abrirAbaComErro() {
            const firstInvalid = document.querySelector('.is-invalid');
            if (!firstInvalid) {
                return;
            }

            const pane = firstInvalid.closest('.tab-pane');
            if (!pane || !pane.id) {
                return;
            }

            const tabLink = document.querySelector(`#usuario-tab a[href="#${pane.id}"]`);
            if (tabLink) {
                $(tabLink).tab('show');
            }
        }

        // Data de emissão deve ser anterior a hoje
        document.getElementById("data_emissao_carta_conducao")?.addEventListener("change", function() {
            const dataEmissao = new Date(this.value);
            const hoje = new Date();
            if (dataEmissao >= hoje) {
                alertConfirmPequena("Aviso", "A data de emissão deve ser anterior à data atual.", "error");
                this.value = '';
            }
            // Revalida data de validade
            validarDataValidade();
        });

        // Data de validade deve ser posterior à data de emissão
        document.getElementById("data_validade_carta_conducao")?.addEventListener("change", validarDataValidade);

        function validarDataValidade() {
            const emissaoEl  = document.getElementById("data_emissao_carta_conducao");
            const validadeEl = document.getElementById("data_validade_carta_conducao");
            if (!emissaoEl || !validadeEl || !emissaoEl.value || !validadeEl.value) return;
            if (new Date(validadeEl.value) <= new Date(emissaoEl.value)) {
                alertConfirmPequena("Aviso", "A data de validade deve ser posterior à data de emissão.", "error");
                validadeEl.value = '';
            }
        }

        $(document).ready(function() {
            $('#celular').mask('(00) 00000-0000');
            $('#telefone').mask('(00) 00000-0000');

            // Máscara de CPF no campo zip_code (quando país = BR)
            function aplicarMascaraCep() {
                const pais = $('#pais_sigla').val();
                if (pais === 'BR') {
                    $('#cep').mask('000.000.000-00', { placeholder: '000.000.000-00' });
                } else {
                    $('#cep').unmask();
                }
            }
            aplicarMascaraCep();
            $('#pais_sigla').on('change', aplicarMascaraCep);

            // Auto-preenchimento de cidade e endereço via CEP (apenas Brasil)
            $('#cep').on('blur', function() {
                const pais = $('#pais_sigla').val();
                if (pais !== 'BR') return;

                // Remove formatação para obter só dígitos
                const cepDigitos = $(this).val().replace(/\D/g, '');
                if (cepDigitos.length !== 8) return;

                $.getJSON('https://viacep.com.br/ws/' + cepDigitos + '/json/', function(data) {
                    if (data.erro) return;
                    if (!$('#cidade').val()) {
                        $('#cidade').val(data.localidade || '').addClass('auto-filled');
                    }
                    if (!$('#endereco').val()) {
                        const logradouro = [data.logradouro, data.bairro].filter(Boolean).join(', ');
                        $('#endereco').val(logradouro || '').addClass('auto-filled');
                    }
                }).fail(function() {
                    // Silencioso — CEP pode ser válido mas a API pode estar indisponível
                });
            });

            $('#celular').on("blur", function() {
                const celular = $(this).val();
                const regex = /^\(\d{2}\) \d{5}-\d{4}$/;
                if (!regex.test(celular)) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            $('#celular').on("input", function() {
                const celular = $(this).val();
                const regex = /^\(\d{2}\) \d{5}-\d{4}$/;
                if (regex.test(celular)) {
                    $(this).removeClass('is-invalid');
                }
            });

            $('#data_nascimento').on('change', function() {
                var dataNascimento = new Date($(this).val());
                var hoje = new Date();
                var idade = hoje.getFullYear() - dataNascimento.getFullYear();
                var mes = hoje.getMonth() - dataNascimento.getMonth();

                if (mes < 0 || (mes === 0 && hoje.getDate() < dataNascimento.getDate())) {
                    idade--;
                }

                $('#idade').val(idade);
            });

            $('#acesso_todas_estacoes').on('change', toggleEstacoesPermitidas);
            toggleEstacoesPermitidas();

            if (isCreateUsuario) {
                $('#senha, #confirmacao_senha').on('keyup change', validatePassword);
            }

            $('#form').on('submit', function(event) {
                if (isCreateUsuario) {
                    validatePassword();
                }

                const pendencias = coletarPendencias($usuarioForm);
                if (pendencias.length > 0) {
                    event.preventDefault();
                    event.stopPropagation();
                    mostrarPendencias(pendencias);
                    return;
                }

                if (isCreateUsuario && !validatePassword()) {
                    event.preventDefault();
                    event.stopPropagation();
                    setWizardStep(5);
                    const pendenciasSenha = coletarPendencias(usuarioPane(5));
                    if (pendenciasSenha.length > 0) {
                        mostrarPendencias(pendenciasSenha);
                    }
                }
            });

            abrirAbaComErro();
        });
    </script>
@stop
