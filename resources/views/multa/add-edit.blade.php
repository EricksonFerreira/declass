@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
<div class="col-12 px-0">
    <div class="card card-outline card-danger">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>{{ $titulo }}</h3>
        </div>

        <form method="POST" action="{{ $rota }}" enctype="multipart/form-data">
            @csrf
            @if($metodo === 'PUT') @method('PUT') @endif

            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="row">
                    {{-- Coluna esquerda --}}
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-1 mb-3">Identificacao da Infracao</h5>

                        <div class="form-group">
                            <label>Reserva</label>
                            <input type="number" name="reserva_id" class="form-control form-control-sm"
                                   value="{{ old('reserva_id', $multa->reserva_id ?? $reserva?->id ?? '') }}"
                                   placeholder="ID da reserva">
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Placa no Momento <span class="text-danger">*</span></label>
                                <input type="text" name="placa_momento" class="form-control form-control-sm"
                                       value="{{ old('placa_momento', $multa->placa_momento ?? '') }}" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Numero do Auto</label>
                                <input type="text" name="numero_auto" class="form-control form-control-sm"
                                       value="{{ old('numero_auto', $multa->numero_auto ?? '') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Orgao Autuador</label>
                            <input type="text" name="orgao_autuador" class="form-control form-control-sm"
                                   value="{{ old('orgao_autuador', $multa->orgao_autuador ?? '') }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Data e Hora da Infracao</label>
                                <input type="datetime-local" name="data_hora_infracao" class="form-control form-control-sm"
                                       value="{{ old('data_hora_infracao', $multa?->data_hora_infracao?->format('Y-m-d\TH:i') ?? '') }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Data de Vencimento</label>
                                <input type="date" name="data_vencimento" class="form-control form-control-sm"
                                       value="{{ old('data_vencimento', $multa?->data_vencimento?->format('Y-m-d') ?? '') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Local da Infracao</label>
                            <input type="text" name="local_infracao" class="form-control form-control-sm"
                                   value="{{ old('local_infracao', $multa->local_infracao ?? '') }}">
                        </div>

                        <div class="form-group">
                            <label>Descricao</label>
                            <textarea name="descricao" class="form-control form-control-sm" rows="3">{{ old('descricao', $multa->descricao ?? '') }}</textarea>
                        </div>
                    </div>

                    {{-- Coluna direita --}}
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-1 mb-3">Valores e Status</h5>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Valor Original <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="valor_original" class="form-control form-control-sm"
                                       value="{{ old('valor_original', $multa->valor_original ?? '') }}" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Valor Desconto</label>
                                <input type="number" step="0.01" name="valor_desconto" class="form-control form-control-sm"
                                       value="{{ old('valor_desconto', $multa->valor_desconto ?? '') }}">
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Valor Final <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="valor_final" class="form-control form-control-sm"
                                       value="{{ old('valor_final', $multa->valor_final ?? '') }}" required id="valor_final">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Pontos (0–10)</label>
                            <input type="number" min="0" max="10" name="pontos" class="form-control form-control-sm"
                                   value="{{ old('pontos', $multa->pontos ?? '') }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Status da Multa <span class="text-danger">*</span></label>
                                <select name="status_multa" class="form-control form-control-sm" required>
                                    @foreach($statusMulta as $key => $label)
                                        <option value="{{ $key }}" {{ old('status_multa', $multa->status_multa ?? 'pendente') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Status de Desconto <span class="text-danger">*</span></label>
                                <select name="status_desconto" class="form-control form-control-sm" required>
                                    @foreach($statusDesconto as $key => $label)
                                        <option value="{{ $key }}" {{ old('status_desconto', $multa->status_desconto ?? 'nao_aplicado') === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Observacao</label>
                            <textarea name="observacao" class="form-control form-control-sm" rows="3">{{ old('observacao', $multa->observacao ?? '') }}</textarea>
                        </div>

                        <h5 class="border-bottom pb-1 mb-3 mt-3">Documentos</h5>
                        <div class="form-group">
                            <label>Comprovante (imagem ou PDF)</label>
                            <input type="file" name="anexo_comprovante" class="form-control-file form-control-sm"
                                   accept=".jpg,.jpeg,.png,.pdf">
                            @if(!empty($multa?->anexo_comprovante_path))
                                <small class="text-muted">
                                    Atual: <a href="{{ $multa->anexo_comprovante_url }}" target="_blank">ver arquivo</a>
                                </small>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>PDF do Auto de Infracao</label>
                            <input type="file" name="anexo_pdf" class="form-control-file form-control-sm" accept=".pdf">
                            @if(!empty($multa?->anexo_pdf_path))
                                <small class="text-muted">
                                    Atual: <a href="{{ $multa->anexo_pdf_url }}" target="_blank">ver PDF</a>
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('multa.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-save mr-1"></i> Salvar
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@push('js')
<script>
    // Preenche valor_final = valor_original - valor_desconto automaticamente
    document.querySelectorAll('[name=valor_original],[name=valor_desconto]').forEach(el => {
        el.addEventListener('input', () => {
            const orig = parseFloat(document.querySelector('[name=valor_original]').value) || 0;
            const desc = parseFloat(document.querySelector('[name=valor_desconto]').value) || 0;
            document.getElementById('valor_final').value = Math.max(0, orig - desc).toFixed(2);
        });
    });
</script>
@endpush
