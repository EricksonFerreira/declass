@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @php
        $isAllStations = (bool) old('acesso_todas_estacoes', $acessoTodasEstacoes ?? true);
        $selectedStations = array_map('strval', (array) old('estacoes', $estacoesSelecionadas ?? []));
        $isCreate = !isset($usuario);
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
        <div class="card card-usuario shadow-sm border-0">
            <div class="card-header card-header-usuario border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Dividimos o cadastro em etapas para facilitar o preenchimento.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" id="form" rowClass="row">
                    <div class="col-12">
                        <ul class="nav nav-pills nav-usuario mb-3" id="usuario-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-geral-link" data-toggle="pill" href="#tab-geral" role="tab" aria-controls="tab-geral" aria-selected="true">
                                    <i class="fa fa-user mr-1"></i> Dados Pessoais
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-contato-link" data-toggle="pill" href="#tab-contato" role="tab" aria-controls="tab-contato" aria-selected="false">
                                    <i class="fa fa-phone mr-1"></i> Contato e Endereco
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-acesso-link" data-toggle="pill" href="#tab-acesso" role="tab" aria-controls="tab-acesso" aria-selected="false">
                                    <i class="fa fa-shield-alt mr-1"></i> Perfil e Acesso
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-carta-link" data-toggle="pill" href="#tab-carta" role="tab" aria-controls="tab-carta" aria-selected="false">
                                    <i class="fa fa-id-card mr-1"></i> Carta de Conducao
                                </a>
                            </li>
                            @if ($isCreate)
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-seguranca-link" data-toggle="pill" href="#tab-seguranca" role="tab" aria-controls="tab-seguranca" aria-selected="false">
                                        <i class="fa fa-lock mr-1"></i> Seguranca
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="col-12">
                        <div class="tab-content" id="usuario-tab-content">
                            <div class="tab-pane fade show active" id="tab-geral" role="tabpanel" aria-labelledby="tab-geral-link">
                                <div class="usuario-section-title">Informacoes Pessoais</div>
                                <div class="form-row">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input type="text" class="form-control-sm" id="nome" name="nome"
                                                label="Nome" value="{{ old('nome', $usuario->nome ?? '') }}" required />
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input type="email" class="form-control-sm" id="email" name="email"
                                                label="E-mail" value="{{ old('email', $usuario->email ?? '') }}" required />
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-select name="sexo" label="Sexo" :options="$sexo" placeholder="Selecione ..."
                                                :selected="old('sexo', isset($usuario->sexo) ? $usuario->sexo : null)"
                                                class="form-control form-control-sm" required />
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="date" class="form-control-sm" id="data_nascimento"
                                                name="data_nascimento" label="Data de Nascimento"
                                                value="{{ old('data_nascimento', $usuario->data_nascimento ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="local_nascimento"
                                                name="local_nascimento" label="Local de Nascimento"
                                                value="{{ old('local_nascimento', $usuario->local_nascimento ?? '') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-contato" role="tabpanel" aria-labelledby="tab-contato-link">
                                <div class="usuario-section-title">Contato e Endereco</div>
                                <div class="form-row">
                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="telefone" name="telefone"
                                                label="Telefone" value="{{ old('telefone', $usuario->telefone ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="celular" name="celular"
                                                placeholder="(99) 99999-9999" label="Celular"
                                                value="{{ old('celular', $usuario->celular ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group form-group-usuario">
                                            <x-form-select name="pais_sigla" label="Pais" :options="$paises" placeholder="Selecione um valor"
                                                :selected="old('pais_sigla', isset($usuario->pais_sigla) ? $usuario->pais_sigla : null)"
                                                id="pais_sigla" class="form-control form-control-sm" required />
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-4">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="cep" name="zip_code"
                                                label="Zip Code" value="{{ old('zip_code', $usuario->zip_code ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-8">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="cidade" name="cidade"
                                                label="Cidade" value="{{ old('cidade', $usuario->cidade ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-5 col-md-12">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="endereco" name="endereco"
                                                label="Endereco" value="{{ old('endereco', $usuario->endereco ?? '') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-acesso" role="tabpanel" aria-labelledby="tab-acesso-link">
                                <div class="usuario-section-title">Perfil e Acesso</div>
                                <div class="form-row">
                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="numero_identificacao"
                                                name="numero_identificacao" label="Numero de Identificacao"
                                                value="{{ old('numero_identificacao', $usuario->numero_identificacao ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-select name="role_id" label="Regra de Usuario" :options="$tiposUsuario"
                                                placeholder="Selecione um valor"
                                                :selected="old('role_id', isset($usuario->role_id) ? $usuario->role_id : null)"
                                                class="form-control form-control-sm" required />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group form-group-usuario mb-1">
                                            <label class="mb-2">Acesso por Estacao</label>
                                            <input type="hidden" name="acesso_todas_estacoes" value="0">

                                            <div class="custom-control custom-switch mb-2">
                                                <input type="checkbox" class="custom-control-input" id="acesso_todas_estacoes"
                                                    name="acesso_todas_estacoes" value="1" {{ $isAllStations ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="acesso_todas_estacoes">
                                                    Acesso a todas as estacoes
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12" id="estacoes-permitidas-wrap" style="{{ $isAllStations ? 'display:none;' : '' }}">
                                        <div class="estacoes-wrap">
                                            <label class="mb-1 d-block">Estacoes permitidas</label>
                                            <div class="estacoes-checkbox-grid">
                                                @forelse ($estacoes ?? [] as $estacaoId => $estacaoNome)
                                                    <label class="estacao-checkbox-item">
                                                        <input
                                                            type="checkbox"
                                                            name="estacoes[]"
                                                            value="{{ $estacaoId }}"
                                                            {{ in_array((string) $estacaoId, $selectedStations, true) ? 'checked' : '' }}
                                                        >
                                                        <span>{{ $estacaoNome }}</span>
                                                    </label>
                                                @empty
                                                    <div class="text-muted small">Nenhuma estacao disponivel.</div>
                                                @endforelse
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                Quando desmarcado, o usuario so acessa dados das estacoes selecionadas.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tab-carta" role="tabpanel" aria-labelledby="tab-carta-link">
                                <div class="usuario-section-title">Carta de Conducao</div>
                                <div class="form-row">
                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="numero_carta_conducao"
                                                name="numero_carta_conducao" label="Numero Carta de Conducao"
                                                value="{{ old('numero_carta_conducao', $usuario->numero_carta_conducao ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="text" class="form-control-sm" id="local_emissao_carta_conducao"
                                                name="local_emissao_carta_conducao" label="Local de Emissao"
                                                value="{{ old('local_emissao_carta_conducao', $usuario->local_emissao_carta_conducao ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group form-group-usuario">
                                            <x-form-select name="pais_emissao_carta_conducao" label="Pais de Emissao" :options="$paises"
                                                placeholder="Selecione um valor"
                                                :selected="old(
                                                    'pais_emissao_carta_conducao',
                                                    isset($usuario->pais_emissao_carta_conducao) ? $usuario->pais_emissao_carta_conducao : null,
                                                )"
                                                class="form-control form-control-sm" required />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input required type="date" class="form-control-sm" id="data_emissao_carta_conducao"
                                                name="data_emissao_carta_conducao" label="Data de Emissao"
                                                value="{{ old('data_emissao_carta_conducao', $usuario->data_emissao_carta_conducao ?? '') }}" />
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-6">
                                        <div class="form-group form-group-usuario">
                                            <x-form-input type="date" class="form-control-sm" id="data_validade_carta_conducao"
                                                name="data_validade_carta_conducao" label="Data de Validade"
                                                value="{{ old('data_validade_carta_conducao', $usuario->data_validade_carta_conducao ?? '') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($isCreate)
                                <div class="tab-pane fade" id="tab-seguranca" role="tabpanel" aria-labelledby="tab-seguranca-link">
                                    <div class="usuario-section-title">Informacoes Privadas</div>
                                    <div class="form-row">
                                        <div class="col-lg-5 col-md-6">
                                            <div class="form-group form-group-usuario">
                                                <x-form-input required type="password" class="form-control-sm" id="senha"
                                                    name="password" label="Senha" value="{{ old('password', '') }}" />
                                            </div>
                                        </div>

                                        <div class="col-lg-5 col-md-6">
                                            <div class="form-group form-group-usuario">
                                                <x-form-input required type="password" class="form-control-sm" id="confirmacao_senha"
                                                    name="confirmacao_senha" label="Confirmacao de Senha"
                                                    value="{{ old('confirmacao_senha', '') }}" />
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div id="passwordValidation" class="alert alert-danger d-none" role="alert">
                                                <ul class="mb-0 pl-3">
                                                    <li id="length" class="d-none">A senha deve ter pelo menos 8 caracteres.</li>
                                                    <li id="uppercase" class="d-none">A senha deve conter pelo menos uma letra maiuscula.</li>
                                                    <li id="lowercase" class="d-none">A senha deve conter pelo menos uma letra minuscula.</li>
                                                    <li id="number" class="d-none">A senha deve conter pelo menos um numero.</li>
                                                    <li id="special" class="d-none">A senha deve conter pelo menos um caractere especial.</li>
                                                    <li id="match" class="d-none">As senhas devem ser iguais.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-save-usuario col-12 mt-2">
                            Salvar
                        </button>
                    </div>
                </x-form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .auto-filled {
        background-color: #e0f7fa;
    }

    .card-usuario {
        border-radius: 14px;
        overflow: hidden;
    }

    .card-header-usuario {
        background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%);
        padding: 1.1rem 1.5rem;
    }

    .card-header-usuario h4 {
        color: #0f2740;
        font-weight: 700;
    }

    .nav-usuario .nav-link {
        border-radius: 8px;
        font-weight: 600;
        color: #334155;
        border: 1px solid #dbe3ef;
        margin-right: .45rem;
        margin-bottom: .45rem;
        background: #fff;
        font-size: .9rem;
    }

    .nav-usuario .nav-link.active {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
    }

    .usuario-section-title {
        font-size: .96rem;
        font-weight: 700;
        color: #21364f;
        letter-spacing: .02em;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5eaf2;
        margin-bottom: 10px;
    }

    .form-group-usuario {
        margin-bottom: 1rem;
    }

    .card-usuario .form-control,
    .card-usuario .form-select {
        border-radius: 10px;
        border-color: #d8e1ec;
        min-height: 38px;
    }

    .card-usuario .form-control:focus,
    .card-usuario .form-select:focus {
        border-color: #4b80ff;
        box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18);
    }

    .estacoes-wrap {
        border: 1px solid #dbe6f3;
        border-radius: 12px;
        padding: 12px;
        background: #f9fbfe;
        margin-bottom: 6px;
    }

    .estacoes-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 8px;
        max-height: 220px;
        overflow: auto;
        padding: 4px 2px;
    }

    .estacao-checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #d7e3f1;
        border-radius: 10px;
        padding: 8px 10px;
        background: #fff;
        cursor: pointer;
        margin: 0;
        transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
    }

    .estacao-checkbox-item:hover {
        border-color: #98b7de;
        background: #f7fbff;
    }

    .estacao-checkbox-item input[type="checkbox"] {
        accent-color: #2563eb;
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
    }

    .estacao-checkbox-item span {
        font-size: .86rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.2;
    }

    .btn-save-usuario {
        border-radius: 10px;
        font-weight: 600;
        padding-top: .58rem;
        padding-bottom: .58rem;
    }

    @media (max-width: 991.98px) {
        .card-header-usuario {
            padding: .95rem 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }
    }

    @media (max-width: 767.98px) {
        .col-xl-10.col-lg-11.col-md-12.col-sm-12.col-xs-12.px-0 {
            padding-left: .5rem !important;
            padding-right: .5rem !important;
        }

        .card-header-usuario h4 {
            font-size: 1.1rem;
            line-height: 1.35;
        }

        .card-header-usuario p {
            font-size: .85rem;
        }

        .usuario-section-title {
            font-size: .9rem;
        }
    }

    @media (max-width: 575.98px) {
        .card-header-usuario {
            padding: .85rem .85rem;
        }

        .card-body {
            padding: .85rem !important;
        }

        .form-group-usuario {
            margin-bottom: .8rem;
        }

        .card-usuario .form-control,
        .card-usuario .form-select {
            min-height: 36px;
            font-size: 13px;
        }
    }
