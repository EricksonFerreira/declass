<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MigrationController extends Controller
{
    public function run(Request $request)
    {
        // Segurança: rota protegida via middleware, mas também permitimos um token opcional via ?token= in case deploy scripts use it
        $allowedToken = config('app.migration_route_token');
        $token = $request->query('token');
        if ($allowedToken && $allowedToken !== '' && $token !== $allowedToken) {
            abort(403, 'Token inválido para execução de migrations.');
        }

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
}
