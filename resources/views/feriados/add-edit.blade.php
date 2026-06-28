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
        <div class="feriado-page">
            <div class="feriado-hero">
                <div class="feriado-hero__eyebrow">Calendário operacional</div>
                <h1 class="feriado-hero__title">{{ $titulo }}</h1>
                <p class="feriado-hero__text">
                    Cadastre feriados por estação seguindo a mesma linguagem visual do cadastro de multa: cards limpos, títulos fortes e ações objetivas.
                </p>
            </div>

            <div class="card shadow-sm border-0 feriado-card">
                <div class="card-body p-4">
                    <div class="feriado-section-title">Informações básicas</div>

                    <x-form method="{{ $metodo }}" action="{{ $rota }}" rowClass="row">
                        <div class="col-lg-3 col-md-4">
                            <div class="form-group">
                                <x-form-input type="date" id="data" name="data" label="Data"
                                    value="{{ old('data', isset($feriado) ? \Illuminate\Support\Carbon::parse($feriado->data)->format('Y-m-d') : '') }}"
                                    required />
                            </div>
                        </div>

                        <div class="col-lg-5 col-md-8">
                            <div class="form-group">
                                <x-form-input type="text" id="nome" name="nome" label="Nome"
                                    value="{{ old('nome', $feriado->nome ?? '') }}" required />
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
                            <button type="submit" class="btn btn-primary col-12 feriado-save-btn">
                                <i class="fas fa-save mr-1"></i>Salvar feriado
                            </button>
                        </div>
                    </x-form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .feriado-page {
        display: grid;
        gap: 18px;
    }

    .feriado-hero,
    .feriado-card {
        border: 1px solid #dbe4f0;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 14px 36px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .feriado-hero {
        padding: 22px 24px;
        color: #fff;
        background:
            radial-gradient(circle at top right, rgba(56, 189, 248, .18), transparent 26%),
            radial-gradient(circle at bottom left, rgba(16, 185, 129, .15), transparent 22%),
            linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #2563eb 100%);
        box-shadow: 0 24px 60px rgba(15, 23, 42, .18);
    }

    .feriado-hero__eyebrow {
        font-size: .72rem;
        letter-spacing: .18em;
        text-transform: uppercase;
        font-weight: 800;
        opacity: .72;
    }

    .feriado-hero__title {
        margin: .35rem 0 .25rem;
        font-size: 1.55rem;
        font-weight: 900;
        letter-spacing: -.02em;
    }

    .feriado-hero__text {
        margin: 0;
        max-width: 58rem;
        color: rgba(255,255,255,.86);
        line-height: 1.6;
    }

    .feriado-section-title {
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: #64748b;
        padding-bottom: 8px;
        border-bottom: 1px solid #e8eef6;
        margin-bottom: 16px;
    }

    .feriado-card .form-control {
        border-radius: 12px;
        border-color: #d7e0ea;
        box-shadow: none;
    }

    .feriado-card .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, .12);
    }

    .feriado-save-btn {
        border-radius: 12px;
        min-height: 42px;
        font-weight: 800;
    }
</style>
@stop
