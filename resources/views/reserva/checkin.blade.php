@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />

    <link rel="stylesheet" href="{{asset('css/select2.min.css')}}">
    <style>
        .img-carro{
            width: 15em;
            height: 10em;
        }
    </style>
@stop

@section('content')

    <div class="card card-dark">

            <div class="card-header d-flex pb-0">

                <div class="col">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" href="#dados-veiculo" data-toggle="tab">Dados do Veículo</a>
                        </li>
                        <li class="nav-item" id="estado_veiculo" style="display: none">
                            <a class="nav-link font-weight-bold" href="#estado-veiculo" data-toggle="tab">Estado do Veículo</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" href="#comentarios-observacoes" data-toggle="tab">Comentários e Observações</a>
                        </li>
                    </ul>
                </div>

                <div class="col">
                    <div class="col d-flex justify-content-end">
                        <button class="btn btn-xs btn-primary"  id="btn_criar_contrato"><i class="fa fa-save"></i> Criar Contrato</button>
                    </div>
                </div>

            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="dados-veiculo">
                        <form id="form_dados_veiculos">
                            <div class="form-row">
                                <div class="col">
                                    <label for="marca">Marca <small class="text-danger">*</small></label>
                                    <select required name="marca" id="marca" class="form-control form-control-sm select2">
                                        <option selected disabled>Selecione uma marca</option>
                                        @foreach ($marcas as $marca)
                                            <option {{$reserva->modelos->marca_id == $marca->id ? 'selected' : ''}} value="{{ $marca->id }}">{{ $marca->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="modelo">Modelo <small class="text-danger">*</small></label>
                                    <select required name="modelo" id="modelo" class="form-control form-control-sm select2">
                                        <option selected disabled>Selecione um modelo</option>
                                        @foreach ($modelos as $modelo)
                                            <option {{$reserva->modelo_id == $modelo->id ? 'selected' : ''}} value="{{ $modelo->id }}">{{ $modelo->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="veiculo">Veículo <small class="text-danger">*</small></label>
                                    <select required name="veiculo" id="veiculo" class="form-control form-control-sm select2">
                                        <option selected disabled>Selecione um veículo</option>
                                        @foreach ($veiculos as $veiculo)
                                            <option {{$reserva->veiculo_id == $veiculo->id ? 'selected' : ''}} value="{{ $veiculo->id }}">{{ $veiculo->nome }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                            <div class="form-row mt-2">
                                <div class="col">
                                    <label for="quilometragem">Quilometragem Atual <small class="text-danger">*</small></label>
                                    <input type="number" required class="form-control form-control-sm" required id="quilometragem" name="quilometragem">
                                </div>
                                <div class="col">
                                    <label for="combustivel">Nível de Combustível <small class="text-danger">*</small></label>
                                    <select class="form-control form-control-sm" id="combustivel" required name="combustivel">
                                        <option>Reserva</option>
                                        <option>1/8</option>
                                        <option>1/4</option>
                                        <option>3/8</option>
                                        <option>1/2</option>
                                        <option>5/8</option>
                                        <option>3/4</option>
                                        <option>7/8</option>
                                        <option>Cheio</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="data-saida">Data de Saída <small class="text-danger">*</small></label>
                                    <input type="date" class="form-control form-control-sm" required id="data_saida" name="data_saida" min="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col">
                                    <label for="hora-saida">Hora de Saída <small class="text-danger">*</small></label>
                                    <input type="time" class="form-control form-control-sm" required id="hora_saida" name="hora_saida">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane" id="estado-veiculo">
                        <div class="d-flex  justify-content-between">
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold">Direita:</span>
                                <img onclick="setNomeDano('DIREITA')" src="{{ asset('assets/imgs/carro/direita.jpg') }}" style="cursor: pointer;" class="img-thumbnail img-carro img-fluid mb-2" alt="Imagem Direita">
                            </div>
                            <div class="d-flex flex-column">
                                 <span class="font-weight-bold">Esquerda:</span>
                                <img onclick="setNomeDano('ESQUERDA')"  src="{{ asset('assets/imgs/carro/esquerda.jpg') }}" style="cursor: pointer;" class="img-thumbnail img-carro img-fluid mb-2" alt="Imagem Esquerda">
                            </div>
                            <div class="d-flex flex-column">
                                 <span class="font-weight-bold">Cima:</span>
                                <img onclick="setNomeDano('CIMA')"  src="{{ asset('assets/imgs/carro/cima.jpg') }}" style="cursor: pointer;" class="img-thumbnail img-carro img-fluid mb-2" alt="Imagem Cima">
                            </div>
                            <div class="d-flex flex-column">
                                 <span class="font-weight-bold">Frente:</span>
                                <img onclick="setNomeDano('FRENTE')"  src="{{ asset('assets/imgs/carro/frente.jpg') }}" style="cursor: pointer;" class="img-thumbnail img-carro img-fluid mb-2" alt="Imagem Frente">
                            </div>
                            <div class="d-flex flex-column">
                                 <span class="font-weight-bold">Atrás:</span>
                                <img onclick="setNomeDano('ATRÁS')" src="{{ asset('assets/imgs/carro/atras.jpg') }}" style="cursor: pointer;" class="img-thumbnail img-carro img-fluid mb-2" alt="Imagem Trás">
                            </div>
                        </div>
                        <small class="text-secondary">Clique no local da imagem onde pretende marcar um novo dano.</small>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="font-weight-bold">Danos Existentes</h6>
                                <table class="table table-sm table-striped table-bordered">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Localização</th>
                                            <th>Descrição</th>
                                            <th>Imagem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="danos_existentes">
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="font-weight-bold">Danos Detectados</h6>
                                <table class="table table-sm table-striped table-bordered">
                                    <thead class="thead-gray">
                                        <tr>
                                            <th>Localização</th>
                                            <th>Descrição</th>
                                            <th>Imagem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="danos_detectados">
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @include('reserva.components.modal_dano_checkin')
                        {{-- <form>
                            <div class="form-group">
                                <label for="categoria-problema">Categoria do Problema</label>
                                <select class="form-control form-control-sm" id="categoria-problema" name="categoria-problema">
                                    <option>Carroçaria</option>
                                    <option>Jantes e Pneus</option>
                                    <option>Vidros</option>
                                    <option>Interior</option>
                                    <option>Diversos</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="localizacao-problema">Localização do Problema</label>
                                <select class="form-control form-control-sm" id="localizacao-problema" name="localizacao-problema">
                                    <option>Externo</option>
                                    <option>Interno</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="detalhes-problema">Detalhes do Problema</label>
                                <textarea class="form-control form-control-sm" id="detalhes-problema" name="detalhes-problema"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="descricao-dano">Descrição Geral do Dano</label>
                                <select class="form-control form-control-sm" id="descricao-dano" name="descricao-dano">
                                    <option>Ausente</option>
                                    <option>Amolgadela leve</option>
                                    <option>Amolgadela grave</option>
                                    <option>Risco leve</option>
                                    <option>Risco grave</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="custo-estimado">Custo Estimado</label>
                                <input type="number" class="form-control form-control-sm" id="custo-estimado" name="custo-estimado">
                            </div>
                            <div class="form-group">
                                <label for="quilometragem-dano">Quilometragem</label>
                                <input type="number" class="form-control form-control-sm" id="quilometragem-dano" name="quilometragem-dano">
                            </div>
                            <div class="form-group">
                                <label for="observacoes-dano">Observações</label>
                                <textarea class="form-control form-control-sm" id="observacoes-dano" name="observacoes-dano"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="fotos-dano">Fotos</label>
                                <input type="file" class="form-control-file" id="fotos-dano" name="fotos-dano" multiple>
                            </div>
                        </form> --}}
                    </div>
                    <input type="hidden" id="reserva_id" name="reserva_id" value="{{$reserva->id}}">
                    <div class="tab-pane" id="comentarios-observacoes">
                        <div class="form-row">
                            <div class="col">

                                <label for="notas-saida">Notas na Saída</label>
                                <textarea class="form-control form-control-sm" id="notas_saida" name="notas_saida"></textarea>
                            </div>
                            <div class="col">
                                <label for="observacoes-saida">Observações na Saída</label>
                                <textarea class="form-control form-control-sm" id="observacoes_saida" name="observacoes_saida"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
@stop

@section('js')
<script src="{{asset('js/select2.min.js')}}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            $('#veiculo').change(function(){
                obterDanosDetectados();
                obterDanosExistentes();
            });
        });
        $('#veiculo').change(function(){
            $('#estado_veiculo').show();
        });

        $('#btn_criar_contrato').click(function(){
            $('#form_dados_veiculos').submit();
        });

        $("#form_dados_veiculos").submit(event => {
            event.preventDefault();
            // obter os campos obrigatorios que estao sem valores
            const campos = document.querySelectorAll('#form_dados_veiculos input:required, #form_dados_veiculos select:required');
            campos.forEach(campo => {
                if(!campo.value || (campo.classList.contains('select2-hidden-accessible') && !$(campo).val())){
                    campo.classList.add('is-invalid');
                }else{
                    campo.classList.remove('is-invalid');
                }
            });
            // faça funcionar para o campo veiculo que utiliza o select2
            const camposInvalidos = [];
            campos.forEach(campo => {
                if(!campo.value || (campo.classList.contains('select2-hidden-accessible') && !$(campo).val())){
                    camposInvalidos.push(campo.name);
                }
            });

            if(camposInvalidos.length > 0){
                Swal.fire({
                    icon: 'error',
                    title: 'Por favor, preencha todos os campos obrigatórios!',
                    html: `<ul>${camposInvalidos.map(campo => `<li>${campo.replace(/_/g, ' ').replace(/(?:^\w|[A-Z]|\b\w)/g, (word, index) => index === 0 ? word.toUpperCase() : word.toLowerCase()).replace(/\s+/g, ' ')}</li>`).join('')}</ul>`,
                    showConfirmButton: false,
                    timer: 3000,
                    toast: true,
                    position: 'top-right',
                    customClass: {
                        title: 'small-font'
                    }
                });
                return;
            }

            criarContrato();
        });

        function criarContrato(){
            let contrato = {};
            contrato['marca_id'] =  document.getElementById('marca') ? document.getElementById('marca').value : null;
            contrato['modelo_id'] = document.getElementById('modelo') ? document.getElementById('modelo').value : null;
            contrato['veiculo_id'] = document.getElementById('veiculo') ? document.getElementById('veiculo').value : null;
            contrato['quilometragem_atual'] = document.getElementById('quilometragem') ? document.getElementById('quilometragem').value : null;
            contrato['nivel_combustivel'] = document.getElementById('combustivel') ? document.getElementById('combustivel').value : null;
            contrato['data_saida'] = document.getElementById('data_saida') ? document.getElementById('data_saida').value : null;
            contrato['hora_saida'] = document.getElementById('hora_saida') ? document.getElementById('hora_saida').value : null;
            contrato['notas_saida'] = document.getElementById('notas_saida') ? document.getElementById('notas_saida').value : null;
            contrato['observacoes_saida'] = document.getElementById('observacoes_saida') ? document.getElementById('observacoes_saida').value : null;

            var reserva_id = $('#reserva_id').val();
            var url =  "{{route('reserva.criarContratoCheckin', ':id')}}".replace(':id', reserva_id);

            csrf = document.querySelector('meta[name="csrf-token"]').content;

            $.ajax({
                url:url,
                type: 'POST',
                data: contrato,
                headers: {
                    'X-CSRF-TOKEN': csrf
                },
                success: function(data){
                    Swal.fire({
                        icon: 'success',
                        title: 'Contrato criado com sucesso!',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true,
                        position: 'top-right',
                        customClass: {
                            title: 'small-font'
                        }
                    });
                    setTimeout(() => {
                        window.location.href = `{{route('reserva.show', ':id')}}`.replace(':id', reserva_id);
                    }, 3000);
                },
                error: function(err){
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro ao criar contrato!',
                        showConfirmButton: false,
                        timer: 3000,
                        toast: true,
                        position: 'top-right',
                        customClass: {
                            title: 'small-font'
                        }
                    });
                }
            })
        }


        function obterDanosDetectados(){
            veiculo_id = $('#veiculo').val();
            reserva_id = $('#reserva_id').val();

            $.ajax({
                url: "{{route('dano.obterDanosDetectados')}}",
                type: 'GET',
                data: {
                    veiculo_id: veiculo_id,
                    reserva_id: reserva_id
                },
                success: function(data){
                    $('#danos_detectados').html('');
                    console.log(data)
                    data.forEach(dano => {
                        $('#danos_detectados').append(`
                            <tr>
                                <td>${dano.localizacao}</td>
                                <td>${dano.descricao ?? ''}</td>
                                <td>
                                    ${dano.imagens.map(imagem => {
                                        return `<img src="{{asset('storage')}}/${imagem.imagem}" class="img-thumbnail img-fluid" alt="Imagem Dano">`
                                    }).join('')}
                                </td>
                            </tr>
                        `);
                    });
                }
            });
        }
        function obterDanosExistentes(){
            veiculo_id = $('#veiculo').val();
            reserva_id = $('#reserva_id').val();

            $.ajax({
                url: "{{route('dano.obterDanosExistentes')}}",
                type: 'GET',
                data: {
                    veiculo_id: veiculo_id,
                    reserva_id: reserva_id
                },
                success: function(data){
                    $('#danos_existentes').html('');
                    data.forEach(dano => {
                        $('#danos_existentes').append(`
                            <tr>
                                <td>${dano.localizacao}</td>
                                <td>${dano.descricao ?? ''}</td><td>
                                    ${dano.imagens.map(imagem => {
                                        return `<img src="{{asset('storage')}}/${imagem.imagem}" class="img-thumbnail img-fluid" alt="Imagem Dano">`
                                    }).join('')}
                                </td>
                            </tr>
                        `);
                    });
                }
            });

        }
    </script>
@stop
