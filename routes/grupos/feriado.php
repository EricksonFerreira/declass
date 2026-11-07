<?php

use App\Http\Controllers\FeriadoController;
use Illuminate\Support\Facades\Route;

Route::resource('feriado', FeriadoController::class)->except('show');
