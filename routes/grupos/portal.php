<?php

use App\Http\Controllers\PortalController;
use App\Http\Controllers\PortalTurmaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/portal', [PortalController::class, 'index'])->name('portal.index');

    // Filtro padrao (sessao) das telas do portal — mesmo padrao do admin
    Route::get('/portal/{tela}/filtro', [PortalController::class, 'filtro'])
        ->where('tela', 'disciplinas|turmas|horarios|frequencia|notas|alertas|avisos|agenda|financeiro|solicitacoes|tarefas')
        ->name('portal.filtro');
    Route::match(['get', 'post'], '/portal/{tela}/removerfiltro', [PortalController::class, 'removerFiltro'])
        ->where('tela', 'disciplinas|turmas|horarios|frequencia|notas|alertas|avisos|agenda|financeiro|solicitacoes|tarefas')
        ->name('portal.removerFiltro');

    // Menu do aluno/familia
    Route::get('/portal/disciplinas', [PortalController::class, 'disciplinas'])->name('portal.disciplinas');
    Route::get('/portal/turmas', [PortalController::class, 'turmas'])->name('portal.turmas');
    Route::get('/portal/horarios', [PortalController::class, 'horarios'])->name('portal.horarios');
    Route::get('/portal/frequencia', [PortalController::class, 'frequencia'])->name('portal.frequencia');
    Route::get('/portal/notas', [PortalController::class, 'notas'])->name('portal.notas');
    Route::get('/portal/alertas', [PortalController::class, 'alertas'])->name('portal.alertas');
    Route::get('/portal/solicitacoes', [PortalController::class, 'solicitacoes'])->name('portal.solicitacoes');
    Route::get('/portal/solicitacoes/nova', [PortalController::class, 'solicitacaoNova'])->name('portal.solicitacoes.create');
    Route::post('/portal/solicitacoes', [PortalController::class, 'solicitacaoStore'])->name('portal.solicitacoes.store');
    Route::post('/portal/solicitacoes/{id}/mensagem', [PortalController::class, 'solicitacaoMensagem'])->name('portal.solicitacoes.mensagem');
    Route::get('/portal/solicitacoes/{id}/anexos/{anexo}', [PortalController::class, 'solicitacaoAnexo'])->name('portal.solicitacoes.anexo');
    Route::get('/portal/documentos', [PortalController::class, 'documentos'])->name('portal.documentos');
    Route::post('/portal/documentos/{id}/enviar', [PortalController::class, 'documentoEnviar'])->name('portal.documentos.enviar');

    // Hub da turma (classroom): avisos, materiais, pessoas — forum por disciplina
    Route::get('/portal/turma/{turma}', [PortalTurmaController::class, 'show'])->name('portal.turma');
    Route::get('/portal/turma/{turma}/disciplina/{disciplina}', [PortalTurmaController::class, 'disciplinaShow'])->name('portal.turma.disciplina');
    Route::post('/portal/turma/{turma}/disciplina/{disciplina}/forum', [PortalTurmaController::class, 'forumStore'])->name('portal.turma.disciplina.forum.store');
    Route::get('/portal/turma/{turma}/disciplina/{disciplina}/forum', [PortalTurmaController::class, 'forumPosts'])->name('portal.turma.disciplina.forum.posts');
    Route::post('/portal/turma/{turma}/disciplina/{disciplina}/forum/{post}/lida', [PortalTurmaController::class, 'forumMarcarLida'])->name('portal.turma.disciplina.forum.lida');
    Route::post('/portal/turma/{turma}/disciplina/{disciplina}/forum/{post}/comentar', [PortalTurmaController::class, 'forumComentar'])->name('portal.turma.disciplina.forum.comentar');
    Route::put('/portal/turma/{turma}/disciplina/{disciplina}/forum/{post}', [PortalTurmaController::class, 'forumUpdate'])->name('portal.turma.disciplina.forum.update');
    Route::patch('/portal/turma/{turma}/disciplina/{disciplina}/forum/{post}/encerrar', [PortalTurmaController::class, 'forumToggleEncerrada'])->name('portal.turma.disciplina.forum.encerrar');
    Route::get('/portal/turma/{turma}/tarefa/{tarefa}', [PortalTurmaController::class, 'tarefaShow'])->name('portal.turma.tarefa');
    Route::post('/portal/turma/{turma}/tarefa/{tarefa}', [PortalTurmaController::class, 'tarefaResponder'])->name('portal.turma.tarefa.responder');
    Route::get('/portal/turma/{turma}/disciplina/{disciplina}/tarefa/{tarefa}', [PortalTurmaController::class, 'disciplinaTarefaShow'])->name('portal.turma.disciplina.tarefa');
    Route::post('/portal/turma/{turma}/disciplina/{disciplina}/tarefa/{tarefa}', [PortalTurmaController::class, 'disciplinaTarefaResponder'])->name('portal.turma.disciplina.tarefa.responder');

    // Paginas existentes do portal
    Route::get('/portal/aluno/{aluno}/pdf', [PortalController::class, 'alunoPdf'])->name('portal.aluno.pdf');
    Route::get('/portal/financeiro', [PortalController::class, 'financeiro'])->name('portal.financeiro');
    Route::get('/portal/mensalidades/{id}/recibo', [PortalController::class, 'reciboMensalidade'])->name('portal.mensalidade.recibo');
    Route::get('/portal/mensalidades/{id}/boleto', [PortalController::class, 'boletoMensalidade'])->name('portal.mensalidade.boleto');
    Route::get('/portal/avisos', [PortalController::class, 'avisos'])->name('portal.avisos');
    Route::get('/portal/transporte', [PortalController::class, 'transporte'])->name('portal.transporte');
    Route::get('/portal/cardapio', [PortalController::class, 'cardapio'])->name('portal.cardapio');
    Route::get('/portal/tarefas', [PortalController::class, 'tarefas'])->name('portal.tarefas');
    Route::get('/portal/meus-emprestimos', [PortalController::class, 'emprestimos'])->name('portal.emprestimos');
    Route::get('/portal/agenda', [PortalController::class, 'agenda'])->name('portal.agenda');
    Route::get('/portal/calendario', [PortalController::class, 'calendario'])->name('portal.calendario');
    Route::get('/portal/pesquisas', [PortalController::class, 'pesquisas'])->name('portal.pesquisas');
    Route::get('/portal/pesquisa/{id}/responder', [PortalController::class, 'pesquisaResponder'])->name('portal.pesquisa.responder');
    Route::post('/portal/pesquisa/{id}/responder', [PortalController::class, 'pesquisaSalvar'])->name('portal.pesquisa.salvar');
    Route::get('/portal/saude', [PortalController::class, 'saude'])->name('portal.saude');
});
