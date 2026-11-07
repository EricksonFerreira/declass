<?php

use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/portal', [PortalController::class, 'index'])->name('portal.index');
    Route::get('/portal/aluno/{aluno}', [PortalController::class, 'aluno'])->name('portal.aluno');
    Route::get('/portal/financeiro', [PortalController::class, 'financeiro'])->name('portal.financeiro');
    Route::get('/portal/mensalidades/{id}/recibo', [PortalController::class, 'reciboMensalidade'])->name('portal.mensalidade.recibo');
    Route::get('/portal/avisos', [PortalController::class, 'avisos'])->name('portal.avisos');
    Route::get('/portal/transporte', [PortalController::class, 'transporte'])->name('portal.transporte');
    Route::get('/portal/cardapio', [PortalController::class, 'cardapio'])->name('portal.cardapio');
    Route::get('/portal/tarefas', [PortalController::class, 'tarefas'])->name('portal.tarefas');
    Route::get('/portal/emprestimos', [PortalController::class, 'emprestimos'])->name('portal.emprestimos');
    Route::get('/portal/agenda', [PortalController::class, 'agenda'])->name('portal.agenda');
    Route::get('/portal/calendario', [PortalController::class, 'calendario'])->name('portal.calendario');
    Route::get('/portal/pesquisas', [PortalController::class, 'pesquisas'])->name('portal.pesquisas');
    Route::get('/portal/pesquisa/{id}/responder', [PortalController::class, 'pesquisaResponder'])->name('portal.pesquisa.responder');
    Route::post('/portal/pesquisa/{id}/responder', [PortalController::class, 'pesquisaSalvar'])->name('portal.pesquisa.salvar');
    Route::get('/portal/saude', [PortalController::class, 'saude'])->name('portal.saude');
});
