<?php

use App\Http\Controllers\Clockify\ClockifyCalendarioController;
use App\Http\Controllers\Clockify\ClockifyClienteController;
use App\Http\Controllers\Clockify\ClockifyProjetoController;
use App\Http\Controllers\Clockify\ClockifyTagController;
use App\Http\Controllers\Clockify\ClockifyTimeController;
use App\Http\Controllers\Clockify\ClockifyTimeOffController;
use App\Http\Controllers\Clockify\ClockifyTrackerController;
use App\Http\Controllers\Clockify\FuncaoController;
use Illuminate\Support\Facades\Route;

Route::name('clockify.')->group(function () {
    // Time Tracker
    Route::get('tracker', [ClockifyTrackerController::class, 'index'])->name('tracker.index');
    Route::post('tracker/start', [ClockifyTrackerController::class, 'start'])->name('tracker.start');
    Route::post('tracker/stop', [ClockifyTrackerController::class, 'stop'])->name('tracker.stop');
    Route::post('tracker/manual', [ClockifyTrackerController::class, 'manualStore'])->name('tracker.manual');
    Route::put('tracker/{id}', [ClockifyTrackerController::class, 'update'])->name('tracker.update');
    Route::patch('tracker/active', [ClockifyTrackerController::class, 'updateActive'])->name('tracker.active.update');
    Route::delete('tracker/{id}', [ClockifyTrackerController::class, 'destroy'])->name('tracker.destroy');

    // Timesheet (Gestão de tempo)
    Route::get('timesheet', [ClockifyTrackerController::class, 'timesheet'])->name('timesheet');
    Route::post('timesheet/save', [ClockifyTrackerController::class, 'timesheetSave'])->name('timesheet.save');

    // Calendar
    Route::get('calendario', [ClockifyCalendarioController::class, 'index'])->name('calendario.index');
    Route::post('calendario', [ClockifyCalendarioController::class, 'store'])->name('calendario.store');
    Route::patch('calendario/{id}/mover', [ClockifyCalendarioController::class, 'mover'])->name('calendario.mover');
    Route::delete('calendario/{id}', [ClockifyCalendarioController::class, 'destroy'])->name('calendario.destroy');

    // Time Off
    Route::get('timeoff', [ClockifyTimeOffController::class, 'index'])->name('timeoff.index');
    Route::post('timeoff', [ClockifyTimeOffController::class, 'store'])->name('timeoff.store');
    Route::put('timeoff/{id}', [ClockifyTimeOffController::class, 'update'])->name('timeoff.update');
    Route::post('timeoff/{id}/approve', [ClockifyTimeOffController::class, 'approve'])->name('timeoff.approve');
    Route::post('timeoff/{id}/reject', [ClockifyTimeOffController::class, 'reject'])->name('timeoff.reject');
    Route::delete('timeoff/{id}', [ClockifyTimeOffController::class, 'destroy'])->name('timeoff.destroy');

    // Resources
    Route::get('clientes/filtro', [ClockifyClienteController::class, 'filtro'])->name('clientes.filtro');
    Route::get('clientes/removerfiltro', [ClockifyClienteController::class, 'removerFiltro'])->name('clientes.removerFiltro');
    Route::post('clientes/setperpage', [ClockifyClienteController::class, 'setPerPage'])->name('clientes.setPerPage');
    Route::resource('clientes', ClockifyClienteController::class)->except(['create', 'show', 'edit']);

    // Projetos com rotas de planejamento
    Route::get('projetos/{id}/planejamento', [ClockifyProjetoController::class, 'planejamento'])->name('projetos.planejamento');
    Route::post('projetos/{id}/planejamento', [ClockifyProjetoController::class, 'planejamentoStore'])->name('projetos.planejamento.store');
    Route::get('projetos/{id}/cronogramas/novo', [ClockifyProjetoController::class, 'cronogramaNovo'])->name('projetos.cronogramas.novo');
    Route::post('projetos/{id}/cronogramas', [ClockifyProjetoController::class, 'cronogramaStore'])->name('projetos.cronogramas.store');
    Route::delete('cronogramas/{id}', [ClockifyProjetoController::class, 'cronogramaDestroy'])->name('projetos.cronogramas.destroy');
    Route::get('projetos/{id}/planejar-equipe', [ClockifyProjetoController::class, 'planejarEquipe'])->name('projetos.planejar-equipe');
    Route::post('projetos/{id}/planejar-equipe', [ClockifyProjetoController::class, 'planejarEquipeStore'])->name('projetos.planejar-equipe.store');
    Route::get('planejar-equipe', [ClockifyProjetoController::class, 'planejarEquipeGeral'])->name('projetos.planejar-equipe-geral');
    Route::post('planejar-equipe', [ClockifyProjetoController::class, 'planejarEquipeGeralStore'])->name('projetos.planejar-equipe-geral.store');
    Route::get('projetos/filtro', [ClockifyProjetoController::class, 'filtro'])->name('projetos.filtro');
    Route::get('projetos/removerfiltro', [ClockifyProjetoController::class, 'removerFiltro'])->name('projetos.removerFiltro');
    Route::post('projetos/setperpage', [ClockifyProjetoController::class, 'setPerPage'])->name('projetos.setPerPage');
    Route::resource('projetos', ClockifyProjetoController::class)->except(['create', 'edit']);

    Route::get('tags/filtro', [ClockifyTagController::class, 'filtro'])->name('tags.filtro');
    Route::get('tags/removerfiltro', [ClockifyTagController::class, 'removerFiltro'])->name('tags.removerFiltro');
    Route::post('tags/setperpage', [ClockifyTagController::class, 'setPerPage'])->name('tags.setPerPage');
    Route::resource('tags', ClockifyTagController::class)->except(['create', 'show', 'edit']);

    Route::get('funcoes/filtro', [FuncaoController::class, 'filtro'])->name('funcoes.filtro');
    Route::get('funcoes/removerfiltro', [FuncaoController::class, 'removerFiltro'])->name('funcoes.removerFiltro');
    Route::post('funcoes/setperpage', [FuncaoController::class, 'setPerPage'])->name('funcoes.setPerPage');
    Route::resource('funcoes', FuncaoController::class)->except(['create', 'show', 'edit']);

    Route::get('times/filtro', [ClockifyTimeController::class, 'filtro'])->name('times.filtro');
    Route::get('times/removerfiltro', [ClockifyTimeController::class, 'removerFiltro'])->name('times.removerFiltro');
    Route::post('times/setperpage', [ClockifyTimeController::class, 'setPerPage'])->name('times.setPerPage');
    Route::resource('times', ClockifyTimeController::class)->except(['create', 'show', 'edit']);
});
