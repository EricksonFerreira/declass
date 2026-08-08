<?php
/**
 * Pricing page view for Clockify.
 * It displays active plans with pricing and feature list.
 * Uses the existing pricing CSS defined in welcome.blade.php.
 */
?>
@extends('layouts.app')

@section('title', 'Planos')

@section('content')
    <section class="pricing" id="planos">
        <div class="container">
            <h1 class="text-center mb-5">Planos de assinatura</h1>

            <div class="row">
                @foreach ($plans as $plan)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100" style="border: 1px solid var(--border); border-radius: 12px;">
                            <div class="card-body d-flex flex-column">
                                <h3 class="card-title text-center">{{ $plan['nome'] }}</h3>
                                <p class="text-muted text-center mb-3">{{ $plan['descricao'] ?? '' }}</p>
                                <div class="mb-3 text-center">
                                    <span class="h4 font-weight-bold">R\$ {{ number_format($plan['valor_mensal'], 2, ',', '.') }}</span>
                                    <small class="text-muted">/ mês</small>
                                </div>
                                @if($plan['valor_anual'] < $plan['valor_mensal'] * 12)
                                    <div class="text-center mb-2">
                                        <span class="text-success font-weight-bold">
                                            R\$ {{ number_format($plan['valor_anual'], 2, ',', '.') }} / ano
                                        </span>
                                        <small class="text-muted">(economia de {{ $plan['desconto_anual_percentual'] }}%)</small>
                                    </div>
                                @endif
                                <hr>
                                <ul class="list-unstyled flex-grow-1">
                                    @foreach ($plan['features'] as $feature)
                                        @if($feature['incluso'])
                                            <li class="mb-2 d-flex align-items-center">
                                                <i class="fas fa-check text-success mr-2"></i>
                                                <span>{{ $feature['label'] }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                                <a href="#" class="btn btn-primary mt-auto">Selecionar</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
