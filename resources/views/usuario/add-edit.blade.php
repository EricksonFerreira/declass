@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
    <style>
        .auto-filled {
            background-color: #e0f7fa;
        }
    </style>
        {{-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> --}}
        <script src="https://js.stripe.com/v3/"></script>
@stop


@section('content')
   {{-- <!-- Exibir erros de validação -->
   @if ($errors->any())
   <div class="alert alert-danger">
       <ul>
           @foreach ($errors->all() as $error)
               <li>{{ $error }}</li>
           @endforeach
       </ul>
   </div>
@endif --}}


    <div class="col-xl-8 col-lg-10 col-md-12 col-sm-12 col-xs-12 card p-4">

        <div class="">
            <x-form method="{{ $metodo }}" action="{{ $rota }}" id="form" rowClass="row">
                <!-- Informações Pessoais -->
                <x-section-division-header-form>Informações Pessoais</x-section-division-header-form>
                <div class="col-md-6">
                    <div class="form-group">
                        <x-form-input type="text" class="form-control-sm" id="nome" name="nome"
                            label="Nome" value="{{ $usuario->nome ?? '' }}" required />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <x-form-input type="email" class="form-control-sm" id="email" name="email"
                            label="E-mail" value="{{ $usuario->email ?? '' }}" required />
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-select name="sexo" label="Sexo" :options="$sexo" placeholder="Selecione ..."
                            :selected="old('sexo', isset($usuario->sexo) ? $usuario->sexo : null)" class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input required type="date" class="form-control-sm" id="data_nascimento"
                            name="data_nascimento" label="Data de Nascimento"
                            value="{{ $usuario->data_nascimento ?? '' }}" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <x-form-input required type="text" class="form-control-sm" id="local_nascimento"
                            name="local_nascimento" label="Local de Nascimento"
                            value="{{ $usuario->local_nascimento ?? '' }}" />
                    </div>
                </div>

                <!-- Contato -->
                <x-section-division-header-form>Contato</x-section-division-header-form>
                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-input required type="text" class="form-control-sm" id="telefone" name="telefone"
                            label="Telefone" value="{{ $usuario->telefone ?? '' }}" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-input required type="text" class="form-control-sm" id="celular" name="celular"
                            placeholder="(99) 99999-9999" label="Celular" value="{{ $usuario->celular ?? '' }}" />
                    </div>
                </div>

                <!-- Endereço -->
                    <x-section-division-header-form>Endereço</x-section-division-header-form>
                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-select name="pais_sigla" label="País" :options="$paises" placeholder="Selecione um valor"
                            :selected="old('pais_sigla', isset($usuario->pais_sigla) ? $usuario->pais_sigla : null)" id="pais_sigla" class="form-control form-control-sm" required />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <x-form-input required type="text" class="form-control-sm" id="cep" name="zip_code"
                            label="Zip Code" value="{{ $usuario->zip_code ?? '' }}" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-input required type="text" class="form-control-sm" id="cidade" name="cidade"
                            label="Cidade" value="{{ $usuario->cidade ?? '' }}" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <x-form-input required type="text" class="form-control-sm" id="endereco" name="endereco"
                            label="Endereço" value="{{ $usuario->endereco ?? '' }}" />
                    </div>
                </div>

                <!-- Informações Adicionais -->
                <x-section-division-header-form>Informações Adicionais</x-section-division-header-form>
                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-input required type="text" class="form-control-sm" id="numero_identificacao"
                            name="numero_identificacao" label="Número de Identificação"
                            value="{{ $usuario->numero_identificacao ?? '' }}" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <x-form-select name="tipo_usuario_id" label="Regra de Usuário" :options="$tiposUsuario"
                            placeholder="Selecione um valor" :selected="old(
                                'tipo_usuario_id',
                                isset($usuario->tipo_usuario_id) ? $usuario->tipo_usuario_id : null,
                            )" class="form-control form-control-sm"
                            required />
                    </div>
                </div>

                @if (!isset($usuario))
                    <!-- Informações Privadas -->
                    <x-section-division-header-form>Informações Privadas</x-section-division-header-form>
                    <div class="col-md-5">
                        <div class="form-group">
                            <x-form-input required type="password" class="form-control-sm" id="senha"
                                name="password" label="Senha" value="{{ $usuario->senha ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <x-form-input required type="password" class="form-control-sm" id="confirmacao_senha"
                                name="confirmacao_senha" label="Confirmação de Senha"
                                value="{{ $usuario->confirmacao_senha ?? '' }}" />
                        </div>
                    </div>
                    <div class="col-md-12">
                        {{-- <div id="passwordHelpBlock" class="form-text text-xs text-muted">
                            A senha deve conter pelo menos uma letra maiúscula, uma letra minúscula, um número e um
                            caractere especial.
                        </div> --}}
                        <div id="passwordValidation" class="alert alert-danger d-none" role="alert">
                            <ul>
                                <li id="length" class="d-none">A senha deve ter pelo menos 8 caracteres.</li>
                                <li id="uppercase" class="d-none">A senha deve conter pelo menos uma letra maiúscula.</li>
                                <li id="lowercase" class="d-none">A senha deve conter pelo menos uma letra minúscula.</li>
                                <li id="number" class="d-none">A senha deve conter pelo menos um número.</li>
                                <li id="special" class="d-none">A senha deve conter pelo menos um caractere especial.
                                </li>
                                <li id="match" class="d-none">As senhas devem ser iguais.</li>
                            </ul>
                        </div>
                    </div>
                    <input type="hidden" id="usuario" name="usuario" value="false">
                @endif
                {{-- <div class="col-md-4">
                    <div class="form-group">
                        <x-form-input type="date" class="form-control-sm" id="created_at" name="created_at"
                            label="Data de Criação" value="{{ $usuario->created_at ?? '' }}" />
                    </div>
                </div> --}}
                {{-- <div class="col-md-6">
                    <div class="form-group">
                        <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione um valor"
                            :selected="isset($usuario->ativo) && $usuario->ativo ? $usuario->ativo : null"
                            class="form-control-sm" required />
                    </div>
                </div> --}}

                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-xs col-12">
                        Salvar
                    </button>
                </div>
            </x-form>
        </div>
    </div>
