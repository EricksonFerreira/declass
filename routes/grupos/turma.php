<?php

use App\Http\Controllers\AtribuicaoController;
use App\Http\Controllers\DiarioController;
use App\Http\Controllers\TurmaConteudoController;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\TurmaDisciplinaController;
use App\Http\Controllers\TurmaGradeController;
use Illuminate\Support\Facades\Route;

Route::prefix('turma')->name('turma.')->group(function () {
    Route::post('setperpage', [TurmaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [TurmaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [TurmaController::class, 'filtro'])->name('filtro');
    Route::post('gerar-lote', [TurmaController::class, 'gerarLote'])->name('gerar_lote');
    Route::post('replicar-organizacao', [TurmaController::class, 'replicarOrganizacao'])->name('replicar_organizacao');

    // Chamada do dia: abre a chamada se ja existe aula hoje, senao leva ao registro da aula
    Route::get('{turma}/chamada', [TurmaController::class, 'chamada'])->name('chamada');

    // Grade horaria da turma
    Route::get('{turma}/grade', [TurmaGradeController::class, 'grade'])->name('grade');
    Route::post('{turma}/grade/celula', [TurmaGradeController::class, 'celulaStore'])->name('grade.celula.store');
    Route::delete('{turma}/grade/celula', [TurmaGradeController::class, 'celulaDestroy'])->name('grade.celula.destroy');
    Route::get('{turma}/grade/pdf', [TurmaGradeController::class, 'pdf'])->name('grade.pdf');

    // Telas aninhadas a turma: URL traz a turma no caminho e o
    // breadcrumb mostra Turma > pagina (mantem as permissoes do
    // controller original).
    Route::get('{turma}/calendario', [\App\Http\Controllers\CalendarioController::class, 'index'])->name('calendario');
    Route::get('{turma}/justificativas', [\App\Http\Controllers\JustificativaController::class, 'index'])->name('justificativas');

    Route::get('{turma}/chamada/create', [DiarioController::class, 'chamada'])->name('chamada.create');

    // Telas de criacao aninhadas a turma (breadcrumb mostra a turma).
    Route::get('{turma}/avaliacao/create', [\App\Http\Controllers\AvaliacaoController::class, 'create'])->name('avaliacao.create');
    Route::get('{turma}/tarefa/create', [\App\Http\Controllers\TarefaController::class, 'create'])->name('tarefa.create');
    Route::get('{turma}/matricula/create', [\App\Http\Controllers\MatriculaController::class, 'create'])->name('matricula.create');
    Route::get('{turma}/justificativa/create', [\App\Http\Controllers\JustificativaController::class, 'create'])->name('justificativa.create');

    // Disciplinas ofertadas pela turma
    Route::get('{turma}/disciplinas/{disciplina}', [TurmaDisciplinaController::class, 'show'])->name('disciplina.show');
    Route::post('{turma}/disciplinas', [TurmaDisciplinaController::class, 'store'])->name('disciplina.store');
    Route::put('{turma}/disciplinas/{disciplina}', [TurmaDisciplinaController::class, 'update'])->name('disciplina.update');
    Route::delete('{turma}/disciplinas/{disciplina}', [TurmaDisciplinaController::class, 'destroy'])->name('disciplina.destroy');

    // Atribuicoes de professores
    Route::post('{turma}/atribuicoes', [AtribuicaoController::class, 'store'])->name('atribuicao.store');
    Route::put('{turma}/atribuicoes/{atribuicao}', [AtribuicaoController::class, 'update'])->name('atribuicao.update');
    Route::delete('{turma}/atribuicoes/{atribuicao}', [AtribuicaoController::class, 'destroy'])->name('atribuicao.destroy');

    // Materiais da turma (aba na tela da turma) e por disciplina (tela da disciplina)
    Route::post('{turma}/materiais', [TurmaConteudoController::class, 'materialStore'])->name('material.store');
    Route::delete('{turma}/materiais/{material}', [TurmaConteudoController::class, 'materialDestroy'])->name('material.destroy');
    Route::post('{turma}/disciplinas/{disciplina}/materiais', [TurmaConteudoController::class, 'disciplinaMaterialStore'])->name('disciplina.material.store');
    Route::delete('{turma}/disciplinas/{disciplina}/materiais/{material}', [TurmaConteudoController::class, 'disciplinaMaterialDestroy'])->name('disciplina.material.destroy');

    // Forum da disciplina (aba na tela da disciplina)
    Route::post('{turma}/disciplinas/{disciplina}/forum', [TurmaConteudoController::class, 'forumStore'])->name('forum.store');
    Route::post('{turma}/disciplinas/{disciplina}/forum/{post}/comentar', [TurmaConteudoController::class, 'forumComentar'])->name('forum.comentar');
    Route::put('{turma}/disciplinas/{disciplina}/forum/{post}', [TurmaConteudoController::class, 'forumUpdate'])->name('forum.update');
    Route::patch('{turma}/disciplinas/{disciplina}/forum/{post}/encerrar', [TurmaConteudoController::class, 'forumToggleEncerrada'])->name('forum.encerrar');
    Route::delete('{turma}/disciplinas/{disciplina}/forum/{post}', [TurmaConteudoController::class, 'forumDestroy'])->name('forum.destroy');
});
Route::resource('turma', TurmaController::class);
