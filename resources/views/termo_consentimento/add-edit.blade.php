@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12">
            <ul class="mb-0 pl-3">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <div class="card card-termo-consentimento shadow-sm border-0">
            <div class="card-header card-header-termo-consentimento border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Configure as condições gerais da locação para check-in e check-out.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                    <div class="col-12">
                        <div class="termo-consentimento-section-title">Informações Básicas</div>
                    </div>

                    <div class="col-lg-2 col-md-12">
                        <div class="form-group form-group-termo-consentimento">
                            <x-form-input type="number" class="form-control form-control-sm" id="ordem" name="ordem"
                                label="Ordem" value="{{ old('ordem', $termo_consentimento->ordem ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-5 col-md-12">
                        <div class="form-group form-group-termo-consentimento">
                            <x-form-input type="text" class="form-control form-control-sm" id="titulo" name="titulo"
                                label="Título" value="{{ old('titulo', $termo_consentimento->titulo ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-12">
                        <div class="form-group form-group-termo-consentimento">
                            <x-form-select name="tipo" label="Tipo" :options="$tipos ?? []" placeholder="Selecione"
                                :selected="old(
                                    'tipo',
                                    isset($termo_consentimento->tipo) && $termo_consentimento->tipo
                                        ? $termo_consentimento->tipo
                                        : 'checkin',
                                )"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-12">
                        <div class="form-group form-group-termo-consentimento">
                            <x-form-select name="ativo" label="Status" :options="$status" placeholder="Selecione"
                                :selected="old(
                                    'ativo',
                                    isset($termo_consentimento->ativo) && $termo_consentimento->ativo
                                        ? $termo_consentimento->ativo
                                        : null,
                                )"
                                class="form-control form-control-sm" required />
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="termo-consentimento-section-title">Conteudo da Clausula</div>
                    </div>

                    <div class="col-12">
                        <div class="form-group form-group-termo-consentimento">
                            <label for="texto">Texto</label>
                            <textarea name="texto" id="texto" class="form-control form-control-sm termo-textarea" rows="8" required>{{ old('texto', isset($termo_consentimento->texto) && $termo_consentimento->texto ? $termo_consentimento->texto : null) }}</textarea>
                            <small class="text-muted d-block mt-1">
                                Texto completo da cláusula exibida no contrato.
                            </small>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-save-termo-consentimento col-12 mt-2">
                            Salvar
                        </button>
                    </div>
                </x-form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-termo-consentimento { border-radius: 14px; overflow: hidden; }
    .card-header-termo-consentimento { background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%); padding: 1.1rem 1.5rem; }
    .card-header-termo-consentimento h4 { color: #0f2740; font-weight: 700; }
    .termo-consentimento-section-title { font-size: .96rem; font-weight: 700; color: #21364f; letter-spacing: .02em; padding-bottom: 8px; border-bottom: 1px solid #e5eaf2; margin-bottom: 10px; }
    .form-group-termo-consentimento { margin-bottom: 1rem; }
    .card-termo-consentimento .form-control, .card-termo-consentimento .form-select { border-radius: 10px; border-color: #d8e1ec; min-height: 38px; }
    .card-termo-consentimento .form-control:focus, .card-termo-consentimento .form-select:focus { border-color: #4b80ff; box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18); }
    .termo-textarea { min-height: 180px; resize: vertical; line-height: 1.45; }
    .btn-save-termo-consentimento { border-radius: 10px; font-weight: 600; padding-top: .58rem; padding-bottom: .58rem; }
    @media (max-width: 991.98px) { .card-header-termo-consentimento { padding: .95rem 1rem; } .card-body { padding: 1rem !important; } }
    @media (max-width: 767.98px) { .col-xl-10.col-lg-11.col-md-12 { padding-left: .5rem !important; padding-right: .5rem !important; } }
</style>
@stop

@section('js')
    <script></script>
@stop
