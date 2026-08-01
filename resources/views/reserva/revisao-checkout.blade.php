@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $checkoutAssinado = ! empty($checkout->assinatura) || ! empty($checkout->data_assinatura) || $checkout->assinado === 'S';
    $veiculo = $reserva->veiculo;
    $cliente = $reserva->usuario;
    $valorCalcaoRecebido = (float) ($reserva->calcao?->valor_capturado ?? $reserva->calcao?->valor_autorizado ?? 0);
    $danosTotal = $danosVeiculo->count();
    $cobrancaTotal = (float) ($cobrancaTotal ?? $cobrancasAvaria->sum(fn ($item) => (float) ($item->valor ?? 0)));
    $valorDevolver = max(0, $valorCalcaoRecebido - $cobrancaTotal);
    $codigoReserva = $reserva->codigoReserva?->codigo ?? $reserva->id;
@endphp

@include('reserva.partials.revisao_checkout_styles')

<div class="container-fluid rev-page">
    <div class="rev-shell">
        @include('reserva.partials.revisao_checkout_header')
        @include('reserva.partials.revisao_checkout_summary_cards')
        <div class="row">
            <div class="col-xl-8 mb-3">
                @include('reserva.partials.revisao_checkout_danos')
            </div>
            <div class="col-xl-4 mb-3">
                @include('reserva.partials.revisao_checkout_sidebar')
            </div>
        </div>
        @include('reserva.partials.revisao_checkout_actions')
    </div>
</div>

@include('reserva.partials.revisao_checkout_modal_cobrar_dano')
@include('reserva.partials.revisao_checkout_modal_detalhes_dano')
@include('reserva.partials.revisao_checkout_modal_novo_dano')
@include('reserva.partials.revisao_checkout_scripts')
@stop
