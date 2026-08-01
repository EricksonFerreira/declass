<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FrotaCatalogSeeder::class,
            PermissoesNovasFuncionalidadesSeeder::class,
            PlanoSeeder::class,
            TiposDanosPorCategoriaELocalizacaoSeeder::class,
            CondicoesGeraisSeeder::class,
            OsCategoriaSeeder::class,
            ChamadosSeeder::class,
        ]);
    }
}
