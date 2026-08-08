@extends('adminlte::page')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Exportar Relatório</h1>
    <form method="POST" action="{{ route('relatorios.exportar') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label for="data_inicio" class="form-label">Data início</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
            </div>
            <div class="col-md-4">
                <label for="data_fim" class="form-label">Data fim</label>
                <input type="date" class="form-control" id="data_fim" name="data_fim" required>
            </div>
            <div class="col-md-4">
                <label for="report_type" class="form-label">Tipo de relatório</label>
                <select class="form-select" id="report_type" name="report_type" required>
                    <option value="horas_trabalhadas_por_projeto">Horas Trabalhadas por Projeto</option>
                </select>
            </div>
        </div>
        <div class="row g-3 mt-3">
            <div class="col-md-12">
                <label for="user_ids" class="form-label">Equipe (múltipla)</label>
                <select class="form-select select2" id="user_ids" name="user_ids[]" multiple="multiple" style="width: 100%;">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                <small class="text-muted">Deixe vazio para incluir todos os usuários.</small>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-success">Exportar CSV</button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Selecione usuários',
            allowClear: true
        });
    });
</script>
@endpush