@stop
@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <script>
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

        function validatePassword() {
            var senha = $('#senha').val();
            var confirmacaoSenha = $('#confirmacao_senha').val();
            var valid = true;

            // Reset validation messages
            $('#passwordValidation li').addClass('d-none');

            // Check length
            if (senha.length < 8) {
                $('#length').removeClass('d-none');
                valid = false;
            }

            // Check for uppercase letter
            if (!/[A-Z]/.test(senha)) {
                $('#uppercase').removeClass('d-none');
                valid = false;
            }

            // Check for lowercase letter
            if (!/[a-z]/.test(senha)) {
                $('#lowercase').removeClass('d-none');
                valid = false;
            }

            // Check for number
            if (!/[0-9]/.test(senha)) {
                $('#number').removeClass('d-none');
                valid = false;
            }

            // Check for special character
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(senha)) {
                $('#special').removeClass('d-none');
                valid = false;
            }

            // Check if passwords match
            if (senha !== confirmacaoSenha) {
                $('#match').removeClass('d-none');
                valid = false;
            }

            // Show or hide validation message
            if (valid) {
                $('#passwordValidation').addClass('d-none');
            } else {
                $('#passwordValidation').removeClass('d-none');
            }

            return valid;
        }

        $('#senha, #confirmacao_senha').on('keyup', validatePassword);

        $(document).ready(function() {
            $('#form').on('submit', function(event) {
                if ($('#usuario').length && $('#usuario').val() === '') {
                    if (!validatePassword()) {
                        event.preventDefault(); // Prevent form submission
                    }
                }
            });
        });
    </script>
@stop
