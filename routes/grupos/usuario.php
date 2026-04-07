<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;

Route::prefix('usuario')->name('usuario.')->group(function () {
    Route::post('setperpage', [UsuarioController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [UsuarioController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [UsuarioController::class, 'filtro'])->name('filtro');
    Route::get('select-options', [UsuarioController::class, 'selectOptions'])->name('selectOptions');
    Route::get('buscar', [UsuarioController::class, 'buscar'])->name('buscar');
    Route::get('{id}/alterarsenha', [UsuarioController::class, 'alterarSenha'])->name('alterarSenha');
    Route::put('{id}/alterarsenha', [UsuarioController::class, 'salvarNovaSenha'])->name('salvarNovaSenha');

    Route::get('{id}/metodopagamento', [UsuarioController::class, 'metodoPagamento'])->name('metodoPagamento');
    Route::post('{id}/adicionarMetodoPagamento', [UsuarioController::class, 'adicionarMetodoPagamento'])->name('adicionarMetodoPagamento');
    Route::delete('{usuarioId}/excluirmetodopagamento/{metodoId}', [UsuarioController::class, 'excluirMetodoPagamento'])->name('excluirMetodoPagamento');
});
Route::resource('usuario', UsuarioController::class);
