@extends('adminlte::page')

@section('title', 'Exportar Relatório')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="'Exportar Relatório'" />
@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    .exp-card { font-family: 'Plus Jakarta Sans', sans-serif; }

    .exp-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #64748b; }
    .exp-hint  { font-size: 12px; color: #94a3b8; }

    .select2-container--bootstrap4 .select2-selection--multiple { min-height: 38px; border-color: #ced4da; }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice { margin-top: 6px; font-size: 12px; }
    .select2-container { max-width: 100%; }
</style>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card exp-card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-file-export mr-1"></i> Parâmetros</h3>
                <small class="exp-hint d-block mt-1">Gere um CSV com as horas registradas pela equipe no período.</small>
            </div>
            @can('relatorios.exportar')
            <form method="POST" action="{{ route('relatorios.exportar') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_inicio" class="exp-label">Data início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio"
                                       value="{{ old('data_inicio') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_fim" class="exp-label">Data fim</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim"
                                       value="{{ old('data_fim') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="report_type" class="exp-label">Tipo de relatório</label>
                        <select class="form-control" id="report_type" name="report_type" required>
                            <option value="horas_trabalhadas_por_projeto">Horas Trabalhadas por Projeto</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label for="user_ids" class="exp-label">Equipe</label>
                        <select class="form-control select2" id="user_ids" name="user_ids[]" multiple="multiple" style="width: 100%;">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(in_array($user->id, (array) old('user_ids', [])))>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <small class="exp-hint">Deixe vazio para incluir todos os usuários.</small>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end" style="gap: 8px; background:#f8fafc; border-top:1px solid #e5e7eb;">
                    <a href="{{ route('relatorios.index') }}" class="btn btn-default font-weight-bold shadow-sm">
                        Voltar
                    </a>
                    <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                        <i class="fas fa-file-csv mr-1"></i> Exportar CSV
                    </button>
                </div>
            </form>
            @endcan
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(function () {
        if ($.fn.select2) {
            $('#user_ids').select2({
                theme: 'bootstrap4',
                placeholder: 'Todos os usuários',
                allowClear: true,
                language: { noResults: function () { return 'Nenhum usuário encontrado'; } }
            });
        }
    });
</script>
@stop
