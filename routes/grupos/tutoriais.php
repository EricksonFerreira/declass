<?php

use App\Http\Controllers\TutorialController;
use Illuminate\Support\Facades\Route;

// Pagina publica de tutoriais — qualquer usuario autenticado
Route::get('tutoriais', [TutorialController::class, 'index'])->name('tutoriais.index');

// Configuracao de tutoriais — somente superadmin
Route::middleware('can:acessar-superadmin')->group(function (): void {
    Route::get('tutoriais/gerenciar', [TutorialController::class, 'gerenciar'])->name('tutoriais.gerenciar');
    Route::get('tutoriais/novo', [TutorialController::class, 'create'])->name('tutoriais.create');
    Route::post('tutoriais', [TutorialController::class, 'store'])->name('tutoriais.store');
    Route::get('tutoriais/{tutorial}/editar', [TutorialController::class, 'edit'])->name('tutoriais.edit');
    Route::put('tutoriais/{tutorial}', [TutorialController::class, 'update'])->name('tutoriais.update');
    Route::delete('tutoriais/{tutorial}', [TutorialController::class, 'destroy'])->name('tutoriais.destroy');

    Route::post('tutoriais/categorias', [TutorialController::class, 'storeCategoria'])->name('tutoriais.categorias.store');
    Route::put('tutoriais/categorias/{categoria}', [TutorialController::class, 'updateCategoria'])->name('tutoriais.categorias.update');
    Route::delete('tutoriais/categorias/{categoria}', [TutorialController::class, 'destroyCategoria'])->name('tutoriais.categorias.destroy');
});
