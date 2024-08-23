@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="col-xl-8 col-lg-10 col-md-10 col-sm-12 col-xs-12 card p-2">
        <div class="card-body">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">

                <x-section-division-header-form>Estações e Datas</x-section-division-header-form>

                <!-- Estações e Datas -->
                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-select name="estacao_retirada_id" label="Estação de Retirada" :options="$estacoes"
                            placeholder="Selecione uma estação" :selected="isset($reserva->estacao_retirada_id) ? $reserva->estacao_retirada_id : null" class="form-control form-control-sm"
                            required />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input type="datetime-local" class="form-control form-control-sm" id="data_inicio"
                            name="data_inicio" label="Data Retirada" value="{{ $reserva->data_inicio ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-select name="estacao_devolucao_id" label="Estação de Devolução" :options="$estacoes"
                            placeholder="Selecione uma estação" :selected="isset($reserva->estacao_devolucao_id) ? $reserva->estacao_devolucao_id : null" class="form-control form-control-sm"
                            required />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input type="datetime-local" class="form-control form-control-sm" id="data_fim"
                            name="data_fim" label="Data Devolção" value="{{ $reserva->data_fim ?? '' }}" required />
                    </div>
                </div>


                <x-section-division-header-form>Veículo</x-section-division-header-form>

                <div class="col-md-8">
                    <div class="form-group">
                        <x-form-select name="veiculo_id" label="Grupo de Veículos" :options="$veiculos"
                            placeholder="Selecione...." :selected="isset($reserva->veiculo_id) ? $reserva->veiculo_id : null" class="form-control form-control-sm" required />
                    </div>
                </div>
                <button class="btn btn-xs btn-info" onclick="exibeModalGrupoVeiculos()"><i class="fa fa-search"></i>
                    Pesquisar</button>
                <div class="col-md-4 pb-2">
                    <x-form-input type="text" class="form-control form-control-sm" disabled id="marca" name="marca"
                        label="Marca" value="" />
                </div>
                <div class="col-md-4">
                    <x-form-input type="text" class="form-control form-control-sm" disabled id="modelo" name="modelo"
                        label="Modelo" value="" />
                </div>
                <div class="col-md-4">
                    <x-form-input type="text" class="form-control form-control-sm" disabled id="segmento"
                        name="segmento" label="Segmento" value="" />
                </div>

                <x-section-division-header-form>Opcionais</x-section-division-header-form>
                <br><br>

                <x-section-division-header-form>Informações Pessoais</x-section-division-header-form>
                <br><br>

                <!-- Identificações -->
                {{-- <div class="col-md-5">
                        <div class="form-group">
                            <x-form-select name="user_id" label="Usuário" :options="$users" placeholder="Selecione um usuário"
                                :selected="isset($reserva->user_id) ? $reserva->user_id : null"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>

                </div> --}}


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

                <button type="submit" class="btn btn-primary btn-xs col-12">
                    Salvar
                </button>
            </x-form>
        </div>
    </div>
@stop

@section('js')
    <script></script>
@stop