</style>
@stop

@section('js')
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="{{ asset('js/sweetAlert.min.js') }}"></script>
    <script src="{{ asset('js/alertConfirmPequena.js') }}"></script>
    <script>
        const isCreateUsuario = @json($isCreate);

        function toggleEstacoesPermitidas() {
            if ($('#acesso_todas_estacoes').is(':checked')) {
                $('#estacoes-permitidas-wrap').hide();
            } else {
                $('#estacoes-permitidas-wrap').show();
            }
        }

        function validatePassword() {
            var senha = $('#senha').val();
            var confirmacaoSenha = $('#confirmacao_senha').val();
            var valid = true;

            $('#passwordValidation li').addClass('d-none');

            if (senha.length < 8) {
                $('#length').removeClass('d-none');
                valid = false;
            }

            if (!/[A-Z]/.test(senha)) {
                $('#uppercase').removeClass('d-none');
                valid = false;
            }

            if (!/[a-z]/.test(senha)) {
                $('#lowercase').removeClass('d-none');
                valid = false;
            }

            if (!/[0-9]/.test(senha)) {
                $('#number').removeClass('d-none');
                valid = false;
            }

            if (!/[!@#$%^&*(),.?":{}|<>]/.test(senha)) {
                $('#special').removeClass('d-none');
                valid = false;
            }

            if (senha !== confirmacaoSenha) {
                $('#match').removeClass('d-none');
                valid = false;
            }

            if (valid) {
                $('#passwordValidation').addClass('d-none');
            } else {
                $('#passwordValidation').removeClass('d-none');
            }

            return valid;
        }

        function abrirAbaComErro() {
            const firstInvalid = document.querySelector('.is-invalid');
            if (!firstInvalid) {
                return;
            }

            const pane = firstInvalid.closest('.tab-pane');
            if (!pane || !pane.id) {
                return;
            }

            const tabLink = document.querySelector(`#usuario-tab a[href="#${pane.id}"]`);
            if (tabLink) {
                $(tabLink).tab('show');
            }
        }

        document.getElementById("data_emissao_carta_conducao")?.addEventListener("change", function() {
            const dataEmissao = new Date(this.value);
            const hoje = new Date();

            if (dataEmissao >= hoje) {
                alertConfirmPequena("Aviso", "A data de emissao deve ser anterior a data atual", "error");
                this.value = '';
            }
        });

        $(document).ready(function() {
            $('#celular').mask('(00) 00000-0000');
            $('#telefone').mask('(00) 00000-0000');

            $('#celular').on("blur", function() {
                const celular = $(this).val();
                const regex = /^\(\d{2}\) \d{5}-\d{4}$/;
                if (!regex.test(celular)) {
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            $('#celular').on("input", function() {
                const celular = $(this).val();
                const regex = /^\(\d{2}\) \d{5}-\d{4}$/;
                if (regex.test(celular)) {
                    $(this).removeClass('is-invalid');
                }
            });

            $('#data_nascimento').on('change', function() {
                var dataNascimento = new Date($(this).val());
                var hoje = new Date();
                var idade = hoje.getFullYear() - dataNascimento.getFullYear();
                var mes = hoje.getMonth() - dataNascimento.getMonth();

                if (mes < 0 || (mes === 0 && hoje.getDate() < dataNascimento.getDate())) {
                    idade--;
                }

                $('#idade').val(idade);
            });

            $('#acesso_todas_estacoes').on('change', toggleEstacoesPermitidas);
            toggleEstacoesPermitidas();

            if (isCreateUsuario) {
                $('#senha, #confirmacao_senha').on('keyup', validatePassword);
            }

            $('#form').on('submit', function(event) {
                if (isCreateUsuario && !validatePassword()) {
                    event.preventDefault();
                    $('#tab-seguranca-link').tab('show');
                }
            });

            abrirAbaComErro();
        });
    </script>
@stop
