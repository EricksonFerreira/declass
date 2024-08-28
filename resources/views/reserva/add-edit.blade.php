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
                        <x-form-select name="veiculo_id" label="Grupo de Veículos" :options="$gruposVeiculo"
                            onchange="getAllModelos(this.value)" :selected="isset($reserva->veiculo_id) ? $reserva->veiculo_id : null" placeholder="Selecione...."
                            class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-center">

                    <a class="btn btn-xs btn-info " data-toggle="modal" data-target="#modalPesquisa">
                        <i class="fa fa-search mr-1"></i>
                        Pesquisar
                    </a>
                </div>
                <div class="col-md-3 pb-2">
                    @if (isset($reserva))
                        <x-form-select name="modelo" label="Modelo" :options="$modelos->pluck('nome', 'id')" :selected="$reserva->modelo_id" id="modelo"
                            onchange="getMarcaESegmento(this.value)" placeholder="Selecione um modelo"
                            class="form-control form-control-sm" required />
                    @else
                        <x-form-select name="modelo" label="Modelo" id="modelo" :options="[]"
                            onchange="getMarcaESegmento(this.value)" placeholder="Selecione primeiro o grupo de frotas"
                            class="form-control form-control-sm" disabled required />
                    @endif
                </div>
                <div class="col-md-3">
                    <x-form-input type="text" class="form-control form-control-sm" disabled id="marca" name="marca"
                        label="Marca" value="" />
                </div>
                <div class="col-md-3">
                    <x-form-input type="text" class="form-control form-control-sm" disabled id="segmento"
                        name="segmento" label="Segmento" value="" />
                </div>
                <div class="col-md-2">
                    <x-form-input maxlength="7" id="cupom_desconto" name="cupom_desconto" label="Cupom de Desconto"
                        value="{{ $reserva->cupom_desconto ?? '' }}" class="form-control form-control-sm" />
                </div>

                <x-section-division-header-form>Opcionais</x-section-division-header-form>
                <div id="opcionais"class="d-flex justify-content-center flex-wrap w-100">
                    @if (!isset($reserva))
                        <div class="alert alert-light col-md-8 d-flex align-items-center text-xs py-1 mt-1">
                            <i class="fa-solid fa-circle-info me-2"></i>
                            <span>Selecione um grupo de veículos e um modelo para visualizar os opcionais
                                disponíveis.</span>
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
                                                    <input type="checkbox" name="opcionais[]" value="{{ $opcional->id }}"
                                                        class="btn-check" id="btn-check-{{ $opcional->id }}-outlined"
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
                </div>
                <br>


                <x-section-division-header-form>Informações Pessoais</x-section-division-header-form>


                <!-- Identificações -->
                <div class="col-md-5">
                    <div class="form-group">
                        <x-form-select name="user_id" label="Usuário" :options="$users" placeholder="Selecione um usuário"
                            :selected="isset($reserva->user_id) ? $reserva->user_id : null" class="form-control form-control-sm" required />
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

        <button type="submit" class="btn btn-primary btn-xs col-12">
            Salvar
        </button>
        </x-form>
    </div>
    </div>
    @component('reserva.components.modal-pesquisa')
    @endcomponent
@stop

@section('js')
    <script>
        function getMarcaESegmento(id) {
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
        }

        function getAllModelos(id) {
            $.ajax({
                url: "{{ route('grupo_frota.getModelosPorGrupoFrota') }}",
                type: 'GET',
                data: {
                    grupo_frota_id: id
                },
                success: function(data) {
                    populeModelosSelect(data.modelo, 'modelo');

                    $("#opcionais").empty();

                    Object.values(data.tipo_opcional).forEach(grupo => {
                        const tipo = grupo.tipo;
                        if (grupo.opcionais.length > 0) {
                            const cardHtml = `
            <div class="card mt-1">
                <div class="card-body col-md-12">
                    <p class="text-xs text-bold mb-0">${tipo.nome}</p>
                    <hr class="my-1">
                    <div class="">
                        ${grupo.opcionais.map(opcional => `
                                                <div class="btn btn-outline-dark btn-xs mx-1">
                                                    <input type="checkbox" name="opcionais[]" value="${opcional.id}" class="btn-check"
                                                           id="btn-check-${opcional.id}-outlined" autocomplete="off">
                                                    <label class="m-0" for="btn-check-${opcional.id}-outlined">${opcional.nome}</label><br>
                                                </div>
                                            `).join('')}
                    </div>
                </div>
            </div>
        `;
                            $("#opcionais").append(cardHtml);
                        }
                    });

                    // data.tipo_opcional
                }
            });
        }

        function populeModelosSelect(modelos, selectId) {
            var modeloSelect = $('#' + selectId);
            modeloSelect.empty(); // Limpa as opções existentes
            modeloSelect.append('<option value="">Selecione um modelo</option>'); // Adiciona a opção padrão

            console.log(modelos);
            // Itera sobre o array de modelos e adiciona cada um como uma opção
            $.each(modelos, function(index, modelo) {
                modeloSelect.append('<option value="' + modelo.id + '">' + modelo.nome + '</option>');
            });
            $(modeloSelect).removeAttr('disabled');
            $(modeloSelect).removeAttr('readonly');
        }

        function exibeModalGrupoVeiculos() {
            $('#modalGrupoVeiculos').modal('show');
        }
    </script>
@stop
