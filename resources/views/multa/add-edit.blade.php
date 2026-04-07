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
            <div class="card-header border-0 pb-0">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-exclamation-triangle text-danger mr-2"></i>{{ $titulo }}
                    </h4>
                    <p class="mb-0 text-muted small">Preencha os dados da infração, valores e anexos.</p>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="formMulta" method="POST" action="{{ $rota }}" enctype="multipart/form-data">
                    @csrf
                    @if($metodo === 'PUT') @method('PUT') @endif

                    {{-- Tabs --}}
                    <ul class="nav nav-pills mb-3" id="multa-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#tab-infracao" role="tab">
                                <i class="fas fa-map-marker-alt mr-1"></i> Infração
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#tab-valores" role="tab">
                                <i class="fas fa-dollar-sign mr-1"></i> Valores e Status
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#tab-documentos" role="tab">
                                <i class="fas fa-paperclip mr-1"></i> Documentos
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="multa-tab-content">

                        {{-- ABA: Infração --}}
                        <div class="tab-pane fade show active" id="tab-infracao" role="tabpanel">
                            <div class="multa-section-title">Vínculo com Reserva e Veículo</div>
                            <div class="form-row">
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Reserva</label>
                                    <input type="number" name="reserva_id" class="form-control form-control-sm"
                                           value="{{ old('reserva_id', $multa->reserva_id ?? $reserva?->id ?? '') }}"
                                           placeholder="ID da reserva">
                                </div>
                                <div class="col-lg-8 form-group">
                                    <label class="form-label-sm">Veículo</label>
                                    <input type="hidden" name="veiculo_id" id="veiculo_id_input"
                                           value="{{ old('veiculo_id', $multa->veiculo_id ?? $veiculo?->id ?? '') }}">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="veiculo_display" class="form-control form-control-sm"
                                               readonly placeholder="Clique na lupa para buscar um veículo..."
                                               value="{{ old('veiculo_id') ? '' : ($veiculo ? (($veiculo->nome ?? '') . ($veiculo->placa_licenca ? ' — ' . $veiculo->placa_licenca : '')) : '') }}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" id="btnBuscarVeiculo" title="Buscar veículo">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger d-none" id="btnLimparVeiculo" title="Limpar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="multa-section-title mt-3">Dados da Infração</div>
                            <div class="form-row">
                                <div class="col-lg-3 form-group">
                                    <label class="form-label-sm">Placa no Momento <span class="text-danger">*</span></label>
                                    <input type="text" name="placa_momento" class="form-control form-control-sm"
                                           value="{{ old('placa_momento', $multa->placa_momento ?? $veiculo?->placa_licenca ?? '') }}" required>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="form-label-sm">Número do Auto</label>
                                    <input type="text" name="numero_auto" class="form-control form-control-sm"
                                           value="{{ old('numero_auto', $multa->numero_auto ?? '') }}">
                                </div>
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">Órgão Autuador</label>
                                    <input type="text" name="orgao_autuador" class="form-control form-control-sm"
                                           value="{{ old('orgao_autuador', $multa->orgao_autuador ?? '') }}">
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Data e Hora da Infração</label>
                                    <input type="datetime-local" name="data_hora_infracao" class="form-control form-control-sm"
                                           value="{{ old('data_hora_infracao', $multa?->data_hora_infracao?->format('Y-m-d\TH:i') ?? '') }}">
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Data de Vencimento</label>
                                    <input type="date" name="data_vencimento" class="form-control form-control-sm"
                                           value="{{ old('data_vencimento', $multa?->data_vencimento?->format('Y-m-d') ?? '') }}">
                                </div>
                                <div class="col-lg-12 form-group">
                                    <label class="form-label-sm">Local da Infração</label>
                                    <input type="text" name="local_infracao" class="form-control form-control-sm"
                                           value="{{ old('local_infracao', $multa->local_infracao ?? '') }}">
                                </div>
                                <div class="col-lg-12 form-group">
                                    <label class="form-label-sm">Descrição</label>
                                    <textarea name="descricao" class="form-control form-control-sm" rows="3">{{ old('descricao', $multa->descricao ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- ABA: Valores e Status --}}
                        <div class="tab-pane fade" id="tab-valores" role="tabpanel">
                            <div class="multa-section-title">Valores</div>
                            <div class="form-row">
                                <div class="col-lg-3 form-group">
                                    <label class="form-label-sm">Valor Original <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                        <input type="number" step="0.01" min="0" name="valor_original" class="form-control form-control-sm"
                                               value="{{ old('valor_original', $multa->valor_original ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="form-label-sm">Valor Desconto</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                        <input type="number" step="0.01" min="0" name="valor_desconto" class="form-control form-control-sm"
                                               value="{{ old('valor_desconto', $multa->valor_desconto ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="form-label-sm">Valor Final <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                        <input type="number" step="0.01" min="0" name="valor_final" id="valor_final" class="form-control form-control-sm"
                                               value="{{ old('valor_final', $multa->valor_final ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="form-label-sm">Pontos (0–10)</label>
                                    <input type="number" min="0" max="10" name="pontos" class="form-control form-control-sm"
                                           value="{{ old('pontos', $multa->pontos ?? '') }}">
                                </div>
                            </div>

                            <div class="multa-section-title mt-3">Status</div>
                            <div class="form-row">
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Status da Multa <span class="text-danger">*</span></label>
                                    <select name="status_multa" class="form-control form-control-sm" required>
                                        @foreach($statusMulta as $key => $label)
                                            <option value="{{ $key }}" {{ old('status_multa', $multa->status_multa ?? 'pendente') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Status de Desconto <span class="text-danger">*</span></label>
                                    <select name="status_desconto" class="form-control form-control-sm" required>
                                        @foreach($statusDesconto as $key => $label)
                                            <option value="{{ $key }}" {{ old('status_desconto', $multa->status_desconto ?? 'nao_aplicado') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-12 form-group">
                                    <label class="form-label-sm">Observação</label>
                                    <textarea name="observacao" class="form-control form-control-sm" rows="3">{{ old('observacao', $multa->observacao ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- ABA: Documentos --}}
                        <div class="tab-pane fade" id="tab-documentos" role="tabpanel">
                            <div class="multa-section-title">Anexos</div>
                            <div class="form-row">
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">Comprovante (imagem ou PDF)</label>
                                    <input type="file" name="anexo_comprovante" class="form-control-file form-control-sm"
                                           accept=".jpg,.jpeg,.png,.pdf">
                                    @if(!empty($multa?->anexo_comprovante_path))
                                        <small class="text-muted d-block mt-1">
                                            Atual: <a href="{{ $multa->anexo_comprovante_url }}" target="_blank">
                                                <i class="fas fa-external-link-alt mr-1"></i>ver arquivo
                                            </a>
                                        </small>
                                    @endif
                                </div>
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">PDF do Auto de Infração</label>
                                    <input type="file" name="anexo_pdf" class="form-control-file form-control-sm" accept=".pdf">
                                    @if(!empty($multa?->anexo_pdf_path))
                                        <small class="text-muted d-block mt-1">
                                            Atual: <a href="{{ $multa->anexo_pdf_url }}" target="_blank">
                                                <i class="fas fa-external-link-alt mr-1"></i>ver PDF
                                            </a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>{{-- /tab-content --}}

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <a href="{{ route('multa.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-save mr-1"></i> Salvar Multa
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

@stop

{{-- Modal busca de veiculo --}}
<div class="modal fade" id="modalBuscarVeiculo" tabindex="-1" role="dialog" aria-labelledby="modalBuscarVeiculoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="modalBuscarVeiculoLabel">
                    <i class="fas fa-car mr-2"></i>Buscar Veículo
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card card-outline card-secondary mb-3">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="fas fa-filter mr-1"></i> Filtros</h6>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body pb-1">
                        <div class="row">
                            <div class="col-md-3 form-group mb-2">
                                <label class="small mb-1">Placa</label>
                                <input type="text" id="fv_placa" class="form-control form-control-sm" placeholder="ABC-1234">
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label class="small mb-1">Nome / Identificação</label>
                                <input type="text" id="fv_nome" class="form-control form-control-sm" placeholder="Ex: Gol, HB20...">
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label class="small mb-1">Chassi</label>
                                <input type="text" id="fv_chassi" class="form-control form-control-sm" placeholder="Número do chassi">
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label class="small mb-1">Cor</label>
                                <input type="text" id="fv_cor" class="form-control form-control-sm" placeholder="Branco, Preto...">
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label class="small mb-1">Marca</label>
                                <select id="fv_marca_id" class="form-control form-control-sm">
                                    <option value="">Todas as marcas</option>
                                    @foreach($filtroMarcas as $id => $nome)
                                        <option value="{{ $id }}">{{ $nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label class="small mb-1">Modelo</label>
                                <input type="text" id="fv_modelo" class="form-control form-control-sm" placeholder="Nome do modelo">
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label class="small mb-1">Grupo de Frota</label>
                                <select id="fv_grupo_frota_id" class="form-control form-control-sm">
                                    <option value="">Todos os grupos</option>
                                    @foreach($filtroGrupos as $id => $nome)
                                        <option value="{{ $id }}">{{ $nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label class="small mb-1">Tipo de Frota</label>
                                <select id="fv_tipo_frota_id" class="form-control form-control-sm">
                                    <option value="">Todos os tipos</option>
                                    @foreach($filtroTipos as $id => $nome)
                                        <option value="{{ $id }}">{{ $nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label class="small mb-1">Status</label>
                                <select id="fv_status_veiculo_id" class="form-control form-control-sm">
                                    <option value="">Todos os status</option>
                                    @foreach($filtroStatus as $id => $nome)
                                        <option value="{{ $id }}">{{ $nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small mb-1">Ano modelo de</label>
                                <input type="number" id="fv_ano_de" class="form-control form-control-sm" placeholder="2015" min="1990" max="{{ date('Y')+1 }}">
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small mb-1">Ano modelo até</label>
                                <input type="number" id="fv_ano_ate" class="form-control form-control-sm" placeholder="{{ date('Y') }}" min="1990" max="{{ date('Y')+1 }}">
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small mb-1">KMs até</label>
                                <input type="number" id="fv_kms_ate" class="form-control form-control-sm" placeholder="100000" min="0">
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small mb-1">Ativo</label>
                                <select id="fv_ativo" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    <option value="1">Sim</option>
                                    <option value="0">Não</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end form-group mb-2">
                                <button type="button" class="btn btn-primary btn-sm btn-block" id="btnAplicarFiltroVeiculo">
                                    <i class="fas fa-search mr-1"></i> Buscar
                                </button>
                            </div>
                            <div class="col-md-2 d-flex align-items-end form-group mb-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimparFiltroVeiculo">
                                    <i class="fas fa-eraser mr-1"></i> Limpar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="modalVeiculoLoading" class="text-center d-none py-3">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Buscando...
                </div>
                <div id="modalVeiculoResultados"></div>
            </div>
        </div>
    </div>
</div>

@section('css')
<style>
.multa-section-title {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #888;
    border-bottom: 1px solid #eee;
    padding-bottom: .3rem;
    margin-bottom: 1rem;
}
.form-label-sm {
    font-size: .82rem;
    font-weight: 600;
    margin-bottom: .25rem;
    display: block;
    color: #444;
}
</style>
@stop

@push('js')
<script>
    // Preenche valor_final automaticamente
    document.querySelectorAll('[name=valor_original],[name=valor_desconto]').forEach(el => {
        el.addEventListener('input', () => {
            const orig = parseFloat(document.querySelector('[name=valor_original]').value) || 0;
            const desc = parseFloat(document.querySelector('[name=valor_desconto]').value) || 0;
            document.getElementById('valor_final').value = Math.max(0, orig - desc).toFixed(2);
        });
    });

    // Abrir aba com erro
    (function () {
        const errosCampos = @json(array_keys($errors->messages()));
        const mapaAbas = {
            'reserva_id': 'tab-infracao', 'veiculo_id': 'tab-infracao',
            'placa_momento': 'tab-infracao', 'numero_auto': 'tab-infracao',
            'orgao_autuador': 'tab-infracao', 'data_hora_infracao': 'tab-infracao',
            'data_vencimento': 'tab-infracao', 'local_infracao': 'tab-infracao',
            'descricao': 'tab-infracao',
            'valor_original': 'tab-valores', 'valor_desconto': 'tab-valores',
            'valor_final': 'tab-valores', 'pontos': 'tab-valores',
            'status_multa': 'tab-valores', 'status_desconto': 'tab-valores',
            'observacao': 'tab-valores',
            'anexo_comprovante': 'tab-documentos', 'anexo_pdf': 'tab-documentos',
        };
        for (const campo of errosCampos) {
            const aba = mapaAbas[campo];
            if (aba) {
                document.querySelector(`[href="#${aba}"]`)?.click();
                break;
            }
        }
    })();

    // Modal busca de veiculo
    (function () {
        const URL_BUSCAR  = '{{ route("veiculo.buscar") }}';
        const hiddenInput  = document.getElementById('veiculo_id_input');
        const displayInput = document.getElementById('veiculo_display');
        const btnLimpar    = document.getElementById('btnLimparVeiculo');

        function atualizarBotaoLimpar() {
            btnLimpar.classList.toggle('d-none', !hiddenInput.value);
        }
        atualizarBotaoLimpar();

        document.getElementById('btnBuscarVeiculo').addEventListener('click', function () {
            $('#modalBuscarVeiculo').modal('show');
            setTimeout(() => document.getElementById('fv_placa').focus(), 400);
            executarBusca();
        });

        btnLimpar.addEventListener('click', function () {
            hiddenInput.value  = '';
            displayInput.value = '';
            atualizarBotaoLimpar();
        });

        document.getElementById('btnAplicarFiltroVeiculo').addEventListener('click', executarBusca);
        document.getElementById('btnLimparFiltroVeiculo').addEventListener('click', function () {
            ['fv_placa','fv_nome','fv_chassi','fv_cor','fv_modelo','fv_ano_de','fv_ano_ate','fv_kms_ate'].forEach(id => {
                const el = document.getElementById(id); if (el) el.value = '';
            });
            ['fv_marca_id','fv_grupo_frota_id','fv_tipo_frota_id','fv_status_veiculo_id','fv_ativo'].forEach(id => {
                const el = document.getElementById(id); if (el) el.selectedIndex = 0;
            });
            document.getElementById('modalVeiculoResultados').innerHTML = '';
        });

        document.querySelectorAll('#fv_placa,#fv_nome,#fv_chassi,#fv_cor,#fv_modelo,#fv_ano_de,#fv_ano_ate,#fv_kms_ate').forEach(el => {
            el.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); executarBusca(); } });
        });

        function coletarFiltros() {
            const p = new URLSearchParams();
            const add = (id, key) => { const v = document.getElementById(id)?.value?.trim(); if (v) p.set(key, v); };
            add('fv_placa',            'placa');
            add('fv_nome',             'nome');
            add('fv_chassi',           'chassi');
            add('fv_cor',              'cor');
            add('fv_modelo',           'modelo');
            add('fv_marca_id',         'marca_id');
            add('fv_grupo_frota_id',   'grupo_frota_id');
            add('fv_tipo_frota_id',    'tipo_frota_id');
            add('fv_status_veiculo_id','status_veiculo_id');
            add('fv_ativo',            'ativo');
            add('fv_ano_de',           'ano_de');
            add('fv_ano_ate',          'ano_ate');
            add('fv_kms_ate',          'kms_ate');
            return p.toString();
        }

        function executarBusca() {
            const loading    = document.getElementById('modalVeiculoLoading');
            const resultados = document.getElementById('modalVeiculoResultados');
            loading.classList.remove('d-none');
            resultados.innerHTML = '';

            fetch(`${URL_BUSCAR}?${coletarFiltros()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                loading.classList.add('d-none');
                if (!data.veiculos || data.veiculos.length === 0) {
                    resultados.innerHTML = '<p class="text-muted text-center py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>Nenhum veículo encontrado com os filtros informados.</p>';
                    return;
                }
                const badge = `<span class="badge badge-secondary mb-2">${data.veiculos.length} resultado(s)</span>`;
                let html = `${badge}<div class="table-responsive"><table class="table table-sm table-hover table-bordered" style="font-size:.82rem">`;
                html += `<thead class="thead-light"><tr>
                    <th>Placa</th><th>Nome</th><th>Marca</th><th>Modelo</th>
                    <th>Grupo</th><th>Tipo</th><th>Status</th>
                    <th>Ano Fab.</th><th>Ano Mod.</th><th>Cor</th>
                    <th>KMs</th><th>Ativo</th><th>Estação</th>
                    <th class="text-center">Ação</th>
                </tr></thead><tbody>`;
                data.veiculos.forEach(v => {
                    const label = `${v.nome}${v.placa ? ' — ' + v.placa : ''}`;
                    html += `<tr>
                        <td><strong>${v.placa || '—'}</strong></td>
                        <td>${v.nome}</td>
                        <td>${v.marca || '—'}</td>
                        <td>${v.modelo || '—'}</td>
                        <td>${v.grupo || '—'}</td>
                        <td>${v.tipo || '—'}</td>
                        <td><span class="badge badge-info">${v.status || '—'}</span></td>
                        <td>${v.ano_fab || '—'}</td>
                        <td>${v.ano_mod || '—'}</td>
                        <td>${v.cor || '—'}</td>
                        <td>${v.kms || '—'}</td>
                        <td>${v.ativo}</td>
                        <td>${v.estacao || '—'}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-success btn-selecionar-veiculo"
                                data-id="${v.id}" data-label="${label.replace(/"/g,'&quot;')}" data-placa="${v.placa || ''}">
                                <i class="fas fa-check"></i>
                            </button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
                resultados.innerHTML = html;

                resultados.querySelectorAll('.btn-selecionar-veiculo').forEach(btn => {
                    btn.addEventListener('click', function () {
                        hiddenInput.value  = this.dataset.id;
                        displayInput.value = this.dataset.label;
                        const placaInput = document.querySelector('[name=placa_momento]');
                        if (placaInput && !placaInput.value && this.dataset.placa) {
                            placaInput.value = this.dataset.placa;
                        }
                        atualizarBotaoLimpar();
                        $('#modalBuscarVeiculo').modal('hide');
                    });
                });
            })
            .catch(() => {
                loading.classList.add('d-none');
                resultados.innerHTML = '<p class="text-danger text-center py-3"><i class="fas fa-exclamation-triangle mr-1"></i>Erro ao buscar veículos.</p>';
            });
        }
    })();
</script>
@endpush
