<?php $__env->startSection('title', 'Horas Trabalhadas'); ?>

<?php $__env->startSection('content_header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Horas Trabalhadas</h1>
        <ol class="breadcrumb float-sm-right mb-0">
            <li class="breadcrumb-item"><a href="<?php echo e(route('home')); ?>">Home</a></li>
            <li class="breadcrumb-item active">Horas Trabalhadas</li>
        </ol>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Relatório de Horas por Projeto</h3>
                        <small class="text-white-50">Última atualização: <?php echo e(now()->format('d/m/Y H:i')); ?></small>
                    </div>
                    <div class="card-body">
                        <!-- Chart placeholder -->
                        <canvas id="hoursChart" height="120"></canvas>
                        <hr class="my-4">
                        <!-- Table placeholder -->
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Projeto</th>
                                        <th>Usuário</th>
                                        <th>Total de Horas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Nenhum dado disponível ainda.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted mt-3">Este relatório ainda está em desenvolvimento. Em breve exibirá gráficos interativos e filtros avançados.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('hoursChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [], // to be populated
            datasets: [{
                label: 'Horas',
                data: [],
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Erickson\Dev\laragon\www\clockify\resources\views/relatorios/horas-trabalhadas.blade.php ENDPATH**/ ?>