<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\Atribuicao;
use App\Models\Avaliacao;
use App\Models\Diario;
use App\Models\Disciplina;
use App\Models\Empresa;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Models\Turma;
use App\Models\User;
use App\Support\EmpresaContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Massa de demonstracao do ciclo academico completo.
 *
 * Pressupoe a empresa 'escola-demo' (criada pelo DatabaseSeeder).
 * Popula estrutura, pessoas, matriculas, diario, frequencia,
 * avaliacoes, notas e contas de portal — tudo idempotente:
 * se o tenant ja tiver alunos, o seeder nao duplica nada.
 *
 * Execute: php artisan db:seed --class=DemoEscolarSeeder
 *
 * ─────────────────────────────────────────────────────────────
 * COMO ACESSAR (tela /login — 3 campos)
 * ─────────────────────────────────────────────────────────────
 *
 * Campo "Slug da empresa":  escola-demo
 * Campo "E-mail ou usuário": um dos e-mails abaixo
 * Campo "Senha":             password   (igual para todos)
 *
 * Contas de teste por perfil:
 *
 *  diretor@escolademo.test        administrador  — acesso total a tudo
 *  prof1@escolademo.test          professor      — diario, chamada,
 *                                                 avaliacoes e boletim
 *                                                 apenas das turmas
 *                                                 em que leciona
 *  prof2@escolademo.test          professor      — idem prof1
 *  secretaria@escolademo.test     secretario    — cadastro e gestao
 *                                                 Academica/Administrativa
 *  coordenador@escolademo.test    coordenador    — visao academica
 *                                                 (sem escrita em
 *                                                 diarios/notas)
 *  responsavel@escolademo.test    responsavel    — PORTAL: ve o
 *                                                 boletim dos 2
 *                                                 primeiros alunos
 *  aluno@escolademo.test          aluno          — PORTAL: ve o
 *                                                 proprio boletim
 *
 * Superadmin da plataforma (slug diferente):
 *  slug:   declass
 *  e-mail: admin@declass.test
 *  senha:  password
 *
 * O que conferir apos o login:
 *  - Menu lateral + cards do dashboard mudam conforme o perfil
 *  - responsavel/aluno caem direto no "Portal Familiar"
 *  - professor so ve as proprias turmas no diario/avaliacoes
 *  - boletim mostra medias, frequencia e notas por disciplina
 * ─────────────────────────────────────────────────────────────
 */
class DemoEscolarSeeder extends Seeder
{
    private const SENHA = 'password';

    public function run(): void
    {
        $empresa = Empresa::query()->where('slug', 'escola-demo')->first();

        if (! $empresa) {
            $this->command->warn('Empresa escola-demo nao encontrada. Rode db:seed primeiro.');

            return;
        }

        app(EmpresaContext::class)->setEmpresaId($empresa->id);

        if (Aluno::query()->where('empresa_id', $empresa->id)->exists()) {
            $this->command->info('Tenant demo ja possui alunos — nada a fazer.');

            return;
        }

        $this->call(PerfisEscolaresSeeder::class);

        $escola = Escola::query()->where('empresa_id', $empresa->id)->first();
        $usuarios = $this->usuarios($empresa);
        $this->estruturar($empresa, $escola);
        [$alunos, $responsaveis] = $this->pessoas($empresa, $escola, $usuarios);
        $this->operacao($empresa, $usuarios, $alunos);

        $this->command->info('Massa demo criada com sucesso.');
    }

    /** Anos letivos, etapas, disciplinas, turmas e atribuicoes. */
    private function estruturar(Empresa $empresa, Escola $escola): void
    {
        $anoLetivo = \App\Models\AnoLetivo::firstOrCreate(
            ['escola_id' => $escola->id, 'ano' => 2026],
            ['empresa_id' => $empresa->id, 'status' => 'ativo']
        );

        $etapas = [];
        foreach ([
            ['Educação Infantil', 'infantil', 1],
            ['Fundamental I', 'fundamental', 2],
            ['Fundamental II', 'fundamental', 3],
            ['Ensino Médio', 'medio', 4],
        ] as [$nome, $segmento, $ordem]) {
            $etapas[$segmento.$ordem] = Etapa::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nome' => $nome],
                ['segmento' => $segmento, 'ordem' => $ordem]
            );
        }

