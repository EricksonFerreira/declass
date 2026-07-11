<?php

use App\Http\Controllers\EmpresaContextController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\TourController;
use App\Http\Controllers\IndicadoresController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\PowerBIController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FipeCatalogController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\SiteApresentacaoController;
use App\Http\Controllers\SiteReservaController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TipoTransacaoController;
use App\Http\Controllers\EmpresaAlertaController;
use App\Http\Controllers\MigrationController;
use App\Http\Controllers\CadastroEmpresaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Webhook Stripe — sem CSRF, sem auth
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::patch('alerta/{alerta}/resolver', [EmpresaAlertaController::class, 'resolver'])->name('alerta.resolver');

    // Tour guiado — status e conclusão por tela
    Route::get('tour/status/{chave}',    [TourController::class, 'status'])->name('tour.status');
    Route::post('tour/concluir/{chave}', [TourController::class, 'concluir'])->name('tour.concluir');
    Route::post('tour/resetar/{chave}',  [TourController::class, 'resetar'])->name('tour.resetar');
});

Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle'])
    ->name('webhook.stripe')
    ->withoutMiddleware('web');

Route::get('/', [SiteApresentacaoController::class, 'index'])->name('site.apresentacao');
Route::get('/sobre', [SiteApresentacaoController::class, 'sobre'])->name('site.sobre');
Route::get('/site/estacoes', [SiteApresentacaoController::class, 'estacoesOptions'])->name('site.apresentacao.estacoes');
Route::get('/sitemap.xml', function (Request $request) {
    $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
    $lastMod = now()->toAtomString();

    $urls = [
        [
            'loc' => $baseUrl . '/',
            'priority' => '1.0',
            'changefreq' => 'daily',
        ],
        [
            'loc' => $baseUrl . '/reserva',
            'priority' => '0.9',
            'changefreq' => 'weekly',
        ],
        [
            'loc' => $baseUrl . '/sobre',
            'priority' => '0.6',
            'changefreq' => 'monthly',
        ],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($urls as $url) {
        $xml .= "  <url>\n";
        $xml .= '    <loc>' . e($url['loc']) . "</loc>\n";
        $xml .= '    <lastmod>' . $lastMod . "</lastmod>\n";
        $xml .= '    <changefreq>' . e($url['changefreq']) . "</changefreq>\n";
        $xml .= '    <priority>' . e($url['priority']) . "</priority>\n";
        $xml .= "  </url>\n";
    }

    $xml .= "</urlset>\n";

    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap.xml');
Route::get('/administrativa', [SiteReservaController::class, 'adminEntry'])->name('site.admin.entry');
Route::get('/reserva', [SiteReservaController::class, 'index'])->name('site.reserva.index');
Route::get('/reserva/novo', [SiteReservaController::class, 'index'])->name('site.reserva.create');
Route::get('/reserva/login', [SiteReservaController::class, 'loginRedirect'])->name('site.reserva.login');
Route::get('/reserva/login/callback', [SiteReservaController::class, 'loginCallback'])->middleware('auth')->name('site.reserva.login.callback');
Route::post('/reserva', [SiteReservaController::class, 'store'])->name('site.reserva.store');
Route::get('/reserva/disponibilidade', [SiteReservaController::class, 'disponibilidade'])->name('site.reserva.disponibilidade');
Route::get('/reserva/dias-disponiveis', [SiteReservaController::class, 'diasDisponiveisPorEstacao'])->name('site.reserva.diasDisponiveisPorEstacao');
Route::get('/reserva/agenda-disponivel', [SiteReservaController::class, 'agendaDisponivelPorEstacao'])->name('site.reserva.agendaDisponivelPorEstacao');
Route::get('/reserva/estacoes-disponiveis', [SiteReservaController::class, 'estacoesDisponiveis'])->name('site.reserva.estacoesDisponiveis');
Route::post('/reserva/verificar-cupom', [SiteReservaController::class, 'verificarCupom'])->name('site.reserva.verificarCupom');
Route::get('/reserva/{reserva}/sucesso', [SiteReservaController::class, 'success'])->name('site.reserva.success');
Route::post('/reserva/{reserva}/satisfacao', [SiteReservaController::class, 'salvarSatisfacao'])->name('site.reserva.satisfacao.store');
Route::get('/reserva/{reserva}/pagamento', [SiteReservaController::class, 'pagamento'])->name('site.reserva.pagamento');
Route::post('/reserva/{reserva}/pagamento', [SiteReservaController::class, 'processarPagamento'])->name('site.reserva.processarPagamento');
Route::post('/reserva/{reserva}/pagamento/metodo', [SiteReservaController::class, 'adicionarMetodoPagamento'])->name('site.reserva.pagamento.metodo');
Route::get('/multa/{multa}/pagamento', [SiteReservaController::class, 'pagamentoMulta'])->name('site.multa.pagamento');
Route::post('/multa/{multa}/pagamento', [SiteReservaController::class, 'processarPagamentoMulta'])->name('site.multa.processarPagamento');
Route::post('/multa/{multa}/pagamento/metodo', [SiteReservaController::class, 'adicionarMetodoPagamentoMulta'])->name('site.multa.pagamento.metodo');
Route::get('/job/fipe/sincronizar-catalogo', [FipeCatalogController::class, 'run'])->name('job.fipe.catalogo.run');
Route::get('/job/fipe/sincronizar-catalogo/motos-caminhoes', [FipeCatalogController::class, 'runMotorcyclesAndTrucks'])->name('job.fipe.catalogo.motos-caminhoes.run');

Route::prefix(trim((string) config('app.admin_prefix', 'admin'), '/'))->group(function (): void {
    Route::middleware(['auth'])->get('/', function () {
        return redirect('/admin/home');
    })->name('admin.index');

    // Comecar agora (cadastro rapido de empresa + usuario unico com senha padrao).
    Route::middleware('guest')->get('/comecar', [CadastroEmpresaController::class, 'create'])->name('cadastro.empresa.create');
    Route::middleware('guest')->post('/comecar', [CadastroEmpresaController::class, 'store'])->name('cadastro.empresa.store');
    Route::middleware('guest')->get('/comecar/sucesso', [CadastroEmpresaController::class, 'success'])->name('cadastro.empresa.success');

    Route::get('/cadastro/sucesso', function () {
        return view('auth.register-success');
    })->name('register.success');

    Route::get('home', [HomeController::class, 'home'])->middleware(['auth'])->name('home');
    Route::get('home/chart-data', [HomeController::class, 'chartData'])->middleware(['auth'])->name('home.chart-data');
    Route::get('indicadores', [IndicadoresController::class, 'index'])->middleware(['auth'])->name('indicadores.index');
    Route::get('indicadores/{slug}', [IndicadoresController::class, 'show'])->middleware(['auth'])->name('indicadores.show');
    Route::get('/dashboard', function () {
        return redirect()->route('reserva.minhas');
    })->middleware('auth')->name('dashboard');

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
        Route::get('/notificacoes', [NotificacaoController::class, 'index'])->name('notificacoes.index');
        Route::get('/notificacoes/navbar', [NotificacaoController::class, 'navbar'])->name('notificacoes.navbar');
        Route::patch('/notificacoes/{notification}/lida', [NotificacaoController::class, 'marcarLida'])->name('notificacoes.marcarLida');
        Route::patch('/notificacoes/lidas', [NotificacaoController::class, 'marcarTodasComoLidas'])->name('notificacoes.marcarTodasComoLidas');
    });

    // Rota segura para executar migrations via web (use apenas em ambientes controlados)
    // Protegida por middleware auth + can:gerenciar-empresa. Opcional: defina APP_MIGRATION_ROUTE_TOKEN para usar ?token=SECRET
    Route::middleware(['auth', 'can:gerenciar-empresa'])->group(function () {
        Route::get('migrations/run', [MigrationController::class, 'run'])->name('migrations.run');
        Route::get('migrations/seed', [MigrationController::class, 'seed'])->name('migrations.seed');
        Route::get('migrations/run-all', [MigrationController::class, 'runAll'])->name('migrations.run-all');
        Route::get('migrations/fix-gateway-customer', [MigrationController::class, 'fixGatewayCustomer'])->name('migrations.fix-gateway-customer');
        Route::get('fipe/sincronizar-catalogo', [FipeCatalogController::class, 'run'])->name('fipe.catalogo.run');
        Route::get('fipe/sincronizar-catalogo/motos-caminhoes', [FipeCatalogController::class, 'runMotorcyclesAndTrucks'])->name('fipe.catalogo.motos-caminhoes.run');
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
        'feriado',
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
        'plano',
        'onboarding',
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

    // Route::get('/powerbi/reports', [PowerBIController::class, 'listReports']);
    // Route::get('/powerbi/report/{reportId}', [PowerBIController::class, 'showReport'])->name('powerbi.report');

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
        'multa',
        'sinistro',
    ];

    foreach ($webGroupFiles as $groupFile) {
        Route::middleware('web')->group(function () use ($groupFile): void {
            require base_path('routes/grupos/' . $groupFile . '.php');
        });
    }

    Route::post('/reserva/salvar-assinatura', [ReservaController::class, 'salvarAssinatura'])->name('reserva.salvar.assinatura');
});

Route::delete('/admin/veiculo-danos/{id}', function ($id) {
    $dano = \App\Models\VeiculoDanos::find($id);
    if ($dano) { $dano->imagem()->delete(); $dano->delete(); }
    return response()->json(['success' => true]);
})->middleware(['auth', 'can:acessar-superadmin']);

Route::post('/limpar-cache', function () {
    Artisan::call('cache:clear');
    return back()->with('success', 'Cache limpo com sucesso!');
})->middleware(['auth', 'can:acessar-superadmin'])->name('limpar-cache');
