<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MigrationController extends Controller
{
    private function checkToken(Request $request): void
    {
        $allowedToken = config('app.migration_route_token');
        $token = $request->query('token');
        if ($allowedToken && $allowedToken !== '' && $token !== $allowedToken) {
            abort(403, 'Token inválido.');
        }
    }

    public function run(Request $request)
    {
        $this->checkToken($request);

        $path = $request->query('path');
        $params = ['--force' => true];
        if ($path) {
            $params['--path'] = $path;
        }

        try {
            Artisan::call('migrate', $params);
            $output = Artisan::output();

            return response("<h2>Migrations executadas</h2><pre style=\"white-space:pre-wrap;max-height:60vh;overflow:auto;border:1px solid #ddd;padding:8px\">" . e($output) . "</pre>");
        } catch (\Throwable $e) {
            return response("<h2>Erro ao executar migrations</h2><pre>" . e($e->getMessage()) . "</pre>", 500);
        }
    }

    public function seed(Request $request)
    {
        $this->checkToken($request);

        $class = $request->query('class');
        $params = ['--force' => true];
        if ($class) {
            $params['--class'] = $class;
        }

        try {
            Artisan::call('db:seed', $params);
            $output = Artisan::output();

            return response("<h2>Seeders executados</h2><pre style=\"white-space:pre-wrap;max-height:60vh;overflow:auto;border:1px solid #ddd;padding:8px\">" . e($output) . "</pre>");
        } catch (\Throwable $e) {
            return response("<h2>Erro ao executar seeders</h2><pre>" . e($e->getMessage()) . "</pre>", 500);
        }
    }

    public function runAll(Request $request)
    {
        $this->checkToken($request);

        $output = '';

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output .= "=== MIGRATIONS ===\n" . Artisan::output();
        } catch (\Throwable $e) {
            $output .= "=== ERRO NAS MIGRATIONS ===\n" . $e->getMessage() . "\n";
        }

        try {
            Artisan::call('db:seed', ['--force' => true]);
            $output .= "\n=== SEEDERS ===\n" . Artisan::output();
        } catch (\Throwable $e) {
            $output .= "=== ERRO NOS SEEDERS ===\n" . $e->getMessage() . "\n";
        }

        return response("<h2>Migrations + Seeders</h2><pre style=\"white-space:pre-wrap;max-height:80vh;overflow:auto;border:1px solid #ddd;padding:8px\">" . e($output) . "</pre>");
    }
}
