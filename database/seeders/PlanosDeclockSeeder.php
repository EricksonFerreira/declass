<?php

namespace Database\Seeders;

use App\Models\Plano;
use Illuminate\Database\Seeder;

/**
 * Sincroniza os planos DECLOCK e seus itens de venda.
 *
 * Idempotente: pode rodar quantas vezes for — os planos são localizados
 * pelo slug e os itens são regravados na ordem definida aqui.
 *
 * O plano "trial" é gratuito e não comprável (ativo = false): ele existe
 * apenas para ser atribuído automaticamente no cadastro de novas empresas,
 * concedendo 7 dias de acesso completo (regra em EmpresaPlanoService::contratar).
 */
class PlanosDeclockSeeder extends Seeder
{
    public const SLUG_TRIAL = 'trial';

    public function run(): void
    {
        foreach ($this->planos() as $slug => $def) {
            $plano = Plano::updateOrCreate(
                ['slug' => $slug],
                $def['atributos']
            );

            $this->sincronizarItens($plano, $def['itens']);
        }
    }

    /**
     * @return array<string, array{atributos: array<string, mixed>, itens: string[]}>
     */
    private function planos(): array
    {
        return [
            self::SLUG_TRIAL => [
                'atributos' => [
                    'nome' => 'Trial',
                    'descricao' => 'Teste grátis por 7 dias: todos os recursos do DECLOCK liberados, sem cobrança e sem compromisso.',
                    'valor_mensal' => 0,
                    'valor_anual' => 0,
                    'limite_usuarios' => null,
                    'limite_veiculos' => null,
                    'limite_armazenamento_mb' => null,
                    'site_vendas' => false,
                    'modulo_multas' => false,
                    'modulo_nfse' => false,
                    'multi_estacao' => false,
                    'api_integracao' => true,
                    // Não comprável: só entra via cadastro de empresa nova.
                    'ativo' => false,
                    'destaque' => false,
                    'ordem' => 0,
                ],
                'itens' => [
                    'Todos os recursos liberados por 7 dias',
                    'Rastreador de tempo em um clique',
                    'Timesheet semanal editável',
                    'Projetos, tarefas e calendário de alocações',
                    'Equipes, folgas e férias',
                    'Relatórios de produtividade',
                    'Usuários ilimitados durante o teste',
                ],
            ],
            'starter' => [
                'atributos' => [
                    'nome' => 'Starter',
                    'descricao' => 'Essencial para começar: rastreador de tempo, timesheet, projetos e relatórios para até 3 usuários.',
                    'valor_mensal' => 149.00,
                    'valor_anual' => 119.00,
                    'limite_usuarios' => 3,
                    'limite_veiculos' => null,
                    'limite_armazenamento_mb' => null,
                    'ativo' => true,
                    'ordem' => 1,
                ],
                'itens' => [
                    'Rastreador de tempo em um clique',
                    'Timesheet semanal editável',
                    'Projetos, tarefas e calendário de alocações',
                    'Aprovação de férias e folgas',
                    'Relatórios de produtividade',
                    'Até 3 usuários',
                ],
            ],
            'professional' => [
                'atributos' => [
                    'nome' => 'Professional',
                    'descricao' => 'Gestão completa do tempo da equipe, com usuários ilimitados, relatórios avançados e API de integração.',
                    'valor_mensal' => 249.00,
                    'valor_anual' => 199.00,
                    'limite_usuarios' => null,
                    'limite_veiculos' => null,
                    'limite_armazenamento_mb' => null,
                    'api_integracao' => true,
                    'ativo' => true,
                    'ordem' => 2,
                ],
                'itens' => [
                    'Rastreador de tempo em um clique',
                    'Timesheet semanal editável',
                    'Projetos, tarefas e calendário de alocações',
                    'Aprovação de férias e folgas',
                    'Relatórios de produtividade e exportação',
                    'Perfis e permissões avançados',
                    'API de integração completa',
                    'Suporte prioritário',
                ],
            ],
        ];
    }

    /**
     * Regrava os itens do plano na ordem informada, removendo sobras
     * (ex.: textos legados de domínios anteriores).
     *
     * @param  string[]  $descricoes
     */
    private function sincronizarItens(Plano $plano, array $descricoes): void
    {
        foreach ($descricoes as $indice => $descricao) {
            $plano->itens()->updateOrCreate(
                ['ordem' => $indice + 1],
                ['descricao' => $descricao, 'incluso' => true]
            );
        }

        $plano->itens()->where('ordem', '>', count($descricoes))->delete();
    }
}
