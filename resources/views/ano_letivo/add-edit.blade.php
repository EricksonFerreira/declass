@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="isset($anoLetivo) ? 'Editar Ano Letivo' : 'Novo Ano Letivo'" />
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ isset($anoLetivo) ? route('ano_letivo.update', $anoLetivo->id) : route('ano_letivo.store') }}">
            @csrf
            @if(isset($anoLetivo))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="ano" type="number" label="Ano" :value="old('ano', $anoLetivo->ano ?? date('Y'))" required min="2000" max="2100" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="escola_id" label="Escola/unidade" :options="$escolas ?? []" :selected="old('escola_id', $anoLetivo->escola_id ?? '')" placeholder="Selecionar" required />
                </div>
                <div class="col-md-4">
                    <x-form-select name="status" label="Status" :options="$status ?? []" :selected="old('status', $anoLetivo->status ?? 'letivo')" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="periodicidade" label="Periodicidade" :options="['' => 'Selecionar'] + ($periodicidades ?? [])" :selected="old('periodicidade', $anoLetivo->periodicidade ?? '')" />
                    <small class="text-muted">Gera os períodos letivos automaticamente na criação.</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <x-form-input name="data_inicio" type="date" label="Início" :value="old('data_inicio', optional($anoLetivo->data_inicio ?? null)->format('Y-m-d'))" />
                </div>
                <div class="col-md-4">
                    <x-form-input name="data_fim" type="date" label="Fim" :value="old('data_fim', optional($anoLetivo->data_fim ?? null)->format('Y-m-d'))" />
                </div>
                <div class="col-md-4">
                    <x-form-select name="ativo" label="Situação" :options="['S' => 'Ativo', 'N' => 'Inativo']" :selected="old('ativo', $anoLetivo->ativo ?? 'S')" />
                </div>
            </div>

            <div class="mt-3 d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('ano_letivo.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>

                @if(isset($anoLetivo) && ($totalPeriodos ?? 0) === 0 && ! in_array($anoLetivo->periodicidade, [null, '', 'personalizado'], true))
                    <span class="text-muted mx-2">|</span>
                    <button type="submit" form="form-gerar-periodos" class="btn btn-info"
                        onclick="return confirm('Gerar {{ \App\Models\AnoLetivo::PERIODICIDADES[$anoLetivo->periodicidade] ?? 'os' }} períodos letivos para este ano?')">
                        <i class="fa fa-calendar-plus"></i> Gerar períodos letivos
                    </button>
                @endif
            </div>
        </form>

        @if(isset($anoLetivo) && ($totalPeriodos ?? 0) === 0 && ! in_array($anoLetivo->periodicidade, [null, '', 'personalizado'], true))
            <form id="form-gerar-periodos" method="POST" action="{{ route('ano_letivo.gerar_periodos', $anoLetivo->id) }}" class="d-none">
                @csrf
            </form>
        @endif
    </div>
</div>
@stop
