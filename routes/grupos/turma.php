<?php

use App\Http\Controllers\AtribuicaoController;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\TurmaDisciplinaController;
use Illuminate\Support\Facades\Route;

Route::prefix('turma')->name('turma.')->group(function () {
    Route::post('setperpage', [TurmaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [TurmaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [TurmaController::class, 'filtro'])->name('filtro');

    // Disciplinas ofertadas pela turma
    Route::post('{turma}/disciplinas', [TurmaDisciplinaController::class, 'store'])->name('disciplina.store');
    Route::put('{turma}/disciplinas/{disciplina}', [TurmaDisciplinaController::class, 'update'])->name('disciplina.update');
    Route::delete('{turma}/disciplinas/{disciplina}', [TurmaDisciplinaController::class, 'destroy'])->name('disciplina.destroy');

    // Atribuicoes de professores
    Route::post('{turma}/atribuicoes', [AtribuicaoController::class, 'store'])->name('atribuicao.store');
    Route::put('{turma}/atribuicoes/{atribuicao}', [AtribuicaoController::class, 'update'])->name('atribuicao.update');
    Route::delete('{turma}/atribuicoes/{atribuicao}', [AtribuicaoController::class, 'destroy'])->name('atribuicao.destroy');
});
Route::resource('turma', TurmaController::class);
