@props([
    'empresa',
    'contratos',
    'planosContrato',
    'razaoSocialPadrao',
    'cnpjPadrao',
    'emailPadrao',
    'telefonePadrao',
    'enderecoPadrao',
    'representantePadrao',
    'quantidadeVeiculosPadrao',
    'valorPadrao',
])

<div class="rs-card">
    <div class="rs-card__header">
        <i class="fas fa-file-signature rs-card__icon"></i>
        <h2>Contratos da Empresa</h2>
        <span class="rs-badge-count">{{ $contratos->count() }}</span>
        <button type="button" class="rs-btn rs-btn--primary ml-auto" data-toggle="modal" data-target="#modalGerarContratoEmpresa">
            <i class="fas fa-file-contract"></i> Gerar contrato
        </button>
    </div>

    @if($contratos->isEmpty())
        <div class="p-3">
            <div class="rs-empty">
                <i class="fas fa-file-alt"></i>
                <span>Nenhum contrato registrado para esta empresa.</span>
            </div>
        </div>
    @else
        <div class="rs-contratos-grid">
            @foreach($contratos as $contrato)
                @php $contratoAssinado = strtolower((string) $contrato->status) === 'assinado' || ! empty($contrato->assinado_em); @endphp
                <div class="rs-contrato-card">
                    <div class="rs-contrato-top">
                        <div>
                            <div class="rs-contrato-numero">{{ $contrato->numero }}</div>
                            <div class="rs-contrato-titulo">{{ $contrato->titulo }}</div>
                        </div>
                        <span class="badge badge-{{ $contratoAssinado ? 'success' : 'info' }}">
                            {{ $contratoAssinado ? 'Assinado' : ucfirst($contrato->status) }}
                        </span>
                    </div>
                    <div class="rs-contrato-meta">Gerado em {{ $contrato->gerado_em?->format('d/m/Y H:i') ?? '-' }}</div>
                    <div class="rs-contrato-actions">
                        @if($contrato->arquivo_pdf_path)
                            <a href="{{ route('empresa.contratos.download', ['empresa' => $empresa->id, 'contrato' => $contrato->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download mr-1"></i> {{$contratoAssinado ? 'Baixar contrato assinado' : 'Baixar contrato'}}
                            </a>
                        @endif
                        @if(! $contratoAssinado)
                            <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#modalAssinarContrato{{ $contrato->id }}">
                                <i class="fas fa-file-upload mr-1"></i> Inserir PDF assinado
                            </button>
                            <form action="{{ route('empresa.contratos.remover', ['empresa' => $empresa->id, 'contrato' => $contrato->id]) }}" method="POST" onsubmit="return confirm('Deseja remover este contrato?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash mr-1"></i> Remover
                                </button>
                            </form>
                        @else
                            <span class="small text-muted align-self-center">Contrato assinado. Apenas download disponível.</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@foreach($contratos as $contrato)
    <div class="modal fade" id="modalAssinarContrato{{ $contrato->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('empresa.contratos.assinar', ['empresa' => $empresa->id, 'contrato' => $contrato->id]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title mb-0"><i class="fas fa-file-signature mr-2"></i>Inserir PDF assinado</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Contrato {{ $contrato->numero }}</p>
                        <div class="form-group">
                            <label>Arquivo PDF assinado</label>
                            <input type="file" name="pdf_assinado" class="form-control" accept="application/pdf" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload mr-1"></i> Enviar PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="modalGerarContratoEmpresa" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form action="{{ route('empresa.contratos.gerar', $empresa->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0"><i class="fas fa-file-contract mr-2"></i>Gerar contrato</h5>
                        <small class="text-muted">Preencha os dados do cliente e selecione o plano.</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label>Razão social</label>
                            <input type="text" name="razao_social" class="form-control" value="{{ old('razao_social', $razaoSocialPadrao) }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>CNPJ do cliente</label>
                            <input type="text" name="cnpj_cliente" class="form-control" value="{{ old('cnpj_cliente', $cnpjPadrao) }}" required>
                        </div>
                        <div class="col-md-8 form-group">
                            <label>E-mail</label>
                            <input type="email" name="email_cliente" class="form-control" value="{{ old('email_cliente', $emailPadrao) }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Telefone</label>
                            <input type="text" name="telefone_cliente" id="telefoneClienteContrato" class="form-control" value="{{ old('telefone_cliente', $telefonePadrao) }}">
                        </div>
                        
                        <div class="col-md-8 form-group">
                            <label>Endereço</label>
                            <input type="text" name="endereco_cliente" class="form-control" value="{{ old('endereco_cliente', $enderecoPadrao) }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Nome do representante</label>
                            <input type="text" name="representante_nome" class="form-control" value="{{ old('representante_nome', $representantePadrao) }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Plano</label>
                            <select name="plano_id" id="empresaContratoPlano" class="form-control" required>
                                <option value="">Selecione um plano</option>
                                @foreach($planosContrato as $pl)
                                    <option value="{{ $pl->id }}" data-valor="{{ $pl->valor_mensal ?: $pl->valor_anual }}" data-veiculos="{{ $pl->limite_veiculos ?? 20 }}">
                                        {{ $pl->nome }} - R$ {{ number_format((float) ($pl->valor_mensal ?: $pl->valor_anual), 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Valor</label>
                            <input type="number" step="0.01" min="0" name="valor" id="empresaContratoValor" class="form-control" value="{{ old('valor', $valorPadrao) }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Quantidade de veículos</label>
                            <input type="number" min="1" step="1" name="quantidade_veiculos" id="quantidadeVeiculosContrato" class="form-control" value="{{ old('quantidade_veiculos', $quantidadeVeiculosPadrao) }}" required>
                        </div>
                    </div>
                    <div class="alert alert-light border mb-0">
                        O valor pode ser ajustado manualmente antes de gerar o PDF.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnGerarContratoEmpresa" class="btn btn-primary">
                        <i class="fas fa-file-pdf mr-1"></i> Gerar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
