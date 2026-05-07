<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TermoConsentimentoController;

Route::prefix('termo-consentimento')->name('termo_consentimento.')->group(function () {
    Route::post('setperpage', [TermoConsentimentoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [TermoConsentimentoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [TermoConsentimentoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [TermoConsentimentoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [TermoConsentimentoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('termo-consentimento', TermoConsentimentoController::class)->as('termo_consentimento');