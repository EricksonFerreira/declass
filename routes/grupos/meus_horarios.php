<?php

use App\Http\Controllers\MeusHorariosController;
use Illuminate\Support\Facades\Route;

Route::prefix('meus-horarios')->name('meus_horarios.')->group(function () {
    Route::get('/', [MeusHorariosController::class, 'index'])->name('index');
});
