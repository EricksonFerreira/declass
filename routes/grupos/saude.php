<?php

use App\Http\Controllers\SaudeAlunoController;
use Illuminate\Support\Facades\Route;

Route::prefix('saude')->name('saude.')->group(function () {
    Route::post('setperpage', [SaudeAlunoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [SaudeAlunoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [SaudeAlunoController::class, 'filtro'])->name('filtro');
    Route::get('aluno/{aluno}/editar', [SaudeAlunoController::class, 'edit'])->name('edit');
    Route::put('aluno/{aluno}', [SaudeAlunoController::class, 'update'])->name('update');
    Route::get('aluno/{aluno}', [SaudeAlunoController::class, 'show'])->name('show');
    Route::get('/', [SaudeAlunoController::class, 'index'])->name('index');
});
