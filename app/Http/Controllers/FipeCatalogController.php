<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class FipeCatalogController extends Controller
{
    public function run(Request $request)
    {
        $allowedToken = config('app.migration_route_token');
        $token = (string) $request->query('token', '');

        if ($allowedToken && $allowedToken !== '' && $token !== $allowedToken) {
            abort(403, 'Token inválido para execução do catálogo FIPE.');
        }

        return $this->executarSincronizacao(
            $request,
            (string) $request->query('vehicle_types', (string) $request->query('vehicle_type', 'cars'))
        );
    }

    public function runMotorcyclesAndTrucks(Request $request)
    {
        $allowedToken = config('app.migration_route_token');
        $token = (string) $request->query('token', '');

        if ($allowedToken && $allowedToken !== '' && $token !== $allowedToken) {
            abort(403, 'Token inválido para execução do catálogo FIPE.');
        }

        return $this->executarSincronizacao($request, 'motorcycles,trucks');
    }

    private function executarSincronizacao(Request $request, string $vehicleTypes)
    {
        $params = [
            '--vehicle-types' => $vehicleTypes,
        ];

        $brand = trim((string) $request->query('brand', ''));
        if ($brand !== '') {
            $params['--brand'] = $brand;
        }

        if ($request->boolean('refresh_existing')) {
            $params['--refresh-existing'] = true;
        }

        if ($request->boolean('dry_run')) {
            $params['--dry-run'] = true;
        }

        try {
            Artisan::call('fipe:sincronizar-catalogo', $params);
            $output = Artisan::output();

            return response(
                "<h2>Catálogo FIPE sincronizado</h2><pre style=\"white-space:pre-wrap;max-height:60vh;overflow:auto;border:1px solid #ddd;padding:8px\">" .
                e($output) .
                '</pre>'
            );
        } catch (\Throwable $exception) {
            return response(
                "<h2>Erro ao sincronizar catálogo FIPE</h2><pre>" . e($exception->getMessage()) . '</pre>',
                500
            );
        }
    }
}
