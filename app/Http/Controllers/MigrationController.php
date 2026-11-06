<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MigrationController extends Controller
{
    /**
     * Senha de acesso publico ao setup de producao: exige
     * APP_MIGRATION_ROUTE_TOKEN configurado no .env e ?token= igual.
     */
    private function checkToken(Request $request): void
    {
        $allowedToken = (string) config('app.migration_route_token', '');
        $token = (string) $request->query('token', '');

        abort_unless($allowedToken !== '' && hash_equals($allowedToken, $token), 403, 'Token inválido.');
    }

    public function run(Request $request)
    {
        $path = $request->query('path');
        $params = ['--force' => true];
        if ($path) {
            $params['--path'] = $path;
        }

        try {
            Artisan::call('migrate', $params);
            $output = Artisan::output();

            return response('<h2>Migrations executadas</h2><pre style="white-space:pre-wrap;max-height:60vh;overflow:auto;border:1px solid #ddd;padding:8px">'.e($output).'</pre>');
        } catch (\Throwable $e) {
            return response('<h2>Erro ao executar migrations</h2><pre>'.e($e->getMessage()).'</pre>', 500);
        }
    }

    public function seed(Request $request)
    {
        $class = $request->query('class');
        $params = ['--force' => true];
        if ($class) {
            $params['--class'] = $class;
        }

        try {
            Artisan::call('db:seed', $params);
            $output = Artisan::output();

            return response('<h2>Seeders executados</h2><pre style="white-space:pre-wrap;max-height:60vh;overflow:auto;border:1px solid #ddd;padding:8px">'.e($output).'</pre>');
        } catch (\Throwable $e) {
            return response('<h2>Erro ao executar seeders</h2><pre>'.e($e->getMessage()).'</pre>', 500);
        }
    }

    public function runAll(Request $request)
    {
        // rode o comando de migrate status

        $output = '';

        Artisan::call('optimize:clear');
        try {
            Artisan::call('migrate:status');
            $output .= "=== MIGRATIONS STATUS ===\n".Artisan::output();
        } catch (\Throwable $e) {
            $output .= "=== ERRO NAS MIGRATIONS ===\n".$e->getMessage()."\n";
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output .= "=== MIGRATIONS ===\n".Artisan::output();
        } catch (\Throwable $e) {
            $output .= "=== ERRO NAS MIGRATIONS ===\n".$e->getMessage()."\n";
        }

        try {
            Artisan::call('db:seed', ['--force' => true]);
            $output .= "\n=== SEEDERS ===\n".Artisan::output();
        } catch (\Throwable $e) {
            $output .= "=== ERRO NOS SEEDERS ===\n".$e->getMessage()."\n";
        }

        return response('<h2>Migrations + Seeders</h2><pre style="white-space:pre-wrap;max-height:80vh;overflow:auto;border:1px solid #ddd;padding:8px">'.e($output).'</pre>');
    }

    public function setupProducao(Request $request)
    {
        $this->checkToken($request);

        try {
            // Buffer proprio: Artisan::output() devolveria o buffer da
            // ultima chamada interna (ja esvaziado pelo comando), entao
            // passamos um BufferedOutput para capturar tudo que o setup
            // escreve via $this->info/$this->line.
            $buffer = new \Symfony\Component\Console\Output\BufferedOutput();
            Artisan::call('declass:setup-producao', [
                '--demo' => (bool) $request->query('demo'),
            ], $buffer);
            $output = $buffer->fetch();

            return response('<h2>Setup de producao</h2><pre style="white-space:pre-wrap;max-height:80vh;overflow:auto;border:1px solid #ddd;padding:8px">'.e($output).'</pre>');
        } catch (\Throwable $e) {
            return response('<h2>Erro no setup de producao</h2><pre>'.e($e->getMessage()).'</pre>', 500);
        }
    }
}
