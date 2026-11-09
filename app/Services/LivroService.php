<?php

namespace App\Services;

use App\Models\Livro;
use Illuminate\Database\Eloquent\Model;

class LivroService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Livro $livro)
    {
        $this->model = $livro;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Livro) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Livro');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        $data = $this->guardarCapa($data);

        return parent::create($data);
    }

    public function update($id, array $data)
    {
        $data = $this->guardarCapa($data);

        return parent::update($id, $data);
    }

    /**
     * Busca "estilo catalogo de biblioteca": cada palavra digitada
     * precisa aparecer em algum campo (titulo, autor, editora,
     * ISBN, localizacao ou descricao), em qualquer ordem. Como o
     * collation utf8mb4_*_ci ignora acentos, "historia" encontra
     * "historia" e "Dom Casmurro" bate com "casmurro dom".
     * Resultado ordenado por relevancia: ISBN exato > titulo que
     * comeca com o termo > contem no titulo > demais campos.
     */
    public function buscaProfunda($query, ?string $termo): void
    {
        $termo = trim((string) $termo);
        if ($termo === '') {
            return;
        }

        $campos = ['titulo', 'autor', 'editora', 'isbn', 'localizacao', 'descricao'];
        $tokens = collect(preg_split('/\s+/u', $termo))
            ->filter(fn ($t) => mb_strlen($t) > 0)
            ->unique()
            ->take(6);

        foreach ($tokens as $token) {
            $like = '%'.$token.'%';
            $query->where(function ($q) use ($campos, $like) {
                foreach ($campos as $campo) {
                    $q->orWhere($campo, 'like', $like);
                }
            });
        }

        $query->orderByRaw(
            'CASE WHEN isbn = ? THEN 0 WHEN titulo LIKE ? THEN 1 WHEN titulo LIKE ? THEN 2 ELSE 3 END',
            [$termo, $termo.'%', '%'.$termo.'%']
        );
    }

    /** Guarda a capa enviada no disk public e ajusta $data. */
    private function guardarCapa(array $data): array
    {
        $arquivo = $data['capa'] ?? null;
        unset($data['capa']);

        if ($arquivo instanceof \Illuminate\Http\UploadedFile && $arquivo->isValid()) {
            $data['capa_path'] = $arquivo->store('livros/capas', 'public');
        }

        return $data;
    }
}
