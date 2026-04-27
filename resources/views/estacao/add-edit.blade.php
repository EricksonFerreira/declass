@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $hasHorarioErrors =
        $errors->has('hora_abertura') || $errors->has('hora_abertura.*') ||
        $errors->has('hora_fechamento') || $errors->has('hora_fechamento.*') ||
        $errors->has('hora_inicio_almoco') || $errors->has('hora_inicio_almoco.*') ||
        $errors->has('hora_fim_almoco') || $errors->has('hora_fim_almoco.*') ||
        $errors->has('hora_inicio_fora_expediente') || $errors->has('hora_inicio_fora_expediente.*') ||
        $errors->has('hora_fim_fora_expediente') || $errors->has('hora_fim_fora_expediente.*');

    $startStep = $hasHorarioErrors ? 4 : 1;
    $isEdit = isset($estacao);
    $diasUteisSelecionados = old('diasUteis', $diasUteisEstacao ?? []);
    $diasUteisSelecionados = is_array($diasUteisSelecionados) ? $diasUteisSelecionados : [$diasUteisSelecionados];
@endphp

@if ($errors->any())
    <div class="alert alert-danger col-xl-11 col-12">
        <ul class="mb-0 pl-3">
            @foreach (array_unique($errors->all()) as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="col-xl-11 col-12 px-0">
    <div class="card card-estacao shadow-sm border-0">
        <div class="card-header card-header-estacao border-0">
            <div>
                <h4 class="mb-1">{{ $titulo }}</h4>
                <p class="mb-0 text-muted">Configure os dados da estação, localização, horários e dias de funcionamento.</p>
            </div>
        </div>

        <div class="card-body p-4">

            {{-- ── WIZARD STEPS ── --}}
            <div class="ec-wizard-steps mb-4" id="wizardSteps">
                @php
                    $steps = [
                        1 => ['icon' => 'fa-info-circle',      'label' => 'Geral'],
                        2 => ['icon' => 'fa-phone',            'label' => 'Contato'],
                        3 => ['icon' => 'fa-map-marker-alt',   'label' => 'Localização'],
                        4 => ['icon' => 'fa-clock',            'label' => 'Horários'],
                        5 => ['icon' => 'fa-cogs',             'label' => 'Operação'],
                    ];
                @endphp
                @foreach($steps as $n => $s)
                    <button type="button" class="ec-step-btn" data-step="{{ $n }}">
                        <span class="ec-step-circle"><i class="fas {{ $s['icon'] }}"></i></span>
                        <span class="ec-step-label">{{ $s['label'] }}</span>
                    </button>
                    @if($n < count($steps))
                        <div class="ec-step-line"></div>
                    @endif
                @endforeach
            </div>

            <form id="formulario" method="POST" action="{{ $rota }}">
                @if ($metodo == 'PUT') @method('PUT') @endif
                @csrf

                {{-- ── STEP 1: GERAL ── --}}
                <div class="ec-step-pane" data-pane="1">
                    <div class="estacao-section-title"><i class="fas fa-info-circle mr-1"></i>Informações Básicas</div>
                    <div class="form-row">
                        <div class="col-lg-8 col-md-12">
                            <div class="form-group form-group-estacao">
                                <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                    label="Nome da Estação" value="{{ old('nome', $estacao->nome ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <div class="form-group form-group-estacao">
                                <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione"
                                    :selected="old('ativo', $estacao->ativo ?? 'S')" class="form-control form-control-sm" required />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 2: CONTATO ── --}}
                <div class="ec-step-pane d-none" data-pane="2">
                    <div class="estacao-section-title"><i class="fas fa-phone mr-1"></i>Informações de Contato</div>
                    <div class="form-row">
                        <div class="col-lg-5 col-md-12">
                            <div class="form-group form-group-estacao">
                                <x-form-input type="email" class="form-control form-control-sm" id="email" name="email"
                                    label="E-mail" value="{{ old('email', $estacao->email ?? '') }}" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-estacao">
                                <x-form-input type="text" class="form-control form-control-sm" id="telefone" name="telefone"
                                    label="Telefone fixo" value="{{ old('telefone', $estacao->telefone ?? '') }}" />
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-estacao">
                                <x-form-input type="text" class="form-control form-control-sm" id="celular" name="celular"
                                    label="Celular / WhatsApp" placeholder="(00) 00000-0000"
                                    value="{{ old('celular', $estacao->celular ?? '') }}" required />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 3: LOCALIZAÇÃO ── --}}
                <div class="ec-step-pane d-none" data-pane="3">
                    <div class="estacao-section-title"><i class="fas fa-map-marker-alt mr-1"></i>Localização</div>
                    <div class="estacao-location-panel mb-3">
                        <div class="estacao-location-panel__head">
                            <i class="fa fa-search-location mr-1"></i> Endereço e Geolocalização
                        </div>
                        <div class="form-row">
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group form-group-estacao location-field">
                                    <x-form-input type="text" class="form-control form-control-sm" id="zip_code" name="zip_code"
                                        label="CEP / Zip Code" value="{{ old('zip_code', $estacao->zip_code ?? '') }}" required />
                                    <small id="zip-feedback" class="location-zip-feedback d-none"></small>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4">
                                <div class="form-group form-group-estacao location-field">
                                    <x-form-select name="pais_id" label="País" :options="$paises" placeholder="Selecione"
                                        :selected="old('pais_id', $estacao->pais_id ?? null)" class="form-control form-control-sm" required />
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="form-group form-group-estacao location-field">
                                    <x-form-input type="text" class="form-control form-control-sm" id="cidade" name="cidade"
                                        label="Cidade" value="{{ old('cidade', $estacao->cidade ?? '') }}" required />
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <div class="form-group form-group-estacao location-field">
                                    <x-form-input type="text" class="form-control form-control-sm" id="numero" name="numero"
                                        label="Número" value="{{ old('numero', $estacao->numero ?? '') }}" required />
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-lg-6 col-md-12">
                                <div class="form-group form-group-estacao location-field">
                                    <x-form-input type="text" class="form-control form-control-sm" id="endereco" name="endereco"
                                        label="Endereço / Logradouro" value="{{ old('endereco', $estacao->endereco ?? '') }}" required />
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-estacao location-field">
                                    <x-form-input type="text" class="form-control form-control-sm" id="latitude" name="latitude"
                                        label="Latitude" value="{{ old('latitude', $estacao->latitude ?? '') }}" required />
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-estacao location-field">
                                    <x-form-input type="text" class="form-control form-control-sm" id="longitude" name="longitude"
                                        label="Longitude" value="{{ old('longitude', $estacao->longitude ?? '') }}" required />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 4: HORÁRIOS ── --}}
                <div class="ec-step-pane d-none" data-pane="4">
                    <div id="error-horarios" class="alert alert-warning {{ $hasHorarioErrors ? '' : 'd-none' }} py-2">
                        Preencha todos os campos de horário antes de salvar.
                    </div>

                    <div class="estacao-section-title"><i class="fas fa-clock mr-1"></i>Horários por Tipo de Dia</div>
                    <p class="text-muted small mb-3">
                        Defina os horários de abertura, fechamento e intervalo para cada tipo de dia.
                        Use o botão de intervalo para sinalizar quando não há almoço.
                        Os campos "Fora de Expediente" são preenchidos automaticamente.
                    </p>

                    <div class="ec-horarios-grid">
                        @php
                            $horariosExistentes = isset($estacao) ? collect($estacao->getHorariosComPivot())->keyBy('horario_diario_id') : collect();
                        @endphp

                        @foreach ($horariosDiarios as $idHorario => $horario)
                            @php
                                $horarioNome = is_array($horario) ? ($horario['nome'] ?? 'Horário') : (is_object($horario) ? ($horario->nome ?? 'Horário') : (string) $horario);
                                $horarioId   = is_array($horario) ? ($horario['id'] ?? $idHorario) : (is_object($horario) ? ($horario->id ?? $idHorario) : $idHorario);

                                $pivotData = $horariosExistentes->get($horarioId);
                                $vAbertura  = $pivotData?->hora_abertura ?? '';
                                $vFechamento= $pivotData?->hora_fechamento ?? '';
                                $vIniAlmoco = $pivotData?->hora_inicio_almoco ?? '';
                                $vFimAlmoco = $pivotData?->hora_fim_almoco ?? '';
                                $vIniForaEx = $pivotData?->hora_inicio_fora_expediente ?? '';
                                $vFimForaEx = $pivotData?->hora_fim_fora_expediente ?? '';

                                $iconMap = [
                                    'dias da semana' => ['icon' => 'fa-briefcase', 'color' => '#3b82f6', 'bg' => '#eff6ff'],
                                    'sábados'        => ['icon' => 'fa-sun',       'color' => '#f59e0b', 'bg' => '#fffbeb'],
                                    'domingos'       => ['icon' => 'fa-star',      'color' => '#8b5cf6', 'bg' => '#f5f3ff'],
                                    'feriados'       => ['icon' => 'fa-calendar-times', 'color' => '#ef4444', 'bg' => '#fef2f2'],
                                ];
                                $nomeLower  = mb_strtolower($horarioNome);
                                $iconInfo   = $iconMap[$nomeLower] ?? ['icon' => 'fa-clock', 'color' => '#64748b', 'bg' => '#f8fafc'];
                            @endphp

                            @php
                                $isFeriado = mb_stripos($horarioNome, 'feriado') !== false;
                                $funcionaFeriado = old('funciona.'.$loop->index, $pivotData?->funciona ?? 'S') === 'S';
                            @endphp
                            <div class="ec-horario-card {{ $isFeriado && !$funcionaFeriado ? 'ec-horario-card--inativo' : '' }}">
                                @php
                                    $is24h = old('funciona_24h.'.$loop->index, $pivotData?->funciona_24h ?? 'N') === 'S';
                                    $temIntervaloAlmoco = old('tem_intervalo_almoco.'.$loop->index, $pivotData?->tem_intervalo_almoco ?? 'S') === 'S';
                                @endphp
                                <div class="ec-horario-card__head" style="background:{{ $iconInfo['bg'] }}; border-color:{{ $iconInfo['color'] }}20;">
                                    <div class="ec-horario-card__icon" style="background:{{ $iconInfo['color'] }}20; color:{{ $iconInfo['color'] }};">
                                        <i class="fas {{ $iconInfo['icon'] }}"></i>
                                    </div>
                                    <span class="ec-horario-card__title">{{ $horarioNome }}</span>
                                    <input type="hidden" name="horario_diario_id[]" value="{{ $horarioId }}">

                                    @if($isFeriado)
                                        {{-- Toggle: Funciona em feriados? --}}
                                        <label class="ec-toggle-24h ec-toggle-24h--feriado ml-auto js-toggle-feriado" title="Funciona em feriados">
                                            <input type="hidden" name="funciona[]" value="N" class="input-funciona-feriado-hidden">
                                            <input type="checkbox" class="input-funciona-feriado-check" value="S"
                                                {{ $funcionaFeriado ? 'checked' : '' }}>
                                            <span class="ec-toggle-24h__track">
                                                <span class="ec-toggle-24h__knob"></span>
                                            </span>
                                            <span class="ec-toggle-24h__label">Funciona</span>
                                        </label>
                                    @else
                                        {{-- Para outros tipos, funciona é sempre S --}}
                                        <input type="hidden" name="funciona[]" value="S">
                                    @endif

                                    {{-- Toggle 24h (oculto se feriado inativo) --}}
                                    <label class="ec-toggle-24h {{ $isFeriado ? 'ml-2' : 'ml-auto' }} js-horario-campos-feriado {{ $isFeriado && !$funcionaFeriado ? 'd-none' : '' }}" title="Funciona 24 horas">
                                        <input type="hidden" name="funciona_24h[]" value="N" class="input-24h-hidden">
                                        <input type="checkbox" class="input-24h-check" value="S"
                                            {{ $is24h ? 'checked' : '' }}>
                                        <span class="ec-toggle-24h__track">
                                            <span class="ec-toggle-24h__knob"></span>
                                        </span>
                                        <span class="ec-toggle-24h__label">24h</span>
                                    </label>
                                    <label class="ec-toggle-24h ec-toggle-24h--warning ml-2 js-horario-campos-feriado {{ $isFeriado && !$funcionaFeriado ? 'd-none' : '' }}" title="Sem intervalo para almoço">
                                        <input type="hidden" name="tem_intervalo_almoco[]" value="{{ $temIntervaloAlmoco ? 'S' : 'N' }}" class="input-almoco-hidden">
                                        <input type="checkbox" class="input-almoco-check" value="N" {{ $temIntervaloAlmoco ? '' : 'checked' }}>
                                        <span class="ec-toggle-24h__track">
                                            <span class="ec-toggle-24h__knob"></span>
                                        </span>
                                        <span class="ec-toggle-24h__label">Sem intervalo</span>
                                    </label>
                                </div>

                                <div class="ec-horario-card__body {{ $is24h ? 'ec-body--24h' : '' }}">
                                    {{-- Banner 24h (visível quando ativo) --}}
                                    <div class="ec-24h-banner {{ $is24h ? '' : 'd-none' }}">
                                        <i class="fas fa-infinity mr-2"></i>
                                        Funcionamento 24 horas — sem restrição de horário
                                    </div>
                                    <div class="ec-no-lunch-banner {{ $temIntervaloAlmoco ? 'd-none' : '' }}">
                                        <i class="fas fa-ban mr-2"></i>
                                        Este dia não possui intervalo para almoço.
                                    </div>

                                    <div class="ec-horario-fields {{ $is24h ? 'd-none' : '' }}">
                                        <div class="ec-time-group">
                                            <div class="ec-time-group__label" style="color:{{ $iconInfo['color'] }};">
                                                <i class="fas fa-sun mr-1"></i> Expediente
                                            </div>
                                            <div class="ec-time-row">
                                                <div class="ec-time-field">
                                                    <label>Abertura</label>
                                                    <input type="time" name="hora_abertura[]" value="{{ old('hora_abertura.'.$loop->index, $vAbertura ?: '08:00') }}"
                                                        class="form-control form-control-sm input-horario input-expediente">
                                                </div>
                                                <div class="ec-time-arrow"><i class="fas fa-arrow-right text-muted"></i></div>
                                                <div class="ec-time-field">
                                                    <label>Fechamento</label>
                                                    <input type="time" name="hora_fechamento[]" value="{{ old('hora_fechamento.'.$loop->index, $vFechamento ?: '18:00') }}"
                                                        class="form-control form-control-sm input-horario input-expediente">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ec-time-group">
                                            <div class="ec-time-group__label" style="color:#9a3412;">
                                                <i class="fas fa-utensils mr-1"></i> Intervalo / Almoço
                                            </div>
                                            <div class="ec-time-row">
                                                <div class="ec-time-field">
                                                    <label>Início</label>
                                                    <input type="time" name="hora_inicio_almoco[]" value="{{ old('hora_inicio_almoco.'.$loop->index, $vIniAlmoco ?: '12:00') }}"
                                                        class="form-control form-control-sm input-horario input-almoco">
                                                </div>
                                                <div class="ec-time-arrow"><i class="fas fa-arrow-right text-muted"></i></div>
                                                <div class="ec-time-field">
                                                    <label>Fim</label>
                                                    <input type="time" name="hora_fim_almoco[]" value="{{ old('hora_fim_almoco.'.$loop->index, $vFimAlmoco ?: '13:00') }}"
                                                        class="form-control form-control-sm input-horario input-almoco">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ec-time-group ec-time-group--fora">
                                            <div class="ec-time-group__label" style="color:#475569;">
                                                <i class="fas fa-moon mr-1"></i> Fora do Expediente
                                                <small class="text-muted ml-1">(automático)</small>
                                            </div>
                                            <div class="ec-time-row">
                                                <div class="ec-time-field">
                                                    <label>Início</label>
                                                    <input type="time" name="hora_inicio_fora_expediente[]" value="{{ old('hora_inicio_fora_expediente.'.$loop->index, $vIniForaEx ?: '18:00') }}"
                                                        class="form-control form-control-sm input-horario input-fora" readonly>
                                                </div>
                                                <div class="ec-time-arrow"><i class="fas fa-arrow-right text-muted"></i></div>
                                                <div class="ec-time-field">
                                                    <label>Fim</label>
                                                    <input type="time" name="hora_fim_fora_expediente[]" value="{{ old('hora_fim_fora_expediente.'.$loop->index, $vFimForaEx ?: '08:00') }}"
                                                        class="form-control form-control-sm input-horario input-fora" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Campos ocultos quando 24h (para não quebrar o array no controller) --}}
                                    <div class="ec-hidden-24h-fields {{ $is24h ? '' : 'd-none' }}">
                                        <input type="hidden" name="hora_abertura[]" value="00:00">
                                        <input type="hidden" name="hora_fechamento[]" value="23:59">
                                        <input type="hidden" name="hora_inicio_almoco[]" value="00:00">
                                        <input type="hidden" name="hora_fim_almoco[]" value="00:00">
                                        <input type="hidden" name="hora_inicio_fora_expediente[]" value="00:00">
                                        <input type="hidden" name="hora_fim_fora_expediente[]" value="00:00">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="estacao-section-title mt-4"><i class="fas fa-calendar-check mr-1"></i>Dias de Funcionamento</div>
                    <div class="dias-uteis-wrap">
                        @foreach ($diasUteis as $idDiaUtil => $nomeDiaUtil)
                            <label class="dia-util-chip">
                                <input type="checkbox" name="diasUteis[]" value="{{ $idDiaUtil }}"
                                    {{ in_array($idDiaUtil, $diasUteisSelecionados) ? 'checked' : '' }}>
                                <span>{{ $nomeDiaUtil }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- ── STEP 5: OPERAÇÃO ── --}}
                <div class="ec-step-pane d-none" data-pane="5">
                    <div class="estacao-section-title"><i class="fas fa-cogs mr-1"></i>Configurações de Operação</div>
                    <div class="form-row">
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-estacao">
                                <x-form-select name="checkin" label="Permite Check-in" :options="$checkin" placeholder="Selecione"
                                    :selected="old('checkin', $estacao->checkin ?? 'N')" class="form-control form-control-sm" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-estacao">
                                <x-form-select name="checkout" label="Permite Check-out" :options="$checkout" placeholder="Selecione"
                                    :selected="old('checkout', $estacao->checkout ?? 'N')" class="form-control form-control-sm" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-estacao">
                                <x-form-select name="cobrar_na_coleta" id="cobrar_na_coleta" label="Cobrar taxa fora de expediente (coleta)" :options="$checkin" placeholder="Selecione"
                                    :selected="old('cobrar_na_coleta', $estacao->cobrar_na_coleta ?? 'N')" class="form-control form-control-sm" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 js-taxa-coleta-wrap {{ old('cobrar_na_coleta', $estacao->cobrar_na_coleta ?? 'N') === 'S' ? '' : 'd-none' }}">
                            <div class="form-group form-group-estacao">
                                <label class="mb-1">Valor da taxa (coleta) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                    <input type="number" name="valor_taxa_coleta" id="valor_taxa_coleta" step="0.01" min="0"
                                        class="form-control form-control-sm"
                                        value="{{ old('valor_taxa_coleta', $estacao->valor_taxa_coleta ?? '') }}"
                                        placeholder="0,00">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-estacao">
                                <x-form-select name="cobrar_na_entrega" id="cobrar_na_entrega" label="Cobrar taxa fora de expediente (entrega)" :options="$checkin" placeholder="Selecione"
                                    :selected="old('cobrar_na_entrega', $estacao->cobrar_na_entrega ?? 'N')" class="form-control form-control-sm" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 js-taxa-entrega-wrap {{ old('cobrar_na_entrega', $estacao->cobrar_na_entrega ?? 'N') === 'S' ? '' : 'd-none' }}">
                            <div class="form-group form-group-estacao">
                                <label class="mb-1">Valor da taxa (entrega) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                    <input type="number" name="valor_taxa_entrega" id="valor_taxa_entrega" step="0.01" min="0"
                                        class="form-control form-control-sm"
                                        value="{{ old('valor_taxa_entrega', $estacao->valor_taxa_entrega ?? '') }}"
                                        placeholder="0,00">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group form-group-estacao">
                                <x-form-select name="valido_no_intervalo" label="Válido no intervalo (almoço)" :options="$checkin" placeholder="Selecione"
                                    :selected="old('valido_no_intervalo', $estacao->valido_no_intervalo ?? 'N')" class="form-control form-control-sm" required />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- NAV BUTTONS --}}
                <div class="ec-wizard-nav mt-4 d-flex justify-content-between align-items-center">
                    <div class="">

                        <button type="button" class="btn btn-sm ec-nav-btn ec-nav-btn--back" id="btnPrev" style="display:none!important;">
                            <i class="fas fa-chevron-left mr-1"></i> Anterior
                        </button>
                    </div>
                    <div class="d-flex align-items-center" style="gap:8px;">
                        <button type="button" class="btn btn-sm ec-nav-btn ec-nav-btn--next" id="btnNext">
                            Próximo <i class="fas fa-chevron-right ml-1"></i>
                        </button>
                        <button type="submit" class="btn btn-sm ec-nav-btn ec-nav-btn--save d-none" id="btnSalvar">
                            <i class="fas fa-save mr-1"></i> Salvar alterações
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<style>
.driver-popover{border-radius:14px!important;box-shadow:0 8px 40px rgba(10,22,40,.2)!important}
.driver-popover-title{font-family:inherit!important;font-weight:800!important;font-size:.95rem!important;color:#0a1628!important}
.driver-popover-description{font-size:.84rem!important;line-height:1.6!important;color:#374151!important}
.driver-popover-footer button{border-radius:8px!important;font-weight:700!important;font-size:.8rem!important;padding:.35rem .85rem!important}
</style>
<style>
.card-estacao { border-radius: 14px; overflow: hidden; }
.card-header-estacao {
    background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
    padding: 1.1rem 1.5rem;
}
.card-header-estacao h4 { color: #0f2740; font-weight: 700; }

/* ── Wizard Steps ── */
.ec-wizard-steps {
    display: flex;
    align-items: center;
    gap: 0;
    overflow-x: auto;
    padding-bottom: 4px;
}
.ec-step-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    background: none;
    border: none;
    padding: 0 8px;
    cursor: pointer;
    flex-shrink: 0;
}
.ec-step-circle {
    width: 38px; height: 38px;
    border-radius: 50%;
    border: 2px solid #d0daea;
    background: #f8fafc;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem;
    color: #94a3b8;
    transition: all .2s;
}
.ec-step-btn.active .ec-step-circle {
    background: #2563eb; border-color: #2563eb; color: #fff;
}
.ec-step-btn.done .ec-step-circle {
    background: #ecfdf5; border-color: #10b981; color: #10b981;
}
.ec-step-label {
    font-size: .72rem;
    font-weight: 600;
    color: #94a3b8;
    white-space: nowrap;
}
.ec-step-btn.active .ec-step-label { color: #2563eb; }
.ec-step-btn.done .ec-step-label { color: #10b981; }
.ec-step-line {
    flex: 1;
    height: 2px;
    background: #e5eaf2;
    min-width: 16px;
    margin-bottom: 18px;
}

/* ── Section titles ── */
.estacao-section-title {
    font-size: .96rem; font-weight: 700; color: #21364f;
    letter-spacing: .02em;
    padding-bottom: 8px; border-bottom: 1px solid #e5eaf2; margin-bottom: 14px;
}

.form-group-estacao { margin-bottom: 1rem; }

/* ── Location panel ── */
.estacao-location-panel {
    border: 1px solid #dbe6f3; border-radius: 12px;
    padding: 12px 12px 4px;
    background: linear-gradient(180deg, #fbfdff 0%, #f6f9ff 100%);
}
.estacao-location-panel__head {
    display: inline-flex; align-items: center;
    border-radius: 999px; background: #fff; border: 1px solid #d7e3f2;
    color: #1f3a57; font-size: 12px; font-weight: 700;
    padding: 5px 10px; margin-bottom: 10px;
}
.location-zip-feedback { display:block; margin-top:4px; font-size:11px; font-weight:600; }
.location-zip-feedback.info    { color:#1d4ed8; }
.location-zip-feedback.success { color:#166534; }
.location-zip-feedback.warning { color:#92400e; }

/* ── Horários grid ── */
.ec-horarios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}
.ec-horario-card {
    border: 1px solid #e5eaf2;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
}
.ec-horario-card__head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-bottom: 1px solid #e5eaf2;
}
.ec-horario-card__icon {
    width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem;
}
.ec-horario-card__title {
    font-weight: 700; color: #1e293b; font-size: .92rem;
}
.ec-horario-card__body { padding: 14px; display: flex; flex-direction: column; gap: 12px; }

.ec-time-group__label {
    font-size: .78rem; font-weight: 700;
    margin-bottom: 6px;
}
.ec-time-row {
    display: flex; align-items: center; gap: 8px;
}
.ec-time-field { flex: 1; }
.ec-time-field label {
    font-size: .72rem; color: #64748b; font-weight: 600;
    display: block; margin-bottom: 2px;
}
.ec-time-arrow { flex-shrink: 0; }
.ec-time-group--fora .ec-time-field input {
    background: #f1f5f9 !important;
    color: #94a3b8;
    cursor: not-allowed;
}

/* ── Toggle 24h ── */
.ec-toggle-24h {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    user-select: none;
    margin: 0;
}
.ec-toggle-24h input[type="checkbox"] { display: none; }
.ec-toggle-24h__track {
    width: 36px; height: 20px;
    background: #d0daea;
    border-radius: 999px;
    position: relative;
    transition: background .2s;
    flex-shrink: 0;
}
.ec-toggle-24h__knob {
    position: absolute;
    top: 3px; left: 3px;
    width: 14px; height: 14px;
    background: #fff;
    border-radius: 50%;
    transition: transform .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.ec-toggle-24h input[type="checkbox"]:checked ~ .ec-toggle-24h__track { background: #2563eb; }
.ec-toggle-24h input[type="checkbox"]:checked ~ .ec-toggle-24h__track .ec-toggle-24h__knob { transform: translateX(16px); }
.ec-toggle-24h__label { font-size: .75rem; font-weight: 700; color: #475569; }

/* Toggle Funciona (feriado) — verde */
.ec-toggle-24h--feriado .ec-toggle-24h__label { color: #15803d; }
.ec-toggle-24h--feriado input[type="checkbox"]:checked ~ .ec-toggle-24h__track { background: #16a34a; }

/* Card de feriado desativado */
.ec-horario-card--inativo { opacity: .55; }
.ec-horario-card--inativo .ec-horario-card__body { display: none; }

/* 24h active state */
.ec-horario-card.is-24h .ec-horario-card__head { border-bottom: 2px solid #2563eb; }
.ec-24h-banner {
    background: linear-gradient(90deg, #eff6ff, #dbeafe);
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 10px 14px;
    color: #1d4ed8;
    font-weight: 700;
    font-size: .85rem;
    display: flex;
    align-items: center;
}
.ec-no-lunch-banner {
    background: linear-gradient(90deg, #fffbeb, #fef3c7);
    border: 1px solid #fcd34d;
    border-radius: 8px;
    padding: 10px 14px;
    color: #b45309;
    font-weight: 700;
    font-size: .85rem;
    display: flex;
    align-items: center;
}

.input-horario { text-align: center; font-weight: 600; color: #1e293b; }
.input-expediente { background: #f0fbff; border-color: #c7eaf5; }
.input-almoco     { background: #fff8f1; border-color: #ffd8a8; }
.input-fora       { background: #f8fafc; border-color: #dbe3ee; }

/* ── Dias úteis chips ── */
.dias-uteis-wrap { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
.dia-util-chip { margin: 0; cursor: pointer; }
.dia-util-chip input { display: none; }
.dia-util-chip span {
    display: inline-block;
    border: 1px solid #d8e1ec; border-radius: 999px;
    padding: 6px 12px; font-size: 12px; font-weight: 600; color: #334155;
    background: #fff; transition: all .15s ease;
}
.dia-util-chip input:checked + span {
    background: #2563eb; color: #fff; border-color: #2563eb;
}

/* ── Form inputs ── */
.ec-nav-btn {
    min-height: 38px;
    padding: .58rem 1.05rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .35rem;
    border-radius: 999px;
    font-weight: 700;
    box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
    transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease, border-color .15s ease, color .15s ease;
}

.ec-nav-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(15, 23, 42, .10);
}

.ec-nav-btn--back {
    background: #fff;
    border: 1px solid #cbd5e1;
    color: #64748b;
}

.ec-nav-btn--back:hover,
.ec-nav-btn--back.ec-nav-btn--visible {
    background: #fff;
    border-color: #94a3b8;
    color: #475569;
}

.ec-nav-btn--next {
    background: #2563eb;
    border: 1px solid #2563eb;
    color: #fff;
}

.ec-nav-btn--next:hover {
    background: #1d4ed8;
    border-color: #1d4ed8;
    color: #fff;
}

.ec-nav-btn--save {
    background: #16a34a;
    border: 1px solid #16a34a;
    color: #fff;
}

.ec-nav-btn--save:hover {
    background: #15803d;
    border-color: #15803d;
    color: #fff;
}

.card-estacao .form-control,
.card-estacao .form-select {
    border-radius: 10px; border-color: #d8e1ec; min-height: 38px;
}
.card-estacao .form-control:focus,
.card-estacao .form-select:focus {
    border-color: #4b80ff;
    box-shadow: 0 0 0 .15rem rgba(75,128,255,.18);
}

@media (max-width:575px) {
    .ec-horarios-grid { grid-template-columns: 1fr; }
    .card-header-estacao { padding: .85rem; }
    .card-body { padding: .85rem !important; }
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
$(function () {
    // ── Phone/ZIP masks ──
    $('#celular').mask('(00) 00000-0000');
    $('#telefone').mask('(00) 00000-0000');
    $('#zip_code').mask('00000-000');
    const isEdit = @json($isEdit);

    // ── Wizard step navigation ──
    const TOTAL_STEPS = 5;
    let currentStep  = {{ $startStep }};

    function goToStep(n) {
        n = Math.max(1, Math.min(TOTAL_STEPS, n));
        currentStep = n;

        $('.ec-step-pane').addClass('d-none');
        $(`.ec-step-pane[data-pane="${n}"]`).removeClass('d-none');

        $('.ec-step-btn').each(function () {
            const s = parseInt($(this).data('step'));
            $(this).removeClass('active done');
            if (s === n)  $(this).addClass('active');
            if (s < n)    $(this).addClass('done');
        });

        $('#btnPrev').css('display', n > 1 ? 'inline-flex' : 'none');
        $('#btnPrev').toggleClass('ec-nav-btn--visible', n > 1);
        $('#btnNext').toggleClass('d-none', n >= TOTAL_STEPS);

        if (isEdit) {
            $('#btnSalvar').removeClass('d-none');
        } else {
            $('#btnSalvar').toggleClass('d-none', n < TOTAL_STEPS);
        }
    }

    goToStep(currentStep);

    $('#btnNext').on('click', function () { goToStep(currentStep + 1); });
    $('#btnPrev').on('click', function () { goToStep(currentStep - 1); });
    $('.ec-step-btn').on('click', function () { goToStep(parseInt($(this).data('step'))); });

    // ── CEP auto-fill ──
    const $zipCode = $('#zip_code');
    const $pais    = $('[name="pais_id"]');
    const $cidade  = $('#cidade');
    const $endereco= $('#endereco');
    const $zipFb   = $('#zip-feedback');
    const paisBrasilId = @json($paisBrasilId ?? null);

    function setZipFeedback(msg, type = 'info') {
        if (!msg) { $zipFb.addClass('d-none').text(''); return; }
        $zipFb.removeClass('d-none info success warning').addClass(type).text(msg);
    }

    function selecionarBrasil() {
        if (paisBrasilId) { $pais.val(String(paisBrasilId)).trigger('change'); return; }
        $pais.find('option').filter(function() {
            return $(this).text().toLowerCase().includes('brasil');
        }).first().each(function() { $pais.val($(this).val()).trigger('change'); });
    }

    async function preencherCep(cep) {
        setZipFeedback('Buscando endereço...', 'info');
        try {
            const r = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const d = await r.json();
            if (d.erro) { setZipFeedback('CEP não encontrado.', 'warning'); return; }
            $cidade.val(d.localidade || '');
            $endereco.val([d.logradouro, d.bairro].filter(Boolean).join(' - '));
            selecionarBrasil();
            setZipFeedback('Endereço preenchido. Confira o número.', 'success');
        } catch (e) {
            setZipFeedback('Não foi possível consultar o CEP.', 'warning');
        }
    }

    $zipCode.on('blur', function() {
        const nums = ($(this).val() || '').replace(/\D/g,'');
        if (nums.length === 8) preencherCep(nums);
        else if (nums.length) setZipFeedback('Preenchimento automático disponível para CEP de 8 dígitos.', 'info');
    });
    $zipCode.on('input', function() {
        const nums = ($(this).val() || '').replace(/\D/g,'');
        if (nums.length === 8) preencherCep(nums);
    });

    // ── Toggle 24h ──
    function applyIntervaloAlmoco($card, semAlmoco) {
        const $lunchInputs = $card.find('input[name="hora_inicio_almoco[]"], input[name="hora_fim_almoco[]"]');

        $card.find('.ec-no-lunch-banner').toggleClass('d-none', !semAlmoco);
        $card.find('.ec-time-group--almoco').toggleClass('d-none', semAlmoco);
        $card.find('.input-almoco-hidden').val(semAlmoco ? 'N' : 'S');
        $lunchInputs.prop('readonly', semAlmoco);

        $lunchInputs.each(function() {
            const $input = $(this);
            if ($input.data('almocoAnterior') === undefined) {
                $input.data('almocoAnterior', $input.val() || '');
            }
        });

        if (semAlmoco) {
            $card.find('input[name="hora_inicio_almoco[]"]').val('00:00');
            $card.find('input[name="hora_fim_almoco[]"]').val('00:00');
        } else {
            $card.find('input[name="hora_inicio_almoco[]"]').each(function() {
                const valorAnterior = $(this).data('almocoAnterior');
                if (valorAnterior !== undefined) {
                    $(this).val(valorAnterior);
                }
            });
            $card.find('input[name="hora_fim_almoco[]"]').each(function() {
                const valorAnterior = $(this).data('almocoAnterior');
                if (valorAnterior !== undefined) {
                    $(this).val(valorAnterior);
                }
            });
        }
    }

    function apply24h($card, is24h) {
        $card.toggleClass('is-24h', is24h);
        $card.find('.ec-24h-banner').toggleClass('d-none', !is24h);
        $card.find('.ec-horario-fields').toggleClass('d-none', is24h);
        $card.find('.ec-hidden-24h-fields').toggleClass('d-none', !is24h);
        $card.find('.ec-horario-fields input[type="time"]').prop('disabled', is24h);
        $card.find('.ec-hidden-24h-fields input').prop('disabled', !is24h);
        $card.find('.input-24h-hidden').val(is24h ? 'S' : 'N');
        applyIntervaloAlmoco($card, $card.find('.input-almoco-check').is(':checked'));
    }

    // Init state on load
    $('.ec-horario-card').each(function() {
        const $card = $(this);
        const checked = $card.find('.input-24h-check').is(':checked');
        apply24h($card, checked);
    });

    $(document).on('change', '.input-24h-check, .input-almoco-check', function() {
        const $card = $(this).closest('.ec-horario-card');
        if ($(this).hasClass('input-24h-check') && $(this).is(':checked')) {
            $card.find('.input-almoco-check').prop('checked', false);
        }
        if ($(this).hasClass('input-almoco-check') && $(this).is(':checked')) {
            $card.find('.input-24h-check').prop('checked', false);
        }
        apply24h($card, $card.find('.input-24h-check').is(':checked'));
        autoFillFora($card);
    });

    // Toggle "Funciona em feriados"
    $(document).on('change', '.input-funciona-feriado-check', function() {
        const $card  = $(this).closest('.ec-horario-card');
        const ativo  = $(this).is(':checked');
        $card.find('.input-funciona-feriado-hidden').val(ativo ? 'S' : 'N');
        $card.toggleClass('ec-horario-card--inativo', !ativo);
        $card.find('.js-horario-campos-feriado').toggleClass('d-none', !ativo);
        $card.find('.ec-horario-card__body').toggle(ativo);
    });

    // ── Horário auto-fill (fora de expediente) ──
    function autoFillFora($card) {
        const abertura   = $card.find('input[name="hora_abertura[]"]').val()   || '';
        const fechamento = $card.find('input[name="hora_fechamento[]"]').val() || '';
        if ($card.find('.input-24h-check').is(':checked') || $card.find('.input-almoco-check').is(':checked')) {
            return;
        }

        if (abertura && fechamento) {
            $card.find('input[name="hora_inicio_fora_expediente[]"]').val(fechamento);
            $card.find('input[name="hora_fim_fora_expediente[]"]').val(abertura);
        }
    }

    $('.ec-horario-card').each(function() { autoFillFora($(this)); });

    $(document).on('change', 'input[name="hora_abertura[]"], input[name="hora_fechamento[]"]', function() {
        autoFillFora($(this).closest('.ec-horario-card'));
    });

    $(document).on('change', 'input[name="hora_inicio_almoco[]"], input[name="hora_fim_almoco[]"]', function() {
        const $card = $(this).closest('.ec-horario-card');
        if (!$card.find('.input-almoco-check').is(':checked')) {
            $(this).data('almocoAnterior', $(this).val() || '');
        }
    });

    // ── Mostrar/ocultar campos de valor de taxa conforme seleção ──
    $('#cobrar_na_coleta').on('change', function() {
        $('.js-taxa-coleta-wrap').toggleClass('d-none', $(this).val() !== 'S');
    });
    $('#cobrar_na_entrega').on('change', function() {
        $('.js-taxa-entrega-wrap').toggleClass('d-none', $(this).val() !== 'S');
    });

    // ── Form submit validation ──
    $('#formulario').on('submit', function(e) {
        let ok = true;

        // Remove readonly from visible fora-expediente inputs so values are sent
        $('.ec-horario-fields input[name="hora_inicio_fora_expediente[]"], .ec-horario-fields input[name="hora_fim_fora_expediente[]"]').prop('readonly', false);

        $('.ec-horario-card').each(function() {
            const $card = $(this);
            // Skip validation for 24h cards (they use hidden fields with preset values)
            if ($card.hasClass('is-24h')) return;
            const semAlmoco = $card.find('.input-almoco-check').is(':checked');

            $card.find('.ec-horario-fields input[type="time"]').each(function() {
                if (semAlmoco && $(this).hasClass('input-almoco')) {
                    $(this).removeClass('is-invalid');
                    return;
                }
                const v = ($(this).val() || '').trim();
                if (!v || v === '00:00') { $(this).addClass('is-invalid'); ok = false; }
                else $(this).removeClass('is-invalid');
            });

            if (semAlmoco) {
                $card.find('input[name="hora_inicio_almoco[]"], input[name="hora_fim_almoco[]"]').removeClass('is-invalid');
            }
        });

        if (!ok) {
            e.preventDefault();
            $('#error-horarios').removeClass('d-none');
            goToStep(4);
        } else {
            $('#error-horarios').addClass('d-none');
        }
    });
});

    // ── Tour guiado do formulário de estação ──
    (function () {
        const TOUR_ID  = 'estacao-form';
        const SKIP_KEY = 'page_tour_done__' + TOUR_ID;
        const forceTour = new URLSearchParams(window.location.search).get('tour') === '1';
        const jaViu     = localStorage.getItem(SKIP_KEY);

        if (jaViu && !forceTour) return;
        if (typeof window.driver === 'undefined' || !window.driver.js) return;

        const driverFn = window.driver.js.driver;
        const tour = driverFn({
            animate: true, smoothScroll: true, showProgress: true,
            progressText: 'Passo @{{current}} de @{{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { localStorage.setItem(SKIP_KEY, '1'); },
            steps: [
                {
                    popover: {
                        title: '🏪 Cadastro de Estação',
                        description: 'Uma <strong>estação</strong> é o ponto físico onde seus clientes retiram e devolvem os veículos. Pode ser uma filial, agência ou qualquer ponto de atendimento. Vamos preencher juntos!',
                        side: 'over', align: 'center',
                    },
                },
                {
                    element: '[data-step="1"]',
                    popover: {
                        title: '1️⃣ Informações Gerais',
                        description: 'Comece pelo <strong>nome da estação</strong> (ex: "Filial Centro", "Aeroporto Recife") e o status. Ativo = aparece disponível para reservas.',
                        side: 'bottom', align: 'start',
                    },
                    onHighlightStarted: () => goToStep(1),
                },
                {
                    element: '#nome',
                    popover: {
                        title: '✏️ Nome da Estação',
                        description: 'Use um nome claro e fácil de identificar. Seus clientes verão esse nome ao fazer reservas online.',
                        side: 'bottom',
                    },
                    onHighlightStarted: () => goToStep(1),
                },
                {
                    element: '[data-step="2"]',
                    popover: {
                        title: '2️⃣ Contato',
                        description: 'Preencha o <strong>e-mail</strong> e o <strong>WhatsApp</strong> da estação. Esses dados aparecem para os clientes confirmarem reservas e tirarem dúvidas.',
                        side: 'bottom', align: 'start',
                    },
                    onHighlightStarted: () => goToStep(2),
                },
                {
                    element: '[data-step="3"]',
                    popover: {
                        title: '3️⃣ Localização',
                        description: 'Informe o <strong>CEP</strong> para preencher o endereço automaticamente. A <strong>latitude e longitude</strong> são usadas para exibir a estação no mapa e calcular rotas.',
                        side: 'bottom', align: 'start',
                    },
                    onHighlightStarted: () => goToStep(3),
                },
                {
                    element: '#zip_code',
                    popover: {
                        title: '🔍 CEP automático',
                        description: 'Digite o CEP e o sistema preenche o endereço automaticamente. Depois ajuste a latitude e longitude se necessário.',
                        side: 'right',
                    },
                    onHighlightStarted: () => goToStep(3),
                },
                {
                    element: '[data-step="4"]',
                    popover: {
                        title: '4️⃣ Horários de Funcionamento',
                        description: 'Configure os horários de <strong>abertura, fechamento e intervalo de almoço</strong> para dias de semana, sábados, domingos e feriados. Isso controla a disponibilidade das reservas.',
                        side: 'bottom', align: 'start',
                    },
                    onHighlightStarted: () => goToStep(4),
                },
                {
                    element: '.ec-horarios-grid',
                    popover: {
                        title: '🕐 Cards de Horário',
                        description: 'Cada card representa um tipo de dia. Use <strong>"24h"</strong> para quem funciona o dia inteiro ou <strong>"Sem almoço"</strong> para quem não tem intervalo. Os campos "Fora de Expediente" são preenchidos automaticamente.',
                        side: 'top',
                    },
                    onHighlightStarted: () => goToStep(4),
                },
                {
                    element: '[data-step="5"]',
                    popover: {
                        title: '5️⃣ Operação',
                        description: 'Configure as <strong>taxas de coleta e entrega</strong> (retirada/devolução em local diferente) e outros parâmetros operacionais da estação.',
                        side: 'bottom', align: 'start',
                    },
                    onHighlightStarted: () => goToStep(5),
                },
                {
                    element: '#wizardSteps',
                    popover: {
                        title: '✅ Tudo pronto!',
                        description: 'Navegue pelas abas, preencha todos os campos e clique em <strong>Salvar</strong>. Após salvar você já pode cadastrar seus veículos!',
                        side: 'bottom', align: 'center',
                    },
                    onHighlightStarted: () => goToStep(1),
                },
            ],
        });

        setTimeout(() => tour.drive(), 400);
    })();
</script>

@stop
