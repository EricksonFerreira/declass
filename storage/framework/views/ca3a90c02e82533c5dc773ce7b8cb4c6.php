<?php $__env->startSection('title', 'Prospecção'); ?>

<?php $__env->startSection('content_header'); ?>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0">Prospecção de Locadoras</h1>
            <p class="text-muted mb-0">Busque locadoras por estado e cidade usando OpenStreetMap + Overpass API.</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $cidadesInput = old('cidades', implode("\n", $listaCidades ?? ['Recife', 'Olinda', 'Jaboatão dos Guararapes', 'Paulista']));
        $termosInput = old('termos', implode("\n", $listaTermos ?? ['locadora de veículos', 'aluguel de carros', 'locação de veículos', 'aluguel de vans']));
        $estados = $estados ?? [];
    ?>

    <?php if(!empty($erroBusca)): ?>
        <div class="alert alert-danger"><?php echo e($erroBusca); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12 col-xl-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title mb-0">Busca por região</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('prospeccao.index')); ?>" id="form-prospeccao">
                        <input type="hidden" name="buscar" value="1">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select id="estado" name="estado" class="form-control">
                                        <option value="">Selecione</option>
                                        <?php $__currentLoopData = $estados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sigla => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($sigla); ?>" <?php if(($estado ?? 'PE') === $sigla): echo 'selected'; endif; ?>><?php echo e($nome); ?> (<?php echo e($sigla); ?>)</option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="cidades">Cidades</label>
                                    <select id="cidades" name="cidades[]" class="form-control" multiple size="8"></select>
                                    <small class="text-muted d-block mt-1">Selecione uma ou mais cidades do estado escolhido.</small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label for="termos">Expressões de busca</label>
                                    <textarea id="termos" name="termos" class="form-control" rows="4" placeholder="Uma expressão por linha"><?php echo e($termosInput); ?></textarea>
                                    <small class="text-muted d-block mt-1">Cada cidade será consultada com cada expressão, para ampliar a chance de encontrar empresas em categorias diferentes.</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap align-items-center mt-3" style="gap:.75rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i> Buscar locadoras
                            </button>
                            <a href="<?php echo e(route('prospeccao.index')); ?>" class="btn btn-outline-secondary">Limpar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title mb-0">Status do módulo</h3>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Fonte:</strong> OpenStreetMap / Overpass API</p>
                    <p class="mb-2"><strong>Estados:</strong> 27 UFs do Brasil</p>
                    <p class="mb-2"><strong>Cidades:</strong> carregadas dinamicamente ao escolher o estado</p>
                    <p class="mb-0"><strong>Busca:</strong> retorna empresas encontradas por cidade e termo.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
            <h3 class="card-title mb-0">Resultados</h3>
            <div class="text-muted small"><?php echo e(count($resultados ?? [])); ?> resultado(s)</div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Locadora</th>
                        <th>Cidade</th>
                        <th>Telefone</th>
                        <th>Site</th>
                        <th>Avaliação</th>
                        <th>Situação</th>
                        <th>Endereço</th>
                        <th>Google Maps</th>
                        <th>Qtd. avaliações</th>
                        <th>Localizada em</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $resultados ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resultado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($resultado['nome'] ?? '-'); ?></td>
                            <td><?php echo e($resultado['cidade'] ?? '-'); ?></td>
                            <td><?php echo e($resultado['telefone'] ?? 'Não informado'); ?></td>
                            <td><?php echo e($resultado['site'] ?? 'Não informado'); ?></td>
                            <td><?php echo e($resultado['avaliacao'] ?? 'Não disponível'); ?></td>
                            <td><span class="badge badge-info"><?php echo e($resultado['status_prospeccao'] ?? 'Nova'); ?></span></td>
                            <td><?php echo e($resultado['endereco'] ?? '—'); ?></td>
                            <td>
                                <?php if(!empty($resultado['google_maps_url'])): ?>
                                    <a href="<?php echo e($resultado['google_maps_url']); ?>" target="_blank" rel="noopener">Abrir</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($resultado['quantidade_avaliacoes'] ?? 'Não disponível'); ?></td>
                            <td><?php echo e(!empty($resultado['consultado_google_em']) ? \Carbon\Carbon::parse($resultado['consultado_google_em'])->format('d/m/Y H:i') : '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                Faça uma busca para exibir as locadoras encontradas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        (function () {
            const estadoSelect = document.getElementById('estado');
            const cidadesSelect = document.getElementById('cidades');
            const cidadesSelecionadas = <?php echo json_encode(old('cidades', $listaCidades ?? []), 512) ?>;
            const cidadesUrlBase = <?php echo json_encode(route('prospeccao.cidades', ['uf' => '__UF__']), 512) ?>;

            async function carregarCidades(uf) {
                cidadesSelect.innerHTML = '<option value="">Carregando...</option>';
                cidadesSelect.disabled = true;

                if (!uf) {
                    cidadesSelect.innerHTML = '';
                    return;
                }

                try {
                    const response = await fetch(cidadesUrlBase.replace('__UF__', uf), {
                        headers: { 'Accept': 'application/json' }
                    });

                    const data = await response.json();
                    cidadesSelect.innerHTML = '';

                    (Array.isArray(data) ? data : []).forEach(function (cidade) {
                        const option = document.createElement('option');
                        option.value = cidade.nome;
                        option.textContent = cidade.nome;
                        if (cidadesSelecionadas.includes(cidade.nome)) {
                            option.selected = true;
                        }
                        cidadesSelect.appendChild(option);
                    });
                } catch (error) {
                    cidadesSelect.innerHTML = '<option value="">Erro ao carregar cidades</option>';
                } finally {
                    cidadesSelect.disabled = false;
                }
            }

            if (estadoSelect) {
                estadoSelect.addEventListener('change', function () {
                    carregarCidades(this.value);
                });

                carregarCidades(estadoSelect.value);
            }
        })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\xpandCars\resources\views/prospeccao/index.blade.php ENDPATH**/ ?>