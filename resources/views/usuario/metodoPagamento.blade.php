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


<div class="col-xl-6 col-lg-8 col-md-10 col-sm-10 col-xs-10 card p-4">

        <div class="">
            <!-- Cartão de Crédito -->
            <x-form method="{{ $metodo }}" action="{{ $rota }}" id="form" rowClass="row">
                <x-section-division-header-form>Adicionar Método de Pagamento</x-section-division-header-form>
                    <div class="col-md-12 mt-2 mb-2">
                        <label class="text-xs">
                            Nome do Titular do Cartão
                            <span style="color: red;">*</span>
                        </label>
                        <div class="form-group">
                            <div id="card-element" class="form-control"></div>
                            <div id="card-errors" role="alert"></div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-xs col-12">
                        Salvar
                    </button>
                </x-form>
            </div>
        </div>
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-10 col-xs-10 card p-4">
            <div class="">
                <x-section-division-header-form>Métodos de Pagamento</x-section-division-header-form>

                <!-- Cartão de Crédito -->
                {{-- <x-section-division-header-form>Cartão de Crédito</x-section-division-header-form> --}}
                <div class="col-md-12 mt-2 ">
                   <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Bandeira</th>
                            <th>Ultimos 4 dígitos</th>
                            <th>Tipo</th>
                            <th>Validade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($metodosPagamento as $metodo)
                            <tr>
                                <td>{{ $metodo['card']->brand }}</td>
                                <td>{{ $metodo['card']->last4 }}</td>
                                <td>{{ $metodo['card']->funding }}</td>
                                <td>{{ $metodo['card']->exp_month }}/{{ $metodo['card']->exp_year }}</td>
                                <td>
                                    <x-delete-button :action="route('usuario.excluirMetodoPagamento', ['usuarioId' => $usuarioId, 'metodoId' => $metodo['id']])" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                   </table>
                </div>

        </div>
    </div>
@stop
@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

 <!-- Integração com Stripe -->
<script>
    const stripePublicKey = "{{ env('STRIPE_KEY') }}";
    var stripe = Stripe(stripePublicKey); // Substitua pela sua chave pública do Stripe
    var elements = stripe.elements();
    var cardElement = elements.create('card');
    cardElement.mount('#card-element');

    var form = document.getElementById('form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        // Remover qualquer referência ao código postal (CEP)
        stripe.createToken(cardElement).then(function(result) {
            if (result.error) {
                console.log(result.error);
                // Exibe erro no formulário
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
            } else {
                // Adiciona o token ao formulário
                var hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', result.token.id);
                form.appendChild(hiddenInput);

                // Envia o formulário
                form.submit(); // Alterado para usar form.submit() diretamente
            }
        });
    });
</script>



@stop
