<div class="modal fade" id="detalhesDanoModal" tabindex="-1" role="dialog" aria-labelledby="detalhesDanoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content dano-modal">

            {{-- Header --}}
            <div class="dano-modal__header">
                <div class="dano-modal__header-left">
                    <div class="dano-modal__header-icon">
                        <i class="fas fa-car-crash"></i>
                    </div>
                    <div>
                        <div class="dano-modal__eyebrow">Registro de Dano</div>
                        <h6 class="dano-modal__title">
                            Vista: <span id="informacao_dano" class="dano-modal__location">—</span>
                        </h6>
                    </div>
                </div>
                <button type="button" class="dano-modal__close" data-dismiss="modal" aria-label="Fechar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="dano-modal__body">
                <form id="form_dano">

                    {{-- Erros --}}
                    <div id="mensagem_erro"></div>

                    {{-- Bloco: Categoria e Localizacao --}}
                    <div class="dano-block">
                        <div class="dano-block__label">
                            <i class="fas fa-tag"></i>
                            <div>
                                <strong>Categoria e Localizacao</strong>
                                <span>Classifique onde e em qual area o dano se encontra</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-7">
                                <x-form-select
                                    id="categoria_problema"
                                    name="categoria_problema"
                                    label="Categoria do Problema"
                                    :options="$categoriaDanos"
                                    placeholder="Selecione uma categoria"
                                    :selected="old('categoria_problema', null)"
                                    class="form-control form-control-sm"
                                    required
                                    onchange="obterDanos()"
                                />
                            </div>
                            <div class="col-md-5">
                                <label for="localizacao_problema">Localizacao</label>
                                <select class="form-control form-control-sm" id="localizacao_problema" name="localizacao-problema" onchange="obterDanos()">
                                    <option value="exterior">Exterior</option>
                                    <option value="interior">Interior</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="dano-select-label">
                                <label for="danos">Tipo de Dano <span class="req">*</span></label>
                                <img id="loading_dano" style="width:14px;height:14px;display:none" src="{{ asset('assets/gifs/loading.gif') }}" alt="Carregando...">
                            </div>
                            <select name="dano" id="danos" class="form-control form-control-sm select2ci-modal" required>
                                <option disabled selected>Primeiro selecione uma categoria</option>
                            </select>
                        </div>
                    </div>

                    {{-- Bloco: Tipo e Custo --}}
                    <div class="dano-block">
                        <div class="dano-block__label">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>Severidade e Custo</strong>
                                <span>Indique o tipo de avaria e o custo estimado de reparo</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="mb-2 d-block">Tipo de Avaria <span class="req">*</span></label>
                                <div class="dano-radio-group">
                                    @php
                                        $tipos = [
                                            ['value' => 'ausente',          'label' => 'Ausente',          'icon' => 'fas fa-check',         'color' => 'green'],
                                            ['value' => 'amolgadela_leve',  'label' => 'Amolgadela Leve',  'icon' => 'fas fa-dot-circle',    'color' => 'amber'],
                                            ['value' => 'amolgadela_grave', 'label' => 'Amolgadela Grave', 'icon' => 'fas fa-circle',        'color' => 'red'],
                                            ['value' => 'risco_leve',       'label' => 'Risco Leve',       'icon' => 'fas fa-slash',         'color' => 'amber'],
                                            ['value' => 'risco_grave',      'label' => 'Risco Grave',      'icon' => 'fas fa-times',         'color' => 'red'],
                                        ];
                                    @endphp
                                    @foreach($tipos as $tipo)
                                        <label class="dano-radio-card dano-radio-card--{{ $tipo['color'] }}">
                                            <input type="radio" name="tipo" value="{{ $tipo['value'] }}" {{ $loop->first ? 'checked' : '' }}>
                                            <i class="{{ $tipo['icon'] }}"></i>
                                            <span>{{ $tipo['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="custo_estimado">Custo Estimado <span class="req">*</span></label>
                                    <div class="dano-input-prefix">
                                        <span>R$</span>
                                        <input type="text" required class="form-control form-control-sm" id="custo_estimado" name="custo_estimado" placeholder="0.00">
                                    </div>
                                </div>
                                <div>
                                    <label for="quilometragem_dano">Quilometragem <span class="req">*</span></label>
                                    <div class="dano-input-prefix">
                                        <span>km</span>
                                        <input type="number" required class="form-control form-control-sm" id="quilometragem_dano" name="quilometragem_dano" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bloco: Observacoes e Fotos --}}
                    <div class="dano-block">
                        <div class="dano-block__label">
                            <i class="fas fa-camera"></i>
                            <div>
                                <strong>Evidencias e Observacoes</strong>
                                <span>Adicione fotos e notas complementares sobre o dano</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="observacoes_dano">Observacoes</label>
                                <textarea class="form-control form-control-sm" id="observacoes_dano" name="observacoes_dano" rows="3" placeholder="Descreva detalhes adicionais sobre o dano..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label>Fotos do Dano</label>
                                <label for="fotos-dano" class="dano-upload-btn">
                                    <i class="fas fa-upload"></i>
                                    <span>Selecionar fotos</span>
                                </label>
                                <input type="file" class="dano-upload-input" id="fotos-dano" name="fotos[]" multiple accept="image/*">
                                <small class="dano-hint">JPG, PNG ou WEBP. Multiplas fotos permitidas.</small>
                            </div>
                        </div>

                        <div style="display:none" id="div_previsualizar">
                            <label class="mt-3 d-block">Pre-visualizacao</label>
                            <div id="previsualizar_imagens" class="dano-preview-grid"></div>
                        </div>
                    </div>

                </form>
            </div>

            {{-- Footer --}}
            <div class="dano-modal__footer">
                <button type="button" class="dano-btn dano-btn--ghost" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" form="form_dano" class="dano-btn dano-btn--primary">
                    <i class="fas fa-save"></i>
                    Salvar Dano
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    /* ================================================
       MODAL DANO — Design System
    ================================================ */
    .dano-modal {
        border: 0;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 32px 80px rgba(12, 20, 40, 0.28);
    }

    /* Header */
    .dano-modal__header {
        position: relative;
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        padding: 1.2rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        overflow: hidden;
    }

    .dano-modal__header::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px);
        background-size: 20px 20px;
        pointer-events: none;
    }

    .dano-modal__header::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4338ca, #0ea5e9, #ef4444);
    }

    .dano-modal__header-left {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 1;
    }

    .dano-modal__header-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: #ef4444;
        flex-shrink: 0;
        backdrop-filter: blur(8px);
    }

    .dano-modal__eyebrow {
        font-size: 0.6rem;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: rgba(148,163,184,0.8);
        margin-bottom: 2px;
    }

    .dano-modal__title {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: #fff;
    }

    .dano-modal__location {
        color: #fbbf24;
        font-weight: 800;
    }

    .dano-modal__close {
        position: relative;
        z-index: 1;
        background: rgba(255,255,255,0.10);
        border: 1px solid rgba(255,255,255,0.15);
        color: rgba(226,232,240,0.8);
        border-radius: 8px;
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.18s;
        flex-shrink: 0;
    }

    .dano-modal__close:hover {
        background: rgba(239,68,68,0.3);
        border-color: rgba(239,68,68,0.5);
        color: #fff;
    }

    /* Body */
    .dano-modal__body {
        padding: 1.2rem 1.4rem;
        background: #f2f5fb;
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Blocks */
    .dano-block {
        border: 1px solid #e2e9f4;
        border-left: 3px solid #4338ca;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        background: #fff;
        margin-bottom: 10px;
        transition: box-shadow 0.2s;
    }

    .dano-block:hover { box-shadow: 0 2px 8px rgba(12,20,40,.07); }

    .dano-block__label {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 0.9rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px dashed #e5eaf3;
    }

    .dano-block__label > i {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        background: #eef2ff;
        color: #4338ca;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        flex-shrink: 0;
    }

    .dano-block__label strong {
        display: block;
        color: #0c1428;
        font-size: 0.84rem;
        font-weight: 700;
    }

    .dano-block__label span {
        color: #64748b;
        font-size: 0.73rem;
        display: block;
        margin-top: 1px;
    }

    .dano-block label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #3d4f63;
        margin-bottom: 4px;
        display: block;
    }

    .dano-block .form-control {
        border-radius: 9px;
        border-color: #dce6f4;
        font-size: 0.83rem;
        background: #fcfeff;
        transition: border-color 0.18s, box-shadow 0.18s;
    }

    .dano-block .form-control:focus {
        border-color: #4338ca;
        box-shadow: 0 0 0 3px rgba(67,56,202,0.10);
    }

    .req { color: #ef4444; font-size: 0.73rem; }

    /* Select2 label row */
    .dano-select-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 4px;
    }

    /* Radio cards */
    .dano-radio-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .dano-radio-card {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 8px 12px;
        border: 1.5px solid #e2e9f4;
        border-radius: 10px;
        cursor: pointer;
        font-size: 0.8rem;
        font-weight: 600;
        color: #3d4f63;
        background: #fafbff;
        transition: all 0.18s;
        margin: 0;
        user-select: none;
    }

    .dano-radio-card input[type="radio"] {
        display: none;
    }

    .dano-radio-card i {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.68rem;
        flex-shrink: 0;
        background: #f1f5f9;
        color: #94a3b8;
        transition: all 0.18s;
    }

    .dano-radio-card:has(input:checked) {
        border-color: currentColor;
    }

    .dano-radio-card--green:has(input:checked) {
        background: #f0fdf4;
        border-color: #16a34a;
        color: #15803d;
    }
    .dano-radio-card--green:has(input:checked) i { background: #dcfce7; color: #16a34a; }

    .dano-radio-card--amber:has(input:checked) {
        background: #fffbeb;
        border-color: #d97706;
        color: #92400e;
    }
    .dano-radio-card--amber:has(input:checked) i { background: #fef3c7; color: #d97706; }

    .dano-radio-card--red:has(input:checked) {
        background: #fff1f2;
        border-color: #dc2626;
        color: #991b1b;
    }
    .dano-radio-card--red:has(input:checked) i { background: #fee2e2; color: #dc2626; }

    .dano-radio-card:hover {
        background: #f0f2ff;
        border-color: #c7d0ff;
    }

    /* Input prefix */
    .dano-input-prefix {
        display: grid;
        grid-template-columns: auto 1fr;
        align-items: center;
        border: 1px solid #dce6f4;
        border-radius: 9px;
        overflow: hidden;
        background: #fcfeff;
        transition: border-color 0.18s, box-shadow 0.18s;
    }

    .dano-input-prefix:focus-within {
        border-color: #4338ca;
        box-shadow: 0 0 0 3px rgba(67,56,202,0.10);
    }

    .dano-input-prefix > span {
        padding: 0 10px;
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        background: #f0f2ff;
        border-right: 1px solid #dce6f4;
        height: 100%;
        display: flex;
        align-items: center;
        white-space: nowrap;
    }

    .dano-input-prefix .form-control {
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    /* Upload */
    .dano-upload-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: #eef0ff;
        border: 1px solid #c7d0ff;
        border-radius: 9px;
        padding: 8px 14px;
        font-size: 0.78rem;
        font-weight: 700;
        color: #4338ca;
        cursor: pointer;
        transition: all 0.18s;
        width: 100%;
        justify-content: center;
        margin-bottom: 6px;
    }

    .dano-upload-btn:hover {
        background: #4338ca;
        border-color: #4338ca;
        color: #fff;
    }

    .dano-upload-input { display: none; }

    .dano-hint {
        display: block;
        font-size: 0.7rem;
        color: #8fa5c0;
        font-style: italic;
    }

    /* Preview grid */
    .dano-preview-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .dano-preview-grid img {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 9px;
        border: 2px solid #e2e9f4;
        cursor: zoom-in;
        transition: border-color 0.18s, transform 0.18s;
    }

    .dano-preview-grid img:hover {
        border-color: #4338ca;
        transform: scale(1.06);
    }

    /* Footer */
    .dano-modal__footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        padding: 12px 1.4rem;
        background: #f0f2ff;
        border-top: 1px solid #dce2ff;
    }

    .dano-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.18s;
        letter-spacing: -0.01em;
    }

    .dano-btn--ghost {
        background: #fff;
        color: #475569;
        border: 1px solid #dce6f4;
    }

    .dano-btn--ghost:hover { background: #f1f5f9; color: #0c1428; }

    .dano-btn--primary {
        background: linear-gradient(135deg, #4338ca, #6366f1);
        color: #fff;
        box-shadow: 0 4px 12px rgba(67,56,202,0.30);
    }

    .dano-btn--primary:hover {
        opacity: 0.92;
        transform: translateY(-1px);
        color: #fff;
    }

    /* Alerta de erros */
    #mensagem_erro .alert {
        border-radius: 10px;
        font-size: 0.82rem;
        margin-bottom: 10px;
    }

    /* Select2 no modal */
    .select2-container .select2-selection--single {
        border-radius: 9px !important;
        border-color: #dce6f4 !important;
        height: calc(1.5em + .5rem + 2px) !important;
        font-size: 0.83rem !important;
        background: #fcfeff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.5em + .5rem + 2px) !important;
        font-size: 0.83rem !important;
        color: #3d4f63 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + .5rem + 2px) !important;
    }
</style>

<script src="{{ asset('js/sweetAlert.min.js') }}"></script>
<script>
    document.getElementById('form_dano').addEventListener('submit', function (event) {
        event.preventDefault();
        const campos = document.querySelectorAll('#form_dano input[required], #form_dano select[required], #form_dano textarea[required]');
        let valido = true;
        campos.forEach(campo => {
            const invalido = !campo.value;
            campo.classList.toggle('is-invalid', invalido);
            if (invalido) valido = false;
        });
        if (valido) adicionarDanoVeiculo();
    });

    function adicionarDanoVeiculo() {
        const custoRaw = document.getElementById('custo_estimado')?.value || '';
        const custoNumerico = custoRaw.replace(',', '.');

        const dano = {
            categoria:           document.getElementById('categoria_problema')?.value || null,
            local_dano:          document.getElementById('informacao_dano')?.innerHTML.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase() || null,
            localizacao_problema: document.getElementById('localizacao_problema')?.value || null,
            dano:                document.getElementById('danos')?.value || null,
            tipo:                document.querySelector('input[name="tipo"]:checked')?.value || null,
            custo_estimado:      custoNumerico,
            quilometragem:       document.getElementById('quilometragem_dano')?.value || null,
            observacoes:         document.getElementById('observacoes_dano')?.value || null,
            fotos:               document.getElementById('fotos-dano')?.files || [],
            veiculo:             document.getElementById('veiculo')?.value || null,
        };

        const reservaId = document.getElementById('reserva_id').value;
        const url = "{{ route('reserva.adicionarDanoVeiculo', ':id') }}".replace(':id', reservaId);
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        const formData = new FormData();
        formData.append('categoria_id', dano.categoria);
        formData.append('localizacao_problema', dano.localizacao_problema);
        if (dano.dano) formData.append('dano_id', dano.dano);
        formData.append('tipo_dano', dano.tipo);
        if (dano.local_dano) formData.append('local_dano', dano.local_dano);
        formData.append('custo_estimado', dano.custo_estimado);
        formData.append('quilometragem_veiculo', dano.quilometragem);
        if (dano.observacoes) formData.append('observacoes', dano.observacoes);
        formData.append('veiculo_id', dano.veiculo);
        Array.from(dano.fotos).forEach(file => formData.append('fotos[]', file));

        $.ajax({
            url, type: 'POST', data: formData,
            headers: { 'X-CSRF-TOKEN': csrf },
            processData: false, contentType: false,
            success: function () {
                $('#detalhesDanoModal').modal('hide');

                /* Limpar form */
                document.getElementById('categoria_problema').value = null;
                document.getElementById('informacao_dano').innerText = '—';
                document.getElementById('localizacao_problema').value = 'exterior';
                document.getElementById('danos').innerHTML = '<option disabled selected>Primeiro selecione uma categoria</option>';
                document.querySelectorAll('input[name="tipo"]').forEach(i => i.checked = false);
                document.querySelector('input[name="tipo"][value="ausente"]').checked = true;
                document.getElementById('custo_estimado').value = '';
                document.getElementById('quilometragem_dano').value = '';
                document.getElementById('observacoes_dano').value = '';
                document.getElementById('fotos-dano').value = '';
                document.getElementById('previsualizar_imagens').innerHTML = '';
                document.getElementById('div_previsualizar').style.display = 'none';
                document.getElementById('mensagem_erro').innerHTML = '';

                Swal.fire({
                    icon: 'success',
                    title: 'Dano registrado com sucesso!',
                    showConfirmButton: false,
                    timer: 1800,
                    toast: true,
                    position: 'top-right',
                });

                window.obterDanosDetectados?.();
                window.obterDanosExistentes?.();
            },
            error: function (response) {
                const json   = response.responseJSON || {};
                const errors = json.errors || {};
                const keys   = Object.keys(errors);

                let html = `<div class="alert alert-danger alert-dismissible fade show" role="alert">`;

                if (keys.length > 0) {
                    html += `<strong>Corrija os erros abaixo:</strong><ul class="mb-0 mt-1">`;
                    keys.forEach(key => {
                        errors[key].forEach(err => { html += `<li>${err}</li>`; });
                    });
                    html += `</ul>`;
                } else if (json.message) {
                    html += `<strong>Erro:</strong> ${json.message}`;
                } else {
                    html += `<strong>Erro ao salvar o dano.</strong> Verifique os campos e tente novamente. (HTTP ${response.status})`;
                }

                html += `<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>`;
                document.getElementById('mensagem_erro').innerHTML = html;
                document.getElementById('mensagem_erro').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    }

    /* Upload preview */
    document.getElementById('fotos-dano').addEventListener('change', function () {
        const container = document.getElementById('previsualizar_imagens');
        const wrapper   = document.getElementById('div_previsualizar');
        container.innerHTML = '';

        if (!this.files.length) { wrapper.style.display = 'none'; return; }

        wrapper.style.display = 'block';
        Array.from(this.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                container.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    });

    /* Zoom nas pre-visualizacoes */
    document.getElementById('previsualizar_imagens').addEventListener('click', function (event) {
        if (event.target.tagName !== 'IMG') return;
        const modal = document.createElement('div');
        modal.classList.add('modal', 'fade');
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content" style="border:0;border-radius:16px;overflow:hidden">
                    <div class="modal-header" style="background:#0c1428;border:0;padding:12px 16px">
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;text-shadow:none"><span>&times;</span></button>
                    </div>
                    <div class="modal-body text-center" style="background:#f2f5fb;padding:20px">
                        <img src="${event.target.src}" class="img-fluid" style="border-radius:10px">
                    </div>
                </div>
            </div>`;
        document.body.appendChild(modal);
        $(modal).modal('show');
        $(modal).on('hidden.bs.modal', () => document.body.removeChild(modal));
    });

    /* Obter danos pelo select de categoria/localizacao */
    function obterDanos() {
        const categoria  = document.getElementById('categoria_problema')?.value;
        const localizacao = document.getElementById('localizacao_problema')?.value;
        if (!categoria || !localizacao) return;

        document.getElementById('loading_dano').style.display = 'inline';
        $.ajax({
            url: "{{ route('dano.obterDanosPorCategoriaELocalizacao') }}",
            type: 'GET',
            data: { categoria, localizacao },
            success: function (response) {
                const select = document.getElementById('danos');
                select.innerHTML = '';
                if (!response.length) {
                    select.innerHTML = '<option disabled selected>Nenhum dano encontrado</option>';
                } else {
                    select.innerHTML = '<option disabled selected>Selecione um dano</option>';
                    response.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = d.nome;
                        select.appendChild(opt);
                    });
                }
                document.getElementById('loading_dano').style.display = 'none';
            },
            error: () => { document.getElementById('loading_dano').style.display = 'none'; }
        });
    }
    window.obterDanos = obterDanos;
</script>
