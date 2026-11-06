<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Reset da massa academica demo do tenant 'escola-demo'.
 *
 * Remove alunos, responsaveis, matriculas, estrutura letiva e
 * dados operacionais gerados pelo DemoEscolarSeeder — preservando
 * empresa, escolas, usuarios, perfis, etapas,
 * disciplinas, livros e planos — e roda o DemoEscolarSeeder de
 * novo do zero.
 *
 * Uso: php artisan db:seed --class=DemoEscolarResetSeeder
 */
class DemoEscolarResetSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->where('slug', 'escola-demo')->first();

        if (! $empresa) {
            $this->command->warn('Empresa escola-demo nao encontrada.');

            return;
        }

        $empresaId = $empresa->id;
        $turmaIds = DB::table('turmas')->where('empresa_id', $empresaId)->pluck('id');
        $alunoIds = DB::table('alunos')->where('empresa_id', $empresaId)->pluck('id');
        $responsavelIds = DB::table('responsaveis')->where('empresa_id', $empresaId)->pluck('id');

        // Ordem respeitando as chaves estrangeiras.
        DB::table('frequencias')->where('empresa_id', $empresaId)->delete();
        DB::table('notas')->where('empresa_id', $empresaId)->delete();
        DB::table('diarios')->where('empresa_id', $empresaId)->delete();
        DB::table('avaliacoes')->where('empresa_id', $empresaId)->delete();
        DB::table('emprestimos_livros')->where('empresa_id', $empresaId)->delete();
        DB::table('mensalidades')->where('empresa_id', $empresaId)->delete();
        DB::table('matriculas')->where('empresa_id', $empresaId)->delete();
        DB::table('turma_disciplina')->whereIn('turma_id', $turmaIds)->delete();
        DB::table('atribuicoes')->where('empresa_id', $empresaId)->delete();
        DB::table('avisos')->where('empresa_id', $empresaId)->delete();
        DB::table('periodos_letivos')->where('empresa_id', $empresaId)->delete();
        DB::table('aluno_documentos')->where('empresa_id', $empresaId)->delete();
        DB::table('turmas')->where('empresa_id', $empresaId)->delete();
        DB::table('anos_letivos')->where('empresa_id', $empresaId)->delete();

        if ($alunoIds->isNotEmpty() || $responsavelIds->isNotEmpty()) {
            DB::table('aluno_responsavel')
                ->whereIn('aluno_id', $alunoIds)
                ->orWhereIn('responsavel_id', $responsavelIds)
                ->delete();
        }

        DB::table('alunos')->where('empresa_id', $empresaId)->delete();
        DB::table('responsaveis')->where('empresa_id', $empresaId)->delete();
        DB::table('pessoas')->where('empresa_id', $empresaId)->delete();

        $this->command->info('Massa academica demo removida. Recriando...');

        $this->call(DemoEscolarSeeder::class);
    }
}
