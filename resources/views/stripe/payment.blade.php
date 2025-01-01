<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <div class="container col-md-4 mt-5" style="border: 3px solid gray; border-radius: 1em; padding: 1.5em;">
        <h2>Pagamento com Stripe</h2>
        <form id="payment-form" class="needs-validation" action="{{ route('stripe.processPayment') }}" method="POST" novalidate>
            @csrf
            <div class="form-group">
                <label for="cardholderName">Nome do Titular do Cartão</label>
                <input type="text" id="cardholderName" name="cardholderName" class="form-control" required>
                <div class="invalid-feedback">Por favor, insira o nome do titular do cartão.</div>
            </div>

            <div class="form-group">
                <label for="cardNumber">Número do Cartão</label>
                <div id="card-errors" role="alert" class="text-danger"></div>
                <div id="card-element" class="form-control"></div>
            </div>

            <button type="submit" class="btn btn-primary">Enviar Pagamento</button>
        </form>
    </div>

    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>

const stripePublicKey = "{{ env('STRIPE_KEY') }}";
        var stripe = Stripe(stripePublicKey); // Substitua pela sua chave pública do Stripe
        var elements = stripe.elements();
        var cardElement = elements.create('card');
        cardElement.mount('#card-element');

        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            stripe.createToken(cardElement).then(function(result) {
                if (result.error) {
                    console.log(result.error);
                    // Exibe erro no formulário
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    console.log(result.token.id);
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
</body>
</html>
