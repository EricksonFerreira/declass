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
        <div class="card shadow-sm border-0">
            <div class="card-header border-0">
                <div>
                    <h4 class="mb-1">{{ $titulo }}</h4>
                    <p class="mb-0 text-muted">Gerencie feriados por estação (cada estação deve ter seu próprio feriado).</p>
                </div>
            </div>

            <div class="card-body p-4">
                <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                    <div class="col-12">
                        <div class="section-title">Informações básicas</div>
                    </div>

                    <div class="col-lg-3 col-md-4">
                        <div class="form-group">
                            <x-form-input type="date" id="data" name="data" label="Data" value="{{ old('data', isset($feriado) ? \Illuminate\Support\Carbon::parse($feriado->data)->format('Y-m-d') : '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-5 col-md-8">
                        <div class="form-group">
                            <x-form-input type="text" id="nome" name="nome" label="Nome" value="{{ old('nome', $feriado->nome ?? '') }}" required />
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-12">
                        <div class="form-group">
                            <label for="estacao_id">Estação</label>
                            <select name="estacao_id" id="estacao_id" class="form-control form-control-sm" required>
                                <option value="">Selecione a estação</option>
                                @foreach($estacoes as $id => $label)
                                    <option value="{{ $id }}" {{ old('estacao_id', $feriado->estacao_id ?? '') == $id ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4">
                        <div class="form-group">
                            <label for="ativo">Status</label>
                             <select name="ativo" id="ativo" class="form-control form-control-sm" required>
                                <option disabled>Selecione o status</option>
                                <option value="S" {{ old('ativo', $feriado->ativo ?? 'S') == 'S' ? 'selected' : '' }}>Ativo</option>
                                <option value="N" {{ old('ativo', $feriado->ativo ?? '') == 'N' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-primary col-12">Salvar</button>
                    </div>
                </x-form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.section-title { font-weight:700; padding-bottom:8px; border-bottom:1px solid #e5eaf2; margin-bottom:10px; }
</style>
@stop
