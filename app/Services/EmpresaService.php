<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;

class EmpresaService extends AbstractBaseService
{
    protected Model $model;
    private ReplicacaoBaseService $replicacaoBaseService;
    private DefaultFrotaTemplateService $defaultFrotaTemplateService;
    private ?array $empresaColumnsCache = null;

    public function __construct(
        Empresa $empresa,
        ReplicacaoBaseService $replicacaoBaseService,
        DefaultFrotaTemplateService $defaultFrotaTemplateService,
    ) {
        $this->model = $empresa;
        $this->replicacaoBaseService = $replicacaoBaseService;
        $this->defaultFrotaTemplateService = $defaultFrotaTemplateService;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Empresa) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Empresa');
        }

        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        $data = $this->sanitizeDataForExistingColumns($data);
        $empresa = $this->model->newQuery()->create($data);

        if (SchemaCache::hasColumn($this->model->getTable(), 'empresa_id') && empty($empresa->empresa_id)) {
            $empresa->empresa_id = $empresa->id;
            $empresa->save();
        }

        return $empresa;
    }

    public function update($id, array $data)
    {
        $data = $this->sanitizeDataForExistingColumns($data);
        $item = $this->model->newQuery()->find($id);

        if (!$item) {
            return null;
        }

        $item->update($data);
        return $item;
    }

    public function createWithDefaultTemplate(array $data): Empresa
    {
        return DB::transaction(function () use ($data): Empresa {
            /** @var Empresa $empresa */
            $empresa = $this->create($data);

            $templateEmpresaId = $this->resolveDefaultTemplateEmpresaId();
            if ($templateEmpresaId === null || $templateEmpresaId === (int) $empresa->id) {
                return $empresa;
            }

            $this->seedDefaultTemplateCompanyData((int) $templateEmpresaId);

            $modulos = array_keys($this->replicacaoBaseService->modulosDisponiveis());
            if (!empty($modulos)) {
                $this->replicacaoBaseService->replicar($templateEmpresaId, [(int) $empresa->id], $modulos);
            }

            return $empresa;
        });
    }

    protected function shouldApplyEmpresaScope(): bool
    {
        // Cadastro de empresas e multi-tenant de administracao global.
        return false;
    }

    private function resolveDefaultTemplateEmpresaId(): ?int
    {
        if (!SchemaCache::hasTable('empresas')) {
            return null;
        }

        $table = $this->model->getTable();
        $defaultId = DB::table($table)
            ->where('slug', Empresa::DEFAULT_TEMPLATE_SLUG)
            ->value('id');

        if ($defaultId) {
            return (int) $defaultId;
        }

        $newId = DB::table($table)->insertGetId([
            'nome' => 'Empresa Padrao',
            'slug' => Empresa::DEFAULT_TEMPLATE_SLUG,
            'ativo' => 'S',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (SchemaCache::hasColumn($table, 'empresa_id')) {
            DB::table($table)->where('id', $newId)->update(['empresa_id' => $newId]);
        }

        return (int) $newId;
    }

    /**
     * Garante que a empresa base tenha os cadastros globais de horarios e dias uteis
     * para que novas empresas recebam esses registros ao serem replicadas.
     */
    private function seedDefaultTemplateCompanyData(int $empresaId): void
    {
        $this->replicateGlobalRowsToEmpresa('dias_uteis', $empresaId, ['dia']);
        $this->replicateGlobalRowsToEmpresa('horarios_diarios', $empresaId, ['nome']);
        $this->defaultFrotaTemplateService->seed($empresaId);
        $this->seedCondicoesGerais($empresaId);
    }

    private function seedCondicoesGerais(int $empresaId): void
    {
        if (!SchemaCache::hasTable('termo_consentimento')) {
            return;
        }

        $clausulas = [
            ['ordem' => 1,  'titulo' => '01 Uso do veículo.',              'texto' => 'O LOCATÁRIO deverá utilizar o veículo de forma regular, prudente e em conformidade com a legislação de trânsito vigente, sendo vedado o uso para fins ilícitos, competições, reboque, sublocação, transporte irregular, condução por pessoa não autorizada ou qualquer finalidade diversa daquela contratada.'],
            ['ordem' => 2,  'titulo' => '02 Entrega e devolução.',          'texto' => 'O veículo deverá ser devolvido na data, hora e local contratados, no mesmo estado em que foi entregue, ressalvado o desgaste natural decorrente do uso regular. A devolução em local diverso poderá gerar taxa adicional de retorno.'],
            ['ordem' => 3,  'titulo' => '03 Atraso na devolução.',          'texto' => 'A devolução após o prazo contratado poderá gerar cobrança de diárias adicionais, taxas operacionais, diferença tarifária e demais encargos previstos nas Condições Particulares, sem prejuízo das medidas legais cabíveis.'],
            ['ordem' => 4,  'titulo' => '04 Multas, pedágios e infrações.', 'texto' => 'O LOCATÁRIO será integralmente responsável por multas, infrações de trânsito, pedágios, estacionamentos, remoções, apreensões, taxas administrativas e demais encargos ocorridos durante o período da locação, ainda que a cobrança seja recebida pela LOCADORA após a devolução do veículo.'],
            ['ordem' => 5,  'titulo' => '05 Danos e avarias.',             'texto' => 'Danos identificados na devolução e não registrados na vistoria de retirada poderão ser cobrados do LOCATÁRIO, conforme orçamento, tabela interna, avaliação técnica ou documento equivalente. O LOCATÁRIO poderá ser responsabilizado por lucros cessantes enquanto o veículo estiver em reparo.'],
            ['ordem' => 6,  'titulo' => '06 Combustível.',                  'texto' => 'O veículo deverá ser devolvido com o mesmo nível de combustível informado na retirada. Havendo diferença, poderão ser cobrados o combustível faltante e a taxa de reabastecimento aplicável, conforme valores vigentes na data da devolução.'],
            ['ordem' => 7,  'titulo' => '07 Quilometragem.',               'texto' => 'Quando houver limite de quilometragem contratado, o LOCATÁRIO será responsável pelo pagamento dos quilômetros excedentes, conforme valor definido nas Condições Particulares. A violação do lacre do hodômetro poderá acarretar multa contratual.'],
            ['ordem' => 8,  'titulo' => '08 Caução e autorização de cobrança.', 'texto' => 'O LOCATÁRIO autoriza a LOCADORA a realizar bloqueio, caução, pré-autorização ou retenção de valores em seu cartão de crédito ou conta bancária, bem como a efetuar cobranças referentes a diárias, taxas, multas, avarias, combustível, limpeza, quilometragem excedente, franquias, coparticipações e demais encargos previstos neste contrato.'],
            ['ordem' => 9,  'titulo' => '09 Acidente, roubo, furto ou pane.', 'texto' => 'O LOCATÁRIO deverá comunicar imediatamente a LOCADORA em caso de acidente, roubo, furto, pane, apreensão ou qualquer ocorrência envolvendo o veículo, apresentando boletim de ocorrência quando aplicável. O não cumprimento poderá acarretar a perda das proteções contratadas.'],
            ['ordem' => 10, 'titulo' => '10 Proteções e franquias.',       'texto' => 'As proteções contratadas, franquias, coparticipações e limites de cobertura serão aqueles descritos nas Condições Particulares. A proteção poderá não se aplicar em caso de uso indevido, dolo, culpa grave, infração contratual ou condução por pessoa não autorizada.'],
            ['ordem' => 11, 'titulo' => '11 Limpeza e conservação.',       'texto' => 'O veículo deverá ser devolvido em condições normais de limpeza e conservação. Sujeira excessiva, mau uso, odores, manchas, danos internos ou necessidade de higienização especial poderão gerar cobrança adicional de taxa de limpeza.'],
            ['ordem' => 12, 'titulo' => '12 Declaração final.',            'texto' => 'Ao assinar este contrato, o LOCATÁRIO declara ter lido, compreendido e aceitado as Condições Particulares, as Condições Gerais, o laudo de vistoria, as regras de cobrança, as políticas de uso do veículo e demais documentos vinculados à locação, comprometendo-se a cumpri-los integralmente.'],
            ['ordem' => 13, 'titulo' => '13 Proteção de dados (LGPD).',     'texto' => 'O LOCATÁRIO declara ciência de que seus dados pessoais serão tratados pela LOCADORA para execução do contrato, cumprimento de obrigações legais, cobrança, segurança, prevenção a fraudes e gestão da locação, nos termos da Lei Geral de Proteção de Dados (Lei nº 13.709/2018). A LOCADORA compromete-se a utilizar os dados exclusivamente para as finalidades contratuais e a mantê-los em ambiente seguro, pelo prazo legal exigido.'],
            ['ordem' => 14, 'titulo' => '14 Foro e resolução de conflitos.', 'texto' => 'Fica eleito o foro da comarca da sede da LOCADORA para dirimir quaisquer controvérsias decorrentes deste contrato, com renúncia expressa a qualquer outro, por mais privilegiado que seja, sem prejuízo da faculdade de a LOCADORA requerer medidas judiciais de urgência em outro foro competente. As partes comprometem-se a buscar solução extrajudicial prévia sempre que possível.'],
        ];

        $table = 'termo_consentimento';
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);

        foreach ($clausulas as $clausula) {
            foreach (['checkin', 'checkout'] as $tipo) {
                $payload = [
                    'empresa_id' => $empresaId,
                    'ordem' => $clausula['ordem'],
                    'titulo' => $clausula['titulo'],
                    'texto' => $clausula['texto'],
                    'tipo' => $tipo,
                    'updated_at' => now(),
                ];

                if (in_array('nome', $columns, true)) {
                    $payload['nome'] = $clausula['titulo'];
                }
                if (in_array('ativo', $columns, true)) {
                    $payload['ativo'] = 'S';
                }
                if (in_array('created_at', $columns, true)) {
                    $payload['created_at'] = now();
                }

                DB::table($table)->updateOrInsert(
                    ['empresa_id' => $empresaId, 'ordem' => $clausula['ordem'], 'tipo' => $tipo],
                    $payload
                );
            }
        }
    }

    /**
     * Copia linhas globais (empresa_id nulo) para a empresa informada, respeitando
     * uma chave natural simples para evitar duplicidade.
     *
     * @param array<int, string> $uniqueColumns
     */
    private function replicateGlobalRowsToEmpresa(string $table, int $empresaId, array $uniqueColumns): void
    {
        if (!SchemaCache::hasTable($table) || !SchemaCache::hasColumn($table, 'empresa_id')) {
            return;
        }

        $columns = Schema::getColumnListing($table);
        $rows = DB::table($table)->whereNull('empresa_id')->get();
        if ($rows->isEmpty()) {
            return;
        }

        foreach ($rows as $row) {
            $payload = [];
            foreach ($columns as $column) {
                if (in_array($column, ['id', 'empresa_id', 'created_at', 'updated_at'], true)) {
                    continue;
                }

                $payload[$column] = $row->{$column} ?? null;
            }

            $where = [];
            foreach ($uniqueColumns as $column) {
                if (array_key_exists($column, $payload)) {
                    $where[$column] = $payload[$column];
                }
            }

            if (empty($where)) {
                continue;
            }

            $payload['empresa_id'] = $empresaId;
            if (in_array('created_at', $columns, true)) {
                $payload['created_at'] = now();
            }
            if (in_array('updated_at', $columns, true)) {
                $payload['updated_at'] = now();
            }

            DB::table($table)->updateOrInsert(
                array_merge($where, ['empresa_id' => $empresaId]),
                $payload
            );
        }
    }

    private function sanitizeDataForExistingColumns(array $data): array
    {
        $columns = $this->getEmpresaColumns();

        return array_filter(
            $data,
            fn (string $key): bool => in_array($key, $columns, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function getEmpresaColumns(): array
    {
        if ($this->empresaColumnsCache !== null) {
            return $this->empresaColumnsCache;
        }

        if (!SchemaCache::hasTable($this->model->getTable())) {
            $this->empresaColumnsCache = [];
            return $this->empresaColumnsCache;
        }

        $this->empresaColumnsCache = Schema::getColumnListing($this->model->getTable());

        return $this->empresaColumnsCache;
    }
}
