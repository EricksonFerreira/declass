<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\DocumentacaoVeiculoController;

Route::prefix('veiculo')->name('veiculo.')->group(function () {
    Route::get('buscar', [VeiculoController::class, 'buscar'])->name('buscar');
    Route::get('documentacao', [DocumentacaoVeiculoController::class, 'index'])->name('documentacao');
    Route::get('documentacao/create', [DocumentacaoVeiculoController::class, 'create'])->name('documentacao.create');
    Route::get('documentacao/{documentacao}/edit', [DocumentacaoVeiculoController::class, 'edit'])->name('documentacao.edit');
    Route::post('documentacao', [DocumentacaoVeiculoController::class, 'store'])->name('documentacao.store');
    Route::put('documentacao/{documentacao}', [DocumentacaoVeiculoController::class, 'update'])->name('documentacao.update');
    Route::post('{veiculo}/manutencao/concluir', [VeiculoController::class, 'concluirManutencaoPreventiva'])->name('manutencao.concluir');
    Route::get('buscar-grupos-modal', [VeiculoController::class, 'buscarGruposModal'])->name('buscarGruposModal');
    Route::get('buscar-marcas-modal', [VeiculoController::class, 'buscarMarcasModal'])->name('buscarMarcasModal');
    Route::get('buscar-modelos-modal', [VeiculoController::class, 'buscarModelosModal'])->name('buscarModelosModal');
    Route::match(['get', 'post'], '{veiculo}/consultar-fipe', [VeiculoController::class, 'consultarFipe'])->name('consultarFipe');
    Route::match(['post', 'put'], '{veiculo}/alterardepreciacao', [VeiculoController::class, 'alterarDepreciacao'])->name('alterardepreciacao');
    Route::get('grupos-por-tipo', [VeiculoController::class, 'gruposPorTipo'])->name('gruposPorTipo');
    Route::get('modelos-por-marca', [VeiculoController::class, 'modelosPorMarca'])->name('modelosPorMarca');
    Route::post('ordenacao', [VeiculoController::class, 'setOrdenacao'])->name('setOrdenacao');
    Route::get('disponibilidade', [VeiculoController::class, 'disponibilidade'])->name('disponibilidade');
    Route::post('setperpage', [VeiculoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [VeiculoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [VeiculoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [VeiculoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [VeiculoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('veiculo', VeiculoController::class);
