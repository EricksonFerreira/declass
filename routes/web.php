<?php

use App\Http\Controllers\CadastroEmpresaController;
use App\Http\Controllers\EmpresaAlertaController;
use App\Http\Controllers\EmpresaContextController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MigrationController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\ProfileController;
use App\Support\EmpresaDominio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Dominio/subdominio de empresa (ex.: altys.declock.app) vai direto
    // para a tela de login, que ja resolve a marca pelo host.
    if (EmpresaDominio::porHost(request()->getHost())) {
        return redirect()->route('login');
    }

    return view('welcome');
})->name('site.apresentacao');

Route::get('/manifest.webmanifest', function () {
    return response()->json([
        'name' => 'Clockify',
        'short_name' => 'Clockify',
        'start_url' => '/',
        'display' => 'standalone',
    ]);
})->name('site.manifest');

Route::get('/pwa/icon', function () {
    return response('', 200)->header('Content-Type', 'image/png');
})->name('site.manifest.icon');

Route::get('/sobre', function () {
    return view('sobre');
})->name('site.sobre');

Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('alertas', [EmpresaAlertaController::class, 'index'])->name('alerta.index');
    Route::patch('alerta/{alerta}/resolver', [EmpresaAlertaController::class, 'resolver'])->name('alerta.resolver');
});

