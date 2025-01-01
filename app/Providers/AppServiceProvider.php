<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Movimentacao;
use App\Models\Veiculo;
use App\Services\BookingService;
use App\Services\DisponibilidadeInicialVeiculoService;
use App\Services\MovimentacaoService;
use App\Services\VeiculoService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Paginator::useBootstrapFour();

        // Registre os serviços sem dependências circulares
        $this->app->singleton(VeiculoService::class, function ($app) {
            return new VeiculoService(
                $app->make(Veiculo::class),
                $app->make(DisponibilidadeInicialVeiculoService::class)
            );
        });

        $this->app->singleton(MovimentacaoService::class, function ($app) {
            return new MovimentacaoService($app->make(Movimentacao::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure as dependências após a criação dos serviços
        $this->app->resolving(VeiculoService::class, function ($veiculoService, $app) {
            $veiculoService->setMovimentacaoService($app->make(MovimentacaoService::class));
        });

        $this->app->resolving(MovimentacaoService::class, function ($movimentacaoService, $app) {
            $movimentacaoService->setVeiculoService($app->make(VeiculoService::class));
        });


        Cashier::useCustomerModel(User::class);


    }
}
