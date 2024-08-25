<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\Cards3Controller;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TipoTransacaoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('home');
});
Route::get('home',[HomeController::class,'home'])->middleware(['auth', 'verified'])->name('home');

Route::post('tipotransacao/setperpage',[TipoTransacaoController::class,'setPerPage'])->name('tipotransacao.setPerPage');
Route::get('tipotransacao/removerfiltro',[TipoTransacaoController::class,'removerFiltro']);
Route::get('tipotransacao/filtro',[TipoTransacaoController::class,'filtro']);
Route::resource('tipotransacao',TipoTransacaoController::class);

Route::resource('countries',PaisController::class);
// Route::resource('booking',BookingController::class);

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('home');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__.'/auth.php';

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/regra.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/permissao.php');

});


Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/usuario.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/pais.php');

});


Route::get('/powerbi/reports', [App\Http\Controllers\PowerBIController::class, 'listReports']);
Route::get('/powerbi/report/{reportId}', [App\Http\Controllers\PowerBIController::class, 'showReport'])->name('powerbi.report');


// Route::middleware('auth')
//     ->group(function() {
//         require base_path('routes/grupos/station.php');

// });

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/estacao.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/dia_util.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/horario_diario.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/frota.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/caracteristica_frota.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/frota_caracteristica.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/grupo_frota.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/caracteristica_grupo_frota.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/caracteristica_grupo.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/tipo_frota.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/tipovalorcaracteristicafrota.php');

});


Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/segmento.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/marca.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/modelo.php');

});


Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/termo_consentimento.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/veiculo.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/status_veiculo.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/movimentacao.php');

});


Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/reserva.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/tempo_entre_entacao.php');

});


Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/opcional.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/tipo_opcional.php');

});

Route::middleware('auth')
    ->group(function() {
        require base_path('routes/grupos/opcional_grupo_veiculo.php');

});


Route::middleware('web')
    ->group(function() {
        require base_path('routes/grupos/cupom_desconto.php');

});