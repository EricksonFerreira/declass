@php
    $statusSlug = $chamado->status?->slug;
    $permiteMensagem = ! in_array($statusSlug, ['cancelado', 'concluido'], true);
@endphp
@if($permiteMensagem)
    <div class="col-lg-4 mb-3">
        <form method="POST" action="{{ route('chamado.meus.mensagem', $chamado) }}" enctype="multipart/form-data">
            @csrf
            <div class="info-card">
                <h5>Enviar mensagem</h5>
                <div class="form-group">
                    <label class="text-muted text-sm">Mensagem</label>
                    <textarea name="mensagem" class="form-control" rows="4" placeholder="Escreva sua mensagem para o suporte..."></textarea>
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
    <div class="col-lg-4 mb-3">
        <form method="POST" action="{{ route('chamado.meus.mensagem', $chamado) }}" enctype="multipart/form-data">
            @csrf
            <div class="info-card">
                <h5>Enviar apenas anexo</h5>
                <div class="form-group">
                    <label class="text-muted text-sm">Arquivos</label>
                    <input type="file" name="anexos[]" class="form-control" multiple required>
                </div>
                <button class="btn btn-outline-primary btn-block">
                    <i class="fas fa-paperclip mr-2"></i> Enviar anexo
                </button>
            </div>
        </form>
    </div>
@endif
