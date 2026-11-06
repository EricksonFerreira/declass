<?php

use App\Http\Controllers\MatriculaPublicaController;
use Illuminate\Support\Facades\Route;

// Formulario publico de solicitacao de matricula (visitante, sem login).
// A escola e resolvida pelos slugs empresa/escola na URL.
Route::get('matricula/{empresaSlug}/{escolaSlug}', [MatriculaPublicaController::class, 'create'])
    ->name('matricula_publica.create');
Route::post('matricula/{empresaSlug}/{escolaSlug}', [MatriculaPublicaController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('matricula_publica.store');
