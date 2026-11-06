<?php

use App\Http\Controllers\AgendaAvaliacaoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('agenda-avaliacoes', [AgendaAvaliacaoController::class, 'index'])
        ->name('avaliacao.agenda');
});
