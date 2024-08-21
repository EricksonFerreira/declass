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
                <!-- Informações Privadas -->
                <x-section-division-header-form>Informações Privadas</x-section-division-header-form>
                <div class="col-md-5">
                    <div class="form-group">
                        <x-form-input required type="password" class="form-control form-control-sm" id="senha"
                            name="password" label="Senha" value="{{ $usuario->senha ?? '' }}" />
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <x-form-input required type="password" class="form-control form-control-sm" id="confirmacao_senha"
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
