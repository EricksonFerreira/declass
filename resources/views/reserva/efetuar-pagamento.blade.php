@extends('adminlte::page')

@section('title', 'Detalhes da Reserva')
@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
    <script src="https://js.stripe.com/v3/"></script>
@stop
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow-lg border-0">
                            <div class="card-header bg-white text-white">
                                <h3 class="card-title mb-0"><i class="fas fa-lock mr-2"></i>Pagamento Seguro</h3>
                            </div>
                            <div class="card-body">
                                <form id="payment-form" action="{{ route('reserva.processarPagamento', $reserva->id) }}" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="payment-method" class="font-weight-bold">Método de Pagamento <small class="text-danger">*</small> <small class="text-muted">(Selecione o método de pagamento para continuar)</small></label>
                                        <select id="payment-method" class="form-control" name="payment_method" required>
                                            @if(count($metodosPagamento) == 0)
                                                <option value="" disabled selected>Nenhum método de pagamento disponível</option>
                                                {{-- <option value="add">Adicionar Método de Pagamento</option> --}}
                                            @else
                                                @foreach($metodosPagamento as $metodo)
                                                    <option value="{{ $metodo['id'] }}">
                                                        {{ strtoupper($metodo['card']->brand) }} - **** **** **** {{ substr($metodo['card']->last4, -4) }} (Validade: {{ $metodo['card']->exp_month }}/{{ $metodo['card']->exp_year }})
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        {{-- @if(count($metodosPagamento) == 0) --}}
                                            <small class="text-muted">Você pode <a href="{{ route('usuario.metodoPagamento', $reserva->user_id) }}" target="_blank">adicionar um novo método de pagamento aqui</a> para facilitar suas futuras reservas.</small>
                                            <small class="text-muted">(Lembre-se: os métodos de pagamento são vinculados ao usuário que está realizando a reserva.)</small>
                                        {{-- @endif --}}
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit"  {{ count($metodosPagamento) == 0 ? 'disabled' : '' }}
                                                @if(count($metodosPagamento) == 0)
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Adicione um método de pagamento para continuar"
                                                @endif
                                                class="btn btn-sm btn-success btn-block">
                                                    <i class="fas fa-lock mr-2"></i> Efetuar Pagamento Seguro
                                        </button>
                                        <a href="{{ route('reserva.show', $reserva->id) }}" class="btn btn-xs btn-link btn-block mt-2"> <i class="fas fa-arrow-left mr-2"></i> Voltar para Detalhes da Reserva</a>
                                    </div>
                                </form>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="text-center">
                                    <small class="text-muted">Seus dados estão protegidos com criptografia de ponta a ponta.</small>
                                    <div class="mt-2">
                                        <i class="fab fa-cc-visa fa-2x mx-1"></i>
                                        <i class="fab fa-cc-mastercard fa-2x mx-1"></i>
                                        <i class="fab fa-cc-amex fa-2x mx-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="card-title mb-0"><i class="fas fa-car mr-2"></i>Resumo da Reserva <strong>Nº {{ $reserva->id }}</strong></h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="p-1 list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex flex-column">
                                            <span><strong>Data de Retirada:</strong></span>
                                            <small>{{ Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}</small>
                                            <span><strong>Estação de Retirada:</strong></span>
                                            <small class="text-truncate">{{ $reserva->estacaoRetirada->nome ?? 'N/A' }}</small>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span><strong>Data de Devolução:</strong></span>
                                            <small>{{ Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}</small>
                                            <span><strong>Estação de Devolução:</strong></span>
                                            <small class="text-truncate">{{ $reserva->estacaoDevolucao->nome ?? 'N/A' }}</small>
                                        </div>
                                    </li>
                                    <li class="p-1 list-group-item d-flex justify-content-between align-items-center">
                                        <span><strong>Grupo de Veículo:</strong></span>
                                        <small class="text-truncate">{{ $reserva->grupoFrota->nome ?? 'N/A' }}</small>
                                    </li>
                                </ul>
                                <hr>

                                <ul class="list-group list-group-flush">
                                    <li class="p-1 list-group-item d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold">Tarifa Base:</span>
                                        <span class="badge bg-light text-dark">€ {{ number_format($reserva->tarifa_base, 2, ',', '.') }}</span>
                                    </li>
                                    <li class="p-1 list-group-item d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold">Valor Total Tarifa Base:</span>
                                        <div class="d-flex">
                                            <span class="badge bg-dark text-dark">
                                                {{ $reserva->quantidade_dias }} dias
                                            </span>
                                            <span class="ml-1 badge bg-light text-dark">€ {{ number_format($reserva->valor_total_tarifa_base, 2, ',', '.') }}</span>
                                        </div>
                                    </li>
                                    <li class="p-1 list-group-item d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold">Valor total dos Opcionais:</span>
                                        <span class="badge bg-light text-dark">€ {{ number_format($reserva->valor_opcionais, 2, ',', '.') }}</span>
                                    </li>
                                    <li class="p-1 list-group-item d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold"  >Desconto {{ $reserva->cupom_desconto ? "<span class='badge bg-pink'>({$reserva->cupom_desconto->codigo})</span>" : '' }}</span>
                                        <div class="d-flex">
                                            <span class="badge bg-info">{{ number_format($reserva->percentual_desconto ?? 0, 2, ',', '.') }} %</span>
                                            <span class="ml-1 {{ isset($reserva->valor_total_com_desconto ) ? 'badge bg-success' : 'badge bg-danger' }}">-€ {{ number_format($reserva->valor_total_com_desconto  ?? 0, 2, ',', '.') }}</span>
                                        </div>
                                    </li>
                                    <li class="p-1 list-group-item d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold">Valor Total:</span>
                                        <span class="badge bg-primary">€ {{ number_format($reserva->valor_total_final , 2, ',', '.') }}</span>
                                    </li>
                                </ul>
                                <hr>

                                @if($reserva->opcionais->count() > 0)
                                    <h6 class="mb-2 font-weight-bold text-sm text-muted">Opcionais Selecionados:</h6>
                                    <ul class="list-group list-group-flush">
                                        @forelse($reserva->opcionais as $opcional)
                                            <li class="p-1 list-group-item d-flex justify-content-between align-items-center">
                                                {{-- {{ $opcional->nome }} --}}
                                                <span class="font-weight-bold">{{ $opcional->nome }} {!! $opcional->tipo_cobranca == 'por_dia' ? "<span class='badge bg-secondary'>Diário</span>" : "<span class='badge bg-purple'>Integral</span>" !!}</span>
                                                    <div class="d-flex">

                                                <span class="badge bg-light ml-2">
                                                    Quantidade: {{ $opcional->pivot->quantidade }}
                                                </span>
                                                <span class="badge badge-primary ml-2">€ {{ number_format($opcional->pivot->quantidade * $opcional->pivot->preco_total, 2, ',', '.') ?? 0}}</span>
                                            </div>
                                            </li>
                                        @empty
                                            <li class="list-group-item py-1">Nenhum opcional selecionado</li>
                                        @endforelse
                                    </ul>
                                @endif
                            </div>
                            <div class="card-footer bg-light">
                                <small class="text-muted">Reserva confirmada em {{ $reserva->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        #card-number {
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 40px;
        }
        .card-visa { background-image: url('/img/visa.png'); }
        .card-mastercard { background-image: url('/img/mastercard.png'); }
        .card-amex { background-image: url('/img/amex.png'); }
        /* Adicione mais classes para outros tipos de cartão */
    </style>
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js"></script>
    <script>
        const stripePublicKey = "{{ env('STRIPE_KEY') }}";
        var stripe = Stripe(stripePublicKey); // Substitua pela sua chave pública do Stripe
        var elements = stripe.elements();
        var cardElement = elements.create('card');
        cardElement.mount('#card-element');

        $(document).ready(function() {
            var form = $('#payment-form');
            form.on('submit', function(event) {
                // quero setar os valores cardnumber exp-date cvc
                const cardNumber = $('#card-number').val();
                const cardExpiry = $('#card-expiry').val();
                const cardCvc = $('#card-cvc').val();

                // Atualiza os valores do cardElement
                cardElement.update({
                    value: cardNumber,
                    cvc: cardCvc,
                    expiry: cardExpiry
                    // Para CVC e data de validade, você deve usar métodos separados
                });

                // // Atualiza o CVC e a data de validade
                // elements.create('cardCvc').update({ value: cardCvc });
                // elements.create('cardExpiry').update({ value: cardExpiry });

                console.log(cardElement,elements);
                event.preventDefault();

                stripe.createToken(cardElement).then(function(result) {
                    if (result.error) {
                        console.log(result.error);
                        // Exibe erro no formulário
                        var errorElement = $('#card-errors');
                        errorElement.text(result.error.message);
                    } else {
                        console.log(result.token.id);
                        // Adiciona o token ao formulário
                        var hiddenInput = $('<input>', {
                            type: 'hidden',
                            name: 'stripeToken',
                            value: result.token.id
                        });
                        form.append(hiddenInput);

                        // Envia o formulário
                        // form.submit();
                    }
                });
            });
        });
    </script>

    {{-- JS para validar o cartao --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Cleave('#card-number', {
                creditCard: true,
                onCreditCardTypeChanged: function(type) {
                    const cardField = document.getElementById('card-number');
                    cardField.className = 'form-control';
                    if (type) {
                        cardField.classList.add('card-' + type);
                    }
                }
            });

            new Cleave('#card-expiry', {
                date: true,
                datePattern: ['m', 'y']
            });

            new Cleave('#card-cvc', {
                numeral: true,
                numeralPositiveOnly: true,
                blocks: [3]
            });
        });
    </script>
@endpush
