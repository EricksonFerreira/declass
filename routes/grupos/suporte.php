<?php

use App\Http\Controllers\ChamadoController;
use Illuminate\Support\Facades\Route;

Route::prefix('suporte')->name('chamado.')->group(function () {
    Route::get('/criar', [ChamadoController::class, 'criar'])->name('create');
    Route::post('/criar', [ChamadoController::class, 'store'])->name('store');
    Route::get('/meus-chamados', [ChamadoController::class, 'meus'])->name('meus');
    Route::post('/meus-chamados/{chamado}/mensagem', [ChamadoController::class, 'enviarMensagemCliente'])->name('meus.mensagem');
    Route::get('/atendimento-chamados', [ChamadoController::class, 'atendimento'])->middleware('can:acessar-superadmin')->name('atendimento');
    Route::get('/atendimento-chamados/fila-total', [ChamadoController::class, 'atendimentoFilaTotal'])->middleware('can:acessar-superadmin')->name('atendimento.total');
    Route::get('/atendimento-chamados/aguardando-analise', [ChamadoController::class, 'atendimentoAguardando'])->middleware('can:acessar-superadmin')->name('atendimento.aguardando');
    Route::get('/atendimento-chamados/em-atendimento', [ChamadoController::class, 'atendimentoEmAtendimento'])->middleware('can:acessar-superadmin')->name('atendimento.em');
    Route::get('/atendimento-chamados/concluidos', [ChamadoController::class, 'atendimentoConcluidos'])->middleware('can:acessar-superadmin')->name('atendimento.concluidos');
    Route::post('/atendimento-chamados/{chamado}/assumir', [ChamadoController::class, 'assumirAtendimento'])->middleware('can:acessar-superadmin')->name('atendimento.assumir');
    Route::post('/atendimento-chamados/{chamado}/cancelar', [ChamadoController::class, 'cancelarAtendimento'])->middleware('can:acessar-superadmin')->name('atendimento.cancelar');
    Route::post('/atendimento-chamados/{chamado}/mensagem', [ChamadoController::class, 'enviarMensagem'])->middleware('can:acessar-superadmin')->name('atendimento.mensagem');
    Route::post('/atendimento-chamados/{chamado}/status', [ChamadoController::class, 'atualizarStatusAtendimento'])->middleware('can:acessar-superadmin')->name('atendimento.status');
    Route::post('/atendimento-chamados/{chamado}/concluir', [ChamadoController::class, 'concluirAtendimento'])->middleware('can:acessar-superadmin')->name('atendimento.concluir');
    Route::post('/atendimento-chamados/{chamado}/transferir', [ChamadoController::class, 'transferirAtendimento'])->middleware('can:acessar-superadmin')->name('atendimento.transferir');
    Route::get('/subcategorias', [ChamadoController::class, 'subcategorias'])->name('subcategorias');
});
