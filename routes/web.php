<?php

use App\Http\Controllers\EmpresaContextController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\PowerBIController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\SiteApresentacaoController;
use App\Http\Controllers\SiteReservaController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\TipoTransacaoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteApresentacaoController::class, 'index'])->name('site.apresentacao');
Route::get('/site/estacoes', [SiteApresentacaoController::class, 'estacoesOptions'])->name('site.apresentacao.estacoes');
Route::get('/administrativa', [SiteReservaController::class, 'adminEntry'])->name('site.admin.entry');
Route::get('/reserva', [SiteReservaController::class, 'index'])->name('site.reserva.index');
Route::get('/reserva/novo', [SiteReservaController::class, 'index'])->name('site.reserva.create');
Route::get('/reserva/login', [SiteReservaController::class, 'loginRedirect'])->name('site.reserva.login');
Route::post('/reserva', [SiteReservaController::class, 'store'])->name('site.reserva.store');
Route::get('/reserva/disponibilidade', [SiteReservaController::class, 'disponibilidade'])->name('site.reserva.disponibilidade');
Route::post('/reserva/verificar-cupom', [SiteReservaController::class, 'verificarCupom'])->name('site.reserva.verificarCupom');
Route::get('/reserva/{reserva}/sucesso', [SiteReservaController::class, 'success'])->name('site.reserva.success');
Route::get('/reserva/{reserva}/pagamento', [SiteReservaController::class, 'pagamento'])->name('site.reserva.pagamento');
Route::post('/reserva/{reserva}/pagamento', [SiteReservaController::class, 'processarPagamento'])->name('site.reserva.processarPagamento');
Route::post('/reserva/{reserva}/pagamento/metodo', [SiteReservaController::class, 'adicionarMetodoPagamento'])->name('site.reserva.pagamento.metodo');

Route::prefix(trim((string) config('app.admin_prefix', 'admin'), '/'))->group(function (): void {
    Route::get('/cadastro/sucesso', function () {
        return view('auth.register-success');
    })->name('register.success');

    Route::get('home', [HomeController::class, 'home'])->middleware(['auth', 'verified'])->name('home');
    Route::get('home/chart-data', [HomeController::class, 'chartData'])->middleware(['auth', 'verified'])->name('home.chart-data');
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

    Route::post('tipotransacao/setperpage', [TipoTransacaoController::class, 'setPerPage'])->name('tipotransacao.setPerPage');
    Route::get('tipotransacao/removerfiltro', [TipoTransacaoController::class, 'removerFiltro']);
    Route::get('tipotransacao/filtro', [TipoTransacaoController::class, 'filtro']);
    Route::resource('tipotransacao', TipoTransacaoController::class);
    Route::resource('countries', PaisController::class);

    Route::get('/pagamento/teste2', [PagamentoController::class, 'teste2']);
    Route::any('/pagamento/teste', [PagamentoController::class, 'teste'])->name('pagamento.teste');
    Route::post('/pagamento/cobrar', [PagamentoController::class, 'cobrar']);

    Route::post('/stripe/processPayment', [StripeController::class, 'processPayment'])->name('stripe.processPayment');
    Route::get('/stripe/payment', [StripeController::class, 'payment'])->name('stripe.payment');
    Route::get('/stripe/failure', [StripeController::class, 'failure'])->name('stripe.failure');
    Route::get('/stripe/success', [StripeController::class, 'success2'])->name('stripe.success');
    Route::get('/checkout', [StripeController::class, 'checkout'])->name('checkout');
    Route::get('/success', [StripeController::class, 'success'])->name('success');
    Route::get('/cancel', [StripeController::class, 'cancel'])->name('cancel');
    Route::get('/subscription-checkout', [StripeController::class, 'subscriptionCheckout'])->name('subscription-checkout');

    Route::middleware('auth')->group(function (): void {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::get('/profile/senha', [ProfileController::class, 'editPassword'])->name('profile.password.edit');
        Route::put('/profile/senha', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
        Route::get('/profile/primeiro-acesso/senha', [ProfileController::class, 'forcePasswordEdit'])->name('profile.force-password.edit');
        Route::put('/profile/primeiro-acesso/senha', [ProfileController::class, 'forcePasswordUpdate'])->name('profile.force-password.update');
    });

    require __DIR__ . '/auth.php';

    $authGroupFiles = [
        'reserva',
        'regra',
        'tipo_usuario',
        'permissao',
        'usuario',
        'empresa',
        'replicacao_base',
        'nfse',
        'financeiro',
        'pais',
        'estacao',
        'dia_util',
        'horario_diario',
        'frota',
        'caracteristica_frota',
        'frota_caracteristica',
        'grupo_frota',
        'caracteristica_grupo_frota',
        'caracteristica_grupo',
        'tipo_frota',
        'tipovalorcaracteristicafrota',
        'segmento',
        'marca',
        'modelo',
        'termo_consentimento',
        'veiculo',
        'status_veiculo',
        'movimentacao',
        'tempo_entre_entacao',
        'opcional',
        'tipo_opcional',
        'opcional_grupo_veiculo',
        'cupom_desconto',
        'tipo_movimentacao',
        'status_movimentacao',
        'status_reserva',
    ];

    foreach ($authGroupFiles as $groupFile) {
        Route::middleware('auth')->group(function () use ($groupFile): void {
            require base_path('routes/grupos/' . $groupFile . '.php');
        });
    }

    Route::middleware(['auth', 'can:gerenciar-empresa'])->group(function (): void {
        Route::get('empresa-contexto', [EmpresaContextController::class, 'edit'])->name('empresa_context.edit');
        Route::post('empresa-contexto', [EmpresaContextController::class, 'update'])->name('empresa_context.update');
    });

    Route::get('/powerbi/reports', [PowerBIController::class, 'listReports']);
    Route::get('/powerbi/report/{reportId}', [PowerBIController::class, 'showReport'])->name('powerbi.report');

    Route::middleware('api')->group(function (): void {
        require base_path('routes/grupos/status_reserva.php');
    });

    $webGroupFiles = [
        'jobs',
        'checklist',
        'item_checklist',
        'status_item_checklist',
        'tipo_checklist',
        'configuracao_checklist',
        'categoria_dano',
        'dano',
        'veiculo_dano_monitoramento',
        'ordem_servico',
    ];

    foreach ($webGroupFiles as $groupFile) {
        Route::middleware('web')->group(function () use ($groupFile): void {
            require base_path('routes/grupos/' . $groupFile . '.php');
        });
    }

    Route::post('/reserva/salvar-assinatura', [ReservaController::class, 'salvarAssinatura'])->name('reserva.salvar.assinatura');
});
