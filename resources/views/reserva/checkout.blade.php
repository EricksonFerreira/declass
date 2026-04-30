@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')

    <div class="col-12 px-0">
        <div class="ci-card co-checkout-card">

            <div class="ci-card-header co-checkout-header">
                <div class="ci-header-left">
                    <div class="ci-header-icon co-checkout-icon">
                        <i class="fas fa-undo-alt"></i>
                    </div>
                    <div>
                        <div class="ci-header-eyebrow">Check-out / Devolução</div>
                        <h4 class="ci-header-title">Reserva #{{ $reserva->id ?? '' }}</h4>
                        <p class="ci-header-sub">
                            <i class="fas fa-user mr-1"></i>{{ $reserva->usuario->nome ?? 'Cliente' }}
                            &nbsp;&middot;&nbsp;
                            <i class="fas fa-car mr-1"></i>{{ $reserva->veiculo->nome ?? $reserva->modelos->nome ?? 'Veículo' }}
                        </p>
                    </div>
                </div>
                <div class="ci-header-right">
                    @php
                        $dataInicio = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y') : null;
                        $dataFim    = $reserva->data_fim    ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y')    : null;
                    @endphp
                    @if($dataInicio)
                        <div class="ci-date-badge">
                            <i class="fas fa-calendar-alt"></i>
                            <span>{{ $dataInicio }}@if($dataFim) &rarr; {{ $dataFim }}@endif</span>
                        </div>
                    @endif
                    <a href="{{ route('reserva.show', $reserva->id) }}" class="ci-btn-back">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>

            <div class="ci-body">

                <input type="hidden" id="reserva_id" name="reserva_id" value="{{ $reserva->id }}">
                <input type="hidden" id="veiculo" name="veiculo" value="{{ $reserva->veiculo_id }}">

                <div class="step-indicator" role="tablist">
                    @php
                        $steps = [
                            ['label' => 'Dados da devolução', 'icon' => 'fas fa-car-side'],
                            ['label' => 'Vistoria do veículo', 'icon' => 'fas fa-search'],
                            ['label' => 'Observações', 'icon' => 'fas fa-comment-alt'],
                        ];
                    @endphp
                    @foreach($steps as $i => $step)
                        <button type="button" class="step-btn {{ $i === 0 ? 'active' : '' }}" data-step-target="{{ $i + 1 }}">
                            <span class="step-num">{{ $i + 1 }}</span>
                            <span class="step-label-text">
                                <i class="{{ $step['icon'] }}"></i>
                                {{ $step['label'] }}
                            </span>
                        </button>
                        @if($i < count($steps) - 1)
                            <div class="step-divider"></div>
                        @endif
                    @endforeach
                </div>

                <section class="wizard-step active" data-step="1">

                    <form id="form_dados_devolucao">
                        <div class="ci-block">
                            <div class="ci-block-label">
                                <i class="fas fa-car"></i>
                                <div>
                                    <strong>Veículo vinculado</strong>
                                    <span>Este é o veículo em uso nesta reserva</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <p class="mb-2"><strong>{{ $reserva->veiculo->nome ?? ('#' . $reserva->veiculo_id) }}</strong></p>
                                    @if($reserva->contratoCheckin)
                                        <p class="text-muted small mb-0">
                                            Referência check-in: KM {{ $reserva->contratoCheckin->quilometragem_atual ?? '—' }},
                                            combustível {{ $reserva->contratoCheckin->nivel_combustivel ?? '—' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="ci-block">
                            <div class="ci-block-label">
                                <i class="fas fa-tachometer-alt"></i>
                                <div>
                                    <strong>Condição na devolução</strong>
                                    <span>Quilometragem, nível de combustível e momento da devolução</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-lg-3">
                                    <label for="quilometragem_devolucao">
                                        Quilometragem na devolução <span class="req">*</span>
                                        @if($reserva->contratoCheckin && $reserva->contratoCheckin->quilometragem_atual !== null)
                                            <small class="text-muted">(KM inicial: {{ $reserva->contratoCheckin->quilometragem_atual }})</small>
                                        @endif
                                    </label>
                                    <input type="number" required class="form-control form-control-sm" id="quilometragem_devolucao" name="quilometragem_devolucao" placeholder="Ex: 45800" min="{{ (float) ($reserva->contratoCheckin->quilometragem_atual ?? 0) }}">
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="combustivel_devolucao">Nível de combustível <span class="req">*</span></label>
                                    <select class="form-control form-control-sm" id="combustivel_devolucao" required name="combustivel_devolucao">
                                        <option value="" disabled selected>Selecione</option>
                                        <option>Reserva</option>
                                        <option>1/8</option>
                                        <option>1/4</option>
                                        <option>3/8</option>
                                        <option>1/2</option>
                                        <option>5/8</option>
                                        <option>3/4</option>
                                        <option>7/8</option>
                                        <option>Cheio</option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="data_devolucao">Data de devolução <span class="req">*</span></label>
                                    <input type="date" class="form-control form-control-sm" required id="data_devolucao" name="data_devolucao" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="hora_devolucao">Hora de devolução <span class="req">*</span></label>
                                    <input type="time" class="form-control form-control-sm" required id="hora_devolucao" name="hora_devolucao" value="{{ date('H:i') }}">
                                </div>
                            </div>
                        </div>
                    </form>
                </section>

                <section class="wizard-step" data-step="2">
        
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-car-crash"></i>
                            <div>
                                <strong>Vistas do veículo</strong>
                                <span>Toque na vista para registrar um dano</span>
                            </div>
                        </div>
                        <div class="car-views-grid">
                            @php
                                $vistas = [
                                    ['nome' => 'DIREITA',   'arquivo' => 'direita.jpg',   'icon' => 'fas fa-arrow-right'],
                                    ['nome' => 'ESQUERDA',  'arquivo' => 'esquerda.jpg',  'icon' => 'fas fa-arrow-left'],
                                    ['nome' => 'CIMA',      'arquivo' => 'cima.jpg',       'icon' => 'fas fa-arrow-up'],
                                    ['nome' => 'FRENTE',    'arquivo' => 'frente.jpg',     'icon' => 'fas fa-dot-circle'],
                                    ['nome' => 'ATRÁS',     'arquivo' => 'atras.jpg',      'icon' => 'fas fa-circle'],
                                ];
                            @endphp
                            @foreach($vistas as $vista)
                                <div class="car-view-card" onclick="setNomeDano('{{ $vista['nome'] }}')" data-toggle="modal" data-target="#detalhesDanoModal">
                                    <div class="car-view-card__img">
                                        <img src="{{ asset('assets/imgs/carro/' . $vista['arquivo']) }}" alt="{{ $vista['nome'] }}">
                                        <div class="car-view-card__overlay">
                                            <i class="fas fa-plus-circle"></i>
                                            <span>Registrar dano</span>
                                        </div>
                                    </div>
                                    <div class="car-view-card__label">
                                        <i class="{{ $vista['icon'] }}"></i>
                                        {{ $vista['nome'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="ci-damages-grid">
                        <div class="ci-block ci-block--no-mb">
                            <div class="ci-block-label">
                                <i class="fas fa-history"></i>
                                <div>
                                    <strong>Histórico no veículo</strong>
                                    <span>Avarias de outras reservas / correções registradas</span>
                                </div>
                            </div>
                            <div class="damage-table-wrap">
                                <table class="damage-table">
                                    <thead>
                                        <tr>
                                            <th>Localização</th>
                                            <th>Descrição</th>
                                            <th>Correção</th>
                                            <th>Imagem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="danos_existentes">
                                        <tr>
                                            <td colspan="4" class="damage-empty">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando…
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="ci-block ci-block--no-mb ci-block--amber">
                            <div class="ci-block-label">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>Nesta reserva</strong>
                                    <span>Avarias já registradas no check-in ou nesta devolução</span>
                                </div>
                            </div>
                            <div class="damage-table-wrap">
                                <table class="damage-table">
                                    <thead>
                                        <tr>
                                            <th>Localização</th>
                                            <th>Descrição</th>
                                            <th>Imagem</th>
                                        </tr>
                                    </thead>
                                    <tbody id="danos_detectados">
                                        <tr>
                                            <td colspan="3" class="damage-empty">
                                                <i class="fas fa-spinner fa-spin"></i> Carregando…
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @include('reserva.components.modal_dano_checkin')
                </section>

                <section class="wizard-step" data-step="3">
       
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-pen-alt"></i>
                            <div>
                                <strong>Notas da devolução</strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="notas_devolucao">Notas (contrato / cliente)</label>
                                <textarea class="form-control form-control-sm" id="notas_devolucao" name="notas_devolucao" rows="5" placeholder="Ex: Arranhão novo no para-lama…"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="observacoes_devolucao">Observações internas</label>
                                <textarea class="form-control form-control-sm" id="observacoes_devolucao" name="observacoes_devolucao" rows="5" placeholder="Ex: Funcionário que recebeu o veículo…"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="ci-summary-card co-summary-checkout">
                        <div class="ci-summary-card__icon">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <div class="ci-summary-card__content">
                            <strong>Concluir check-out</strong>
                            <p>Revise o passo 1 (dados da devolução). Esta ação finaliza a reserva e registra a movimentação de devolução.</p>
                        </div>
                        <button type="button" class="ci-btn-create co-btn-finalizar" id="btn_finalizar_checkout">
                            <i class="fas fa-check-circle"></i>
                            Concluir check-out
                        </button>
                    </div>
                </section>

                <div class="wizard-actions">
                    <button type="button" class="btn-action btn-action-secondary" id="wizardPrev">
                        <i class="fas fa-arrow-left mr-1"></i>Voltar
                    </button>
                    <div class="wizard-actions-right">
                        <button type="button" class="btn-action btn-primary" id="wizardNext">
                            Continuar<i class="fas fa-arrow-right ml-1"></i>
                        </button>
                        <button type="button" class="btn-action btn-action-success d-none" id="wizardCreateBtn">
                            <i class="fas fa-check-circle mr-1"></i>Concluir check-out
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
<style>
    @include('reserva.partials.vistoria_reserva_estilos_inner')

    /* Header bar verde */
    .co-checkout-header::after {
        background: linear-gradient(90deg, #059669, #10b981, #0ea5e9);
    }
    .co-checkout-icon { color: #d1fae5; }

    /* Card de conclusão */
    .co-summary-checkout .ci-summary-card__icon {
        background: linear-gradient(135deg, #059669, #10b981);
        box-shadow: 0 6px 18px rgba(5,150,105,.35);
    }
    .co-btn-finalizar {
        background: linear-gradient(135deg, #059669, #10b981) !important;
        box-shadow: 0 6px 18px rgba(5,150,105,.35) !important;
    }
</style>
@stop

@push('js')
<script src="{{ asset('js/select2.min.js') }}"></script>
<script src="{{ asset('js/sweetAlert.min.js') }}"></script>
<script>
(function () {

    $(document).ready(function () {
        $('.select2ci-modal').select2({ dropdownParent: $('#detalhesDanoModal') });

        $('#detalhesDanoModal').on('show.bs.modal', function () {
            const km = document.getElementById('quilometragem_devolucao')?.value;
            const q = document.getElementById('quilometragem_dano');
            if (q && km) q.value = km;
        });

        obterDanosDetectados();
        obterDanosExistentes();
    });

    const stepBtns   = Array.from(document.querySelectorAll('.step-btn'));
    const stepPanels = Array.from(document.querySelectorAll('.wizard-step[data-step]'));
    const prevBtn    = document.getElementById('wizardPrev');
    const nextBtn    = document.getElementById('wizardNext');
    const createBtn  = document.getElementById('wizardCreateBtn');
    let current      = 1;
    const lastStep   = stepPanels.length;

    function showStep(n) {
        current = Math.min(Math.max(n, 1), lastStep);

        stepPanels.forEach(panel => {
            panel.classList.toggle('active', Number(panel.dataset.step) === current);
        });

        stepBtns.forEach(btn => {
            const t = Number(btn.dataset.stepTarget);
            btn.classList.toggle('active', t === current);
            btn.classList.toggle('completed', t < current);
        });

        prevBtn.style.visibility = current === 1 ? 'hidden' : 'visible';
        nextBtn.style.display    = current === lastStep ? 'none' : 'inline-flex';
        createBtn.classList.toggle('d-none', current !== lastStep);

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    stepBtns.forEach(btn => {
        btn.addEventListener('click', () => showStep(Number(btn.dataset.stepTarget)));
    });

    prevBtn.addEventListener('click', () => showStep(current - 1));
    nextBtn.addEventListener('click', () => showStep(current + 1));

    function triggerFinalizar() {
        document.getElementById('form_dados_devolucao').dispatchEvent(new Event('submit'));
    }

    document.getElementById('btn_finalizar_checkout')?.addEventListener('click', triggerFinalizar);
    createBtn?.addEventListener('click', triggerFinalizar);

    document.getElementById('form_dados_devolucao').addEventListener('submit', function (event) {
        event.preventDefault();

        const kmInicialRaw = @json($reserva->contratoCheckin?->quilometragem_atual);
        const kmInicial = kmInicialRaw === null ? null : Number(kmInicialRaw);
        const kmDevolucao = Number(document.getElementById('quilometragem_devolucao')?.value || 0);
        if (kmInicial !== null && !Number.isNaN(kmInicial) && (Number.isNaN(kmDevolucao) || kmDevolucao < kmInicial)) {
            const campoKm = document.getElementById('quilometragem_devolucao');
            if (campoKm) campoKm.classList.add('is-invalid');
            Swal.fire({
                icon: 'error',
                title: 'Quilometragem invÃ¡lida',
                text: `A quilometragem na devoluÃ§Ã£o deve ser maior ou igual ao KM inicial do check-in (${kmInicial}).`,
                confirmButtonColor: '#4338ca',
            });
            showStep(1);
            return;
        }

        const campos = document.querySelectorAll('#form_dados_devolucao input:required, #form_dados_devolucao select:required');
        const camposInvalidos = [];
        campos.forEach(campo => {
            const invalido = !campo.value;
            campo.classList.toggle('is-invalid', invalido);
            if (invalido) camposInvalidos.push(campo.name);
        });

        if (camposInvalidos.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Campos obrigatórios',
                html: `<ul style="text-align:left">${camposInvalidos.map(c => `<li>${c.replace(/_/g, ' ')}</li>`).join('')}</ul>`,
                confirmButtonColor: '#4338ca',
            });
            showStep(1);
            return;
        }

        finalizarCheckout();
    });

    function finalizarCheckout() {
        const reserva_id = document.getElementById('reserva_id').value;
        const url = "{{ route('reserva.checkout.finalizar', ':id') }}".replace(':id', reserva_id);
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        const payload = {
            quilometragem_devolucao: document.getElementById('quilometragem_devolucao')?.value,
            nivel_combustivel_devolucao: document.getElementById('combustivel_devolucao')?.value,
            data_devolucao: document.getElementById('data_devolucao')?.value,
            hora_devolucao: document.getElementById('hora_devolucao')?.value,
            notas_devolucao: document.getElementById('notas_devolucao')?.value || '',
            observacoes_devolucao: document.getElementById('observacoes_devolucao')?.value || '',
        };

        const btnFinal = document.getElementById('btn_finalizar_checkout');
        const wizBtn   = document.getElementById('wizardCreateBtn');
        [btnFinal, wizBtn].forEach(b => {
            if (b) {
                b.disabled = true;
                b._oh = b.innerHTML;
                b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            }
        });

        $.ajax({
            url,
            type: 'POST',
            data: payload,
            headers: { 'X-CSRF-TOKEN': csrf },
            success: function (data) {
                Swal.fire({
                    icon: 'success',
                    title: data.message || 'Check-out concluído!',
                    showConfirmButton: false,
                    timer: 2200,
                    toast: true,
                    position: 'top-right',
                });
                setTimeout(() => {
                    window.location.href = data.redirect || "{{ route('reserva.show', ':id') }}".replace(':id', reserva_id);
                }, 800);
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Erro ao finalizar check-out.';
                Swal.fire({ icon: 'error', title: 'Erro', text: msg, confirmButtonColor: '#4338ca' });
                [btnFinal, wizBtn].forEach(b => {
                    if (b) { b.disabled = false; b.innerHTML = b._oh || b.innerHTML; }
                });
            }
        });
    }

    function rowImagens(dano) {
        const imgs = dano.imagens || [];
        return imgs.map(function (i) {
            const url = i.url || (i.path ? `{{ asset('storage') }}/${i.path}` : (i.imagem ? `{{ asset('storage') }}/${i.imagem}` : null));
            if (!url) return '';
            return `<a href="${url}" target="_blank" rel="noopener"><img src="${url}" alt="Dano" style="width:44px;height:44px;object-fit:cover;border-radius:10px;border:1px solid #e2e8f0;margin-right:6px;"></a>`;
        }).filter(Boolean).join(' ');
    }

    function obterDanosDetectados() {
        const veiculo_id = document.getElementById('veiculo')?.value;
        const reserva_id = document.getElementById('reserva_id')?.value;

        $.ajax({
            url: "{{ route('dano.obterDanosDetectados') }}",
            type: 'GET',
            data: { veiculo_id, reserva_id },
            success: function (data) {
                const tbody = document.getElementById('danos_detectados');
                tbody.innerHTML = '';
                if (!data.length) {
                    tbody.innerHTML = '<tr><td colspan="3" class="damage-empty"><i class="fas fa-shield-alt"></i>Nenhuma avaria nesta reserva</td></tr>';
                    return;
                }
                data.forEach(dano => {
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${dano.localizacao}</td>
                            <td>${dano.descricao ?? ''}</td>
                            <td>${rowImagens(dano)}</td>
                        </tr>
                    `);
                });
            }
        });
    }

    function obterDanosExistentes() {
        const veiculo_id = document.getElementById('veiculo')?.value;
        const reserva_id = document.getElementById('reserva_id')?.value;

        $.ajax({
            url: "{{ route('dano.obterDanosExistentes') }}",
            type: 'GET',
            data: { veiculo_id, reserva_id },
            success: function (data) {
                const tbody = document.getElementById('danos_existentes');
                tbody.innerHTML = '';
                if (!data.length) {
                    tbody.innerHTML = '<tr><td colspan="4" class="damage-empty"><i class="fas fa-check-circle"></i>Sem histórico de avarias anteriores</td></tr>';
                    return;
                }
                data.forEach(dano => {
                    let corr = '<span class="text-muted">—</span>';
                    if (dano.correcao_realizada) {
                        corr = `<span class="text-success"><i class="fas fa-wrench"></i> ${dano.correcao_descricao ?? ''}<br><small>${dano.correcao_data ?? ''}</small></span>`;
                    } else {
                        corr = '<span class="text-warning"><i class="fas fa-hourglass-half"></i> Pendente</span>';
                    }
                    tbody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${dano.localizacao}</td>
                            <td>${dano.descricao ?? ''}</td>
                            <td>${corr}</td>
                            <td>${rowImagens(dano)}</td>
                        </tr>
                    `);
                });
            }
        });
    }

    window.obterDanosDetectados = obterDanosDetectados;
    window.obterDanosExistentes = obterDanosExistentes;

    function setNomeDano(nome) {
        const el = document.getElementById('informacao_dano');
        if (el) el.innerText = nome;
    }
    window.setNomeDano = setNomeDano;

    showStep(1);

})();
</script>
@endpush
