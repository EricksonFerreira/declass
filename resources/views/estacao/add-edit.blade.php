@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $hasHorarioErrors =
            $errors->has('hora_abertura') ||
            $errors->has('hora_abertura.*') ||
            $errors->has('hora_fechamento') ||
            $errors->has('hora_fechamento.*') ||
            $errors->has('hora_inicio_almoco') ||
            $errors->has('hora_inicio_almoco.*') ||
            $errors->has('hora_fim_almoco') ||
            $errors->has('hora_fim_almoco.*') ||
            $errors->has('hora_inicio_fora_expediente') ||
            $errors->has('hora_inicio_fora_expediente.*') ||
            $errors->has('hora_fim_fora_expediente') ||
            $errors->has('hora_fim_fora_expediente.*');
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
        <div class="card card-estacao shadow-sm border-0">
            <div class="card-header card-header-estacao border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure os dados da estacao, horarios e dias de funcionamento.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formulario" method="POST" action="{{ $rota }}">
                    @if ($metodo == 'PUT')
                        @method('PUT')
                    @endif
                    @csrf

                    <ul class="nav nav-pills nav-estacao mb-3" id="estacao-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="geral-tab" data-toggle="pill" href="#geral" role="tab" aria-controls="geral" aria-selected="true">
                                <i class="fa fa-info-circle mr-1"></i> Geral
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="horarios_diarios-tab" data-toggle="pill" href="#horarios_diarios" role="tab" aria-controls="horarios_diarios" aria-selected="false">
                                <i class="fa fa-clock mr-1"></i> Horarios
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="estacao-tab-content">
                        <div class="tab-pane fade show active" id="geral" role="tabpanel" aria-labelledby="geral-tab">
                            <div class="estacao-section-title">Informacoes Basicas</div>
                            <div class="form-row">
                                <div class="col-lg-7">
                                    <div class="form-group form-group-estacao">
                                        <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome" label="Nome"
                                            value="{{ $estacao->nome ?? '' }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="form-group form-group-estacao">
                                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                                            :selected="old('ativo', isset($estacao->ativo) ? $estacao->ativo : null)" class="form-control form-control-sm" required />
                                    </div>
                                </div>
                            </div>

                            <div class="estacao-section-title">Contato</div>
                            <div class="form-row">
                                <div class="col-lg-5">
                                    <div class="form-group form-group-estacao">
                                        <x-form-input type="email" class="form-control form-control-sm" id="email" name="email" label="E-mail"
                                            value="{{ $estacao->email ?? '' }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group form-group-estacao">
                                        <x-form-input type="text" class="form-control form-control-sm" id="telefone" name="telefone" label="Telefone"
                                            value="{{ $estacao->telefone ?? '' }}" />
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group form-group-estacao">
                                        <x-form-input type="text" class="form-control form-control-sm" id="celular" name="celular" label="Celular"
                                            placeholder="(00) 00000-0000" value="{{ $estacao->celular ?? '' }}" required />
                                    </div>
                                </div>
                            </div>

                            <div class="estacao-section-title">Localizacao</div>
                            <div class="estacao-location-panel mb-2">
                                <div class="estacao-location-panel__head">
                                    <i class="fa fa-map-marker-alt mr-1"></i> Dados de endereco e geolocalizacao
                                </div>

                                <div class="form-row">
                                    <div class="col-lg-3">
                                        <div class="form-group form-group-estacao location-field">
                                            <x-form-input type="text" class="form-control form-control-sm" id="zip_code" name="zip_code" label="Zip Code / CEP"
                                                value="{{ $estacao->zip_code ?? '' }}" required />
                                            <small id="zip-feedback" class="location-zip-feedback d-none"></small>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group form-group-estacao location-field">
                                            <x-form-select name="pais_id" label="Pais" :options="$paises" placeholder="Selecione um valor"
                                                :selected="old('pais_id', isset($estacao->pais_id) ? $estacao->pais_id : null)" class="form-control form-control-sm" required />
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group form-group-estacao location-field">
                                            <x-form-input type="text" class="form-control form-control-sm" id="cidade" name="cidade" label="Cidade"
                                            value="{{ $estacao->cidade ?? '' }}" required />
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group form-group-estacao location-field">
                                            <x-form-input type="text" class="form-control form-control-sm" id="numero" name="numero" label="Numero"
                                                value="{{ $estacao->numero ?? '' }}" required />
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="col-lg-6">
                                        <div class="form-group form-group-estacao location-field">
                                            <x-form-input type="text" class="form-control form-control-sm" id="endereco" name="endereco" label="Endereco"
                                                value="{{ $estacao->endereco ?? '' }}" required />
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group form-group-estacao location-field">
                                            <x-form-input type="text" class="form-control form-control-sm" id="latitude" name="latitude" label="Latitude"
                                                value="{{ $estacao->latitude ?? '' }}" required />
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group form-group-estacao location-field">
                                            <x-form-input type="text" class="form-control form-control-sm" id="longitude" name="longitude" label="Longitude"
                                                value="{{ $estacao->longitude ?? '' }}" required />
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-lg-3">
                                        <div class="form-group form-group-estacao location-field">
                                            <x-form-select name="checkin" label="Permite Check-in" :options="$checkin" placeholder="Selecione um valor"
                                                :selected="old('checkin', isset($estacao->checkin) ? $estacao->checkin : null)" class="form-control form-control-sm" required />
                                        </div>
                                    </div>
                                <div class="col-lg-3">
                                    <div class="form-group form-group-estacao">
                                        <x-form-select name="checkout" label="Permite Check-out" :options="$checkout" placeholder="Selecione um valor"
                                            :selected="old('checkout', isset($estacao->checkout) ? $estacao->checkout : null)" class="form-control form-control-sm" required />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="horarios_diarios" role="tabpanel" aria-labelledby="horarios_diarios-tab">
                            <div id="error-horarios" class="alert alert-warning {{ $hasHorarioErrors ? '' : 'd-none' }} py-2">
                                Preencha todos os campos de Horario Diario antes de salvar.
                            </div>
                            <div class="estacao-section-title d-flex align-items-center justify-content-between">
                                <span><i class="fa fa-clock mr-1"></i> Horarios Diarios</span>
                                <small class="text-muted">Preencha no formato 24h</small>
                            </div>
                            <div class="horarios-wrap">
                                <div class="horarios-legend">
                                    <span class="legend-chip legend-chip--expediente"><i class="fa fa-sun-o mr-1"></i> Expediente</span>
                                    <span class="legend-chip legend-chip--almoco"><i class="fa fa-cutlery mr-1"></i> Almoco</span>
                                    <span class="legend-chip legend-chip--fora"><i class="fa fa-moon-o mr-1"></i> Fora de expediente</span>
                                </div>
                                <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover table-horarios mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th class="th-expediente">Abertura</th>
                                            <th class="th-expediente">Fechamento</th>
                                            <th class="th-almoco">Inicio Almoco</th>
                                            <th class="th-almoco">Fim Almoco</th>
                                            <th class="th-fora">Inicio Fora Expediente</th>
                                            <th class="th-fora">Fim Fora Expediente</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($estacao))
                                            @foreach ($estacao->getHorariosComPivot() as $horario)
                                                <tr>
                                                    <td>
                                                        {{ $horario->nome }}
                                                        <input type="hidden" name="horario_diario_id[]" value="{{ $horario->horario_diario_id ?? null }}">
                                                    </td>
                                                    <td><input type="time" name="hora_abertura[]" value="{{ $horario->hora_abertura ?? '00:00' }}" class="form-control form-control-sm input-horario input-expediente"></td>
                                                    <td><input type="time" name="hora_fechamento[]" value="{{ $horario->hora_fechamento ?? '00:00' }}" class="form-control form-control-sm input-horario input-expediente"></td>
                                                    <td><input type="time" name="hora_inicio_almoco[]" value="{{ $horario->hora_inicio_almoco ?? '00:00' }}" class="form-control form-control-sm input-horario input-almoco"></td>
                                                    <td><input type="time" name="hora_fim_almoco[]" value="{{ $horario->hora_fim_almoco ?? '00:00' }}" class="form-control form-control-sm input-horario input-almoco"></td>
                                                    <td><input type="time" name="hora_inicio_fora_expediente[]" value="{{ $horario->hora_inicio_fora_expediente ?? '00:00' }}" class="form-control form-control-sm input-horario input-fora"></td>
                                                    <td><input type="time" name="hora_fim_fora_expediente[]" value="{{ $horario->hora_fim_fora_expediente ?? '00:00' }}" class="form-control form-control-sm input-horario input-fora"></td>
                                                </tr>
                                            @endforeach
                                        @else
                                            @foreach ($horariosDiarios as $idHorario => $horario)
                                                @php
                                                    $horarioNome = is_array($horario)
                                                        ? ($horario['nome'] ?? ($horario['label'] ?? 'Horario'))
                                                        : (is_object($horario)
                                                            ? ($horario->nome ?? ($horario->label ?? 'Horario'))
                                                            : (string) $horario);
                                                    $horarioId = is_array($horario)
                                                        ? ($horario['id'] ?? $idHorario)
                                                        : (is_object($horario)
                                                            ? ($horario->id ?? $idHorario)
                                                            : $idHorario);
                                                @endphp
                                                <tr>
                                                    <td>
                                                        {{ $horarioNome }}
                                                        <input type="hidden" name="horario_diario_id[]" value="{{ $horarioId }}">
                                                    </td>
                                                    <td><input type="time" name="hora_abertura[]" value="00:00" class="form-control form-control-sm input-horario input-expediente"></td>
                                                    <td><input type="time" name="hora_fechamento[]" value="00:00" class="form-control form-control-sm input-horario input-expediente"></td>
                                                    <td><input type="time" name="hora_inicio_almoco[]" value="00:00" class="form-control form-control-sm input-horario input-almoco"></td>
                                                    <td><input type="time" name="hora_fim_almoco[]" value="00:00" class="form-control form-control-sm input-horario input-almoco"></td>
                                                    <td><input type="time" name="hora_inicio_fora_expediente[]" value="00:00" class="form-control form-control-sm input-horario input-fora"></td>
                                                    <td><input type="time" name="hora_fim_fora_expediente[]" value="00:00" class="form-control form-control-sm input-horario input-fora"></td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                                </div>
                            </div>

                            <div class="estacao-section-title mt-3">Fora de Horario</div>
                            <div class="form-row">
                                <div class="col-lg-4">
                                    <div class="form-group form-group-estacao">
                                        <x-form-select name="cobrar_na_coleta" label="Cobrar na coleta" :options="$checkin" placeholder="Selecione um valor"
                                            :selected="old('cobrar_na_coleta', isset($estacao->cobrar_na_coleta) ? $estacao->cobrar_na_coleta : 'N')"
                                            class="form-control form-control-sm" required />
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group form-group-estacao">
                                        <x-form-select name="cobrar_na_entrega" label="Cobrar na entrega" :options="$checkin" placeholder="Selecione um valor"
                                            :selected="old('cobrar_na_entrega', isset($estacao->cobrar_na_entrega) ? $estacao->cobrar_na_entrega : 'N')"
                                            class="form-control form-control-sm" required />
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group form-group-estacao">
                                        <x-form-select name="valido_no_intervalo" label="Valido no intervalo" :options="$checkin" placeholder="Selecione um valor"
                                            :selected="old('valido_no_intervalo', isset($estacao->valido_no_intervalo) ? $estacao->valido_no_intervalo : 'N')"
                                            class="form-control form-control-sm" required />
                                    </div>
                                </div>
                            </div>

                            <div class="estacao-section-title">Dias Trabalhados</div>
                            <div class="dias-uteis-wrap">
                                @foreach ($diasUteis as $idDiaUtil => $nomeDiaUtil)
                                    <label class="dia-util-chip">
                                        <input type="checkbox" name="diasUteis[]" value="{{ $idDiaUtil }}"
                                            {{ isset($diasUteisEstacao) && in_array($idDiaUtil, $diasUteisEstacao) ? 'checked' : '' }}>
                                        <span>{{ $nomeDiaUtil }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <button id="btn_salvar" type="submit" class="btn btn-primary btn-save-estacao col-12 mt-3">
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-estacao {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-estacao {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-estacao h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .nav-estacao .nav-link {
        border-radius: 8px;
        font-weight: 600;
        color: #334155;
        border: 1px solid #dbe3ef;
        margin-right: .45rem;
        background: #fff;
    }

    .nav-estacao .nav-link.active {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
    }

    .estacao-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-estacao {
        margin-bottom: 1rem;
    }

    .estacao-location-panel {
        border: 1px solid #dbe6f3;
        border-radius: 12px;
        padding: 12px 12px 2px;
        background: linear-gradient(180deg, #fbfdff 0%, #f6f9ff 100%);
    }

    .estacao-location-panel__head {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        background: #ffffff;
        border: 1px solid #d7e3f2;
        color: #1f3a57;
        font-size: 12px;
        font-weight: 700;
        padding: 5px 10px;
        margin-bottom: 10px;
    }

    .location-field {
        margin-bottom: .9rem;
    }

    .location-field .form-label {
        font-weight: 600;
    }

    .location-zip-feedback {
        display: block;
        margin-top: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    .location-zip-feedback.info {
        color: #1d4ed8;
    }

    .location-zip-feedback.success {
        color: #166534;
    }

    .location-zip-feedback.warning {
        color: #92400e;
    }

    .card-estacao .form-control,
    .card-estacao .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-estacao .form-control:focus,
    .card-estacao .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .table-horarios thead th {
        background: linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
        color: #334155;
        font-weight: 700;
        font-size: .78rem;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid #d5e0ee;
    }

    .table-horarios td {
        vertical-align: middle;
        background: #fff;
    }

    .table-horarios tbody tr:nth-child(even) td {
        background: #fbfdff;
    }

    .table-horarios tbody tr:hover td {
        background: #f2f8ff;
    }

    .horarios-wrap {
        border: 1px solid #dbe6f3;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .horarios-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 10px;
        border-bottom: 1px solid #e5edf7;
        background: #f9fbfe;
    }

    .legend-chip {
        display: inline-flex;
        align-items: center;
        border: 1px solid #d6e2f1;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        background: #fff;
    }

    .legend-chip--expediente {
        background: #ecfeff;
        border-color: #a5f3fc;
        color: #155e75;
    }

    .legend-chip--almoco {
        background: #fff7ed;
        border-color: #fed7aa;
        color: #9a3412;
    }

    .legend-chip--fora {
        background: #f3f4f6;
        border-color: #d1d5db;
        color: #374151;
    }

    .input-horario {
        min-width: 118px;
        text-align: center;
        font-weight: 600;
        color: #1e293b;
    }

    .table-horarios thead th.th-expediente {
        background: #dff6fb;
        color: #0f4b5f;
    }

    .table-horarios thead th.th-almoco {
        background: #ffedd5;
        color: #8a2d0b;
    }

    .table-horarios thead th.th-fora {
        background: #eceff3;
        color: #334155;
    }

    .input-horario.input-expediente {
        background: #f0fbff;
        border-color: #c7eaf5;
    }

    .input-horario.input-almoco {
        background: #fff8f1;
        border-color: #ffd8a8;
    }

    .input-horario.input-fora {
        background: #f8fafc;
        border-color: #dbe3ee;
    }

    .dias-uteis-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .dia-util-chip {
        margin: 0;
        cursor: pointer;
    }

    .dia-util-chip input {
        display: none;
    }

    .dia-util-chip span {
        display: inline-block;
        border: 1px solid #d8e1ec;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 600;
        color: #334155;
        background: #fff;
        transition: all .15s ease;
    }

    .dia-util-chip input:checked + span {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }

    .btn-save-estacao {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-estacao {
            padding: .95rem 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }

        .nav-estacao {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 4px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .nav-estacao .nav-item {
            flex: 0 0 auto;
        }

        .nav-estacao .nav-link {
            white-space: nowrap;
        }

        .table-horarios {
            min-width: 760px;
        }

        .input-horario {
            min-width: 92px;
            font-size: 12px;
        }
    }

    @media (max-width: 767.98px) {
        .col-xl-10.col-lg-11.col-md-12.col-sm-12.col-xs-12.px-0 {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .card-header-estacao h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-estacao p {
            font-size: .85rem;
        }

        .estacao-section-title {
            font-size: .9rem;
        }

        .estacao-location-panel {
            padding: 10px 10px 2px;
        }

        .estacao-location-panel__head {
            font-size: 11px;
            margin-bottom: 8px;
        }

        .legend-chip {
            font-size: 10px;
            padding: 4px 8px;
        }

        .table-horarios thead th,
        .table-horarios td {
            font-size: 11px;
            padding: .4rem .35rem;
        }

        .dias-uteis-wrap {
            gap: 6px;
        }

        .dia-util-chip span {
            font-size: 11px;
            padding: 5px 9px;
        }

        .btn-save-estacao {
            min-height: 42px;
            font-size: .95rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-estacao {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-estacao {
            margin-bottom: .8rem;
        }

        .location-field {
            margin-bottom: .75rem;
        }

        .card-estacao .form-control,
        .card-estacao .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(function () {
            $('#celular').mask('(00) 00000-0000');
            $('#telefone').mask('(00) 00000-0000');
            $('#zip_code').mask('00000-000');

            const regexTelefone = /^\(\d{2}\) \d{4,5}-\d{4}$/;

            $('#celular, #telefone').on('blur', function () {
                const valor = ($(this).val() || '').trim();
                const isTelefoneOpcional = this.id === 'telefone';
                const isVazio = valor === '';
                const valido = regexTelefone.test(valor);

                if ((isTelefoneOpcional && isVazio) || valido) {
                    $(this).removeClass('is-invalid');
                    return;
                }

                $(this).addClass('is-invalid');
            });

            $('#celular, #telefone').on('input', function () {
                $(this).removeClass('is-invalid');
            });

            const $zipCode = $('#zip_code');
            const $pais = $('[name="pais_id"]');
            const $cidade = $('#cidade');
            const $endereco = $('#endereco');
            const $zipFeedback = $('#zip-feedback');
            const paisBrasilId = @json($paisBrasilId ?? null);

            function setZipFeedback(message, type = 'info') {
                if (!message) {
                    $zipFeedback.addClass('d-none').removeClass('info success warning').text('');
                    return;
                }

                $zipFeedback
                    .removeClass('d-none info success warning')
                    .addClass(type)
                    .text(message);
            }

            function selecionarPaisBrasil() {
                if (!$pais.length) {
                    return;
                }

                if (paisBrasilId) {
                    $pais.val(String(paisBrasilId)).trigger('change');
                    return;
                }

                const $optionBrasil = $pais.find('option').filter(function () {
                    const texto = ($(this).text() || '').toLowerCase();
                    return texto.includes('brasil') || texto.includes('brazil');
                }).first();

                if ($optionBrasil.length) {
                    $pais.val($optionBrasil.val()).trigger('change');
                }
            }

            async function preencherEnderecoPorCep(cep) {
                setZipFeedback('Buscando endereco pelo CEP...', 'info');

                try {
                    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    if (!response.ok) {
                        throw new Error('Falha ao consultar CEP');
                    }

                    const data = await response.json();
                    if (data.erro) {
                        setZipFeedback('CEP nao encontrado. Confira o valor digitado.', 'warning');
                        return;
                    }

                    const cidade = (data.localidade || '').trim();
                    const enderecoCompleto = [data.logradouro, data.bairro].filter(Boolean).join(' - ').trim();

                    $cidade.val(cidade);
                    $endereco.val(enderecoCompleto);

                    selecionarPaisBrasil();
                    setZipFeedback('Endereco preenchido automaticamente. Confira numero/complemento.', 'success');
                } catch (error) {
                    setZipFeedback('Nao foi possivel consultar o CEP agora. Preencha manualmente.', 'warning');
                }
            }

            $zipCode.on('blur', function () {
                const valor = ($(this).val() || '').trim();
                if (!valor) {
                    setZipFeedback('');
                    return;
                }

                const cepNumerico = valor.replace(/\D/g, '');
                if (cepNumerico.length === 8) {
                    preencherEnderecoPorCep(cepNumerico);
                    return;
                }

                setZipFeedback('Preenchimento automatico disponivel para CEP brasileiro (8 digitos).', 'info');
            });

            $zipCode.on('input', function () {
                const cepNumerico = (($(this).val() || '').trim()).replace(/\D/g, '');
                if (cepNumerico.length === 8) {
                    preencherEnderecoPorCep(cepNumerico);
                }
            });

            function parseTimeToMinutes(time) {
                if (!time || time === '00:00') {
                    return null;
                }
                const parts = time.split(':');
                if (parts.length < 2) {
                    return null;
                }
                const h = parseInt(parts[0], 10);
                const m = parseInt(parts[1], 10);
                if (Number.isNaN(h) || Number.isNaN(m)) {
                    return null;
                }
                return (h * 60) + m;
            }

            function validarLinhaHorario($row) {
                const $abertura = $row.find('input[name="hora_abertura[]"]');
                const $fechamento = $row.find('input[name="hora_fechamento[]"]');
                const $inicioAlmoco = $row.find('input[name="hora_inicio_almoco[]"]');
                const $fimAlmoco = $row.find('input[name="hora_fim_almoco[]"]');
                const $inicioFora = $row.find('input[name="hora_inicio_fora_expediente[]"]');
                const $fimFora = $row.find('input[name="hora_fim_fora_expediente[]"]');

                const abertura = ($abertura.val() || '').trim();
                const fechamento = ($fechamento.val() || '').trim();
                const inicioAlmoco = ($inicioAlmoco.val() || '').trim();
                const fimAlmoco = ($fimAlmoco.val() || '').trim();

                const aberturaMin = parseTimeToMinutes(abertura);
                const fechamentoMin = parseTimeToMinutes(fechamento);
                const inicioAlmocoMin = parseTimeToMinutes(inicioAlmoco);
                const fimAlmocoMin = parseTimeToMinutes(fimAlmoco);

                if (aberturaMin !== null && fechamentoMin !== null && aberturaMin < fechamentoMin) {
                    // Expediente termina no fechamento e volta no proximo dia na abertura
                    $inicioFora.val(fechamento);
                    $fimFora.val(abertura);
                    $inicioFora.removeClass('is-invalid');
                    $fimFora.removeClass('is-invalid');
                }

                let almocoValido = true;
                $inicioAlmoco.removeClass('is-invalid');
                $fimAlmoco.removeClass('is-invalid');

                if (aberturaMin !== null && fechamentoMin !== null) {
                    if (inicioAlmocoMin !== null && (inicioAlmocoMin < aberturaMin || inicioAlmocoMin > fechamentoMin)) {
                        almocoValido = false;
                        $inicioAlmoco.addClass('is-invalid');
                    }

                    if (fimAlmocoMin !== null && (fimAlmocoMin < aberturaMin || fimAlmocoMin > fechamentoMin)) {
                        almocoValido = false;
                        $fimAlmoco.addClass('is-invalid');
                    }

                    if (inicioAlmocoMin !== null && fimAlmocoMin !== null && inicioAlmocoMin >= fimAlmocoMin) {
                        almocoValido = false;
                        $inicioAlmoco.addClass('is-invalid');
                        $fimAlmoco.addClass('is-invalid');
                    }
                }

                return almocoValido;
            }

            $('.table-horarios').on(
                'change',
                'input[name="hora_abertura[]"], input[name="hora_fechamento[]"], input[name="hora_inicio_almoco[]"], input[name="hora_fim_almoco[]"]',
                function () {
                    const $row = $(this).closest('tr');
                    validarLinhaHorario($row);
                }
            );

            $('#formulario').on('submit', function (e) {
                let horariosValidos = true;
                let almocoValidoGlobal = true;

                $('input[name=\"hora_abertura[]\"], input[name=\"hora_fechamento[]\"], input[name=\"hora_inicio_almoco[]\"], input[name=\"hora_fim_almoco[]\"], input[name=\"hora_inicio_fora_expediente[]\"], input[name=\"hora_fim_fora_expediente[]\"]').each(function () {
                    const valor = ($(this).val() || '').trim();
                    if (valor === '' || valor === '00:00') {
                        horariosValidos = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                $('.table-horarios tbody tr').each(function () {
                    if (!validarLinhaHorario($(this))) {
                        almocoValidoGlobal = false;
                    }
                });

                if (!horariosValidos || !almocoValidoGlobal) {
                    e.preventDefault();
                    $('#error-horarios').removeClass('d-none');
                    if (!almocoValidoGlobal) {
                        $('#error-horarios').text('Horario de almoco deve estar dentro da abertura/fechamento e o inicio deve ser menor que o fim.');
                    } else {
                        $('#error-horarios').text('Preencha todos os campos de Horario Diario antes de salvar.');
                    }
                    $('#horarios_diarios-tab').tab('show');
                    const blocoErro = document.getElementById('error-horarios');
                    if (blocoErro) {
                        blocoErro.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    $('#error-horarios').addClass('d-none');
                }
            });

            const hasHorarioErrors = @json($hasHorarioErrors);
            if (hasHorarioErrors) {
                $('#horarios_diarios-tab').tab('show');
            }
        });
    </script>
@stop
