<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PermissaoController;

Route::middleware('can:acessar-superadmin')->prefix('permissao')->name('permissao.')->group(function () {
    Route::post('setperpage', [PermissaoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [PermissaoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [PermissaoController::class, 'filtro'])->name('filtro');
});
Route::middleware('can:acessar-superadmin')->group(function () {
    Route::resource('permissao', PermissaoController::class);
});
