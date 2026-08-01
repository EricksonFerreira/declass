<div class="row">
    <div class="col-md-4 mb-3">
        <form method="POST" action="{{ route('chamado.atendimento.assumir', $chamado) }}">
            @csrf
            <div class="info-card">
                <h5>Atribuir e assumir atendimento</h5>
                <div class="form-group">
                    <label class="text-muted text-sm">Superadmin responsável</label>
                    <select name="responsavel_user_id" class="form-control" required>
                        <option value="">Selecione</option>
                        @foreach($superadmins ?? [] as $superadmin)
                            <option value="{{ $superadmin->id }}">{{ $superadmin->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary btn-block">
                    <i class="fas fa-user-check mr-2"></i> Assumir atendimento
                </button>
            </div>
        </form>
    </div>

    <div class="col-md-4 mb-3">
        <form method="POST" action="{{ route('chamado.atendimento.concluir', $chamado) }}" enctype="multipart/form-data">
            @csrf
            <div class="info-card">
                <h5>Concluir chamado</h5>
                <div class="form-group">
                    <label class="text-muted text-sm">Justificativa / solução</label>
                    <textarea name="justificativa" class="form-control" rows="4" required placeholder="Descreva o que foi feito e o desfecho..."></textarea>
                </div>
                <div class="form-group">
                    <label class="text-muted text-sm">Imagens</label>
                    <input type="file" name="anexos[]" class="form-control" multiple accept="image/*">
                </div>
                <button class="btn btn-success btn-block">
                    <i class="fas fa-check mr-2"></i> Concluir
                </button>
            </div>
        </form>
    </div>

    <div class="col-md-4 mb-3">
        <form method="POST" action="{{ route('chamado.atendimento.cancelar', $chamado) }}" enctype="multipart/form-data">
            @csrf
            <div class="info-card">
                <h5>Cancelar chamado</h5>
                <div class="form-group">
                    <label class="text-muted text-sm">Justificativa</label>
                    <textarea name="cancelamento_justificativa" class="form-control" rows="4" required placeholder="Explique o motivo do cancelamento..."></textarea>
                </div>
                <div class="form-group">
                    <label class="text-muted text-sm">Anexos</label>
                    <input type="file" name="anexos[]" class="form-control" multiple>
                </div>
                <button class="btn btn-outline-danger btn-block">
                    <i class="fas fa-ban mr-2"></i> Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
