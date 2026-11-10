@extends('adminlte::page')
@section('title', 'Integrações de Pagamento')
@section('content_header')
    <x-page-title title="Integrações de Pagamento" subtitle="Geração de boletos/Pix e baixa automática das mensalidades" />
@stop

@section('content')
    @if(session('success'))<div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}</div>@endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card card-outline card-primary">
                <div class="card-header font-weight-bold"><i class="fas fa-plug mr-1"></i> Gateway de cobrança</div>
                <form method="POST" action="{{ route('empresa.integracoes.atualizar') }}">
                    @csrf @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label>Provedor</label>
                            <select name="gateway_pagamento" class="form-control">
                                <option value="">Nenhum (desativado)</option>
                                <option value="asaas" {{ $empresa->gateway_pagamento === 'asaas' ? 'selected' : '' }}>Asaas — boleto, Pix e cartão</option>
                            </select>
                            <small class="text-muted">Em breve: outros provedores poderão ser plugados aqui.</small>
                        </div>
                        <div class="form-group">
                            <label>API Key <small class="text-muted">(deixe em branco para manter a atual)</small></label>
                            <input type="password" name="gateway_api_key" class="form-control" autocomplete="new-password"
                                   placeholder="{{ $empresa->gateway_api_key ? '•••••••••••• (configurada)' : 'Cole a API key do Asaas' }}">
                        </div>
                        <div class="form-group">
                            <label>Ambiente</label>
                            <select name="gateway_ambiente" class="form-control">
                                <option value="sandbox" {{ $empresa->gateway_ambiente !== 'producao' ? 'selected' : '' }}>Sandbox (testes)</option>
                                <option value="producao" {{ $empresa->gateway_ambiente === 'producao' ? 'selected' : '' }}>Produção (valores reais)</option>
                            </select>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="regerar" name="regerar_webhook" value="1">
                            <label class="custom-control-label" for="regerar_webhook">Gerar novo token do webhook</label>
                        </div>
                    </div>
                    <div class="card-footer"><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Salvar integração</button></div>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-outline card-info">
                <div class="card-header font-weight-bold"><i class="fas fa-bell mr-1"></i> Webhook (baixa automática)</div>
                <div class="card-body">
                    <p class="small text-muted">Cadastre esta URL no painel Asaas (Integrações → Webhooks) para a mensalidade baixar sozinha quando o boleto/Pix for pago:</p>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" readonly value="{{ $webhookUrl }}" onclick="this.select()">
                    </div>
                    <hr>
                    <p class="small mb-1"><strong>Como funciona:</strong></p>
                    <ol class="small text-muted pl-3">
                        <li>Secretaria gera a cobrança na mensalidade (botão <i class="fas fa-barcode"></i>).</li>
                        <li>Família paga o boleto ou o Pix copia-e-cola.</li>
                        <li>O webhook baixa a mensalidade automaticamente — ou a sincronização horária confere.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@stop
