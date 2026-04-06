@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h3 class="card-title mb-0"><i class="fas fa-wrench mr-2"></i>Avarias e correções</h3>
            <div class="btn-group btn-group-sm" role="group">
                <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'pendentes']) }}"
                   class="btn btn-{{ $filtro === 'pendentes' ? 'primary' : 'outline-primary' }}">Pendentes</a>
                <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'corrigidos']) }}"
                   class="btn btn-{{ $filtro === 'corrigidos' ? 'primary' : 'outline-primary' }}">Corrigidas</a>
                <a href="{{ route('veiculo-dano.monitoramento', ['filtro' => 'todos']) }}"
                   class="btn btn-{{ $filtro === 'todos' ? 'primary' : 'outline-primary' }}">Todas</a>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0 text-nowrap">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Veículo</th>
                        <th>Reserva</th>
                        <th>Local</th>
                        <th>Tipo</th>
                        <th>Observações</th>
                        <th>Correção</th>
                        <th class="text-right">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($danos as $dano)
                        <tr>
                            <td>{{ $dano->id }}</td>
                            <td>{{ $dano->veiculo->nome ?? ('#' . $dano->veiculo_id) }}</td>
                            <td>@if($dano->reserva_id)
                                    <a href="{{ route('reserva.show', $dano->reserva_id) }}">#{{ $dano->reserva_id }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ strtoupper($dano->local_dano ?? '') }}</td>
                            <td>{{ $dano->tipo_dano }}</td>
                            <td style="max-width:220px;white-space:normal;">{{ \Illuminate\Support\Str::limit($dano->observacoes, 120) }}</td>
                            <td style="max-width:260px;white-space:normal;">
                                @if($dano->correcao_data)
                                    <span class="text-success"><i class="fas fa-check-circle"></i>
                                        {{ \Illuminate\Support\Str::limit($dano->correcao_descricao, 160) }}
                                        <br><small class="text-muted">{{ $dano->correcao_data->format('d/m/Y H:i') }}</small>
                                    </span>
                                @else
                                    <span class="badge badge-warning">Pendente</span>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">
                                @if(!$dano->correcao_data)
                                    <a href="{{ route('ordem-servico.create', ['veiculo_id' => $dano->veiculo_id, 'dano_ids' => [$dano->id]]) }}"
                                       class="btn btn-sm btn-primary mb-1 mb-md-0">
                                        <i class="fas fa-file-alt"></i> Abrir OS
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success btn-registrar-correcao"
                                            data-id="{{ $dano->id }}"
                                            data-veiculo="{{ e($dano->veiculo->nome ?? $dano->veiculo_id) }}">
                                        <i class="fas fa-plus"></i> Correção
                                    </button>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Nenhum registro encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($danos->hasPages())
            <div class="card-footer">{{ $danos->links() }}</div>
        @endif
    </div>

    <div class="modal fade" id="modalCorrecao" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white"><i class="fas fa-wrench mr-2"></i>Registrar correção</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">Veículo: <strong id="correcao_veiculo_label"></strong></p>
                    <div class="form-group">
                        <label for="correcao_descricao">Descrição do serviço / peças / observação da correção <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="correcao_descricao" rows="6" required
                                  placeholder="Ex: Para-choque pintado na oficina X, substituição do retrovisor…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btn_salvar_correcao">
                        <i class="fas fa-save"></i> Salvar correção
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="{{ asset('js/sweetAlert.min.js') }}"></script>
<script>
(function () {
    let danoIdAtual = null;
    const modal = $('#modalCorrecao');
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelectorAll('.btn-registrar-correcao').forEach(btn => {
        btn.addEventListener('click', function () {
            danoIdAtual = this.getAttribute('data-id');
            document.getElementById('correcao_veiculo_label').textContent = this.getAttribute('data-veiculo') || '';
            document.getElementById('correcao_descricao').value = '';
            modal.modal('show');
        });
    });

    document.getElementById('btn_salvar_correcao').addEventListener('click', function () {
        const desc = document.getElementById('correcao_descricao').value.trim();
        if (!desc) {
            Swal.fire({ icon: 'warning', title: 'Informe a descrição da correção.' });
            return;
        }
        if (!danoIdAtual) return;

        const btn = this;
        btn.disabled = true;
        const url = "{{ route('veiculo-dano.correcao', ['id' => '__DANO_ID__']) }}".replace('__DANO_ID__', danoIdAtual);
        $.ajax({
            url,
            type: 'POST',
            data: { correcao_descricao: desc, _token: token },
            success: function (res) {
                modal.modal('hide');
                Swal.fire({ icon: 'success', title: res.message || 'Salvo!', timer: 1600, showConfirmButton: false });
                setTimeout(() => window.location.reload(), 600);
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Erro ao salvar.';
                Swal.fire({ icon: 'error', title: msg });
            },
            complete: function () {
                btn.disabled = false;
            }
        });
    });
})();
</script>
@stop
