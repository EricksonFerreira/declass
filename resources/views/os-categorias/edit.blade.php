@extends('adminlte::page')

@section('title', 'Editar Categoria de OS')

@section('content_header')
    <x-breadcrumb :breadcrumbs="[['name' => 'Categorias de OS', 'url' => route('os-categorias.index')], ['name' => 'Editar']]" />
@stop

@section('content')
    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <div class="card card-opcional shadow-sm border-0">
            <div class="card-header card-header-opcional border-0">
                <div>
                    <h4 class="mb-1">Editar Categoria de OS</h4>
                    <p class="mb-0 text-muted">Configure os dados da categoria de OS.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('os-categorias.update', $osCategoria->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="opcional-section-title">Informações Básicas</div>
                    <div class="form-row">
                        <div class="col-lg-6">
                            <div class="form-group form-group-opcional">
                                <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                    label="Nome" value="{{ old('nome', $osCategoria->nome) }}" required />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group form-group-opcional mt-4 pt-2">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $osCategoria->is_active) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Ativo</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-opcional col-12 mt-2">
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .card-opcional { border-radius: 14px; overflow: hidden; }
    .card-header-opcional { background: linear-gradient(120deg, #f6f9ff 0%, #edf2ff 50%, #f7fbff 100%); padding: 1.1rem 1.5rem; }
    .card-header-opcional h4 { color: #0f2740; font-weight: 700; }
    .opcional-section-title { font-size: .96rem; font-weight: 700; color: #21364f; letter-spacing: .02em; padding-bottom: 8px; border-bottom: 1px solid #e5eaf2; margin-bottom: 10px; }
    .form-group-opcional { margin-bottom: 1rem; }
    .card-opcional .form-control, .card-opcional .form-select { border-radius: 10px; border-color: #d8e1ec; min-height: 38px; }
    .card-opcional .form-control:focus, .card-opcional .form-select:focus { border-color: #4b80ff; box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18); }
    .btn-save-opcional { border-radius: 10px; font-weight: 600; padding-top: .58rem; padding-bottom: .58rem; }
</style>
@stop
