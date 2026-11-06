<?php

namespace App\Services;

use App\Models\EmprestimoLivro;
use App\Models\Livro;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmprestimoLivroService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(EmprestimoLivro $emprestimo)
    {
        $this->model = $emprestimo;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof EmprestimoLivro) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\EmprestimoLivro');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        $this->ensureEmpresaSelectedForWrite();

        $livroId = (int) ($data['livro_id'] ?? 0);
        $livro = Livro::query()->whereKey($livroId)->first();
        if (! $livro || ! $this->pertenceAoTenant('livros', $livroId)) {
            throw ValidationException::withMessages(['livro_id' => 'Livro não encontrado.']);
        }
        if (! $livro->estaAtivo()) {
            throw ValidationException::withMessages(['livro_id' => 'Livro inativo não pode ser emprestado.']);
        }

        $alunoId = (int) ($data['aluno_id'] ?? 0);
        if (! $this->pertenceAoTenant('alunos', $alunoId)) {
            throw ValidationException::withMessages(['aluno_id' => 'Aluno não encontrado.']);
        }

        $jaEmprestado = EmprestimoLivro::query()
            ->where('aluno_id', $alunoId)
            ->where('livro_id', $livroId)
            ->where('status', 'ativo')
            ->exists();
        if ($jaEmprestado) {
            throw ValidationException::withMessages(['livro_id' => 'Este aluno já possui este livro emprestado.']);
        }

        if (! $livro->temDisponiveis()) {
            throw ValidationException::withMessages(['livro_id' => 'Todos os exemplares estão emprestados.']);
        }

        return parent::create($data);
    }

    /**
     * Baixa do emprestimo: marca devolvido com a data atual.
     * Retorna false quando ja estava devolvido.
     */
    public function devolver($id)
    {
        $this->ensureEmpresaSelectedForWrite();
        $item = $this->applyEmpresaScope($this->model->newQuery())->find($id);
        if (! $item) {
            return null;
        }
        if ($item->estaDevolvido()) {
            return false;
        }

        $item->update([
            'status' => 'devolvido',
            'data_devolucao' => now()->toDateString(),
        ]);

        return $item;
    }

    /**
     * Excluir so emprestimos ainda nao devolvidos — devolvidos
     * ficam como historico de circulacao.
     */
    public function delete($id)
    {
        $this->ensureEmpresaSelectedForWrite();
        $item = $this->applyEmpresaScope($this->model->newQuery())->find($id);
        if (! $item) {
            return false;
        }
        if ($item->estaDevolvido()) {
            return false;
        }

        return $item->delete();
    }

    private function pertenceAoTenant(string $tabela, int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $query = DB::table($tabela)->where('id', $id);
        $empresaId = $this->currentEmpresaId();
        if ($empresaId !== null) {
            $query->where('empresa_id', $empresaId);
        }

        return $query->exists();
    }
}
