@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[['name' => 'Planos de Manutenção', 'url' => route('manutencao.planos.index')], ['name' => 'Editar']]" />
@stop

@section('content')
    <div class="col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12 px-0">
        <div class="card card-opcional shadow-sm border-0">
            <div class="card-header card-header-opcional border-0">
                <div>
                    <h4 class="mb-1">Editar Plano de Manutenção</h4>
                    <p class="mb-0 text-muted">Configure as regras de manutenção preventiva por grupo de frota.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('manutencao.planos.update', $plano->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="opcional-section-title">Informações Básicas</div>
                    <div class="form-row">
                        <div class="col-lg-6">
                            <div class="form-group form-group-opcional">
                                <x-form-input type="text" class="form-control form-control-sm" id="nome" name="nome"
                                    label="Nome do Plano" value="{{ old('nome', $plano->nome) }}" required />
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group form-group-opcional">
                                <x-form-select name="grupo_frota_id" label="Grupo de Frota" :options="$grupos->pluck('nome', 'id')"
                                    placeholder="Selecione..." :selected="old('grupo_frota_id', $plano->grupo_frota_id)" class="form-control form-control-sm" required />
                            </div>
                        </div>
                    </div>

                    <div class="form-group form-group-opcional mt-3">
                        <label for="descricao" class="small text-muted font-weight-bold">Descrição</label>
                        <textarea name="descricao" id="descricao" class="form-control" rows="3">{{ old('descricao', $plano->descricao) }}</textarea>
                    </div>

                    <div class="opcional-section-title mt-4">Itens de Manutenção Preventiva</div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Frequência (Meses)</th>
                                    <th>Frequência (KM)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itens as $index => $item)
                                    @php
                                        $planoItem = $plano->itens->firstWhere('item_id', $item->id);
                                        $meses = $planoItem ? $planoItem->frequencia_meses : '';
                                        $km = $planoItem ? $planoItem->frequencia_km : '';
                                    @endphp
                                    <tr>
                                        <td class="align-middle font-weight-bold">
                                            <input type="hidden" name="itens[{{ $index }}][id]" value="{{ $item->id }}">
                                            {{ $item->nome }}
                                        </td>
                                        <td>
                                            <input type="number" name="itens[{{ $index }}][meses]" class="form-control form-control-sm" value="{{ old('itens.'.$index.'.meses', $meses) }}" placeholder="Ex: 6">
                                        </td>
                                        <td>
                                            <input type="number" name="itens[{{ $index }}][km]" class="form-control form-control-sm" value="{{ old('itens.'.$index.'.km', $km) }}" placeholder="Ex: 10000">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="submit" class="btn btn-primary btn-save-opcional col-12 mt-4">
                        Salvar Plano
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
    .opcional-section-title { font-size: .96rem; font-weight: 700; color: #21364f; letter-spacing: .02em; padding-bottom: 8px; border-bottom: 1px solid #e5eaf2; margin-bottom: 15px; }
    .form-group-opcional { margin-bottom: 1rem; }
    .card-opcional .form-control, .card-opcional .form-select { border-radius: 10px; border-color: #d8e1ec; min-height: 38px; }
    .card-opcional .form-control:focus, .card-opcional .form-select:focus { border-color: #4b80ff; box-shadow: 0 0 0 .15rem rgba(75, 128, 255, .18); }
    .btn-save-opcional { border-radius: 10px; font-weight: 600; padding-top: .58rem; padding-bottom: .58rem; }
</style>
@stop
