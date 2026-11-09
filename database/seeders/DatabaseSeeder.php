<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed inicial do sistema escolar:
     *  - empresa/tenant demo
     *  - escola de exemplo
     *  - superadmin da plataforma
     *  - administrador do tenant demo
     *  - todos os perfis escolares com matriz padrao
     */
    public function run(): void
    {
        $empresa = Empresa::query()->firstOrCreate(
            ['slug' => 'escola-demo'],
            [
                'nome' => 'Escola Vila Nova',
                'ativo' => 'S',
                'email' => 'contato@vilanova.test',
                'cor_primaria' => '#1e3a8a',
                'cor_secundaria' => '#f59e0b',
            ]
        );
        $empresa->update([
            'nome' => 'Escola Vila Nova',
            'dominio' => 'vilanova.ericksondev.online',
        ]);
        $this->configurarSiteVilaNova($empresa);

        $escolaExistente = DB::table('escolas')
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'unidade-central')
            ->first();

        if (! $escolaExistente) {
            DB::table('escolas')->insert([
                'empresa_id' => $empresa->id,
                'nome' => 'Escola Vila Nova',
                'slug' => 'unidade-central',
                'email' => 'central@vilanova.test',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'capacidade_alunos' => 500,
                'turno_padrao' => 'integral',
                'ativa' => 'S',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Tenant da propria plataforma: os superadmins ficam vinculados
        // a ele, mas continuam vendo e trocando para qualquer empresa
        // pelo contexto de sessao (visao consolidada por padrao).
        $declass = Empresa::query()->firstOrCreate(
            ['slug' => 'declass'],
            [
                'nome' => 'DeClass',
                'ativo' => 'S',
                'email' => 'contato@declass.test',
            ]
        );

        // Superadmin da plataforma (tipo_usuario_id = 1, vinculado ao
        // tenant declass). tipo_usuario_id nao pode ir no create(): o
        // mutator do model redireciona a escrita para role_id. Mesmo
        // motivo para email_verified_at — ambos via update direto.
        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'admin@declass.test'],
            [
                'name' => 'Super Admin',
                'nome' => 'Super Admin',
                'password' => Hash::make('password'),
                'empresa_id' => $declass->id,
                'ativo' => true,
            ]
        );
        DB::table('users')->where('id', $superAdmin->id)->update([
            'tipo_usuario_id' => 1,
            'empresa_id' => $declass->id,
            'email_verified_at' => now(),
        ]);

        // Superadmins antigos soltos (empresa_id null) passam a pertencer
        // ao tenant declass — continuam com a visao consolidada.
        DB::table('users')
            ->where('tipo_usuario_id', 1)
            ->whereNull('empresa_id')
            ->update(['empresa_id' => $declass->id]);

        // Administrador do tenant demo.
        $admin = User::query()->firstOrCreate(
            ['email' => 'diretor@escolademo.test'],
            [
                'name' => 'Diretor Demo',
                'nome' => 'Diretor Demo',
                'password' => Hash::make('password'),
                'empresa_id' => $empresa->id,
                'ativo' => true,
            ]
        );
        DB::table('users')->where('id', $admin->id)->update([
            'email_verified_at' => now(),
        ]);

        $this->call(PerfisEscolaresSeeder::class);
        $this->call(DeclassSuperadminSeeder::class);

        // Vincula o admin a todos os perfis? Nao — role_id aponta 1 perfil.
        // O papel 'administrador' do tenant e o perfil padrao dele.
        $roleAdmin = DB::table('roles')
            ->where('empresa_id', $empresa->id)
            ->where('name', 'administrador')
            ->value('id');

        if ($roleAdmin) {
            DB::table('users')->where('id', $admin->id)->update(['role_id' => $roleAdmin]);
        }
    }

    /**
     * Site publico da Vila Nova: copia as imagens de public/img/vilanova
     * para o disco public (storage) e liga o site com modelo classico.
     */
    private function configurarSiteVilaNova(Empresa $empresa): void
    {
        $disk = Storage::disk('public');
        $copiar = function (string $arquivo) use ($disk): string {
            $dest = 'site/vilanova/'.$arquivo;
            $origem = public_path('img/vilanova/'.$arquivo);
            if (! $disk->exists($dest) && is_file($origem)) {
                $disk->put($dest, file_get_contents($origem));
            }

            return $dest;
        };

        $galeria = collect(['foto_escolar_02.png', 'foto_escolar_03.png', 'foto_escolar_04.png',
            'foto_escolar_05.png', 'foto_escolar_06.png', 'foto_escolar_07.png',
            'foto_escolar_08.png', 'foto_escolar_09.png', 'foto_escolar_10.png'])
            ->map(fn ($f) => $copiar($f))->all();

        $empresa->update([
            'logo_path' => $copiar('logo_colegio_vidanova.png'),
            'site_ativo' => 'S',
            'site_modelo' => 'classico',
            'site_titulo' => 'Escola Vila Nova',
            'site_descricao' => 'Ensino de qualidade do Infantil ao Ensino Medio, com diario digital, portal da familia e gestao escolar integrada.',
            'site_unidades' => 'S',
            'site_matricula' => 'S',
            'site_contato' => 'S',
            'site_banner' => $copiar('banner_escolar_01.png'),
            'site_imagem_sobre' => $copiar('foto_escolar_01.png'),
            'site_galeria' => $galeria,
            'site_whatsapp' => '(11) 99999-0000',
            'site_instagram' => '@escolavilanova',
            'site_depoimentos' => "Familia Silva | A plataforma facilitou demais o acompanhamento dos nossos filhos.\nProf. Ana | Lanco notas e chamadas em minutos.",
        ]);
    }
}
