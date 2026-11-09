<?php

use App\Http\Controllers\FinanceiroVisaoGeralController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('financeiro/visao-geral', [FinanceiroVisaoGeralController::class, 'index'])
        ->name('financeiro_visao_geral.index');
});
