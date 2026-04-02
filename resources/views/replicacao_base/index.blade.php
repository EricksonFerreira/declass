@extends('adminlte::page')

@section('title', $titulo)
@section('plugins.Select2', true)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <strong>Replicar dados de uma empresa base</strong>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('replicacao_base.store') }}" id="formReplicacaoBase">
                @csrf

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="empresa_origem_id">Empresa base <span class="text-danger">*</span></label>
                            <select name="empresa_origem_id" id="empresa_origem_id" class="form-control select2 @error('empresa_origem_id') is-invalid @enderror" required>
                                <option value="">Selecione</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ $oldEmpresaOrigem === (int) $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('empresa_origem_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="mb-1 d-block">Empresas de destino <span class="text-danger">*</span></label>
                            <div class="replicacao-empresas-wrap @error('empresas_destino') border-danger @enderror">
                                @foreach($empresas as $empresa)
                                    <label class="replicacao-empresa-item">
                                        <input
                                            type="checkbox"
                                            class="empresa-destino-checkbox"
                                            name="empresas_destino[]"
                                            value="{{ $empresa->id }}"
                                            {{ in_array((int) $empresa->id, $oldDestinoIds, true) ? 'checked' : '' }}
                                        >
                                        <span>{{ $empresa->nome }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <small class="text-muted">Selecione uma ou mais empresas de destino.</small>
                            <div id="empresas_destino_feedback" class="text-danger mt-1 d-none">
                                Selecione ao menos uma empresa de destino.
                            </div>
                            @error('empresas_destino')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('empresas_destino.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    @php
                        $modulosPorArea = collect($modulos)
                            ->map(function ($modulo, $chave) {
                                $modulo['chave'] = $chave;
                                return $modulo;
                            })
                            ->groupBy(fn ($modulo) => $modulo['area'] ?? 'Geral');
                    @endphp
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <label class="mb-1">Modulos para replicacao <span class="text-danger">*</span></label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-xs btn-outline-primary mr-1" id="btnMarcarTodosModulos" onclick="return replicacaoMarcarTodos(event);">
                                Marcar todos
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary" id="btnLimparTodosModulos" onclick="return replicacaoLimparTodos(event);">
                                Limpar todos
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        @forelse($modulosPorArea as $nomeArea => $itensArea)
                            @php
                                $areaSlug = \Illuminate\Support\Str::slug($nomeArea, '-');
                                $areaCollapseId = 'area_modulos_' . $loop->index;
                            @endphp
                            <div class="col-lg-6">
                                <div class="replicacao-area-card">
                                    <div class="replicacao-area-card__header">
                                        <button
                                            type="button"
                                            class="btn btn-link p-0 text-left replicacao-area-card__toggle {{ $loop->first ? '' : 'collapsed' }}"
                                            data-toggle="collapse"
                                            data-target="#{{ $areaCollapseId }}"
                                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                            aria-controls="{{ $areaCollapseId }}"
                                        >
                                            <i class="fas fa-chevron-down replicacao-toggle-icon mr-1"></i>
                                            <strong>{{ $nomeArea }}</strong>
                                            <span class="badge badge-light ml-2">{{ $itensArea->count() }} modulos</span>
                                        </button>
                                        <div class="replicacao-area-card__actions">
                                            <button type="button" class="btn btn-xs btn-outline-primary btn-marcar-area" data-area="{{ $areaSlug }}" onclick="return replicacaoMarcarArea('{{ $areaSlug }}', event);">
                                                Marcar area
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary btn-desmarcar-area" data-area="{{ $areaSlug }}" onclick="return replicacaoLimparArea('{{ $areaSlug }}', event);">
                                                Limpar area
                                            </button>
                                        </div>
                                    </div>
                                    <div id="{{ $areaCollapseId }}" class="collapse {{ $loop->first ? 'show' : '' }}">
                                        <div class="replicacao-area-card__body">
                                            @foreach($itensArea as $modulo)
                                                @php
                                                    $moduloChave = (string) ($modulo['chave'] ?? '');
                                                    $moduloId = 'modulo_' . $areaSlug . '_' . $moduloChave;
                                                @endphp
                                                <div class="custom-control custom-checkbox mb-2">
                                                    <input
                                                        type="checkbox"
                                                        class="custom-control-input modulo-checkbox modulo-area-{{ $areaSlug }}"
                                                        id="{{ $moduloId }}"
                                                        name="modulos[]"
                                                        value="{{ $moduloChave }}"
                                                        data-area="{{ $areaSlug }}"
                                                        {{ in_array($moduloChave, $oldModulos, true) ? 'checked' : '' }}
                                                    >
                                                    <label class="custom-control-label" for="{{ $moduloId }}">
                                                        {{ $modulo['label'] }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">Nenhum modulo disponivel para replicacao.</div>
                            </div>
                        @endforelse
                    </div>
                    @error('modulos')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    @error('modulos.*')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    @error('replicacao')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-copy mr-1"></i> Executar replicacao
                </button>
            </form>
        </div>
    </div>

    @if(!empty($resultado))
        <div class="card">
            <div class="card-header">
                <strong>Resultado da replicacao</strong>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Area</th>
                            <th>Modulo</th>
                            <th>Tabela</th>
                            <th class="text-center">Inseridos</th>
                            <th class="text-center">Ignorados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultado as $linha)
                            <tr>
                                <td>{{ $linha['area'] ?? '-' }}</td>
                                <td>{{ $linha['modulo'] }}</td>
                                <td><code>{{ $linha['tabela'] }}</code></td>
                                <td class="text-center">{{ $linha['inseridos'] }}</td>
                                <td class="text-center">{{ $linha['ignorados'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@stop

@section('js')
<script>
    (function () {
        window.replicacaoMarcarTodos = function (event) {
            if (event) {
                event.preventDefault();
            }

            var checkboxes = document.querySelectorAll('.modulo-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }
            return false;
        };

        window.replicacaoLimparTodos = function (event) {
            if (event) {
                event.preventDefault();
            }

            var checkboxes = document.querySelectorAll('.modulo-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = false;
            }
            return false;
        };

        window.replicacaoMarcarArea = function (areaSlug, event) {
            if (event) {
                event.preventDefault();
            }

            var checkboxes = document.querySelectorAll('.modulo-area-' + areaSlug);
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }
            return false;
        };

        window.replicacaoLimparArea = function (areaSlug, event) {
            if (event) {
                event.preventDefault();
            }

            var checkboxes = document.querySelectorAll('.modulo-area-' + areaSlug);
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = false;
            }
            return false;
        };

        function setCheckboxesChecked(nodeList, checked) {
            if (!nodeList || !nodeList.length) {
                return;
            }

            for (var i = 0; i < nodeList.length; i++) {
                nodeList[i].checked = checked;
            }
        }

        function hideDestinoFeedbackIfNeeded() {
            var selecionados = document.querySelectorAll('.empresa-destino-checkbox:checked');
            var feedback = document.getElementById('empresas_destino_feedback');
            if (!feedback) {
                return;
            }

            if (selecionados.length > 0) {
                feedback.classList.add('d-none');
            }
        }

        function inicializarTelaReplicacao() {
            if (window.__replicacaoBaseInitDone) {
                return;
            }
            window.__replicacaoBaseInitDone = true;

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery('#empresa_origem_id').select2({
                    placeholder: 'Selecione',
                    allowClear: true,
                    width: '100%',
                    dropdownCssClass: 'replicacao-select2-dropdown'
                });
            }

            var btnMarcarTodos = document.getElementById('btnMarcarTodosModulos');
            var btnLimparTodos = document.getElementById('btnLimparTodosModulos');
            var formReplicacao = document.getElementById('formReplicacaoBase');

            if (btnMarcarTodos) {
                btnMarcarTodos.addEventListener('click', function (event) {
                    event.preventDefault();
                    window.replicacaoMarcarTodos(event);
                });
            }

            if (btnLimparTodos) {
                btnLimparTodos.addEventListener('click', function (event) {
                    event.preventDefault();
                    window.replicacaoLimparTodos(event);
                });
            }

            document.addEventListener('click', function (event) {
                var marcarAreaBtn = event.target.closest('.btn-marcar-area');
                if (marcarAreaBtn) {
                    event.preventDefault();
                    var areaMarcar = marcarAreaBtn.getAttribute('data-area');
                    if (areaMarcar) {
                        window.replicacaoMarcarArea(areaMarcar, event);
                    }
                    return;
                }

                var desmarcarAreaBtn = event.target.closest('.btn-desmarcar-area');
                if (desmarcarAreaBtn) {
                    event.preventDefault();
                    var areaLimpar = desmarcarAreaBtn.getAttribute('data-area');
                    if (areaLimpar) {
                        window.replicacaoLimparArea(areaLimpar, event);
                    }
                }
            });

            if (formReplicacao) {
                formReplicacao.addEventListener('submit', function (event) {
                    var possuiDestino = document.querySelectorAll('.empresa-destino-checkbox:checked').length > 0;
                    var feedback = document.getElementById('empresas_destino_feedback');

                    if (!possuiDestino) {
                        event.preventDefault();
                        if (feedback) {
                            feedback.classList.remove('d-none');
                        }
                    }
                });
            }

            var checkboxesDestino = document.querySelectorAll('.empresa-destino-checkbox');
            for (var i = 0; i < checkboxesDestino.length; i++) {
                checkboxesDestino[i].addEventListener('change', hideDestinoFeedbackIfNeeded);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', inicializarTelaReplicacao);
        } else {
            inicializarTelaReplicacao();
        }
    })();
</script>
@stop

@section('css')
<style>
    .replicacao-empresas-wrap {
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        padding: 10px;
        background: #f8fbff;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 8px;
        max-height: 220px;
        overflow: auto;
    }

    .replicacao-empresa-item {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #d7e3f1;
        border-radius: 10px;
        padding: 8px 10px;
        background: #fff;
        margin: 0;
        cursor: pointer;
    }

    .replicacao-empresa-item input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #2563eb;
    }

    .replicacao-empresa-item span {
        font-size: .88rem;
        font-weight: 600;
        color: #1e293b;
    }

    .replicacao-area-card {
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        padding: 10px 12px;
        margin-bottom: 12px;
        background: #f9fbff;
    }

    .replicacao-area-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 8px;
        margin-bottom: 8px;
    }

    .replicacao-area-card__toggle {
        color: #1e293b;
        text-decoration: none !important;
    }

    .replicacao-area-card__toggle:hover {
        color: #0f2740;
    }

    .replicacao-toggle-icon {
        transition: transform .2s ease;
    }

    .replicacao-area-card__toggle.collapsed .replicacao-toggle-icon {
        transform: rotate(-90deg);
    }

    .replicacao-area-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .replicacao-area-card__body {
        max-height: 280px;
        overflow: auto;
    }

    #empresa_origem_id + .select2 .select2-selection__rendered {
        background-color: #000 !important;
        color: #fff !important;
        border-color: #000 !important;
    }

    .replicacao-select2-dropdown .select2-results__option--highlighted[aria-selected],
    .replicacao-select2-dropdown .select2-results__option[aria-selected=true] {
        background-color: #000 !important;
        color: #fff !important;
    }
</style>
@stop
