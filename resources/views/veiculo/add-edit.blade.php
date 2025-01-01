@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <!-- Exibir erros de validação -->
    @if ($errors->any())
        <div class="alert alert-danger col-xl-8 col-lg-10 col-md-12 col-sm-12 col-xs-12 ">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-xl-8 col-lg-10 col-md-10 col-sm-12 col-xs-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                <x-section-division-header-form>Informações Básicas</x-section-division-header-form><br>

                @isset($veiculo)
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                disabled readonly label="Nome" value="{{ old('nome', $veiculo->nome ?? '') }}" required />
                        </div>
                    </div>
                @endisset
                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-select name="tipo_frota_id" label="Tipo de Frota" :options="$tipoFrotas"
                            placeholder="Selecione um valor" :selected="old(
                                'tipo_frota_id',
                                isset($veiculo->tipo_frota_id) && $veiculo->tipo_frota_id
                                    ? $veiculo->tipo_frota_id
                                    : null,
                            )" class="form-control form-control-sm"
                            required />
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <x-form-select name="grupo_frota_id" label="Grupo de Frota" :options="$grupoFrotas"
                            placeholder="Selecione um valor" :selected="old(
                                'grupo_frota_id',
                                isset($veiculo->grupo_frota_id) && $veiculo->grupo_frota_id
                                    ? $veiculo->grupo_frota_id
                                    : null,
                            )" class="form-control form-control-sm"
                            required />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-select name="marca_id" label="Marca" :options="$marcas" placeholder="Selecione um valor"
                            :selected="old(
                                'marca_id',
                                isset($veiculo->marca_id) && $veiculo->marca_id ? $veiculo->marca_id : null,
                            )" class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <x-form-select name="modelo_id" label="Modelo" :options="$modelos" placeholder="Selecione um valor"
                            :selected="old(
                                'modelo_id',
                                isset($veiculo->modelo_id) && $veiculo->modelo_id ? $veiculo->modelo_id : null,
                            )" class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-select name="status_veiculo_id" label="Status" :options="$statusVeiculos"
                            placeholder="Selecione um valor" :selected="old(
                                'status_veiculo_id',
                                isset($veiculo->status_veiculo_id) && $veiculo->status_veiculo_id
                                    ? $veiculo->status_veiculo_id
                                    : null,
                            )" class="form-control form-control-sm"
                            onchange="handleValidateStatus(this.value)" required />
                    </div>
                </div>
                <div class="col-md-2" id="div_data_vendido" style="display:none">
                    <div class="form-group">
                        <x-form-input type="date" class="form-control form-control-sm" id="data_vendido"
                            id="data_vendido" name="data_vendido" label="Data de Venda" required
                            value="{{ old('data_vendido', $veiculo->data_vendido ?? '') }}" />
                    </div>
                </div>

                <x-section-division-header-form>Dados de Identificação</x-section-division-header-form><br>
                <div class="col-md-2">
                    <div class="form-group">
                        <x-form-input type="number" class="form-control form-control-sm" id="ano_fabricacao"
                            name="ano_fabricacao" label="Ano de Fabricação" min="1950"
                            value="{{ old('ano_fabricacao', $veiculo->ano_fabricacao ?? '1950') }}" required />
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <x-form-input type="number" class="form-control form-control-sm" id="ano_modelo" name="ano_modelo"
                            min="1950" label="Ano do Modelo"
                            value="{{ old('ano_modelo', $veiculo->ano_modelo ?? '1950') }}" required />
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="placa_licenca"
                            name="placa_licenca" label="Placa"
                            value="{{ old('placa_licenca', $veiculo->placa_licenca ?? '') }}" required />
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="chassi" name="chassi"
                            label="Chassi" value="{{ old('chassi', $veiculo->chassi ?? '') }}" required />
                    </div>
                </div>
                {{-- <div class="col-md-2">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="renavam" name="renavam"
                            label="RENAVAM" value="{{ old('renavam', $veiculo->renavam ?? '') }}" required />
                    </div>
                </div> --}}
                <div class="col-md-2">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="cor" name="cor"
                            label="Cor" value="{{ old('cor', $veiculo->cor ?? '') }}" required />
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="numero_motor"
                            name="numero_motor" label="Número do Motor"
                            value="{{ old('numero_motor', $veiculo->numero_motor ?? '') }}" required />
                    </div>
                </div>

                <x-section-division-header-form>Datas Importantes</x-section-division-header-form><br>
                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-input type="date" class="form-control form-control-sm" id="data_placa_licenca"
                            name="data_placa_licenca" label="Data de Emissão da Placa/Licença"
                            value="{{ old('data_placa_licenca', $veiculo->data_placa_licenca ?? '') }}" required />
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <x-form-input type="date" class="form-control form-control-sm" id="data_compra"
                            name="data_compra" label="Data de Compra"
                            value="{{ old('data_compra', $veiculo->data_compra ?? '') }}" required />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-input type="date" class="form-control form-control-sm" id="data_validade"
                            name="data_validade" label="Data de Validade da Documentação/Licença"
                            value="{{ old('data_validade', $veiculo->data_validade ?? '') }}" />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input type="date" class="form-control form-control-sm" id="data_proxima_manutencao"
                            name="data_proxima_manutencao" label="Data da Próxima Manutenção"
                            value="{{ old('data_proxima_manutencao', $veiculo->data_proxima_manutencao ?? '') }}"
                            required />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input type="date" class="form-control form-control-sm" id="data_proxima_inspecao"
                            name="data_proxima_inspecao" label="Data da Próxima Inspeção"
                            value="{{ old('data_proxima_inspecao', $veiculo->data_proxima_inspecao ?? '') }}" required />
                    </div>
                </div>

                <x-section-division-header-form>Métricas e Limites</x-section-division-header-form><br>
                <div class="col-md-2">
                    <div class="form-group">
                        <x-form-input type="number" class="form-control form-control-sm" id="kms" name="kms"
                            label="Km Atual" value="{{ old('kms', $veiculo->kms ?? '') }}" required />
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <x-form-input type="number" class="form-control form-control-sm" id="limite_kms"
                            name="limite_kms" label="Limite de Km"
                            value="{{ old('limite_kms', $veiculo->limite_kms ?? '') }}" required />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input type="number" class="form-control form-control-sm" id="proxima_revisao_kms"
                            name="proxima_revisao_kms" label="Km da Próxima Revisão"
                            value="{{ old('proxima_revisao_kms', $veiculo->proxima_revisao_kms ?? '') }}" required />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input type="number" class="form-control form-control-sm" id="proxima_manutencao_kms"
                            name="proxima_manutencao_kms" label="Km da Próxima Manutenção"
                            value="{{ old('proxima_manutencao_kms', $veiculo->proxima_manutencao_kms ?? '') }}"
                            required />
                    </div>
                </div>

                @if (!isset($veiculo) || (isset($veiculo) && ($veiculo->local_disponibilidade == null || $veiculo->local_disponibilidade == null)))
                    <x-section-division-header-form>Disponibilidade</x-section-division-header-form><br>
                    <div class="col-md-4">
                        <div class="form-group">
                            <x-form-select name="local_disponibilidade" label="Local" :options="$estacoes"
                                placeholder="Selecione um valor" :selected="old(
                                    'local_disponibilidade',
                                    isset($veiculo->local_disponibilidade) && $veiculo->local_disponibilidade
                                        ? $veiculo->local_disponibilidade
                                        : null,
                                )" class="form-control form-control-sm"
                                required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <x-form-input type="datetime-local" class="form-control form-control-sm"
                                id="data_hora_disponibilidade" name="data_hora_disponibilidade" label="Data e Hora"
                                value="{{ old('data_hora_disponibilidade', $veiculo->data_hora_disponibilidade ?? '') }}"
                                required />
                        </div>
                    </div>
                @endif


                <button type="submit" class="btn btn-primary btn-xs col-12">
                    Salvar
                </button>
            </x-form>
        </div>
    </div>
@stop

@section('js')
    <script>
    $(document).ready(function () {
        configureDataVendido(false);
    });

    function handleValidateStatus(valor) {
        console.log(valor);
        hideCamposValidacaoStatus();
        if (valor == 3) { // Vendido
            configureDataVendido(true);
        }
    }

    function hideCamposValidacaoStatus() {
        configureDataVendido(false);
    }

    function configureDataVendido(show) {
        if (show) {
            $("#data_vendido").attr('required', 'required');
            $('#data_vendido').val(null);
            $('#div_data_vendido').show();
        } else {
            $('#div_data_vendido').hide();
            $("#data_vendido").removeAttr('required');
        }
    }
    </script>
@stop
