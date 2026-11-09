<?php

use App\Http\Controllers\CadastroEmpresaController;
use App\Http\Controllers\EmpresaContextController;
use App\Http\Controllers\EscolaContextController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MigrationController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteEmpresaController;
use App\Support\EmpresaDominio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Dominio personalizado de empresa serve o site publico dela
    // ("rede de escolas"); sem site ativo, vai para o login.
    $empresa = EmpresaDominio::porHost(request()->getHost());

    if ($empresa?->sitePublicoAtivo()) {
        return app(SiteEmpresaController::class)->show($empresa);
    }

    if ($empresa) {
        return redirect()->route('login');
    }

    return view('welcome');
})->name('site.apresentacao');

// Login na raiz — o dominio/subdominio da empresa cai direto em /login,
// sem o prefixo /admin (que segue valendo para as demais rotas autenticadas).
Route::middleware('guest')->group(function (): void {
    Route::get('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
});

Route::get('/sobre', function () {
    return view('sobre');
})->name('site.sobre');

// Formulario publico de solicitacao de matricula (visitante, sem login).
require __DIR__.'/publicas.php';

Route::get('/sitemap.xml', function (Request $request) {
    $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
    $lastMod = now()->toAtomString();

    $urls = [
        ['loc' => $baseUrl.'/', 'priority' => '1.0', 'changefreq' => 'daily'],
    ];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

    foreach ($urls as $url) {
        $xml .= "  <url>\n";
        $xml .= '    <loc>'.e($url['loc'])."</loc>\n";
        $xml .= '    <lastmod>'.$lastMod.'</lastmod>'."\n";
        $xml .= '    <changefreq>'.e($url['changefreq'])."</changefreq>\n";
        $xml .= '    <priority>'.e($url['priority'])."</priority>\n";
        $xml .= "  </url>\n";
    }

    $xml .= "</urlset>\n";

    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap.xml');

// Setup de producao — publico, protegido pela senha APP_MIGRATION_ROUTE_TOKEN do .env
Route::get('migrations/setup-producao', [MigrationController::class, 'setupProducao'])->name('migrations.setup-producao');

Route::prefix(trim((string) config('app.admin_prefix', 'admin'), '/'))->group(function (): void {
    Route::middleware(['auth'])->get('/', function () {
        return redirect('/admin/home');
    })->name('admin.index');

    // Cadastro de tenant (escola/rede) — tela publica de entrada.
    Route::middleware('guest')->get('/comecar', [CadastroEmpresaController::class, 'create'])->name('cadastro.empresa.create');
    Route::middleware('guest')->post('/comecar', [CadastroEmpresaController::class, 'store'])->name('cadastro.empresa.store');
    Route::middleware('guest')->get('/comecar/sucesso', [CadastroEmpresaController::class, 'success'])->name('cadastro.empresa.success');

    Route::get('home', [HomeController::class, 'home'])->middleware(['auth'])->name('home');
    Route::get('dashboard', [HomeController::class, 'dashboard'])->middleware('auth')->name('dashboard');
    Route::get('menu/{secao?}', [\App\Http\Controllers\MenuController::class, 'index'])->middleware(['auth'])->name('menu.index');

    Route::middleware('auth')->group(function (): void {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/foto', [ProfileController::class, 'updateFoto'])->name('profile.foto.update');
        Route::get('/profile/senha', [ProfileController::class, 'editPassword'])->name('profile.password.edit');
        Route::put('/profile/senha', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
        Route::get('/profile/primeiro-acesso/senha', [ProfileController::class, 'forcePasswordEdit'])->name('profile.force-password.edit');
        Route::put('/profile/primeiro-acesso/senha', [ProfileController::class, 'forcePasswordUpdate'])->name('profile.force-password.update');
        Route::get('/notificacoes', [NotificacaoController::class, 'index'])->name('notificacoes.index');
        Route::get('/notificacoes/navbar', [NotificacaoController::class, 'navbar'])->name('notificacoes.navbar');
        Route::patch('/notificacoes/{notification}/lida', [NotificacaoController::class, 'marcarLida'])->name('notificacoes.marcarLida');
        Route::patch('/notificacoes/lidas', [NotificacaoController::class, 'marcarTodasComoLidas'])->name('notificacoes.marcarTodasComoLidas');
    });

    // Migrations/seed via web — somente superadmin
    Route::middleware(['auth', 'can:acessar-superadmin'])->group(function () {
        Route::get('migrations/prod', [MigrationController::class, 'run'])->name('migrations.prod');
        Route::get('migrations/run', [MigrationController::class, 'run'])->name('migrations.run');
        Route::get('migrations/seed', [MigrationController::class, 'seed'])->name('migrations.seed');
        Route::get('migrations/run-all', [MigrationController::class, 'runAll'])->name('migrations.run-all');
    });

    // Central de comandos Artisan — somente superadmin
    Route::middleware(['auth', 'can:acessar-superadmin'])->group(function () {
        Route::get('comandos', [\App\Http\Controllers\ComandoSistemaController::class, 'index'])->name('comandos.index');
        Route::post('comandos/executar', [\App\Http\Controllers\ComandoSistemaController::class, 'run'])->name('comandos.executar');
    });

    // Espelhamento de acesso — o guard fica no controller (isSuperAdmin)
    // porque durante o espelho o gate 'acessar-superadmin' e suprimido;
    // o prefixo 'espelhamento.' e ignorado pela checagem por rota.
    Route::middleware('auth')->group(function () {
        Route::get('espelhamento', [\App\Http\Controllers\EspelhamentoController::class, 'index'])->name('espelhamento.index');
        Route::post('espelhamento/{usuario}', [\App\Http\Controllers\EspelhamentoController::class, 'store'])->name('espelhamento.store');
        Route::delete('espelhamento', [\App\Http\Controllers\EspelhamentoController::class, 'destroy'])->name('espelhamento.destroy');
    });

    require __DIR__.'/auth.php';

    $authGroupFiles = [
        'regra',
        'usuario',
        'empresa',
        'escola',
        'aluno',
        'responsavel',
        'ano_letivo',
        'etapa',
        'disciplina',
        'turma',
        'matricula',
        'diario',
        'avaliacao',
        'boletim',
        'portal',
        'plano_mensalidade',
        'mensalidade',
        'aviso',
        'livro',
        'emprestimo',
        'periodo_letivo',
        'horario_matriz',
        'horarios',
        'calendario',
        'feriado',
        'justificativa',
        'meus_horarios',
        'veiculo',
        'rota_transporte',
        'transporte_aluno',
        'cardapio',
        'ocorrencia',
        'plano_atendimento',
        'tarefa',
        'relatorio_frequencia',
        'relatorio_inadimplencia',
        'relatorio_transporte',
        'relatorio_aproveitamento',
        'agenda_avaliacao',
        'solicitacao_matricula',
        'solicitacao',
        'evento',
        'pesquisa',
        'saude',
        'material',
        'despesa',
        'conta_receber',
        'fluxo_caixa',
        'dre',
        'financeiro_visao_geral',
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

    Route::middleware('auth')->group(function (): void {
        Route::post('escola-contexto', [EscolaContextController::class, 'update'])->name('escola_context.update');
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
