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
                <div class="col-12">
                    <div class="empresa-section-title">Dados da Empresa</div>
                </div>

                <div class="col-lg-8">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $empresa->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group form-group-empresa">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="old(
                                'ativo',
                                isset($empresa->ativo) && $empresa->ativo ? $empresa->ativo : 'S',
                            )" class="form-control form-control-sm" required />
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="slug" name="slug"
                            label="Slug" value="{{ $empresa->slug ?? '' }}" required />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="cnpj" name="cnpj"
                            label="CNPJ" value="{{ $empresa->cnpj ?? '' }}" />
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="email" class="form-control form-control-sm" id="email" name="email"
                            label="E-mail" value="{{ $empresa->email ?? '' }}" />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group form-group-empresa">
                        <x-form-input type="text" class="form-control form-control-sm" id="telefone" name="telefone"
                            label="Telefone" value="{{ $empresa->telefone ?? '' }}" />
                    </div>
                </div>

                <div class="col-12">
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
                </div>

                <div class="col-12 mt-2">
                    <div class="empresa-section-title">Identidade Visual da Empresa</div>
                </div>

                <div class="col-lg-5">
                    <div class="form-group form-group-empresa">
                        <label for="logo">Logo da empresa</label>
                        <input type="file" id="logo" name="logo" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.svg">
                        <small class="text-muted d-block mt-1">Formatos: JPG, PNG, WEBP ou SVG. Tamanho maximo: 2MB.</small>
                    </div>
                </div>
                <div class="col-lg-7">
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

                <div class="col-lg-4">
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
                <div class="col-lg-4">
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
                <div class="col-lg-4">
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

                <div class="col-12">
                    <div class="empresa-theme-preview" id="empresa-theme-preview"></div>
                    <small class="text-muted">Preview aproximado das cores para fundo/realces no acesso do colaborador.</small>
                </div>

                <div class="col-12 mt-2">
                    <div class="empresa-section-title">Faturamento e Gateway</div>
                </div>

                <div class="col-lg-8">
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

                <div class="col-lg-4">
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

                <div class="col-12 mt-2">
                    <div class="empresa-section-title">Credenciais do Provedor</div>
                </div>

                <div class="col-12 gateway-fields gateway-stripe">
                    <div class="gateway-title">Stripe</div>
                </div>
                <div class="col-lg-6 gateway-fields gateway-stripe">
                    <div class="form-group form-group-empresa">
                        <label for="stripe_public_key">Stripe Public Key</label>
                        <input type="text" class="form-control form-control-sm" id="stripe_public_key" name="stripe_public_key"
                               value="{{ old('stripe_public_key', $empresa->stripe_public_key ?? '') }}">
                    </div>
                </div>
                <div class="col-lg-6 gateway-fields gateway-stripe">
                    <div class="form-group form-group-empresa">
                        <label for="stripe_secret">Stripe Secret</label>
                        <input type="text" class="form-control form-control-sm" id="stripe_secret" name="stripe_secret"
                               value="">
                        <small class="text-muted d-block mt-1">Deixe em branco para manter o secret atual.</small>
                    </div>
                </div>

                <div class="col-12 gateway-fields gateway-mercado_livre">
                    <div class="gateway-title">Mercado Livre</div>
                </div>
                <div class="col-lg-6 gateway-fields gateway-mercado_livre">
                    <div class="form-group form-group-empresa">
                        <label for="mercado_livre_public_key">Mercado Livre Public Key</label>
                        <input type="text" class="form-control form-control-sm" id="mercado_livre_public_key" name="mercado_livre_public_key"
                               value="{{ old('mercado_livre_public_key', $empresa->mercado_livre_public_key ?? '') }}">
                    </div>
                </div>
                <div class="col-lg-6 gateway-fields gateway-mercado_livre">
                    <div class="form-group form-group-empresa">
                        <label for="mercado_livre_access_token">Mercado Livre Access Token</label>
                        <input type="text" class="form-control form-control-sm" id="mercado_livre_access_token" name="mercado_livre_access_token"
                               value="">
                        <small class="text-muted d-block mt-1">Deixe em branco para manter o token atual.</small>
                    </div>
                </div>

                <div class="col-12 gateway-fields gateway-paypal">
                    <div class="gateway-title">PayPal</div>
                </div>
                <div class="col-lg-4 gateway-fields gateway-paypal">
                    <div class="form-group form-group-empresa">
                        <label for="paypal_client_id">PayPal Client ID</label>
                        <input type="text" class="form-control form-control-sm" id="paypal_client_id" name="paypal_client_id"
                               value="{{ old('paypal_client_id', $empresa->paypal_client_id ?? '') }}">
                    </div>
                </div>
                <div class="col-lg-4 gateway-fields gateway-paypal">
                    <div class="form-group form-group-empresa">
                        <label for="paypal_client_secret">PayPal Client Secret</label>
                        <input type="text" class="form-control form-control-sm" id="paypal_client_secret" name="paypal_client_secret"
                               value="">
                        <small class="text-muted d-block mt-1">Deixe em branco para manter o secret atual.</small>
                    </div>
                </div>
                <div class="col-lg-4 gateway-fields gateway-paypal">
                    <div class="form-group form-group-empresa">
                        <label for="paypal_mode">PayPal Mode</label>
                        <select id="paypal_mode" name="paypal_mode" class="form-control form-control-sm">
                            @php $paypalMode = old('paypal_mode', $empresa->paypal_mode ?? 'sandbox'); @endphp
                            <option value="sandbox" {{ $paypalMode === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                            <option value="live" {{ $paypalMode === 'live' ? 'selected' : '' }}>Live</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-save-empresa col-12 mt-2">
                    Salvar
                </button>
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

    .empresa-section-title {
        font-size: 0.96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
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
