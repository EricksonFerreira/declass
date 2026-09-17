<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

/**
 * Central de comandos Artisan para o superadmin.
 * Apenas comandos da whitelist abaixo podem ser executados.
 */
class ComandoSistemaController extends Controller
{
    private const COMANDOS = [
        [
            'titulo' => 'Cache e Otimização',
            'icone' => 'fas fa-bolt',
            'itens' => [
                ['key' => 'cache-clear', 'nome' => 'Limpar Cache', 'artisan' => 'cache:clear', 'icone' => 'fas fa-broom', 'cor' => '#3b82f6'],
                ['key' => 'config-clear', 'nome' => 'Limpar Cache de Config', 'artisan' => 'config:clear', 'icone' => 'fas fa-cog', 'cor' => '#3b82f6'],
                ['key' => 'route-clear', 'nome' => 'Limpar Cache de Rotas', 'artisan' => 'route:clear', 'icone' => 'fas fa-route', 'cor' => '#3b82f6'],
                ['key' => 'view-clear', 'nome' => 'Limpar Cache de Views', 'artisan' => 'view:clear', 'icone' => 'fas fa-eye', 'cor' => '#3b82f6'],
                ['key' => 'optimize-clear', 'nome' => 'Limpar Toda Otimização', 'artisan' => 'optimize:clear', 'icone' => 'fas fa-box-open', 'cor' => '#f59e0b'],
                ['key' => 'optimize', 'nome' => 'Otimizar Aplicação', 'artisan' => 'optimize', 'icone' => 'fas fa-rocket', 'cor' => '#16a34a'],
            ],
        ],
        [
            'titulo' => 'Permissões e Perfis',
            'icone' => 'fas fa-key',
            'itens' => [
                ['key' => 'permissoes-sync', 'nome' => 'Sincronizar Permissões', 'artisan' => 'permissoes:sincronizar-telas', 'icone' => 'fas fa-sync-alt', 'cor' => '#0ea5e9', 'descricao' => 'Cria permissões por tela/ação e ressincroniza os perfis padrão'],
            ],
        ],
        [
            'titulo' => 'Migrations e Seeders',
            'icone' => 'fas fa-database',
            'itens' => [
                ['key' => 'migrate-status', 'nome' => 'Status das Migrations', 'artisan' => 'migrate:status', 'icone' => 'fas fa-list-ol', 'cor' => '#3b82f6'],
                ['key' => 'migrate-force', 'nome' => 'Executar Migrations', 'artisan' => 'migrate', 'params' => ['--force' => true], 'icone' => 'fas fa-database', 'cor' => '#16a34a', 'confirm' => 'Executa as migrations pendentes no banco atual. Continuar?'],
                ['key' => 'seed-permissoes', 'nome' => 'Seeder: Permissões do Sistema', 'artisan' => 'db:seed', 'params' => ['--class' => 'PermissoesSistemaSeeder'], 'icone' => 'fas fa-shield-alt', 'cor' => '#0d9488', 'confirm' => 'Recria as permissões a partir das rotas registradas (idempotente). Continuar?'],
                ['key' => 'seed-novas', 'nome' => 'Seeder: Novas Funcionalidades', 'artisan' => 'db:seed', 'params' => ['--class' => 'PermissoesNovasFuncionalidadesSeeder'], 'icone' => 'fas fa-plus-circle', 'cor' => '#0d9488', 'confirm' => 'Executa o seeder de permissões das novas funcionalidades. Continuar?'],
            ],
        ],
        [
            'titulo' => 'Sistema',
            'icone' => 'fas fa-server',
            'itens' => [
                ['key' => 'storage-link', 'nome' => 'Criar Link Simbólico', 'artisan' => 'storage:link', 'icone' => 'fas fa-link', 'cor' => '#16a34a'],
                ['key' => 'queue-restart', 'nome' => 'Reiniciar Queue Workers', 'artisan' => 'queue:restart', 'icone' => 'fas fa-redo', 'cor' => '#f59e0b'],
                ['key' => 'route-list', 'nome' => 'Listar Rotas', 'artisan' => 'route:list', 'icone' => 'fas fa-map-signs', 'cor' => '#64748b'],
                ['key' => 'schedule-list', 'nome' => 'Tarefas Agendadas', 'artisan' => 'schedule:list', 'icone' => 'far fa-clock', 'cor' => '#64748b'],
            ],
        ],
    ];

    public function index(): View
    {
        return view('comandos.index', [
            'titulo' => 'Comandos do Sistema',
            'grupos' => self::COMANDOS,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Configurações'],
                ['name' => 'Comandos', 'active' => true],
            ],
        ]);
    }

    public function run(Request $request): JsonResponse
    {
        $data = $request->validate(['comando' => 'required|string|max:60']);

        $comando = collect(self::COMANDOS)
            ->flatMap(fn (array $grupo) => $grupo['itens'])
            ->firstWhere('key', $data['comando']);

        if (! $comando) {
            return response()->json(['ok' => false, 'saida' => 'Comando desconhecido.'], 404);
        }

        set_time_limit(300);
        $inicio = microtime(true);

        try {
            Artisan::call($comando['artisan'], $comando['params'] ?? []);
            $saida = trim((string) Artisan::output());

            return response()->json([
                'ok' => true,
                'comando' => $comando['nome'],
                'artisan' => $this->assinaturaComando($comando),
                'saida' => $saida !== '' ? $saida : 'Comando executado com sucesso.',
                'duracao' => round(microtime(true) - $inicio, 2),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'comando' => $comando['nome'],
                'artisan' => $this->assinaturaComando($comando),
                'saida' => $e->getMessage(),
                'duracao' => round(microtime(true) - $inicio, 2),
            ], 500);
        }
    }

    private function assinaturaComando(array $comando): string
    {
        $assinatura = 'php artisan '.$comando['artisan'];

        foreach ($comando['params'] ?? [] as $chave => $valor) {
            $assinatura .= $valor === true ? ' '.$chave : ' '.$chave.'='.$valor;
        }

        return $assinatura;
    }
}
