<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CondicoesGeraisSeeder extends Seeder
{
    private array $clausulas = [
        [
            'ordem' => 1,
            'titulo' => '01 Uso do veículo.',
            'texto' => 'O LOCATÁRIO deverá utilizar o veículo de forma regular, prudente e em conformidade com a legislação de trânsito vigente, sendo vedado o uso para fins ilícitos, competições, reboque, sublocação, transporte irregular, condução por pessoa não autorizada ou qualquer finalidade diversa daquela contratada.',
        ],
        [
            'ordem' => 2,
            'titulo' => '02 Entrega e devolução.',
            'texto' => 'O veículo deverá ser devolvido na data, hora e local contratados, no mesmo estado em que foi entregue, ressalvado o desgaste natural decorrente do uso regular. A devolução em local diverso poderá gerar taxa adicional de retorno.',
        ],
        [
            'ordem' => 3,
            'titulo' => '03 Atraso na devolução.',
            'texto' => 'A devolução após o prazo contratado poderá gerar cobrança de diárias adicionais, taxas operacionais, diferença tarifária e demais encargos previstos nas Condições Particulares, sem prejuízo das medidas legais cabíveis.',
        ],
        [
            'ordem' => 4,
            'titulo' => '04 Multas, pedágios e infrações.',
            'texto' => 'O LOCATÁRIO será integralmente responsável por multas, infrações de trânsito, pedágios, estacionamentos, remoções, apreensões, taxas administrativas e demais encargos ocorridos durante o período da locação, ainda que a cobrança seja recebida pela LOCADORA após a devolução do veículo.',
        ],
        [
            'ordem' => 5,
            'titulo' => '05 Danos e avarias.',
            'texto' => 'Danos identificados na devolução e não registrados na vistoria de retirada poderão ser cobrados do LOCATÁRIO, conforme orçamento, tabela interna, avaliação técnica ou documento equivalente. O LOCATÁRIO poderá ser responsabilizado por lucros cessantes enquanto o veículo estiver em reparo.',
        ],
        [
            'ordem' => 6,
            'titulo' => '06 Combustível.',
            'texto' => 'O veículo deverá ser devolvido com o mesmo nível de combustível informado na retirada. Havendo diferença, poderão ser cobrados o combustível faltante e a taxa de reabastecimento aplicável, conforme valores vigentes na data da devolução.',
        ],
        [
            'ordem' => 7,
            'titulo' => '07 Quilometragem.',
            'texto' => 'Quando houver limite de quilometragem contratado, o LOCATÁRIO será responsável pelo pagamento dos quilômetros excedentes, conforme valor definido nas Condições Particulares. A violação do lacre do hodômetro poderá acarretar multa contratual.',
        ],
        [
            'ordem' => 8,
            'titulo' => '08 Caução e autorização de cobrança.',
            'texto' => 'O LOCATÁRIO autoriza a LOCADORA a realizar bloqueio, caução, pré-autorização ou retenção de valores em seu cartão de crédito ou conta bancária, bem como a efetuar cobranças referentes a diárias, taxas, multas, avarias, combustível, limpeza, quilometragem excedente, franquias, coparticipações e demais encargos previstos neste contrato. O prazo para liberação da caução será de até 30 dias após a devolução do veículo, salvo pendências a regularizar.',
        ],
        [
            'ordem' => 9,
            'titulo' => '09 Acidente, roubo, furto ou pane.',
            'texto' => 'O LOCATÁRIO deverá comunicar imediatamente a LOCADORA em caso de acidente, roubo, furto, pane, apreensão ou qualquer ocorrência envolvendo o veículo, apresentando boletim de ocorrência quando aplicável. O não cumprimento poderá acarretar a perda das proteções contratadas.',
        ],
        [
            'ordem' => 10,
            'titulo' => '10 Proteções e franquias.',
            'texto' => 'As proteções contratadas, franquias, coparticipações e limites de cobertura serão aqueles descritos nas Condições Particulares. A proteção poderá não se aplicar em caso de uso indevido, dolo, culpa grave, infração contratual ou condução por pessoa não autorizada.',
        ],
        [
            'ordem' => 11,
            'titulo' => '11 Limpeza e conservação.',
            'texto' => 'O veículo deverá ser devolvido em condições normais de limpeza e conservação. Sujeira excessiva, mau uso, odores, manchas, danos internos ou necessidade de higienização especial poderão gerar cobrança adicional de taxa de limpeza.',
        ],
        [
            'ordem' => 12,
            'titulo' => '12 Declaração final.',
            'texto' => 'Ao assinar este contrato, o LOCATÁRIO declara ter lido, compreendido e aceitado as Condições Particulares, as Condições Gerais, o laudo de vistoria, as regras de cobrança, as políticas de uso do veículo e demais documentos vinculados à locação, comprometendo-se a cumpri-los integralmente.',
        ],
        [
            'ordem' => 13,
            'titulo' => '13 Proteção de dados (LGPD).',
            'texto' => 'O LOCATÁRIO declara ciência de que seus dados pessoais serão tratados pela LOCADORA para execução do contrato, cumprimento de obrigações legais, cobrança, segurança, prevenção a fraudes e gestão da locação, nos termos da Lei Geral de Proteção de Dados (Lei nº 13.709/2018). A LOCADORA compromete-se a utilizar os dados exclusivamente para as finalidades contratuais e a mantê-los em ambiente seguro, pelo prazo legal exigido.',
        ],
        [
            'ordem' => 14,
            'titulo' => '14 Foro e resolução de conflitos.',
            'texto' => 'Fica eleito o foro da comarca da sede da LOCADORA para dirimir quaisquer controvérsias decorrentes deste contrato, com renúncia expressa a qualquer outro, por mais privilegiado que seja, sem prejuízo da faculdade de a LOCADORA requerer medidas judiciais de urgência em outro foro competente. As partes comprometem-se a buscar solução extrajudicial prévia sempre que possível.',
        ],
    ];

    public function run(): void
    {
        $templateEmpresaId = DB::table('empresas')
            ->where('slug', Empresa::DEFAULT_TEMPLATE_SLUG)
            ->value('id');

        if (!$templateEmpresaId) {
            $this->command->warn('Empresa padrão não encontrada. Seeder de Condições Gerais ignorado.');
            return;
        }

        if (!\App\Support\SchemaCache::hasTable('termo_consentimento')) {
            return;
        }

        foreach ($this->clausulas as $clausula) {
            foreach (['checkin', 'checkout'] as $tipo) {
                DB::table('termo_consentimento')->updateOrInsert(
                    [
                        'empresa_id' => $templateEmpresaId,
                        'ordem' => $clausula['ordem'],
                        'tipo' => $tipo,
                    ],
                    [
                        'titulo' => $clausula['titulo'],
                        'texto' => $clausula['texto'],
                        'nome' => $clausula['titulo'],
                        'ativo' => 'S',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('Condições Gerais da Locação semeadas para a empresa padrão (checkin e checkout).');
    }
}
