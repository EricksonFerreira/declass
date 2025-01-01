@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
    <!-- Adicione isso na seção de cabeçalho do seu layout -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css">
        <link rel="stylesheet" href="{{asset('css/select2.min.css')}}">

@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Atenção!</h5>
            @foreach ($errors->all() as $error)
                <span>{{ $error }}</span>
            @endforeach
        </div>
    @endif
    <div class="d-flex justify-content-between flex-wrap">
        <div class="col-xl-8 col-lg-10 col-md-10 col-sm-12 col-xs-12 card p-2">
            <div class="card-body">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" id="formularioReserva" rowClass="row">

                    <x-section-division-header-form>Estações e Datas</x-section-division-header-form>

                    <!-- Estações e Datas -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-select id="estacao_retirada_id" name="estacao_retirada_id" label="Estação de Retirada"
                                :options="$estacoes" placeholder="Selecione uma estação" :selected="old(
                                    'estacao_retirada_id',
                                    isset($reserva->estacao_retirada_id) ? $reserva->estacao_retirada_id : null,
                                )"
                                class="handleGetGrupoVeiculos form-control form-control-sm " required />
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
                                label="Estação de Devolução" :options="$estacoes" placeholder="Selecione uma estação"
                                :selected="old(
                                    'estacao_devolucao_id',
                                    isset($reserva->estacao_devolucao_id) ? $reserva->estacao_devolucao_id : null,
                                )" class="handleGetGrupoVeiculos form-control form-control-sm" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-input type="datetime-local" class="handleGetGrupoVeiculos form-control form-control-sm"
                                id="data_fim" name="data_fim" label="Data Devolução" value="{{ $reserva->data_fim ?? '' }}"
                                required onchange="validarDatas()"
                                />
                        </div>
                    </div>


                    <x-section-division-header-form>Veículo</x-section-division-header-form>

                    <div class="col-md-8">
                        <div class="form-group">
                            <div class="form-group">
                                <label for="grupo_frota_id">Grupo de Veículos</label>
                                <select name="grupo_frota_id" id="grupo_frota_id"
                                    class="form-control selectpicker form-control-sm" data-live-search="true"
                                    onchange="obterModelosComVeiculosDisponiveis(this)" required>
                                    <option value="" disabled selected>Selecione...</option>
                                    @foreach ($gruposVeiculo as $grupo)
                                        <option value="{{ $grupo->id }}" data-subtext="€ {{ $grupo->valor_diario }}"
                                            {{ old('grupo_frota_id', isset($reserva->grupo_frota_id) ? $reserva->grupo_frota_id : null) == $grupo->id ? 'selected' : '' }}>
                                            {{ $grupo->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>
                     <div class="col-md-2">
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
                                class="form-control form-control-sm" required />
                        @else
                            <x-form-select name="modelo_id" label="Modelo" id="modelo" :options="[]"
                                onchange="getMarcaESegmento(this.value)" placeholder="Selecione primeiro o grupo de frotas"
                                class="form-control form-control-sm" disabled required />
                        @endif
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


                    <x-section-division-header-form>Opcionais</x-section-division-header-form>
                    <div id="opcionais"class="d-flex justify-content-center flex-wrap w-100">
                        <div class="alert alert-light col-md-8 d-flex align-items-center text-xs py-1 mt-1">
                            <i class="fa-solid fa-circle-info me-2"></i>
                            <span>Selecione um grupo de veículos e um modelo para visualizar os opcionais
                                disponíveis.</span>
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


                    <x-section-division-header-form>Informações Pessoais</x-section-division-header-form>


                    <!-- Identificações -->
                    <div class="col-md-5">
                        <div class="form-group">
                            <x-form-select name="user_id" label="Usuário" :options="$users" id="user_id"
                                placeholder="Selecione um usuário" :selected="old('user_id', isset($reserva->user_id) ? $reserva->user_id : null)" placeholder="Selecione...."
                                class="form-control form-control-sm" required />
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
            <button type="submit" class="btn btn-primary btn-xs col-12">
                Salvar
            </button>
            </x-form>
        </div>
        <div class="col-xl-3 col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
            @component('reserva.components.card_valores')
            @endcomponent
        </div>
    </div>
    </div>
    @component('reserva.components.modal-pesquisa')
    @endcomponent
@stop

@section('js')

    <!-- Select2 -->
{{-- <script src="{{asset('js/select2.min.js')}}"></script> --}}
{{-- <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js"></script>
    <script src="{{ asset('js/reserva/reserva.js') }}"></script>
    <script>

        $('#formularioReserva').on('submit', function(e) {
            e.preventDefault();
            // quero que formate os opcionais para eu levar para o servidor em input hidden
            $('#reservaHidden').val(JSON.stringify(getMinhaReserva()));

            this.submit();
        });
        $(document).ready(function() {

            // Se for edição, definir os valores da variável reserva
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


            $('.selectpicker').selectpicker();

            // Inicializar Select2 quando o modal for aberto
            $('.select2 ').select2();
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
            dataInicio.setDate(dataInicio.getDate() + 1); // Adiciona 1 dia à data de início
            var dataMinima = dataInicio.toISOString().split('T')[0] + 'T' + dataInicio.toTimeString().slice(0,5);
            $('#data_fim').attr('min', dataMinima);

            // Verifica se a data fim atual é menor que a nova data mínima
            var dataFimAtual = new Date($('#data_fim').val());
            if (dataFimAtual < dataInicio) {
                $('#data_fim').val(dataMinima);
            }
        });

        $('#data_fim').on('change', function() {
            var dataFim = new Date($(this).val());
            var dataInicio = new Date($('#data_inicio').val());
            if (dataFim < dataInicio) {
                alert('A data de devolução não pode ser anterior à data de retirada.');
                $(this).val($('#data_inicio').val());
            }
        })

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
                    alert('A data de início não pode ser maior que a data de fim.');
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
            let isValid = true;
            $('.handleGetGrupoVeiculos').each(function() {
                if ($(this).val() === '' || $(this).val() == null) {
                    isValid = false;
                    $(this).addClass('is-invalid'); // Adiciona uma classe para destacar o input inválido
                } else {
                    $(this).removeClass('is-invalid'); // Remove a classe se o input estiver válido
                }
            });

            // if (!isValid) {
            //     alert('Por favor, preencha todos os campos corretamente.');
            // }

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

            if (faltaPreencherEstacoesEDatas()) {
                alert('Por favor, preencha as estações e as datas.');
                $('#grupo_frota_id').val('');
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
            modeloSelect.empty(); // Limpa as opções existentes
            modeloSelect.append('<option value="">Selecione um modelo</option>'); // Adiciona a opção padrão

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


        function handleSuccess(data) {
            // Populate the model select dropdown
            populeModelosSelect(data.modelo, 'modelo');


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


            // Definindo uma variável global para armazenar todos os opcionais
            // window.opcionaisGlobais = [];
            // console.group('Opcionais Globais');

            removeAllOpcionalReserva();

            removeAllOpcional();
            for (const key in data.tipo_opcional) {
                // console.log(`Verificando tipo_opcional ${key}`);

                // Garantindo que opcionais existe e é um array
                if (data.tipo_opcional[key].opcionais && Array.isArray(data.tipo_opcional[key].opcionais)) {
                    // console.log(`Adicionando opcionais do tipo_opcional ${key}`, data.tipo_opcional[key].opcionais);
                    // Concatenando os opcionais ao array global
                    data.tipo_opcional[key].opcionais.forEach(opcional => {
                        addOpcional(opcional);
                    });
                } else {
                    // console.log(`Nenvhum opcional encontrado para tipo_opcional ${key}`);
                }
            }

            // Agora 'opcionaisGlobais' contém todos os opcionais
            // console.log("Todos os opcionais:", window.opcionaisGlobais);

            $('#tarifa_base').text("€ " + data.grupo_frota.valor_diario);


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
                        <span>€ ${opcional.preco_integral}</span>
                    </div>
                `;
            } else {
                return `
                    <div class="border p-2 mx-1">

                        <label class="m-0" for="btn-check-${opcional.id}-outlined">${opcional.nome}</label><br>
                                            <span>€ ${opcional.preco_por_dia}</span>
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
                    € ${valor}</div>

            </li>`;

            $('#minhaReserva_opcionals_ul').append(html);

        }
    </script>
@stop
