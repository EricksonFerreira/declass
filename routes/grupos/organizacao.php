<?php

use App\Http\Controllers\OrganizacaoController;
use Illuminate\Support\Facades\Route;

Route::prefix('organizacao')->name('organizacao.')->group(function () {
    Route::post('setperpage', [OrganizacaoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [OrganizacaoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [OrganizacaoController::class, 'filtro'])->name('filtro');
});
Route::resource('organizacao', OrganizacaoController::class);
