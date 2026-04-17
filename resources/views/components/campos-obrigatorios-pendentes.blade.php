@props([
    'title' => 'Campos obrigatórios pendentes',
    'icon' => 'fas fa-exclamation-circle',
    'actionLabel' => 'Ir para o passo correto',
    'alertClass' => '',
    'actionClass' => '',
    'summaryClass' => '',
    'listClass' => '',
])

@php
    $outerClass = trim(
        'pre-requisitos-alerta pre-requisitos-alerta--warning d-none js-campos-obrigatorios-pendentes ' .
            $alertClass,
    );
@endphp

<div {{ $attributes->merge(['class' => $outerClass]) }} role="alert" aria-live="assertive">
    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
        <div class="pre-requisitos-alerta__title mb-0">
            <i class="{{ $icon }} mr-1"></i>
            {{ $title }}
        </div>
        <button type="button" class="btn btn-sm btn-outline-warning {{ $actionClass }}">
            <i class="fas fa-arrow-up mr-1"></i> {{ $actionLabel }}
        </button>
    </div>
    <div class="js-campos-obrigatorios-pendentes__resumo {{ $summaryClass }} mt-2 small text-muted"></div>
    <div class="pre-requisitos-alerta__details js-campos-obrigatorios-pendentes__lista {{ $listClass }} mt-2 mb-0"></div>
</div>
