@extends('adminlte::page')

@section('title', 'Ajuste de Frota — Downgrade')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px;">
        <div>
            <h1 class="m-0 text-dark" style="font-size:1.3rem;font-weight:700;">Ajuste de Frota</h1>
            <small class="text-muted">
                O plano <strong>{{ $plano->nome }}</strong> permite até
                <strong>{{ $plano->limite_veiculos_texto }}</strong> veículo(s)
            </small>
        </div>
        <a href="{{ route('assinatura.meu-plano') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Cancelar
        </a>
    </div>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="callout callout-warning mb-3">
                <h5 class="mb-1" style="font-size:.95rem;font-weight:700;">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Ação necessária
                </h5>
                <p class="mb-0" style="font-size:.875rem;">
                    Sua frota ativa tem <strong>{{ $todos->count() }}</strong> veículo(s).
                    O plano <strong>{{ $plano->nome }}</strong> permite até
                    <strong>{{ $plano->limite_veiculos }}</strong>.
                    Selecione ao menos <strong>{{ $excedentes->count() }}</strong> veículo(s) para desativar.
                    Os veículos desativados <strong>não são excluídos</strong> — podem ser reativados com upgrade.
                </p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    @foreach ($errors->all() as $e)
                        <div><i class="fas fa-exclamation-circle mr-1"></i> {{ $e }}</div>
                    @endforeach
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        Veículos ativos
                        <span class="badge badge-primary ml-1">{{ $todos->count() }}</span>
                    </h3>
                    <span class="text-muted" style="font-size:.8rem;">
                        Desativar selecionados:
                        <strong id="contadorBadge" class="text-danger">0</strong>
                        / {{ $excedentes->count() }} necessários
                    </span>
                </div>

                <form action="{{ route('assinatura.downgrade.confirmar') }}" method="POST" id="formDowngrade">
                    @csrf
                    <div class="card-body p-0">
                        <table class="table table-hover table-sm mb-0" style="font-size:13px;">
                            <thead class="thead-light">
                                <tr>
                                    <th class="pl-3" style="width:36px;">
                                        <input type="checkbox" id="selectAll" title="Selecionar todos">
                                    </th>
                                    <th>Veículo</th>
                                    <th>Grupo</th>
                                    <th>Placa</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($todos as $v)
                                    @php $excede = $excedentes->contains('id', $v->id); @endphp
                                    <tr class="veiculo-row {{ $excede ? 'table-warning' : '' }}"
                                        data-id="{{ $v->id }}"
                                        onclick="toggleVeiculo({{ $v->id }})"
                                        style="cursor:pointer;">
                                        <td class="pl-3 align-middle" onclick="event.stopPropagation()">
                                            <input type="checkbox" name="veiculos_desativar[]"
                                                   value="{{ $v->id }}"
                                                   id="vch{{ $v->id }}"
                                                   onchange="atualizarContador()"
                                                   class="veiculo-check"
                                                   style="accent-color:#dc2626;">
                                        </td>
                                        <td class="align-middle font-weight-bold">
                                            {{ $v->nome }}
                                            @if ($excede)
                                                <span class="badge badge-warning ml-1" style="font-size:.65rem;">excedente</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-muted">{{ $v->grupoFrota?->nome ?? '—' }}</td>
                                        <td class="align-middle">
                                            @if ($v->placa_licenca)
                                                <span class="badge badge-light border">{{ $v->placa_licenca }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-success">Ativo</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer py-2 d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
                        <div id="errVeiculos" class="text-danger" style="font-size:.83rem;display:none;">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            <span id="errVeiculosMsg"></span>
                        </div>
                        <div class="ml-auto d-flex" style="gap:.5rem;">
                            <a href="{{ route('assinatura.meu-plano') }}" class="btn btn-secondary btn-sm">Cancelar</a>
                            <button type="button" onclick="confirmarDowngrade()" class="btn btn-danger btn-sm">
                                <i class="fas fa-check mr-1"></i> Confirmar Downgrade
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
const necessario = {{ $excedentes->count() }};
const limite     = {{ $plano->limite_veiculos ?? 9999 }};
const totalAtivo = {{ $todos->count() }};

function toggleVeiculo(id) {
    const cb = document.getElementById('vch' + id);
    cb.checked = !cb.checked;
    atualizarContador();
}

function atualizarContador() {
    const n = document.querySelectorAll('.veiculo-check:checked').length;
    document.getElementById('contadorBadge').textContent = n;

    document.querySelectorAll('.veiculo-row').forEach(row => {
        const cb = row.querySelector('.veiculo-check');
        row.classList.toggle('table-danger', cb.checked);
        row.classList.toggle('table-warning', !cb.checked && row.dataset.excede === '1');
    });
}

document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.veiculo-check').forEach(cb => { cb.checked = this.checked; });
    atualizarContador();
});

function confirmarDowngrade() {
    const selecionados = document.querySelectorAll('.veiculo-check:checked').length;
    const restantes    = totalAtivo - selecionados;

    if (restantes > limite) {
        const faltam = restantes - limite;
        document.getElementById('errVeiculosMsg').textContent =
            `Desative mais ${faltam} veículo(s). Com a seleção atual, ${restantes} ficariam ativos (limite: ${limite}).`;
        document.getElementById('errVeiculos').style.display = 'block';
        return;
    }

    document.getElementById('errVeiculos').style.display = 'none';
    document.getElementById('formDowngrade').submit();
}
</script>
@endpush
