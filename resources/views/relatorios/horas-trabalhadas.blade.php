@extends('adminlte::page')

@section('title', 'Horas Trabalhadas')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Horas Trabalhadas'" />
@stop

@section('content')
@php
    $fmtHora = fn ($v) => number_format($v, 2, ',', '.').'h';
@endphp

{{-- KPIs --}}
<div class="row mb-3">
    <div class="col-lg-4 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">Total de horas</div>
            <div class="h4 font-weight-bold mb-0">{{ $fmtHora($totais['horas']) }}</div>
        </div>
    </div>
    <div class="col-lg-4 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">Projetos com apontamento</div>
            <div class="h4 font-weight-bold mb-0">{{ $totais['projetos'] }}</div>
        </div>
    </div>
    <div class="col-lg-4 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">Colaboradores</div>
            <div class="h4 font-weight-bold mb-0">{{ $totais['colaboradores'] }}</div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Tabela projeto x colaborador --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-clock mr-1"></i> Horas por projeto e colaborador</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-3">Projeto</th>
                            <th>Colaborador</th>
                            <th class="text-right pr-3">Horas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($linhas as $l)
                            <tr>
                                <td class="pl-3 font-weight-bold">{{ $l->projeto?->nome ?? 'Sem projeto' }}</td>
                                <td>{{ $l->user?->nome ?? '—' }}</td>
                                <td class="text-right pr-3">{{ $fmtHora(round($l->segundos / 3600, 2)) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="fas fa-clock fa-2x mb-2 d-block"></i>
                                    Nenhuma hora registrada ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Gráfico por projeto --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Horas por projeto</h3>
            </div>
            <div class="card-body">
                @if (count($chart['labels']))
                    <canvas id="hoursChart" height="220"></canvas>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                        Sem dados para exibir.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
@if (count($chart['labels']))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('hoursChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chart['labels']),
            datasets: [{
                label: 'Horas',
                data: @json($chart['data']),
                backgroundColor: 'rgba(37, 99, 235, 0.55)',
                borderColor: 'rgba(37, 99, 235, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (item) => ' ' + item.parsed.x.toFixed(2).replace('.', ',') + 'h'
                    }
                }
            },
            scales: {
                x: { beginAtZero: true, ticks: { callback: (v) => v + 'h' } }
            }
        }
    });
</script>
@endif
@endpush
