<?php

use App\Http\Controllers\BoletimController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('boletim', [BoletimController::class, 'index'])->name('boletim.index');
    Route::get('boletim/turma/{turma}', [BoletimController::class, 'turma'])->name('boletim.turma');
    Route::get('boletim/turma/{turma}/pdf', [BoletimController::class, 'turmaPdf'])->name('boletim.turma.pdf');
    Route::get('boletim/aluno/{aluno}', [BoletimController::class, 'aluno'])->name('boletim.aluno');
    Route::get('boletim/aluno/{aluno}/pdf', [BoletimController::class, 'alunoPdf'])->name('boletim.aluno.pdf');
});
