@extends('adminlte::page')

@section('title', 'Relatórios')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Relatórios'" subtitle="Visões e exportações de horas do sistema" />
@stop

@section('css')
@include('partials.home-cards-styles')
@stop

@section('content')
@php
    $cards = [
        ['Horas Trabalhadas', 'fas fa-clock', 'relatorios.horas', 'Horas apontadas por projeto e período', '#2563eb'],
        ['Relatório de Membros', 'fas fa-user-clock', 'relatorios.membros', 'Horas e custo por colaborador', '#16a34a'],
        ['Consumo × Estimado', 'fas fa-chart-bar', 'relatorios.consumo', 'Horas consumidas vs estimadas por projeto', '#b45309'],
        ['Matriz de Horas', 'fas fa-th', 'relatorios.matriz', 'Horas de cada colaborador por projeto', '#7c3aed'],
        ['Exportar Dados', 'fas fa-file-export', 'relatorios.exportar', 'Baixe os dados em planilha CSV', '#475569'],
    ];
@endphp

<div class="home-launcher">
    <div class="home-section">
        <div class="home-section-title">Relatórios disponíveis</div>
        <div class="row">
            @foreach ($cards as $card)
                <div class="col-6 col-md-4 col-lg-3 mb-2">
                    <a href="{{ route($card[2]) }}" class="home-card" style="--card-cor: {{ $card[4] }};">
                        <span class="home-card-icone"><i class="{{ $card[1] }}"></i></span>
                        <span class="home-card-texto">
                            <span class="home-card-nome">{{ $card[0] }}</span>
                            <p class="home-card-desc">{{ $card[3] }}</p>
                        </span>
                        <span class="home-card-seta"><i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
