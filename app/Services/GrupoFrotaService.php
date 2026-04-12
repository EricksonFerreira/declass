<?php

namespace App\Services;

use App\Models\GrupoFrota;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GrupoFrotaService extends AbstractBaseService
{
    protected Model $model;
    protected TipoOpcionalService $tipoOpcionalService;

    public function __construct(GrupoFrota $grupoFrota, TipoOpcionalService $tipoOpcionalService)
    {
        $this->model = $grupoFrota;
        $this->tipoOpcionalService = $tipoOpcionalService;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof GrupoFrota) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\GrupoFrota');
        }

        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data = $this->normalizarCamposMonetarios($data);
            $data = $this->processarImagens($data);
            $data = $this->processarImagem($data);

            $dadosGrupoFrota = collect($data)->except([
                'caracteristicas',
                'caracteristicaValor',
                'opcionais',
                'modelos',
            ])->toArray();

            $grupoFrota = $this->model->create($dadosGrupoFrota);

            $this->sincronizarRelacionamentos($grupoFrota, $data);

            return $grupoFrota;
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            /** @var \App\Models\GrupoFrota|null $item */
            $item = $this->model->find($id);

            if (!$item) {
                return null;
            }

            $data = $this->normalizarCamposMonetarios($data);
            $data = $this->processarImagens($data, $item);
            $data = $this->processarImagem($data, $item);

            $dadosGrupoFrota = collect($data)->except([
                'caracteristicas',
                'caracteristicaValor',
                'opcionais',
                'modelos',
            ])->toArray();

            $item->update($dadosGrupoFrota);

            $this->sincronizarRelacionamentos($item, $data);

            return $item;
        });
    }

    private function sincronizarRelacionamentos(GrupoFrota $grupoFrota, array $data): void
    {
        $caracteristicasSelecionadas = $data['caracteristicas'] ?? [];
        $valoresCaracteristicas = $data['caracteristicaValor'] ?? [];
        $caracteristicasSync = [];

        foreach ($caracteristicasSelecionadas as $caracteristicaId) {
            $caracteristicaId = (int) $caracteristicaId;

            $valor = $valoresCaracteristicas[$caracteristicaId] ?? null;

            if (is_string($valor)) {
                $valor = trim($valor);
            }

            $caracteristicasSync[$caracteristicaId] = [
                'valor' => $valor !== '' ? $valor : null,
            ];
        }

        $grupoFrota->caracteristicas()->sync($caracteristicasSync);
        $grupoFrota->opcionais()->sync($data['opcionais'] ?? []);
        $grupoFrota->modelos()->sync($data['modelos'] ?? []);
    }

    private function processarImagem(array $data, ?GrupoFrota $grupoFrota = null): array
    {
        $arquivo = $data['imagem'] ?? null;

        if (!$arquivo instanceof UploadedFile) {
            unset($data['imagem']);
            return $data;
        }

        if (!$arquivo->isValid()) {
            throw new \RuntimeException('Upload da imagem invalido.');
        }

        $path = $this->storeImagemArquivo($arquivo);
        $data['imagem'] = $path;

        $imagensAtuais = $data['imagens'] ?? (is_array($grupoFrota?->imagens) ? $grupoFrota->imagens : []);
        $imagensAtuais = $this->normalizeImagensArray($imagensAtuais);
        if (!in_array($path, $imagensAtuais, true)) {
            array_unshift($imagensAtuais, $path);
            $data['imagens'] = $imagensAtuais;
        }

        $imagemAntiga = is_string($grupoFrota?->imagem) ? trim($grupoFrota->imagem) : '';
        if ($imagemAntiga !== '' && $imagemAntiga !== $path) {
            $mantidas = $data['imagens'] ?? [];
            if (!in_array($imagemAntiga, $mantidas, true) && Storage::disk('public')->exists($imagemAntiga)) {
                Storage::disk('public')->delete($imagemAntiga);
            }
        }

        return $data;
    }

    private function processarImagens(array $data, ?GrupoFrota $grupoFrota = null): array
    {
        $arquivos = $data['imagens'] ?? null;
        if (!is_array($arquivos)) {
            unset($data['imagens']);
            return $data;
        }

        $novas = [];
        foreach ($arquivos as $arquivo) {
            if (!$arquivo instanceof UploadedFile) {
                continue;
            }
            if (!$arquivo->isValid()) {
                throw new \RuntimeException('Upload de uma das imagens invalido.');
            }
            $novas[] = $this->storeImagemArquivo($arquivo);
        }

        if (empty($novas)) {
            unset($data['imagens']);
            return $data;
        }

        $existentes = $this->normalizeImagensArray($grupoFrota?->imagens ?? []);
        $final = array_values(array_unique(array_merge($existentes, $novas)));
        $data['imagens'] = $final;

        return $data;
    }

    private function storeImagemArquivo(UploadedFile $arquivo): string
    {
        $extensao = trim((string) $arquivo->getClientOriginalExtension());
        $nomeArquivo = Str::uuid()->toString() . ($extensao !== '' ? '.' . $extensao : '');

        $disk = Storage::disk('public');
        $diretorio = 'grupos-frota';

        if (!$disk->exists($diretorio)) {
            $disk->makeDirectory($diretorio);
        }

        $conteudo = file_get_contents($arquivo->getPathname());
        if ($conteudo === false) {
            throw new \RuntimeException('Nao foi possivel ler o arquivo da imagem.');
        }

        $salvou = $disk->put($diretorio . '/' . $nomeArquivo, $conteudo);
        if (!$salvou) {
            throw new \RuntimeException('Nao foi possivel salvar a imagem do grupo de frota.');
        }

        return $diretorio . '/' . $nomeArquivo;
    }

    private function normalizeImagensArray(array $imagens): array
    {
        $result = [];
        foreach ($imagens as $imagem) {
            $path = is_string($imagem) ? trim($imagem) : '';
            if ($path !== '') {
                $result[] = $path;
            }
        }

        return array_values(array_unique($result));
    }

    public function pesquisarComFiltros(array $filtros, int $porPagina = 12): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->where('grupos_frota.ativo', 'S')
            ->with(['modelos' => function ($q) {
                $q->with(['marcas:id,nome', 'segmentos:id,nome'])->select('modelos.id', 'modelos.nome', 'modelos.marca_id', 'modelos.segmento_id', 'modelos.imagem');
            }])
            ->select('grupos_frota.id', 'grupos_frota.nome', 'grupos_frota.valor_diario', 'grupos_frota.imagem');

        if (!empty($filtros['grupo'])) {
            $query->where('grupos_frota.nome', 'like', '%' . $filtros['grupo'] . '%');
        }

        if (!empty($filtros['valor_min'])) {
            $query->where('grupos_frota.valor_diario', '>=', (float) $filtros['valor_min']);
        }

        if (!empty($filtros['valor_max'])) {
            $query->where('grupos_frota.valor_diario', '<=', (float) $filtros['valor_max']);
        }

        if (!empty($filtros['modelo']) || !empty($filtros['marca']) || !empty($filtros['segmento'])) {
            $query->whereHas('modelos', function ($q) use ($filtros) {
                if (!empty($filtros['modelo'])) {
                    $q->where('modelos.nome', 'like', '%' . $filtros['modelo'] . '%');
                }
                if (!empty($filtros['marca'])) {
                    $q->whereHas('marcas', function ($m) use ($filtros) {
                        $m->where('marcas.nome', 'like', '%' . $filtros['marca'] . '%');
                    });
                }
                if (!empty($filtros['segmento'])) {
                    $q->whereHas('segmentos', function ($s) use ($filtros) {
                        $s->where('segmentos.nome', 'like', '%' . $filtros['segmento'] . '%');
                    });
                }
            });
        }

        return $query->orderBy('grupos_frota.nome')->paginate($porPagina);
    }

    public function getModelosPorGrupoFrota($grupoFrotaId)
    {
        $grupoFrota = $this->model->find($grupoFrotaId);
        if ($grupoFrota) {
            return $grupoFrota->modelos()->get();
        }

        return collect();
    }

    public function getTiposOpcionaisComOpcionais($grupoFrotaId)
    {
        $tiposOpcionais = $this->tipoOpcionalService->getAll();

        $grupoFrota = $this->model->find($grupoFrotaId);
        if (!$grupoFrota) {
            return [];
        }

        $opcionais = $grupoFrota->opcionais()->get();
        $result = [];

        foreach ($tiposOpcionais as $tipoOpcional) {
            $result[$tipoOpcional->id] = [
                'tipo' => $tipoOpcional,
                'opcionais' => [],
            ];
        }

        foreach ($opcionais as $opcional) {
            $tipoId = $opcional['tipo_opcional_id'];
            if (isset($result[$tipoId])) {
                $result[$tipoId]['opcionais'][] = $opcional;
            }
        }

        return $result;
    }

    public function getGrupoFrotaByModelo($modeloId)
    {
        $grupoFrota = $this->model->whereHas('modelos', function ($query) use ($modeloId) {
            $query->where('modelo_id', $modeloId);
        })->select('nome', 'id')->first();

        if ($grupoFrota) {
            return [$grupoFrota->id => $grupoFrota->nome];
        }

        return null;
    }

    public function verificaGruposFrotaDisponiveis($estacao_retirada_id, $data_inicio, $estacao_devolucao_id, $data_fim) {}

    private function normalizarCamposMonetarios(array $data): array
    {
        if (!array_key_exists('valor_diario', $data)) {
            return $data;
        }

        $normalizado = $this->normalizarDecimal($data['valor_diario']);
        if ($normalizado !== null) {
            $data['valor_diario'] = $normalizado;
        }

        return $data;
    }

    private function normalizarDecimal($valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        if (is_int($valor) || is_float($valor)) {
            return (string) $valor;
        }

        $texto = trim((string) $valor);
        if ($texto === '') {
            return null;
        }

        $texto = str_replace(['R$', 'EUR', '$', ' '], '', $texto);
        $texto = preg_replace('/[^0-9,.\-]/', '', $texto) ?? '';
        if ($texto === '') {
            return null;
        }

        if (str_contains($texto, ',') && str_contains($texto, '.')) {
            $ultimaVirgula = strrpos($texto, ',');
            $ultimoPonto = strrpos($texto, '.');

            if ($ultimaVirgula !== false && $ultimoPonto !== false && $ultimaVirgula > $ultimoPonto) {
                $texto = str_replace('.', '', $texto);
                $texto = str_replace(',', '.', $texto);
            } else {
                $texto = str_replace(',', '', $texto);
            }
        } elseif (str_contains($texto, ',')) {
            $texto = str_replace('.', '', $texto);
            $texto = str_replace(',', '.', $texto);
        } elseif (substr_count($texto, '.') > 1) {
            $ultimaPosicao = strrpos($texto, '.');
            $parteInteira = str_replace('.', '', substr($texto, 0, $ultimaPosicao));
            $parteDecimal = substr($texto, $ultimaPosicao + 1);
            $texto = $parteInteira . '.' . $parteDecimal;
        }

        return $texto;
    }
}
