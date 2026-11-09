<?php

use App\Http\Controllers\EventoController;
use Illuminate\Support\Facades\Route;

Route::prefix('agenda')->name('evento.')->group(function () {
    Route::post('setperpage', [EventoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [EventoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [EventoController::class, 'filtro'])->name('filtro');
});
Route::resource('agenda', EventoController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('evento');

// URLs antigas /admin/evento* viram /admin/agenda* (nomes de rota mantidos).
Route::any('evento/{path?}', function (?string $path = null) {
    $prefix = trim((string) config('app.admin_prefix', 'admin'), '/');
    $destino = '/'.$prefix.'/agenda'.($path ? '/'.$path : '');
    $query = request()->getQueryString();

    return redirect($destino.($query ? '?'.$query : ''), 301);
})->where('path', '.*');
