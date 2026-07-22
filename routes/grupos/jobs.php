<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Reserva\ReservaController;

Route::get('atualizaStatusReservas', [ReservaController::class, 'atualizaStatusReservas'])->name('atualizaStatusReservas');
