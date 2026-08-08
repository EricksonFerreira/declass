<?php

use App\Http\Controllers\Clockify\ClockifyCalendarioController;
use App\Http\Controllers\Clockify\ClockifyClienteController;
use App\Http\Controllers\Clockify\ClockifyProjetoController;
use App\Http\Controllers\Clockify\ClockifyTagController;
use App\Http\Controllers\Clockify\ClockifyTimeController;
use App\Http\Controllers\Clockify\ClockifyTimeOffController;
use App\Http\Controllers\Clockify\ClockifyTrackerController;
use Illuminate\Support\Facades\Route;

Route::prefix('clockify')->name('clockify.')->group(function () {
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
    Route::delete('calendario/{id}', [ClockifyCalendarioController::class, 'destroy'])->name('calendario.destroy');

    // Time Off
    Route::get('timeoff', [ClockifyTimeOffController::class, 'index'])->name('timeoff.index');
    Route::post('timeoff', [ClockifyTimeOffController::class, 'store'])->name('timeoff.store');
    Route::post('timeoff/{id}/approve', [ClockifyTimeOffController::class, 'approve'])->name('timeoff.approve');
    Route::post('timeoff/{id}/reject', [ClockifyTimeOffController::class, 'reject'])->name('timeoff.reject');
    Route::delete('timeoff/{id}', [ClockifyTimeOffController::class, 'destroy'])->name('timeoff.destroy');

    // Resources
    Route::resource('clientes', ClockifyClienteController::class)->except(['create', 'show', 'edit']);
    Route::resource('projetos', ClockifyProjetoController::class)->except(['create', 'show', 'edit']);
    Route::resource('tags', ClockifyTagController::class)->except(['create', 'show', 'edit']);
    Route::resource('times', ClockifyTimeController::class)->except(['create', 'show', 'edit']);
});
