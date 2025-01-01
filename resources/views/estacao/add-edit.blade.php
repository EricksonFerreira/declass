@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop


{{-- card card-primary card-outline card-tabs --}}
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
    <div class="col-xl-8 col-lg-10 col-md-12 col-sm-12 col-xs-12 card p-2">
        <div class="row mb-2">
            <div class="card-header col-12" style="border-bottom: 0px">

                <ul class="nav nav-tabs text-sm" id="custom-tabs-three-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="geral-tab" data-toggle="pill" href="#geral" role="tab"
                            aria-controls="geral" aria-selected="true"><i class="fa fa-info-circle text-xs"></i> Geral</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="horarios_diarios-tab" data-toggle="pill" href="#horarios_diarios"
                            role="tab" aria-controls="horarios_diarios" aria-selected="false"><i
                                class="fa fa-clock text-xs"></i> Horários</a>
                    </li>
                </ul>
            </div>

        </div>
        <form id="formulario" method="POST" action="{{ $rota }}">
            @if ($metodo == 'PUT')
                @method('PUT')
            @endif
            @csrf
            <div id="geral-content" class="content-tab">
                <span class="alert alert-danger d-none" id="error-geral">Preencha todos os campos obrigatórios na tela
                    Geral.</span>

                <!-- Informações Básicas -->
                <x-section-division-header-form>Informações Básicas</x-section-division-header-form>
                <div class="col-md-6">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $estacao->nome ?? '' }}" required />
                    </div>
                </div>

                <!-- Contato -->
                <x-section-division-header-form>Contato</x-section-division-header-form>
                <div class="d-flex flex-wrap">

                    <div class="col-md-5">
                        <div class="form-group">
                            <x-form-input type="email" class="form-control form-control-sm" id="email" name="email"
                                label="E-mail" value="{{ $estacao->email ?? '' }}" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-input type="text" class="form-control form-control-sm" id="telefone" name="telefone"
                                label="Telefone" value="{{ $estacao->telefone ?? '' }}" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-input type="text" class="form-control form-control-sm" id="celular" name="celular"
                                placeholder="(00) 00000-0000" label="Celular" value="{{ $estacao->celular ?? '' }}"
                                required />
                        </div>
                    </div>
                </div>

                <!-- Localização -->
                <x-section-division-header-form>Localização</x-section-division-header-form>
                <div class="d-flex flex-wrap">
                    <div class="col-md-3">
                        <div class="form-group ">
                            <x-form-select name="pais_id" label="País" :options="$paises" placeholder="Selecione um valor"
                                :selected="old('pais_id', isset($estacao->pais_id) ? $estacao->pais_id : null)" class="form-control form-control-sm" required />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-input type="text" class="form-control form-control-sm" id="cidade" name="cidade"
                                label="Cidade" value="{{ $estacao->cidade ?? '' }}" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-input type="text" class="form-control form-control-sm" id="endereco" name="endereco"
                                label="Endereço" value="{{ $estacao->endereco ?? '' }}" required />
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <x-form-input type="text" class="form-control form-control-sm" id="numero" name="numero"
                                label="Número" value="{{ $estacao->numero ?? '' }}" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <x-form-input type="text" class="form-control form-control-sm" id="zip_code"
                                name="zip_code" label="Zip Code" value="{{ $estacao->zip_code ?? '' }}" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <x-form-input type="text" class="form-control form-control-sm" id="latitude"
                                name="latitude" label="Latitude" value="{{ $estacao->latitude ?? '' }}" required />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <x-form-input type="text" class="form-control form-control-sm" id="longitude"
                                name="longitude" label="Longitude" value="{{ $estacao->longitude ?? '' }}" required />
                        </div>
                    </div>
                </div>

                <!-- Configurações -->
                <x-section-division-header-form>Configurações</x-section-division-header-form>
                <div class="d-flex">

                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-select name="ativo" label="Status" :options="$status"
                                placeholder="Selecione um valor" :selected="old('ativo', isset($estacao->ativo) ? $estacao->ativo : null)" class="form-control form-control-sm"
                                required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-select name="checkin" label="Permite Check-in" :options="$checkin"
                                placeholder="Selecione um valor" :selected="old('checkin', isset($estacao->checkin) ? $estacao->checkin : null)" class="form-control form-control-sm"
                                required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <x-form-select name="checkout" label="Permite Check-out" :options="$checkout"
                                placeholder="Selecione um valor" :selected="old('checkout', isset($estacao->checkout) ? $estacao->checkout : null)" class="form-control form-control-sm"
                                required />
                        </div>
                    </div>
                </div>
            </div>
            <div id="horarios_diarios-content" class="content-tab mb-2" style="display: none;">
                <span class="alert alert-danger d-none" id="error-horarios">Preencha todos os campos obrigatórios na tela
                    Horários.</span>
                <x-section-division-header-form>Horários Diários</x-section-division-header-form><br>

                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Abertura</th>
                            <th>Fechamento</th>
                            <th>Inicio Almoço</th>
                            <th>Fim Almoço</th>
                            <th>Inicio Fora Expediente</th>
                            <th>Fim Fora Expediente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($estacao))
                            @foreach ($estacao->getHorariosComPivot() as $horario)
                                <tr>
                                    <td>
                                        {{ $horario->nome }}
                                        <input type="hidden" name="horario_diario_id[]"
                                            value="{{ $horario->horario_diario_id ?? null }}">
                                    </td>
                                    <td>
                                        <input type="time" name="hora_abertura[]"
                                            value="{{ $horario->hora_abertura ?? '00:00' }}">
                                    </td>
                                    <td>
                                        <input type="time" name="hora_fechamento[]"
                                            value="{{ $horario->hora_fechamento ?? '00:00' }}">
                                    </td>
                                    <td>
                                        <input type="time" name="hora_inicio_almoco[]"
                                            value="{{ $horario->hora_inicio_almoco ?? '00:00' }}">
                                    </td>
                                    <td>
                                        <input type="time" name="hora_fim_almoco[]"
                                            value="{{ $horario->hora_fim_almoco ?? '00:00' }}">
                                    </td>
                                    <td>
                                        <input type="time" name="hora_inicio_fora_expediente[]"
                                            value="{{ $horario->hora_inicio_fora_expediente ?? '00:00' }}">
                                    </td>
                                    <td>
                                        <input type="time" name="hora_fim_fora_expediente[]"
                                            value="{{ $horario->hora_fim_fora_expediente ?? '00:00' }}">
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @foreach ($horariosDiarios as $horario)
                                <tr>
                                    <td>
                                        {{ $horario->nome }}
                                        <input type="hidden" name="horario_diario_id[]"
                                            value="{{ $horario->id ?? null }}">
                                    </td>
                                    <td>
                                        <input type="time" name="hora_abertura[]" value="00:00">
                                    </td>
                                    <td>
                                        <input type="time" name="hora_fechamento[]" value="00:00">
                                    </td>
                                    <td>
                                        <input type="time" name="hora_inicio_almoco[]" value="00:00">
                                    </td>
                                    <td>
                                        <input type="time" name="hora_fim_almoco[]" value="00:00">
                                    </td>
                                    <td>
                                        <input type="time" name="hora_inicio_fora_expediente[]" value="00:00">
                                    </td>
                                    <td>
                                        <input type="time" name="hora_fim_fora_expediente[]" value="00:00">
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>

                <!-- Conteúdo da aba Fora de Horário -->
                <x-section-division-header-form>Fora de Horário</x-section-division-header-form><br>
                <table class="table table-sm table-bordered table-striped">
                    <tbody>
                        <tr>
                            <td>
                                <x-form-select name="cobrar_na_coleta" label="Permite Check-in" :options="$checkin"
                                    placeholder="Selecione um valor" :selected="old(
                                        'cobrar_na_coleta',
                                        isset($estacao->cobrar_na_coleta) ? $estacao->cobrar_na_coleta : 'N',
                                    )"
                                    class="form-control form-control-sm" required />
                            </td>
                            <td>
                                <x-form-select name="cobrar_na_entrega" label="Permite Check-in" :options="$checkin"
                                    placeholder="Selecione um valor" :selected="old(
                                        'cobrar_na_entrega',
                                        isset($estacao->cobrar_na_entrega) ? $estacao->cobrar_na_entrega : 'N',
                                    )"
                                    class="form-control form-control-sm" required />
                            </td>
                            <td>
                                <x-form-select name="valido_no_intervalo" label="Válido" :options="$checkin"
                                    placeholder="Selecione um valor" :selected="old(
                                        'valido_no_intervalo',
                                        isset($estacao->valido_no_intervalo) ? $estacao->valido_no_intervalo : 'N',
                                    )"
                                    class="form-control form-control-sm" required />
                            </td>
                        </tr>
                    </tbody>
                </table>

                <x-section-division-header-form>Dias Trabalhados</x-section-division-header-form><br>
                <div class="d-flex justify-content-center">
                    @foreach ($diasUteis as $idDiaUtil =>$nomeDiaUtil)
                        <div class="btn btn-outline-secondary btn-xs mx-1">

                            <input type="checkbox" name="diasUteis[]" value="{{ $idDiaUtil }}" class="btn-check"
                                id="btn-check-{{ $idDiaUtil }}-outlined" autocomplete="off"
                                {{ isset($diasUteisEstacao) && in_array($idDiaUtil, $diasUteisEstacao) ? 'checked' : '' }}>
                            <label class="m-0"
                                for="btn-check-{{ $idDiaUtil }}-outlined">{{ $nomeDiaUtil }}</label><br>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-12">
                <button onclick="validateForms()" id="btn_salvar" type="submit" class="btn btn-primary btn-xs col-12">
                    Salvar
                </button>
            </div>
        </form>

    </div>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <script>
        function validateRequiredInputs() {
            let valid = true;
            $('input[required], select[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('error');
                    valid = false;
                } else {
                    $(this).removeClass('error');
                }
            });
            return valid;
        }

        $(document).ready(function() {
            $('button').click(function() {
                if (!validateRequiredInputs()) {
                    alert(
                        'Existem campos obrigatórios que não foram preenchidos.\nVerifique todos os campos da área de Geral e Horários.'
                    );
                }
            });
        });

        $('#celular').mask('(00) 00000-0000');

        // Validar número de celular
        $("#celular").on("blur", function() {
            var celular = $(this).val();
            var regex = /^\(\d{2}\) \d{5}-\d{4}$/;
            if (!regex.test(celular)) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Remover classe is-invalid ao corrigir o número
        $("#celular").on("input", function() {
            var celular = $(this).val();
            var regex = /^\(\d{2}\) \d{5}-\d{4}$/;
            if (regex.test(celular)) {
                $(this).removeClass('is-invalid');
            }
        });

        $(document).ready(function() {
            $('.nav-link').on('click', function() {
                $('.nav-link').removeClass('active');
                $(this).addClass('active');

                $('.content-tab').hide();
                $($(this).attr('href') + '-content').show();
            });
        });
    </script>
@stop