Route::get('/sitemap.xml', function (Request $request) {
    $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
    $lastMod = now()->toAtomString();

    $urls = [
        [
            'loc' => $baseUrl.'/',
            'priority' => '1.0',
            'changefreq' => 'daily',
        ],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

    foreach ($urls as $url) {
        $xml .= "  <url>\n";
        $xml .= '    <loc>'.e($url['loc'])."</loc>\n";
        $xml .= '    <lastmod>'.$lastMod."</lastmod>\n";
        $xml .= '    <changefreq>'.e($url['changefreq'])."</changefreq>\n";
        $xml .= '    <priority>'.e($url['priority'])."</priority>\n";
        $xml .= "  </url>\n";
    }

    $xml .= "</urlset>\n";

    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap.xml');

// Setup de producao — publico, protegido pela senha APP_MIGRATION_ROUTE_TOKEN do .env
Route::get('migrations/setup-producao', [MigrationController::class, 'setupProducao'])->name('migrations.setup-producao');

// Migrations/seed via web — somente superadmin
Route::middleware(['auth', 'can:acessar-superadmin'])->group(function () {
    Route::get('migrations/prod', [MigrationController::class, 'run'])->name('migrations.prod');
    Route::get('migrations/run', [MigrationController::class, 'run'])->name('migrations.run');
    Route::get('migrations/seed', [MigrationController::class, 'seed'])->name('migrations.seed');
    Route::get('migrations/run-all', [MigrationController::class, 'runAll'])->name('migrations.run-all');
    Route::get('migrations/fix-gateway-customer', [MigrationController::class, 'fixGatewayCustomer'])->name('migrations.fix-gateway-customer');
});

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

    Route::get('home', function () {
        return view('home', [
            'titulo' => 'Início',
            'breadcrumb' => [['name' => 'Home', 'active' => true]],
        ]);
    })->middleware(['auth'])->name('home');
    Route::get('menu/{secao?}', [\App\Http\Controllers\MenuController::class, 'index'])->middleware(['auth'])->name('menu.index');
    Route::get('home/chart-data', [HomeController::class, 'chartData'])->middleware(['auth'])->name('home.chart-data');
    Route::get('home/produtividade', [\App\Http\Controllers\Clockify\ClockifyDashboardController::class, 'produtividade'])->middleware(['auth'])->name('home.produtividade');
    Route::get('home/equipe', [\App\Http\Controllers\Clockify\ClockifyDashboardController::class, 'equipe'])->middleware(['auth'])->name('home.equipe');
    Route::get('home/projetos', [\App\Http\Controllers\Clockify\ClockifyDashboardController::class, 'projetos'])->middleware(['auth'])->name('home.projetos');

    Route::get('/dashboard', [\App\Http\Controllers\Clockify\ClockifyDashboardController::class, 'index'])->middleware('auth')->name('dashboard');
    Route::get('pricing', [\App\Http\Controllers\Clockify\PricingController::class, 'index'])->middleware('auth')->name('pricing');
    Route::get('relatorios', [\App\Http\Controllers\Clockify\ReportController::class, 'index'])->middleware('auth')->name('relatorios.index');
    Route::get('relatorios/horas-trabalhadas', [\App\Http\Controllers\Clockify\ReportController::class, 'horasTrabalhadas'])->middleware('auth')->name('relatorios.horas-trabalhadas');
    Route::get('relatorios/horas', [\App\Http\Controllers\Clockify\ReportController::class, 'horasTrabalhadas'])->middleware('auth')->name('relatorios.horas');
    Route::match(['get', 'post'], 'relatorios/exportar', [\App\Http\Controllers\Clockify\ReportController::class, 'export'])->middleware('auth')->name('relatorios.exportar');
    Route::match(['get', 'post'], 'relatorios/export', [\App\Http\Controllers\Clockify\ReportController::class, 'export'])->middleware('auth')->name('relatorios.export');
    Route::get('relatorios/membros', [\App\Http\Controllers\Clockify\ReportController::class, 'membros'])->middleware('auth')->name('relatorios.membros');
    Route::delete('relatorios/membros/{user}', [\App\Http\Controllers\Clockify\ReportController::class, 'destroyMembro'])->middleware('auth')->name('relatorios.membros.destroy');
    Route::post('relatorios/membros/store', [\App\Http\Controllers\Clockify\ReportController::class, 'storeMembro'])->middleware('auth')->name('relatorios.membros.store');
    Route::put('relatorios/membros/{user}', [\App\Http\Controllers\Clockify\ReportController::class, 'updateMembro'])->middleware('auth')->name('relatorios.membros.update');
    Route::post('relatorios/membros/{user}/reset-password', [\App\Http\Controllers\Clockify\ReportController::class, 'resetPassword'])->middleware('auth')->name('relatorios.membros.reset-password');

    Route::middleware('auth')->group(function (): void {
        Route::get('relatorios/consumo', [\App\Http\Controllers\Clockify\ConsumoRelatorioController::class, 'index'])->name('relatorios.consumo');
        Route::get('relatorios/matriz', [\App\Http\Controllers\Clockify\ConsumoRelatorioController::class, 'matriz'])->name('relatorios.matriz');
        Route::get('relatorios/consumo/{id}', [\App\Http\Controllers\Clockify\ConsumoRelatorioController::class, 'show'])->name('relatorios.consumo.show');
        Route::post('relatorios/consumo/{id}/estimativas', [\App\Http\Controllers\Clockify\ConsumoRelatorioController::class, 'estimativaStore'])->name('relatorios.consumo.estimativas.store');
        Route::delete('relatorios/consumo/estimativas/{estimativa}', [\App\Http\Controllers\Clockify\ConsumoRelatorioController::class, 'estimativaDestroy'])->name('relatorios.consumo.estimativas.destroy');
        Route::patch('relatorios/consumo/{id}/status', [\App\Http\Controllers\Clockify\ConsumoRelatorioController::class, 'updateStatus'])->name('relatorios.consumo.status');
    });

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

    // Setup de producao — publico, protegido pela senha APP_MIGRATION_ROUTE_TOKEN do .env
    Route::get('migrations/setup-producao', [MigrationController::class, 'setupProducao'])->name('migrations.setup-producao');

    // Migrations/seed via web — somente superadmin
    Route::middleware(['auth', 'can:acessar-superadmin'])->group(function () {
        Route::get('migrations/prod', [MigrationController::class, 'run'])->name('migrations.prod');
        Route::get('migrations/run', [MigrationController::class, 'run'])->name('migrations.run');
        Route::get('migrations/seed', [MigrationController::class, 'seed'])->name('migrations.seed');
        Route::get('migrations/run-all', [MigrationController::class, 'runAll'])->name('migrations.run-all');
        Route::get('migrations/fix-gateway-customer', [MigrationController::class, 'fixGatewayCustomer'])->name('migrations.fix-gateway-customer');
    });

    // Central de comandos Artisan — somente superadmin
    Route::middleware(['auth', 'can:acessar-superadmin'])->group(function () {
        Route::get('comandos', [\App\Http\Controllers\ComandoSistemaController::class, 'index'])->name('comandos.index');
        Route::post('comandos/executar', [\App\Http\Controllers\ComandoSistemaController::class, 'run'])->name('comandos.executar');
        Route::get('ia/config', [\App\Http\Controllers\IaGlobalController::class, 'edit'])->name('ia-global.edit');
        Route::put('ia/config', [\App\Http\Controllers\IaGlobalController::class, 'update'])->name('ia-global.update');
        Route::get('ia/uso', [\App\Http\Controllers\IaUsoController::class, 'index'])->name('ia-uso.index');
        Route::post('ia/uso/limite', [\App\Http\Controllers\IaUsoController::class, 'salvarLimite'])->name('ia-uso.limite');
    });

    require __DIR__.'/auth.php';

    $authGroupFiles = [
        'regra',
        'permissao',
        'usuario',
        'empresa',
        'suporte',
        'onboarding',
        'clockify',
        'assinatura',
        'licitacao',
        'importacao',
        'ia',
        'documentos',
        'tutoriais',
    ];

    foreach ($authGroupFiles as $groupFile) {
        $filePath = base_path('routes/grupos/'.$groupFile.'.php');
        if (file_exists($filePath)) {
            Route::middleware('auth')->group(function () use ($filePath): void {
                require $filePath;
            });
        }
    }

    Route::middleware(['auth', 'can:gerenciar-empresa'])->group(function (): void {
        Route::get('empresa-contexto', [EmpresaContextController::class, 'edit'])->name('empresa_context.edit');
        Route::post('empresa-contexto', [EmpresaContextController::class, 'update'])->name('empresa_context.update');
    });
});

Route::post('/limpar-cache', function () {
    Artisan::call('cache:clear');

    return back()->with('success', 'Cache limpo com sucesso!');
})->middleware(['auth', 'can:acessar-superadmin'])->name('limpar-cache');

// Endpoint para cron via URL (hospedagem compartilhada sem acesso a CLI):
// configure CRON_TOKEN no .env e agende GET https://<dominio>/cron/schedule-run/<token> a cada minuto.
Route::get('/cron/schedule-run/{token}', function (string $token) {
    $expected = (string) config('app.cron_token');

    abort_if($expected === '' || ! hash_equals($expected, $token), 404);

    Artisan::call('schedule:run');

    return response('ok');
})->name('cron.schedule-run');