        $disciplinas = [];
        foreach ([
            ['Matemática', 'MAT'], ['Português', 'POR'], ['Ciências', 'CIE'],
            ['História', 'HIS'], ['Geografia', 'GEO'], ['Inglês', 'ING'],
            ['Educação Física', 'EDF'], ['Artes', 'ART'],
        ] as [$nome, $sigla]) {
            $disciplinas[$sigla] = Disciplina::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nome' => $nome],
                ['sigla' => $sigla]
            );
        }

        $turmas = [];
        foreach ([
            ['1º Ano A', 'matutino', $etapas['fundamental2'], ['MAT', 'POR', 'CIE']],
            ['5º Ano A', 'vespertino', $etapas['fundamental3'], ['MAT', 'POR', 'CIE', 'HIS', 'GEO', 'ING']],
        ] as [$nome, $turno, $etapa, $siglas]) {
            $turma = Turma::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nome' => $nome, 'ano_letivo_id' => $anoLetivo->id],
                [
                    'escola_id' => $escola->id,
                    'etapa_id' => $etapa->id,
                    'turno' => $turno,
                    'sala' => 'Sala '.fake()->unique()->numberBetween(1, 20),
                    'capacidade_maxima' => 35,
                    'ativo' => 'S',
                ]
            );
            $turmas[$nome] = $turma;

            foreach ($siglas as $sigla) {
                DB::table('turma_disciplina')->updateOrInsert(
                    ['turma_id' => $turma->id, 'disciplina_id' => $disciplinas[$sigla]->id],
                    ['carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // Atribuicoes: professor titular por turma+disciplina
        $professores = User::query()
            ->where('empresa_id', $empresa->id)
            ->whereIn('email', ['prof1@escolademo.test', 'prof2@escolademo.test'])
            ->get();

        $i = 0;
        foreach ($turmas as $turma) {
            foreach ($turma->disciplinas as $disciplina) {
                Atribuicao::firstOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'turma_id' => $turma->id,
                        'disciplina_id' => $disciplina->id,
                    ],
                    [
                        'user_id' => $professores[$i % max(1, $professores->count())]->id ?? null,
                        'papel' => 'titular',
                        'ativo' => 'S',
                    ]
                );
                $i++;
            }
        }
    }

    /** Usuarios de acesso para testar os perfis. */
    private function usuarios(Empresa $empresa): array
    {
        $criar = function (string $email, string $nome, string $perfil) use ($empresa) {
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nome,
                    'nome' => $nome,
                    'password' => Hash::make(self::SENHA),
                    'empresa_id' => $empresa->id,
                    'ativo' => true,
                ]
            );

            $roleId = DB::table('roles')
                ->where('empresa_id', $empresa->id)
                ->where('name', $perfil)
                ->where('guard_name', 'web')
                ->value('id');

            DB::table('users')->where('id', $user->id)->update([
                'role_id' => $roleId,
                'email_verified_at' => now(),
            ]);

            return $user->fresh();
        };

        return [
            'prof1' => $criar('prof1@escolademo.test', 'Professora Ana Souza', 'professor'),
            'prof2' => $criar('prof2@escolademo.test', 'Professor Carlos Lima', 'professor'),
            'secretaria' => $criar('secretaria@escolademo.test', 'Secretaria Beatriz', 'secretario'),
            'coordenador' => $criar('coordenador@escolademo.test', 'Coordenadora Paula Reis', 'coordenador_pedagogico'),
            'responsavel' => $criar('responsavel@escolademo.test', 'Responsável Demo', 'responsavel_legal'),
            'aluno' => $criar('aluno@escolademo.test', 'Aluno Demo Portal', 'aluno'),
        ];
    }

    /** Alunos, responsaveis e vinculos. */
    private function pessoas(Empresa $empresa, Escola $escola, array $usuarios): array
    {
        $alunos = [];
        $nomes = [
            ['Ana Beatriz Oliveira', 'F', '2015-03-12'],
            ['Bruno Henrique Santos', 'M', '2014-07-25'],
            ['Carla Eduarda Lima', 'F', '2013-11-02'],
            ['Diego Fernandes Rocha', 'M', '2015-01-30'],
            ['Elisa Martins Costa', 'F', '2014-09-18'],
            ['Felipe Augusto Alves', 'M', '2013-05-05'],
            ['Gabriela Nunes Pereira', 'F', '2015-08-22'],
            ['Heitor Ramos Barbosa', 'M', '2014-02-14'],
        ];

        $turma1 = Turma::query()->where('nome', '1º Ano A')->first();
        $turma2 = Turma::query()->where('nome', '5º Ano A')->first();
        $anoLetivo = \App\Models\AnoLetivo::query()->where('escola_id', $escola->id)->first();

        foreach ($nomes as $i => [$nome, $sexo, $nasc]) {
            $pessoa = Pessoa::create([
                'empresa_id' => $empresa->id,
                'nome' => $nome,
                'cpf' => fake()->unique()->numerify('###.###.###-##'),
                'data_nascimento' => $nasc,
                'sexo' => $sexo,
                'email' => fake()->unique()->safeEmail(),
                'telefone' => fake()->numerify('119########'),
            ]);

            $aluno = Aluno::create([
                'empresa_id' => $empresa->id,
                'pessoa_id' => $pessoa->id,
                'escola_id' => $escola->id,
                'registro_academico' => 'RA-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'data_ingresso' => '2025-02-01',
                'turno_preferencial' => $i < 4 ? 'matutino' : 'vespertino',
                'ativo' => 'S',
            ]);
            $alunos[] = $aluno;

            $turma = $i < 4 ? $turma1 : $turma2;
            Matricula::create([
                'empresa_id' => $empresa->id,
                'aluno_id' => $aluno->id,
                'turma_id' => $turma->id,
                'ano_letivo_id' => $anoLetivo->id,
                'status' => 'ativa',
                'numero_chamada' => $i + 1,
                'data_matricula' => '2025-02-01',
            ]);
        }

        // Responsaveis: um principal por aluno + um financeiro compartilhado
        $responsaveis = [];
        foreach ($alunos as $i => $aluno) {
            $pessoa = Pessoa::create([
                'empresa_id' => $empresa->id,
                'nome' => 'Responsável de '.$aluno->pessoa->nome,
                'cpf' => fake()->unique()->numerify('###.###.###-##'),
                'data_nascimento' => fake()->dateTimeBetween('-45 years', '-25 years')->format('Y-m-d'),
                'sexo' => fake()->randomElement(['M', 'F']),
                'email' => fake()->unique()->safeEmail(),
                'celular' => fake()->numerify('119########'),
            ]);

            $responsavel = Responsavel::create([
                'empresa_id' => $empresa->id,
                'pessoa_id' => $pessoa->id,
                'profissao' => fake()->randomElement(['Comerciante', 'Professor', 'Enfermeiro', 'Advogado', 'Autônomo']),
                'local_trabalho' => fake()->company(),
                'ativo' => 'S',
            ]);
            $responsaveis[] = $responsavel;

            $aluno->responsaveis()->attach($responsavel->id, [
                'tipo' => 'legal',
                'principal' => true,
                'pode_retirar' => true,
                'autoriza_contato' => true,
            ]);
        }

        // Responsavel financeiro compartilhado (vinculado a todos)
        $pessoaFin = Pessoa::create([
            'empresa_id' => $empresa->id,
            'nome' => 'Financeiro Demo',
            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'data_nascimento' => '1985-04-10',
            'sexo' => 'F',
            'email' => 'financeiro@escolademo.test',
        ]);
        $respFin = Responsavel::create([
            'empresa_id' => $empresa->id,
            'pessoa_id' => $pessoaFin->id,
            'profissao' => 'Contadora',
            'ativo' => 'S',
        ]);
        foreach ($alunos as $aluno) {
            $aluno->responsaveis()->attach($respFin->id, [
                'tipo' => 'financeiro',
                'principal' => false,
                'pode_retirar' => false,
                'autoriza_contato' => true,
            ]);
        }

        // Contas de portal: responsavel ve os 2 primeiros filhos;
        // aluno demo ve a si mesmo
        $responsaveis[0]->update(['user_id' => $usuarios['responsavel']->id]);
        $responsaveis[1]->update(['user_id' => $usuarios['responsavel']->id]);

        $alunos[2]->update(['user_id' => $usuarios['aluno']->id]);

        return [$alunos, $responsaveis];
    }

    /** Diarios, frequencias, avaliacoes e notas. */
    private function operacao(Empresa $empresa, array $usuarios, array $alunos): void
    {
        $turmas = Turma::query()->with('disciplinas')->get();
        $matriculasPorTurma = Matricula::query()
            ->whereIn('turma_id', $turmas->pluck('id'))
            ->where('status', 'ativa')
            ->get()
            ->groupBy('turma_id');

        $professorPorTurma = [];
        foreach (Turma::query()->with('atribuicoes')->get() as $turma) {
            $professorPorTurma[$turma->id] = $turma->atribuicoes->first()?->user_id
                ?? $usuarios['prof1']->id;
        }

        // Diarios: 3 aulas por disciplina nas ultimas semanas
        foreach ($turmas as $turma) {
            $matriculas = $matriculasPorTurma->get($turma->id, collect());
            if ($matriculas->isEmpty()) {
                continue;
            }

            foreach ($turma->disciplinas as $disciplina) {
                $atribuicao = Atribuicao::query()
                    ->where('turma_id', $turma->id)
                    ->where('disciplina_id', $disciplina->id)
                    ->first();

                foreach ([3, 2, 1] as $diasAtras) {
                    $data = now()->subDays($diasAtras)->toDateString();

                    $diario = Diario::firstOrCreate(
                        [
                            'empresa_id' => $empresa->id,
                            'turma_id' => $turma->id,
                            'disciplina_id' => $disciplina->id,
                            'data' => $data,
                        ],
                        [
                            'user_id' => $atribuicao?->user_id ?? $professorPorTurma[$turma->id],
                            'conteudo' => fake()->randomElement([
                                'Revisão de conteúdo', 'Exercícios práticos', 'Leitura e interpretação',
                                'Apresentação de projeto', 'Dinâmica em grupo', 'Correção de atividade',
                            ]),
                        ]
                    );

                    foreach ($matriculas as $matricula) {
                        $status = fake()->randomElement(
                            ['presente', 'presente', 'presente', 'presente', 'falta', 'justificada']
                        );
                        Frequencia::firstOrCreate(
                            [
                                'empresa_id' => $empresa->id,
                                'diario_id' => $diario->id,
                                'matricula_id' => $matricula->id,
                            ],
                            [
                                'status' => $status,
                                'justificativa' => $status === 'justificada' ? 'Atestado médico' : null,
                            ]
                        );
                    }
                }

                // Avaliacoes: prova + trabalho por disciplina
                foreach ([
                    ['prova', 'Avaliação 1', 10],
                    ['trabalho', 'Trabalho Prático', 10],
                ] as [$tipo, $nome, $max]) {
                    $avaliacao = Avaliacao::firstOrCreate(
                        [
                            'empresa_id' => $empresa->id,
                            'turma_id' => $turma->id,
                            'disciplina_id' => $disciplina->id,
                            'nome' => $nome,
                        ],
                        [
                            'user_id' => $atribuicao?->user_id ?? $professorPorTurma[$turma->id],
                            'tipo' => $tipo,
                            'periodo' => '1º Bimestre',
                            'nota_maxima' => $max,
                        ]
                    );

                    foreach ($matriculas as $matricula) {
                        Nota::firstOrCreate(
                            [
                                'empresa_id' => $empresa->id,
                                'avaliacao_id' => $avaliacao->id,
                                'matricula_id' => $matricula->id,
                            ],
                            ['nota' => fake()->randomFloat(1, 4, 10)]
                        );
                    }
                }
            }
        }
    }
}
