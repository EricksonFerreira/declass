@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
    <script src="https://js.stripe.com/v3/"></script>
@stop

@section('content')
<div class="row">

    {{-- Adicionar cartão --}}
    <div class="col-xl-4 col-lg-5 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-header py-2">
                <h3 class="card-title text-sm font-weight-bold">
                    <i class="fas fa-plus-circle mr-1 text-success"></i> Adicionar Cartão
                </h3>
            </div>
            <div class="card-body">
                <x-form method="POST" action="{{ $rota }}" id="formStripe" rowClass="row">
                    <div class="col-12 mb-3">
                        <label class="text-xs font-weight-bold">Dados do Cartão</label>
                        <div id="card-element" class="form-control" style="padding-top:10px;"></div>
                        <div id="card-errors" class="text-danger text-xs mt-1" role="alert"></div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success btn-sm btn-block">
                            <i class="fas fa-save mr-1"></i> Salvar Cartão
                        </button>
                    </div>
                </x-form>
            </div>
        </div>
    </div>

    {{-- Cartões salvos --}}
    <div class="col-xl-8 col-lg-7 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-header py-2">
                <h3 class="card-title text-sm font-weight-bold">
                    <i class="fas fa-credit-card mr-1 text-primary"></i> Cartões Salvos
                </h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-sm mb-0" style="font-size:13px;">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-3">Bandeira</th>
                            <th>Últimos 4 dígitos</th>
                            <th>Tipo</th>
                            <th>Validade</th>
                            <th class="text-center">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($metodosPagamento as $metodo)
                            <tr>
                                <td class="pl-3 align-middle text-capitalize">{{ $metodo['card']->brand }}</td>
                                <td class="align-middle">•••• {{ $metodo['card']->last4 }}</td>
                                <td class="align-middle text-capitalize">{{ $metodo['card']->funding }}</td>
                                <td class="align-middle">
                                    {{ str_pad($metodo['card']->exp_month, 2, '0', STR_PAD_LEFT) }}/{{ $metodo['card']->exp_year }}
                                </td>
                                <td class="text-center align-middle">
                                    <x-delete-button
                                        :action="route('usuario.excluirMetodoPagamento', ['usuarioId' => $usuarioId, 'metodoId' => $metodo['id']])" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    <i class="fas fa-credit-card fa-2x mb-2 d-block"></i>
                                    Nenhum cartão cadastrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@stop

@section('js')
<script>
    const stripeKey = @json($stripePublicKey ?? (config('services.stripe.key') ?? env('STRIPE_KEY') ?? null));
    if (!stripeKey || stripeKey === '') {
        document.getElementById('card-errors').textContent = 'Chave pública do Stripe não configurada.';
    } else {
        const stripe = Stripe(stripeKey);
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

        const form = document.getElementById('formStripe');
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            stripe.createToken(cardElement).then(function (result) {
                if (result.error) {
                    document.getElementById('card-errors').textContent = result.error.message;
                } else {
                    const input = document.createElement('input');
                    input.type  = 'hidden';
                    input.name  = 'stripeToken';
                    input.value = result.token.id;
                    form.appendChild(input);
                    form.submit();
                }
            });
        });
    }
</script>
@stop
