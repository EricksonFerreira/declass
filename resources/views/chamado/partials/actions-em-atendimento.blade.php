<div class="row">
    <div class="col-md-6 mb-3">
        <form method="POST" action="{{ route('chamado.atendimento.mensagem', $chamado) }}" enctype="multipart/form-data">
            @csrf
            <div class="info-card h-100">
                <h5>Enviar mensagem</h5>
                <div class="form-group">
                    <label class="text-muted text-sm">Mensagem</label>
                    <textarea name="mensagem" class="form-control" rows="5" required placeholder="Escreva a mensagem para o solicitante..."></textarea>
                </div>
                <div class="form-group">
                    <label class="text-muted text-sm">Anexos</label>
                    <input type="file" name="anexos[]" class="form-control" multiple>
                </div>
                <button class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane mr-2"></i> Enviar mensagem
                </button>
            </div>
        </form>
    </div>

    <div class="col-md-6 mb-3">
        <form method="POST" action="{{ route('chamado.atendimento.status', $chamado) }}" enctype="multipart/form-data">
            @csrf
            <div class="info-card h-100">
                <h5>Alterar status</h5>
                <div class="form-group">
                    <label class="text-muted text-sm">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="aguardando_solicitante">Aguardar solicitante</option>
                        <option value="concluido">Concluir</option>
                        <option value="cancelado">Cancelar</option>
                        <option value="em_atendimento">Em atendimento</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="text-muted text-sm">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3" required placeholder="Explique a alteração de status..."></textarea>
                </div>
                <div class="form-group">
                    <label class="text-muted text-sm">Anexos</label>
                    <input type="file" name="anexos[]" class="form-control" multiple>
                </div>
                <button class="btn btn-warning btn-block">
                    <i class="fas fa-exchange-alt mr-2"></i> Salvar alteração
                </button>
            </div>
        </form>
    </div>

    <div class="col-md-6 mb-3">
        <form method="POST" action="{{ route('chamado.atendimento.transferir', $chamado) }}">
            @csrf
            <div class="info-card h-100">
                <h5>Transferir atendimento</h5>
                <div class="form-group">
                    <label class="text-muted text-sm">Novo superadmin</label>
                    <select name="responsavel_user_id" class="form-control" required>
                        <option value="">Selecione</option>
                        @foreach($superadmins ?? [] as $superadmin)
                            <option value="{{ $superadmin->id }}">{{ $superadmin->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="text-muted text-sm">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3" required placeholder="Explique o motivo da transferência..."></textarea>
                </div>
                <button class="btn btn-primary btn-block">
                    <i class="fas fa-exchange-alt mr-2"></i> Transferir
                </button>
            </div>
        </form>
    </div>
</div>
