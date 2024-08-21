<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrotaCaracteristicaController;

Route::prefix('frota_caracteristica')->name('frota_caracteristica.')->group(function () {
    Route::post('setperpage', [FrotaCaracteristicaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [FrotaCaracteristicaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [FrotaCaracteristicaController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [FrotaCaracteristicaController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [FrotaCaracteristicaController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('frota_caracteristica', FrotaCaracteristicaController::class);