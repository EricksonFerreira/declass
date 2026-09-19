<?php

use App\Http\Controllers\EmpresaDocumentoController;
use Illuminate\Support\Facades\Route;

Route::prefix('documentos')->name('documentos.')->group(function () {
    Route::get('/', [EmpresaDocumentoController::class, 'index'])->name('index');
    Route::post('/', [EmpresaDocumentoController::class, 'store'])->name('store');
    Route::put('/{documento}', [EmpresaDocumentoController::class, 'update'])->name('update');
    Route::get('/{documento}/arquivo', [EmpresaDocumentoController::class, 'download'])->name('arquivo');
    Route::delete('/{documento}', [EmpresaDocumentoController::class, 'destroy'])->name('destroy');
});
