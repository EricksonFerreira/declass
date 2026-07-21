@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $startStep = old('step', isset($plano) ? 1 : 1);
        $startStep = in_array((int) $startStep, [1, 2, 3], true) ? (int) $startStep : 1;
        $selecionados = old('opcionais', isset($plano) ? $plano->opcionais->pluck('id')->map(fn ($id) => (string) $id)->all() : []);
        $selecionados = is_array($selecionados) ? $selecionados : [];
    @endphp

    <div class="col-xl-11 col-12 px-0">
        <div class="card card-veiculo shadow-sm border-0">
            <div class="card-header card-header-veiculo border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure o plano especial para motorista de app com o mesmo padrão visual do veículo.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="vc-wizard-steps mb-4" id="wizardSteps">
                    @php
                        $steps = [
                            1 => ['icon' => 'fa-info-circle', 'label' => 'Geral'],
                            2 => ['icon' => 'fa-road', 'label' => 'Km e Caução'],
                            3 => ['icon' => 'fa-puzzle-piece', 'label' => 'Opcionais'],
                        ];
                    @endphp
                    @foreach($steps as $n => $s)
                        <button type="button" class="vc-step-btn {{ $startStep === $n ? 'active' : '' }}" data-step="{{ $n }}">
                            <span class="vc-step-circle"><i class="fas {{ $s['icon'] }}"></i></span>
                            <span class="vc-step-label">{{ $s['label'] }}</span>
                        </button>
                        @if(!$loop->last)
                            <div class="vc-step-line"></div>
                        @endif
                    @endforeach
                </div>

                <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row" enctype="multipart/form-data">
                    <div class="vc-step-pane {{ $startStep === 1 ? '' : 'd-none' }}" data-pane="1">
                        <div class="veiculo-section-title"><i class="fas fa-info-circle mr-1"></i>Informações Básicas</div>
                        <div class="form-row">
                            <div class="col-lg-6 col-md-12">
                                <div class="form-group form-group-veiculo">
                                    <label for="nome">Nome</label>
                                    <input type="text" class="form-control form-control-sm" id="nome" name="nome" value="{{ old('nome', $plano->nome ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <label for="valor_periodo">Valor por período</label>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="valor_periodo" name="valor_periodo" value="{{ old('valor_periodo', $plano->valor_periodo ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <label for="periodo">Período</label>
                                    <select class="form-control form-control-sm" id="periodo" name="periodo" required>
                                        <option value="">Selecione...</option>
                                        @foreach($periodos as $valor => $rotulo)
                                            <option value="{{ $valor }}" {{ old('periodo', $plano->periodo ?? '') === $valor ? 'selected' : '' }}>{{ $rotulo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <label for="ativo">Status</label>
                                    <select class="form-control form-control-sm" id="ativo" name="ativo" required>
                                        <option value="S" {{ old('ativo', $plano->ativo ?? 'S') === 'S' ? 'selected' : '' }}>Ativo</option>
                                        <option value="N" {{ old('ativo', $plano->ativo ?? 'S') === 'N' ? 'selected' : '' }}>Inativo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <label for="ordem">Ordem</label>
                                    <input type="number" min="0" class="form-control form-control-sm" id="ordem" name="ordem" value="{{ old('ordem', $plano->ordem ?? 0) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vc-step-pane d-none" data-pane="2">
                        <div class="veiculo-section-title"><i class="fas fa-road mr-1"></i>Km e Caução</div>
                        <div class="form-row">
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <label for="km_tipo">Km</label>
                                    <select class="form-control form-control-sm" id="km_tipo" name="km_tipo" required>
                                        <option value="livre" {{ old('km_tipo', $plano->km_tipo ?? 'livre') === 'livre' ? 'selected' : '' }}>Livre</option>
                                        <option value="limitado" {{ old('km_tipo', $plano->km_tipo ?? 'livre') === 'limitado' ? 'selected' : '' }}>Limitado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <label for="km_por_periodo">Km por período</label>
                                    <input type="number" min="1" class="form-control form-control-sm" id="km_por_periodo" name="km_por_periodo" value="{{ old('km_por_periodo', $plano->km_por_periodo ?? '') }}" placeholder="Ex: 300">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group form-group-veiculo">
                                    <label for="calcao">Calção</label>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="calcao" name="calcao" value="{{ old('calcao', $plano->calcao ?? '') }}" placeholder="0,00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vc-step-pane d-none" data-pane="3">
                        <div class="veiculo-section-title"><i class="fas fa-puzzle-piece mr-1"></i>Opcionais ativos</div>
                        <p class="text-muted small mb-3">Selecione os opcionais ativos que devem aparecer no plano de motorista de app. Os opcionais já vinculados, mesmo inativos, continuam visíveis em cinza.</p>
                        <div class="form-group form-group-veiculo mb-3">
                            <label for="filter_opcionais">Filtrar opcionais</label>
                            <input type="text" class="form-control form-control-sm" id="filter_opcionais" placeholder="Digite o nome ou tipo do opcional">
                        </div>
                        <div class="row">
                            @forelse($opcionais as $opcional)
                                @php
                                    $ativo = ($opcional->ativo ?? 'N') === 'S';
                                    $isSelected = in_array((string) $opcional->id, $selecionados, true);
                                    $tipoCobranca = $opcional->tipo_cobranca === 'integral' ? 'Integral' : 'Por dia';
                                    $valorCobrado = $opcional->tipo_cobranca === 'integral'
                                        ? 'R$ ' . number_format((float) ($opcional->preco_integral ?? 0), 2, ',', '.')
                                        : 'R$ ' . number_format((float) ($opcional->preco_por_dia ?? 0), 2, ',', '.') . '/dia';
                                    $cardClass = ($ativo ? 'pm-option-card--active' : 'pm-option-card--inactive') . ($isSelected ? ' pm-option-card--selected' : '');
                                @endphp
                                <div class="col-lg-4 col-md-6 mb-2 pm-option-item">
                                    <label class="pm-option-card {{ $cardClass }}">
                                        <input class="pm-option-card__check" type="checkbox" name="opcionais[]" id="opcional_{{ $opcional->id }}" value="{{ $opcional->id }}" {{ $isSelected ? 'checked' : '' }}>
                                        <div class="pm-option-card__head">
                                            <div class="pm-option-card__title">{{ $opcional->nome }}</div>
                                            <span class="pm-option-card__status {{ $ativo ? 'is-active' : 'is-inactive' }}">
                                                {{ $ativo ? 'Ativo' : 'Inativo' }}
                                            </span>
                                        </div>
                                        <div class="pm-option-card__meta pm-option-card__meta--inline">
                                            <span><i class="fas fa-tag mr-1"></i>{{ $opcional->tipoOpcional?->nome ?? 'Sem tipo' }}</span>
                                            <span><i class="fas fa-money-bill-wave mr-1"></i>{{ $tipoCobranca }}</span>
                                            <span><i class="fas fa-dollar-sign mr-1"></i>{{ $valorCobrado }}</span>
                                        </div>
                                    </label>
                                </div>
                            @empty
                                <div class="col-12 text-muted">Nenhum vínculo de opcional encontrado.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-secondary btn-sm vc-nav-btn vc-nav-btn--back d-none" id="btnPrev">
                            <i class="fas fa-chevron-left mr-1"></i> Anterior
                        </button>
                        <div class="d-flex align-items-center" style="gap:8px;">
                            <button type="button" class="btn btn-primary btn-sm vc-nav-btn vc-nav-btn--next" id="btnNext">
                                Próximo <i class="fas fa-chevron-right ml-1"></i>
                            </button>
                            <button type="submit" class="btn btn-success btn-sm vc-nav-btn vc-nav-btn--save d-none" id="btnSalvar">
                                <i class="fas fa-save mr-1"></i> Salvar alterações
                            </button>
                        </div>
                    </div>
                </x-form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-veiculo {
            border-radius: 14px;
            overflow: hidden;
        }

        .card-header-veiculo {
            background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
            padding: 1.1rem 1.5rem;
        }

        .card-header-veiculo h4 {
            color: #0f2740;
            font-weight: 700;
        }

        .vc-wizard-steps {
            display: flex;
            align-items: center;
            gap: 0;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .vc-step-btn {
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

        .vc-step-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2px solid #d0daea;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            color: #94a3b8;
            transition: all .2s;
            flex-shrink: 0;
        }

        .vc-step-btn.active .vc-step-circle {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .vc-step-label {
            font-size: .72rem;
            font-weight: 600;
            color: #94a3b8;
            white-space: nowrap;
        }

        .vc-step-btn.active .vc-step-label {
            color: #2563eb;
        }

        .vc-step-line {
            flex: 1;
            height: 2px;
            background: #e5eaf2;
            min-width: 16px;
            margin-bottom: 18px;
        }

        .veiculo-section-title {
            font-size: .96rem;
            font-weight: 700;
            color: #21364f;
            letter-spacing: .02em;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5eaf2;
            margin-bottom: 14px;
        }

        .form-group-veiculo {
            margin-bottom: 1rem;
        }

        .pm-option-card {
            display: block;
            border: 1px solid #dbe6f3;
            border-radius: 14px;
            background: #f8fbff;
            padding: 12px 12px 10px;
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease;
            height: 100%;
        }

        .pm-option-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .07);
        }

        .pm-option-card__check {
            margin-bottom: 8px;
            transform: translateY(1px);
        }

        .pm-option-card__head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }

        .pm-option-card__title {
            font-size: .9rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
        }

        .pm-option-card__status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: .64rem;
            font-weight: 800;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .pm-option-card__status.is-active {
            background: rgba(16, 185, 129, .14);
            color: #047857;
        }

        .pm-option-card__status.is-inactive {
            background: rgba(100, 116, 139, .14);
            color: #475569;
        }

        .pm-option-card__meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            color: #475569;
            font-size: .77rem;
            line-height: 1.35;
        }

        .pm-option-card__meta--inline {
            flex-direction: row;
            flex-wrap: wrap;
            gap: 8px 14px;
            align-items: center;
        }

        .pm-option-card__meta--inline span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .pm-option-card--selected {
            border-color: #3b82f6;
            background: #eff6ff;
            box-shadow: 0 10px 24px rgba(59, 130, 246, .12);
        }

        .pm-option-card--active .pm-option-card__status {
            border: 1px solid rgba(16, 185, 129, .16);
        }

        .pm-option-card--inactive {
            background: #eef2f7;
            border-color: #cbd5e1;
            color: #475569;
            opacity: .9;
        }

        .pm-option-card--inactive .pm-option-card__title,
        .pm-option-card--inactive .pm-option-card__meta,
        .pm-option-card--inactive .pm-option-card__desc {
            color: #64748b;
        }

        .pm-option-card--inactive.pm-option-card--selected {
            border-color: #94a3b8;
            background: #e2e8f0;
            box-shadow: 0 10px 24px rgba(100, 116, 139, .12);
        }

        .card-veiculo .form-control,
        .card-veiculo .form-select {
            border-radius: 10px;
            border-color: #d8e1ec;
            min-height: 38px;
        }

        .card-veiculo .form-control:focus,
        .card-veiculo .form-select:focus {
            border-color: #4b80ff;
            box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
        }

        .vc-nav-btn {
            min-width: 138px;
            border-radius: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
        }

        .vc-nav-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(15, 23, 42, .12);
        }

        .vc-nav-btn--back {
            min-width: 132px;
        }

        .vc-nav-btn--next {
            min-width: 120px;
        }

        .vc-nav-btn--save {
            min-width: 160px;
        }
    </style>
@stop

@section('js')
    <script>
        (function () {
            const $stepBtns = $('.vc-step-btn');
            const $stepPanes = $('.vc-step-pane');
            const $btnPrev = $('#btnPrev');
            const $btnNext = $('#btnNext');
            const $btnSalvar = $('#btnSalvar');
            const $kmTipo = $('#km_tipo');
            const $kmPorPeriodo = $('#km_por_periodo');
            const $opcionaisCards = $('.pm-option-card');
            const $filterOpcionais = $('#filter_opcionais');
            const $opcionaisItems = $('.pm-option-item');
            let currentStep = {{ (int) $startStep }};

            function renderStep() {
                $stepBtns.removeClass('active done');
                $stepBtns.filter('[data-step="' + currentStep + '"]').addClass('active');
                $stepPanes.addClass('d-none');
                $stepPanes.filter('[data-pane="' + currentStep + '"]').removeClass('d-none');

                $btnPrev.toggleClass('d-none', currentStep === 1);
                $btnNext.toggleClass('d-none', currentStep === 3);
                $btnSalvar.toggleClass('d-none', currentStep !== 3);
                $('html, body').animate({ scrollTop: $('.card-veiculo').offset().top - 20 }, 150);
            }

            function syncKmField() {
                if (!$kmTipo.length || !$kmPorPeriodo.length) return;
                const limitado = $kmTipo.val() === 'limitado';
                $kmPorPeriodo.prop('required', limitado);
                $kmPorPeriodo.prop('disabled', !limitado);
            }

            function syncOpcionalCards() {
                $opcionaisCards.each(function () {
                    const $card = $(this);
                    const checked = $card.find('.pm-option-card__check').is(':checked');
                    $card.toggleClass('pm-option-card--selected', checked);
                });
            }

            function filterOpcionais() {
                const term = String($filterOpcionais.val() || '').trim().toLowerCase();

                if (!term) {
                    $opcionaisItems.removeClass('d-none');
                    return;
                }

                $opcionaisItems.each(function () {
                    const $item = $(this);
                    const text = $item.text().toLowerCase();
                    $item.toggleClass('d-none', !text.includes(term));
                });
            }

            $stepBtns.on('click', function () {
                currentStep = Number($(this).data('step')) || 1;
                renderStep();
            });

            $btnPrev.on('click', function () {
                currentStep = Math.max(1, currentStep - 1);
                renderStep();
            });

            $btnNext.on('click', function () {
                currentStep = Math.min(3, currentStep + 1);
                renderStep();
            });

            $kmTipo.on('change', syncKmField);
            $opcionaisCards.find('.pm-option-card__check').on('change', syncOpcionalCards);
            $filterOpcionais.on('input', filterOpcionais);

            syncKmField();
            syncOpcionalCards();
            filterOpcionais();
            renderStep();
        })();
    </script>
@stop
