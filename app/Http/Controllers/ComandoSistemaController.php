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
                ['key' => 'permissoes-matriz', 'nome' => 'Aplicar Matriz de Permissões', 'artisan' => 'permissoes:aplicar-matriz', 'icone' => 'fas fa-key', 'cor' => '#0ea5e9', 'descricao' => 'Concede aos perfis as permissões que faltam da matriz (não remove nenhuma)'],
                ['key' => 'permissoes-matriz-sync', 'nome' => 'Sincronizar Matriz (revoga excedentes)', 'artisan' => 'permissoes:aplicar-matriz', 'params' => ['--sincronizar' => true], 'icone' => 'fas fa-sync-alt', 'cor' => '#f59e0b', 'confirm' => 'Deixa cada perfil exatamente igual à matriz: permissões concedidas manualmente fora dela serão REVOGADAS. Continuar?'],
            ],
        ],
        [
            'titulo' => 'Migrations',
            'icone' => 'fas fa-database',
            'itens' => [
                ['key' => 'migrate-status', 'nome' => 'Status das Migrations', 'artisan' => 'migrate:status', 'icone' => 'fas fa-list-ol', 'cor' => '#3b82f6'],
                ['key' => 'migrate-force', 'nome' => 'Executar Migrations', 'artisan' => 'migrate', 'params' => ['--force' => true], 'icone' => 'fas fa-database', 'cor' => '#16a34a', 'confirm' => 'Executa as migrations pendentes no banco atual. Continuar?'],
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
            'grupos' => $this->grupos(),
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

        $comando = collect($this->grupos())
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

    /**
     * Grupos da whitelist: comandos fixos + um item por seeder
     * encontrado em database/seeders (descoberta automatica).
     */
    private function grupos(): array
    {
        $grupos = self::COMANDOS;

        $seeders = collect(glob(database_path('seeders/*.php')) ?: [])
            ->map(fn (string $arquivo) => pathinfo($arquivo, PATHINFO_FILENAME))
            // Seeders destrutivos (*ResetSeeder apagam dados) ficam fora da
            // central — uso somente via artisan, com ciencia do operador.
            ->reject(fn (string $classe) => str_ends_with($classe, 'ResetSeeder'))
            ->sort()
            ->values();

        if ($seeders->isNotEmpty()) {
            $grupos[] = [
                'titulo' => 'Seeders',
                'icone' => 'fas fa-seedling',
                'itens' => $seeders->map(function (string $classe) {
                    $nome = trim((string) preg_replace('/(?<!^)[A-Z]/', ' $0', (string) preg_replace('/Seeder$/', '', $classe)));

                    return [
                        'key' => 'seed-'.strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $classe)),
                        'nome' => $classe === 'DatabaseSeeder' ? 'Seeder: Todos (DatabaseSeeder)' : 'Seeder: '.$nome,
                        'artisan' => 'db:seed',
                        'params' => ['--class' => $classe],
                        'icone' => 'fas fa-seedling',
                        'cor' => '#16a34a',
                        'confirm' => "Executa php artisan db:seed --class={$classe} no banco atual. Continuar?",
                    ];
                })->all(),
            ];
        }

        return $grupos;
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
