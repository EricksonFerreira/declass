<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaisController;

Route::prefix('pais')->name('pais.')->group(function () {
    Route::post('setperpage', [PaisController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [PaisController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [PaisController::class, 'filtro'])->name('filtro');
});
Route::resource('pais', PaisController::class);
// pais/ get (todos os países)     index
// pais/create get (tela criar pais)     create
// pais/ store (efetua a criação do pais)    store
// pais/{id}/ get (ver pais)    show
// pais/{id}/edit get (tela de editar pais)  edit
// pais/{id}/ put (editar pais)     update
// pais/{id}/ delete (delete pais)  destroy