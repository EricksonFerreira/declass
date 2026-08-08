@extends('adminlte::page')

@section('content_header')
    <h1>Relatórios</h1>
@stop

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Relatórios</h1>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Horas Trabalhadas por Projeto</h5>
                    <a href="{{ route('relatorios.horas') }}" class="btn btn-primary mt-2">Ver detalhes</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <i class="fas fa-file-export fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Exportar Dados</h5>
                    <a href="{{ route('relatorios.exportar') }}" class="btn btn-success mt-2">Exportar CSV</a>
                </div>
            </div>
        </div>
        <!-- Add more report cards here as needed -->
    </div>
</div>
@endsection
