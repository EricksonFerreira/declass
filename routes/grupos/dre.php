<?php

use App\Http\Controllers\DreController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dre', [DreController::class, 'index'])->name('dre.index');
});
