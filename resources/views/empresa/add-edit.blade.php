@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    {{-- <x-page-title :title="$titulo" /> --}}
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <div class="card card-empresa shadow-sm border-0">
            <div class="card-header card-header-empresa border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure os dados da empresa e as informacoes de pagamento.</p>
                </div>
                @if(isset($empresa))
                    <a href="{{ route('empresa.site-apresentacao.edit', $empresa->id) }}" class="btn btn-outline-primary btn-sm mt-2 mt-md-0">
                        Configurar site de apresentacao
                    </a>
                @endif
            </div>

            <div class="card-body p-4">
            @if(!isset($empresa))
                <div class="alert alert-info py-2 rounded-lg">
                    Ao salvar, a nova empresa sera criada usando a <strong>empresa-padrao</strong> como modelo de informacoes.
                </div>
            @endif
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row" enctype="multipart/form-data">
                <div class="col-12 empresa-wizard-top">
                    <div class="empresa-wizard-steps" role="tablist" aria-label="Passos da empresa">
                        <button type="button" class="empresa-wizard-step is-active" data-empresa-step-btn="1">
                            <span class="num">1</span> Dados
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="2">
                            <span class="num">2</span> Contato
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="3">
                            <span class="num">3</span> Dominio
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="4">
                            <span class="num">4</span> Identidade
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="5">
                            <span class="num">5</span> Gateway
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="6">
                            <span class="num">6</span> Credenciais
                        </button>
                        <div class="div"></div>
                        <button type="button" class="empresa-wizard-step" data-empresa-step-btn="7">
                            <span class="num">7</span> Fiscal
                        </button>
                    </div>
                </div>

                <div class="col-12" data-empresa-step="1">
                    <div class="empresa-section-title" id="sec-dados">Dados da Empresa</div>
                </div>

                <div class="col-lg-8" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $empresa->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="old(
                                'ativo',
                                isset($empresa->ativo) && $empresa->ativo ? $empresa->ativo : 'S',
                            )" class="form-control form-control-sm" required />
                    </div>
                </div>

                <div class="col-lg-6" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="slug" name="slug"
                            label="Slug" value="{{ $empresa->slug ?? '' }}" required />
                    </div>
                </div>
                <div class="col-lg-6" data-empresa-step="1">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="cnpj" name="cnpj"
                            label="CNPJ" value="{{ $empresa->cnpj ?? '' }}" />
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="2">
                    <div class="empresa-section-title" id="sec-contato">Contato</div>
                </div>

                <div class="col-lg-6" data-empresa-step="2">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="email" class="form-control form-control-sm" id="email" name="email"
                            label="E-mail" value="{{ $empresa->email ?? '' }}" />
                    </div>
                </div>
                <div class="col-lg-6" data-empresa-step="2">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="telefone" name="telefone"
                            label="Telefone" value="{{ $empresa->telefone ?? '' }}" />
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="3">
                    <div class="empresa-section-title" id="sec-dominio">Dominio e Reserva</div>
                </div>

                <div class="col-12" data-empresa-step="3">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="dominio" name="dominio"
                            label="Dominio" value="{{ $empresa->dominio ?? '' }}" />
                    </div>
                    <div class="form-group form-group-empresa">
                        <label for="reserva_selecionar_modelo">Selecionar modelo na reserva</label>
                        @php
                            $selecionarModeloReserva = old('reserva_selecionar_modelo', $empresa->reserva_selecionar_modelo ?? 'N');
                        @endphp
                        <select class="form-control form-control-sm" id="reserva_selecionar_modelo" name="reserva_selecionar_modelo">
                            <option value="N" {{ $selecionarModeloReserva === 'N' ? 'selected' : '' }}>Nao</option>
                            <option value="S" {{ $selecionarModeloReserva === 'S' ? 'selected' : '' }}>Sim</option>
                        </select>
                        <small class="text-muted d-block mt-1">Se "Nao", o modelo sera escolhido automaticamente apos selecionar o grupo.</small>
                    </div>
                    <div class="form-group form-group-empresa">
                        <label for="reserva_atraso_max_minutos">Maximo de minutos de atraso para cancelamento automatico</label>
                        <input
                            type="number"
                            class="form-control form-control-sm"
                            id="reserva_atraso_max_minutos"
                            name="reserva_atraso_max_minutos"
                            min="0"
                            value="{{ old('reserva_atraso_max_minutos', $empresa->reserva_atraso_max_minutos ?? 120) }}"
                        >
                        <small class="text-muted d-block mt-1">Apos esse tempo, reservas atrasadas sao canceladas e o valor pago vira saldo.</small>
                    </div>
                    <div class="form-group form-group-empresa">
                        <label for="reserva_bloqueio_pos_reserva_horas">Horas de indisponibilidade apos o fim da reserva</label>
                        <input
                            type="number"
                            class="form-control form-control-sm"
                            id="reserva_bloqueio_pos_reserva_horas"
                            name="reserva_bloqueio_pos_reserva_horas"
                            min="0"
                            value="{{ old('reserva_bloqueio_pos_reserva_horas', $empresa->reserva_bloqueio_pos_reserva_horas ?? 0) }}"
                        >
                        <small class="text-muted d-block mt-1">Usado como "folga" para limpeza/revisao: o veiculo so fica disponivel para nova reserva apos o fim + X horas.</small>
                    </div>
                    <div class="form-group form-group-empresa">
                        <label for="adicionar_novas_marcas_automaticamente">Adicionar novas marcas automaticamente</label>
                        @php
                            $adicionarMarcasAutomaticamente = old('adicionar_novas_marcas_automaticamente', $empresa->adicionar_novas_marcas_automaticamente ?? 'S');
                        @endphp
                        <select class="form-control form-control-sm" id="adicionar_novas_marcas_automaticamente" name="adicionar_novas_marcas_automaticamente">
                            <option value="S" {{ $adicionarMarcasAutomaticamente === 'S' ? 'selected' : '' }}>Sim</option>
                            <option value="N" {{ $adicionarMarcasAutomaticamente === 'N' ? 'selected' : '' }}>Nao</option>
                        </select>
                        <small class="text-muted d-block mt-1">Quando ativo, a sincronizacao FIPE replica novas marcas para esta empresa.</small>
                    </div>
                    <div class="form-group form-group-empresa">
                        <label for="adicionar_novos_modelos_automaticamente">Adicionar novos modelos automaticamente</label>
                        @php
                            $adicionarModelosAutomaticamente = old('adicionar_novos_modelos_automaticamente', $empresa->adicionar_novos_modelos_automaticamente ?? 'S');
                        @endphp
                        <select class="form-control form-control-sm" id="adicionar_novos_modelos_automaticamente" name="adicionar_novos_modelos_automaticamente">
                            <option value="S" {{ $adicionarModelosAutomaticamente === 'S' ? 'selected' : '' }}>Sim</option>
                            <option value="N" {{ $adicionarModelosAutomaticamente === 'N' ? 'selected' : '' }}>Nao</option>
                        </select>
                        <small class="text-muted d-block mt-1">Quando ativo, a sincronizacao FIPE replica novos modelos para esta empresa.</small>
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="4">
                    <div class="empresa-section-title" id="sec-identidade">Identidade Visual da Empresa</div>
                </div>

                <div class="col-lg-5" data-empresa-step="4">
                    <div class="form-group form-group-empresa">
                        <label for="logo">Logo da empresa</label>
                        <input type="file" id="logo" name="logo" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.svg">
                        <small class="text-muted d-block mt-1">Formatos: JPG, PNG, WEBP ou SVG. Tamanho maximo: 2MB.</small>
                    </div>
                </div>
                <div class="col-lg-7" data-empresa-step="4">
                    <div
                        class="empresa-logo-preview-wrap"
                        style="background: {{ old('cor_primaria', $empresa->cor_primaria ?? '#1F2937') }};"
                    >
                        @if(isset($empresa) && $empresa->logo_url)
                            <img src="{{ $empresa->logo_url }}" alt="Logo atual" class="empresa-logo-preview">
                        @else
                            <div class="empresa-logo-placeholder">Sem logo cadastrada</div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-4" data-empresa-step="4">
                    <div class="form-group form-group-empresa">
                        <label for="cor_primaria">Cor primaria <small>Cor de fundo</small></label>
                        <input
                            type="color"
                            class="form-control form-control-sm p-1"
                            id="cor_primaria"
                            name="cor_primaria"
                            value="{{ old('cor_primaria', $empresa->cor_primaria ?? '#1F2937') }}"
                        >
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="4">
                    <div class="form-group form-group-empresa">
                        <label for="cor_secundaria">Cor secundaria <small>Cor das fontes</small></label>
                        <input
                            type="color"
                            class="form-control form-control-sm p-1"
                            id="cor_secundaria"
                            name="cor_secundaria"
                            value="{{ old('cor_secundaria', $empresa->cor_secundaria ?? '#334155') }}"
                        >
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="4">
                    <div class="form-group form-group-empresa">
                        <label for="cor_terciaria">Cor terciaria</label>
                        <input
                            type="color"
                            class="form-control form-control-sm p-1"
                            id="cor_terciaria"
                            name="cor_terciaria"
                            value="{{ old('cor_terciaria', $empresa->cor_terciaria ?? '#64748B') }}"
                        >
                    </div>
                </div>

                <div class="col-12" data-empresa-step="4">
                    <div class="empresa-theme-preview" id="empresa-theme-preview"></div>
                    <small class="text-muted">Preview aproximado das cores para fundo/realces no acesso do colaborador.</small>
                </div>

                <div class="col-12 mt-2" data-empresa-step="5">
                    <div class="empresa-section-title" id="sec-faturamento">Faturamento e Gateway</div>
                </div>

                <div class="col-lg-8" data-empresa-step="5">
                    <div class="form-group form-group-empresa">
                        <label for="gateway_pagamento">Gateway de pagamento padrao</label>
                        <select name="gateway_pagamento" id="gateway_pagamento" class="form-control form-control-sm">
                            @php
                                $gatewaySelecionado = old('gateway_pagamento', $empresa->gateway_pagamento ?? 'stripe');
                            @endphp
                            <option value="stripe" {{ $gatewaySelecionado === 'stripe' ? 'selected' : '' }}>Stripe</option>
                            <option value="mercado_livre" {{ $gatewaySelecionado === 'mercado_livre' ? 'selected' : '' }}>Mercado Livre</option>
                            <option value="paypal" {{ $gatewaySelecionado === 'paypal' ? 'selected' : '' }}>PayPal</option>
                        </select>
                        <small class="text-muted d-block mt-1">Este gateway sera usado como padrao para cobrancas desta empresa.</small>
                    </div>
                </div>

                <div class="col-lg-4" data-empresa-step="5">
                    <div class="form-group form-group-empresa">
                        <label for="moeda_pagamento">Moeda de pagamento</label>
                        @php
                            $moedaSelecionada = strtoupper(old('moeda_pagamento', $empresa->moeda_pagamento ?? 'BRL'));
                        @endphp
                        <select id="moeda_pagamento" name="moeda_pagamento" class="form-control form-control-sm">
                            <option value="BRL" {{ $moedaSelecionada === 'BRL' ? 'selected' : '' }}>BRL - Real Brasileiro</option>
                            <option value="USD" {{ $moedaSelecionada === 'USD' ? 'selected' : '' }}>USD - Dolar Americano</option>
                            <option value="EUR" {{ $moedaSelecionada === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="6">
                    <div class="empresa-section-title" id="sec-credenciais">Credenciais do Provedor</div>
                </div>

                <div class="col-12 gateway-fields gateway-stripe" data-empresa-step="6">
                    <div class="gateway-title">Stripe</div>
                </div>
                <div class="col-lg-6 gateway-fields gateway-stripe" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="stripe_public_key">Stripe Public Key</label>
                        <input type="text" class="form-control form-control-sm" id="stripe_public_key" name="stripe_public_key"
                               value="{{ old('stripe_public_key', $empresa->stripe_public_key ?? '') }}">
                    </div>
                </div>
                <div class="col-lg-6 gateway-fields gateway-stripe" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="stripe_secret">Stripe Secret</label>
                        <input type="text" class="form-control form-control-sm" id="stripe_secret" name="stripe_secret"
                               value="">
                        <small class="text-muted d-block mt-1">Deixe em branco para manter o secret atual.</small>
                    </div>
                </div>

                <div class="col-12 gateway-fields gateway-mercado_livre" data-empresa-step="6">
                    <div class="gateway-title">Mercado Livre</div>
                </div>
                <div class="col-lg-6 gateway-fields gateway-mercado_livre" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="mercado_livre_public_key">Mercado Livre Public Key</label>
                        <input type="text" class="form-control form-control-sm" id="mercado_livre_public_key" name="mercado_livre_public_key"
                               value="{{ old('mercado_livre_public_key', $empresa->mercado_livre_public_key ?? '') }}">
                    </div>
                </div>
                <div class="col-lg-6 gateway-fields gateway-mercado_livre" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="mercado_livre_access_token">Mercado Livre Access Token</label>
                        <input type="text" class="form-control form-control-sm" id="mercado_livre_access_token" name="mercado_livre_access_token"
                               value="">
                        <small class="text-muted d-block mt-1">Deixe em branco para manter o token atual.</small>
                    </div>
                </div>

                <div class="col-12 gateway-fields gateway-paypal" data-empresa-step="6">
                    <div class="gateway-title">PayPal</div>
                </div>
                <div class="col-lg-4 gateway-fields gateway-paypal" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="paypal_client_id">PayPal Client ID</label>
                        <input type="text" class="form-control form-control-sm" id="paypal_client_id" name="paypal_client_id"
                               value="{{ old('paypal_client_id', $empresa->paypal_client_id ?? '') }}">
                    </div>
                </div>
                <div class="col-lg-4 gateway-fields gateway-paypal" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="paypal_client_secret">PayPal Client Secret</label>
                        <input type="text" class="form-control form-control-sm" id="paypal_client_secret" name="paypal_client_secret"
                               value="">
                        <small class="text-muted d-block mt-1">Deixe em branco para manter o secret atual.</small>
                    </div>
                </div>
                <div class="col-lg-4 gateway-fields gateway-paypal" data-empresa-step="6">
                    <div class="form-group form-group-empresa">
                        <label for="paypal_mode">PayPal Mode</label>
                        <select id="paypal_mode" name="paypal_mode" class="form-control form-control-sm">
                            @php $paypalMode = old('paypal_mode', $empresa->paypal_mode ?? 'sandbox'); @endphp
                            <option value="sandbox" {{ $paypalMode === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                            <option value="live" {{ $paypalMode === 'live' ? 'selected' : '' }}>Live</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 mt-2" data-empresa-step="7">
                    <div class="empresa-section-title" id="sec-fiscal">Fiscal - NFS-e (Nuvem Fiscal)</div>
                </div>

                <div class="col-lg-3" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_ativo">Emitir NFS-e</label>
                        @php $nfseAtivo = old('nfse_ativo', $empresa->nfse_ativo ?? 'N'); @endphp
                        <select id="nfse_ativo" name="nfse_ativo" class="form-control form-control-sm">
                            <option value="N" {{ $nfseAtivo === 'N' ? 'selected' : '' }}>Nao</option>
                            <option value="S" {{ $nfseAtivo === 'S' ? 'selected' : '' }}>Sim</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_ambiente">Ambiente</label>
                        @php $nfseAmbiente = old('nfse_ambiente', $empresa->nfse_ambiente ?? 'homologacao'); @endphp
                        <select id="nfse_ambiente" name="nfse_ambiente" class="form-control form-control-sm">
                            <option value="homologacao" {{ $nfseAmbiente === 'homologacao' ? 'selected' : '' }}>Homologacao</option>
                            <option value="producao" {{ $nfseAmbiente === 'producao' ? 'selected' : '' }}>Producao</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_provedor">Provedor</label>
                        @php $nfseProvedor = old('nfse_provedor', $empresa->nfse_provedor ?? 'nuvemfiscal'); @endphp
                        <select id="nfse_provedor" name="nfse_provedor" class="form-control form-control-sm">
                            <option value="nuvemfiscal" {{ $nfseProvedor === 'nuvemfiscal' ? 'selected' : '' }}>Nuvem Fiscal (teste)</option>
                            <option value="nfephp" {{ $nfseProvedor === 'nfephp' ? 'selected' : '' }}>NFePHP (final)</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-3" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_inscricao_municipal">Inscricao municipal</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_inscricao_municipal" name="nfse_inscricao_municipal"
                               value="{{ old('nfse_inscricao_municipal', $empresa->nfse_inscricao_municipal ?? '') }}">
                    </div>
                </div>

                <div class="col-lg-6" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_client_id">Nuvem Fiscal Client ID</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_client_id" name="nfse_client_id"
                               value="{{ old('nfse_client_id', $empresa->nfse_client_id ?? '') }}">
                    </div>
                </div>
                <div class="col-lg-6" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_client_secret">Nuvem Fiscal Client Secret</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_client_secret" name="nfse_client_secret" value="">
                        <small class="text-muted d-block mt-1">Deixe em branco para manter o secret atual.</small>
                    </div>
                </div>

                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_municipio_codigo">Codigo do municipio (IBGE)</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_municipio_codigo" name="nfse_municipio_codigo"
                               value="{{ old('nfse_municipio_codigo', $empresa->nfse_municipio_codigo ?? '') }}">
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_codigo_servico">Codigo do servico</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_codigo_servico" name="nfse_codigo_servico"
                               value="{{ old('nfse_codigo_servico', $empresa->nfse_codigo_servico ?? '') }}">
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_cnae">CNAE (opcional)</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_cnae" name="nfse_cnae"
                               value="{{ old('nfse_cnae', $empresa->nfse_cnae ?? '') }}">
                    </div>
                </div>

                <div class="col-lg-8" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_descricao_servico">Descricao do servico</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_descricao_servico" name="nfse_descricao_servico"
                               value="{{ old('nfse_descricao_servico', $empresa->nfse_descricao_servico ?? '') }}">
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_aliquota">Aliquota ISS (%)</label>
                        <input type="number" step="0.01" class="form-control form-control-sm" id="nfse_aliquota" name="nfse_aliquota"
                               value="{{ old('nfse_aliquota', $empresa->nfse_aliquota ?? '') }}">
                    </div>
                </div>

                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_tributacao_issqn">Tributacao ISSQN</label>
                        @php $trib = (string) old('nfse_tributacao_issqn', $empresa->nfse_tributacao_issqn ?? '1'); @endphp
                        <select id="nfse_tributacao_issqn" name="nfse_tributacao_issqn" class="form-control form-control-sm">
                            <option value="1" {{ $trib === '1' ? 'selected' : '' }}>1 - Tributacao no municipio</option>
                            <option value="2" {{ $trib === '2' ? 'selected' : '' }}>2 - Tributacao fora do municipio</option>
                            <option value="3" {{ $trib === '3' ? 'selected' : '' }}>3 - Isento</option>
                            <option value="4" {{ $trib === '4' ? 'selected' : '' }}>4 - Imune</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_tomador_numero_padrao">Numero tomador (padrao)</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_tomador_numero_padrao" name="nfse_tomador_numero_padrao"
                               value="{{ old('nfse_tomador_numero_padrao', $empresa->nfse_tomador_numero_padrao ?? '') }}">
                    </div>
                </div>
                <div class="col-lg-4" data-empresa-step="7">
                    <div class="form-group form-group-empresa">
                        <label for="nfse_tomador_bairro_padrao">Bairro tomador (padrao)</label>
                        <input type="text" class="form-control form-control-sm" id="nfse_tomador_bairro_padrao" name="nfse_tomador_bairro_padrao"
                               value="{{ old('nfse_tomador_bairro_padrao', $empresa->nfse_tomador_bairro_padrao ?? '') }}">
                    </div>
                </div>

                <div class="col-12 empresa-wizard-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="empresaWizardPrev">
                        <i class="fas fa-arrow-left mr-1"></i>Voltar
                    </button>
                    <div class="d-flex align-items-center" style="gap:8px;">
                        <button type="button" class="btn btn-primary btn-sm" id="empresaWizardNext">
                            Continuar <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                        <button type="submit" class="btn btn-primary btn-save-empresa d-none" id="btnSalvarEmpresa">
                            Salvar
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
    .card-empresa {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-empresa {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        flex-wrap: wrap;
    }

    .card-header-empresa h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .empresa-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 16px;
    }

    .empresa-nav-item {
        padding: 6px 12px;
        border-radius: 999px;
        border: 1px solid #dbe3ee;
        background: #f8fbff;
        color: #1f334d;
        font-size: 0.82rem;
        font-weight: 600;
        text-decoration: none;
    }

    .empresa-nav-item:hover {
        background: #eef4ff;
        border-color: #c6d6ef;
        color: #1d4ed8;
    }

    .empresa-nav {
        display: none;
    }

    .empresa-wizard-top {
        border: 1px solid #e5eaf2;
        border-radius: 14px;
        padding: 12px 12px 10px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        margin-bottom: 14px;
    }

    .empresa-wizard-steps {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .empresa-wizard-steps .div {
        flex: 1 1 20px;
        height: 2px;
        background: color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 22%, #e5edf7 78%);
        border-radius: 999px;
        min-width: 12px;
    }

    .empresa-wizard-step {
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
        white-space: nowrap;
    }

    .empresa-wizard-step .num {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 12%, #ffffff 88%);
        color: color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 88%, #111827 12%);
        border: 1px solid color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 25%, #dbe3ef 75%);
    }

    .empresa-wizard-step.is-active {
        background: var(--empresa-cor-primaria, #1F2937);
        border-color: var(--empresa-cor-primaria, #1F2937);
        color: var(--empresa-cor-primaria-contraste, #fff);
        box-shadow: 0 10px 22px color-mix(in srgb, var(--empresa-cor-primaria, #1F2937) 22%, transparent 78%);
    }

    .empresa-wizard-step.is-active .num {
        background: rgba(255,255,255,.18);
        color: var(--empresa-cor-primaria-contraste, #fff);
        border-color: rgba(255,255,255,.24);
    }

    .empresa-wizard-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 0 0;
        margin-top: 14px;
        border-top: 1px dashed color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 28%, #e5edf7 72%);
    }

    .empresa-wizard-footer .btn {
        border-radius: 999px;
        font-weight: 700;
    }

    .empresa-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1f334d;
        letter-spacing: .02em;
        padding: 8px 12px;
        border-radius: 10px;
        background: #f6f9ff;
        border: 1px solid #e5eaf2;
        margin-bottom: 12px;
        scroll-margin-top: 90px;
    }

    .gateway-title {
        background: #0f172a;
        color: #ffffff;
        border-radius: 8px;
        padding: .4rem .7rem;
        font-size: .86rem;
        font-weight: 700;
        margin-bottom: 6px;
        display: inline-block;
    }

    .form-group-empresa {
        margin-bottom: 1rem;
    }

    .card-empresa .form-control,
    .card-empresa .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-empresa .form-control:focus,
    .card-empresa .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .btn-save-empresa {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    .empresa-logo-preview-wrap {
        min-height: 110px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px dashed #d8e1ec;
        border-radius: 10px;
        padding: .75rem;
    }

    .empresa-logo-preview {
        max-height: 90px;
        max-width: 100%;
        object-fit: contain;
    }

    .empresa-logo-placeholder {
        color: #64748b;
        font-size: .88rem;
    }

    .empresa-theme-preview {
        height: 72px;
        border-radius: 12px;
        border: 1px solid #d8e1ec;
        margin-bottom: .45rem;
    }
</style>
@stop

@section('js')
<script>
    (function () {
        const gateway = document.getElementById('gateway_pagamento');
        const cnpjInput = document.getElementById('cnpj');
        const telefoneInput = document.getElementById('telefone');
        const corPrimaria = document.getElementById('cor_primaria');
        const corSecundaria = document.getElementById('cor_secundaria');
        const corTerciaria = document.getElementById('cor_terciaria');
        const themePreview = document.getElementById('empresa-theme-preview');
        const blocks = document.querySelectorAll('.gateway-fields');

        function onlyDigits(value) {
            return (value || '').replace(/\D/g, '');
        }

        function formatCnpj(value) {
            const digits = onlyDigits(value).slice(0, 14);

            if (digits.length <= 2) return digits;
            if (digits.length <= 5) return digits.replace(/^(\d{2})(\d+)/, '$1.$2');
            if (digits.length <= 8) return digits.replace(/^(\d{2})(\d{3})(\d+)/, '$1.$2.$3');
            if (digits.length <= 12) return digits.replace(/^(\d{2})(\d{3})(\d{3})(\d+)/, '$1.$2.$3/$4');
            return digits.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d+)/, '$1.$2.$3/$4-$5');
        }

        function formatTelefone(value) {
            const digits = onlyDigits(value).slice(0, 11);

            if (digits.length <= 2) return digits;
            if (digits.length <= 6) return digits.replace(/^(\d{2})(\d+)/, '($1) $2');
            if (digits.length <= 10) return digits.replace(/^(\d{2})(\d{4})(\d+)/, '($1) $2-$3');
            return digits.replace(/^(\d{2})(\d{5})(\d+)/, '($1) $2-$3');
        }

        function bindMask(input, formatter) {
            if (!input) {
                return;
            }

            input.addEventListener('input', function () {
                input.value = formatter(input.value);
            });

            input.value = formatter(input.value);
        }

        function syncGatewayFields() {
            if (!gateway) {
                return;
            }

            const selected = gateway.value;
            blocks.forEach((el) => {
                el.style.display = 'none';
            });

            document.querySelectorAll('.gateway-' + selected).forEach((el) => {
                el.style.display = '';
            });
        }

        function syncThemePreview() {
            if (!themePreview) {
                return;
            }

            const c1 = corPrimaria?.value || '#1F2937';
            const c2 = corSecundaria?.value || '#334155';
            const c3 = corTerciaria?.value || '#64748B';

            themePreview.style.background = 'linear-gradient(135deg, ' + c1 + ' 0%, ' + c2 + ' 55%, ' + c3 + ' 100%)';
        }

        // Wizard/Steps do cadastro de empresa (igual ao admin reserva)
        (function initEmpresaWizard() {
            const maxStep = 7;
            const storageKey = 'empresaWizardStep';

            const prevBtn = document.getElementById('empresaWizardPrev');
            const nextBtn = document.getElementById('empresaWizardNext');
            const submitBtn = document.getElementById('btnSalvarEmpresa');

            function getStep() {
                const saved = Number(localStorage.getItem(storageKey) || '1');
                return Number.isFinite(saved) && saved >= 1 && saved <= maxStep ? saved : 1;
            }

            function setStep(step, persist = true) {
                const s = Math.min(maxStep, Math.max(1, Number(step) || 1));

                document.querySelectorAll('[data-empresa-step]').forEach(el => {
                    el.classList.toggle('d-none', Number(el.dataset.empresaStep) !== s);
                });

                document.querySelectorAll('[data-empresa-step-btn]').forEach(btn => {
                    btn.classList.toggle('is-active', Number(btn.dataset.empresaStepBtn) === s);
                });

                if (prevBtn) prevBtn.disabled = s <= 1;
                if (nextBtn) nextBtn.classList.toggle('d-none', s >= maxStep);
                if (submitBtn) submitBtn.classList.toggle('d-none', s < maxStep);

                if (persist) localStorage.setItem(storageKey, String(s));

                const top = document.querySelector('.empresa-wizard-top');
                if (top) {
                    top.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

            function validateStep(step) {
                const containers = Array.from(document.querySelectorAll(`[data-empresa-step="${step}"]`));
                const fields = [];
                containers.forEach(c => fields.push(...Array.from(c.querySelectorAll('input,select,textarea'))));

                for (const field of fields) {
                    if (field.disabled) continue;
                    if (field.type === 'hidden') continue;
                    if (typeof field.checkValidity !== 'function') continue;
                    if (!field.checkValidity()) {
                        if (typeof field.reportValidity === 'function') field.reportValidity();
                        field.focus?.();
                        return false;
                    }
                }

                return true;
            }

            window.empresaWizardSetStep = setStep;

            document.querySelectorAll('[data-empresa-step-btn]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const current = getStep();
                    const target = Number(btn.dataset.empresaStepBtn);
                    if (target > current && !validateStep(current)) return;
                    setStep(target);
                });
            });

            if (prevBtn) {
                prevBtn.addEventListener('click', () => setStep(getStep() - 1));
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    const current = getStep();
                    if (!validateStep(current)) return;
                    setStep(current + 1);
                });
            }

            // Abrir step com erro de validacao do backend
            (function openStepWithErrors() {
                const errosCampos = @json(array_keys($errors->messages()));
                if (!Array.isArray(errosCampos) || errosCampos.length === 0) return;

                const mapa = {
                    nome: 1, ativo: 1, slug: 1, cnpj: 1,
                    email: 2, telefone: 2,
                    dominio: 3, reserva_selecionar_modelo: 3, reserva_atraso_max_minutos: 3, reserva_bloqueio_pos_reserva_horas: 3,
                    adicionar_novas_marcas_automaticamente: 3, adicionar_novos_modelos_automaticamente: 3,
                    logo: 4, cor_primaria: 4, cor_secundaria: 4, cor_terciaria: 4,
                    gateway_pagamento: 5, moeda_pagamento: 5,
                    stripe_public_key: 6, stripe_secret: 6,
                    mercado_livre_public_key: 6, mercado_livre_access_token: 6,
                    paypal_client_id: 6, paypal_client_secret: 6, paypal_mode: 6,
                    nfse_ativo: 7, nfse_ambiente: 7, nfse_provedor: 7,
                    nfse_client_id: 7, nfse_client_secret: 7,
                    nfse_inscricao_municipal: 7, nfse_municipio_codigo: 7,
                    nfse_codigo_servico: 7, nfse_cnae: 7, nfse_descricao_servico: 7,
                    nfse_aliquota: 7, nfse_tributacao_issqn: 7,
                    nfse_tomador_numero_padrao: 7, nfse_tomador_bairro_padrao: 7,
                };

                for (const campo of errosCampos) {
                    const step = mapa[campo];
                    if (step) {
                        setStep(step, true);
                        return;
                    }
                }
            })();

            setStep(getStep(), false);
        })();

        if (gateway) {
            gateway.addEventListener('change', syncGatewayFields);
        }

        [corPrimaria, corSecundaria, corTerciaria].forEach((input) => {
            if (input) {
                input.addEventListener('input', syncThemePreview);
            }
        });

        bindMask(cnpjInput, formatCnpj);
        bindMask(telefoneInput, formatTelefone);

        syncGatewayFields();
        syncThemePreview();
    })();
</script>
@stop
