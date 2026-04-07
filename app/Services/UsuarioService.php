<?php

namespace App\Services;

use App\Models\User;
use App\Models\Estacao;
use App\Support\EmpresaContext;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use Illuminate\Support\Facades\Schema;

class UsuarioService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(User $usuario)
    {
        $this->model = $usuario;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof User) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\User");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Retorna usuarios para selects no contexto de reserva.
     * Formato: "Nome - CPF" (numero_identificacao) ou "Nome - email".
     */
    public function getAllForReservaSelect(): array
    {
        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->select(['id', 'nome', 'numero_identificacao', 'email'])
            ->orderBy('nome');

        $usuarios = $query->get();

        $out = [];
        foreach ($usuarios as $u) {
            $nome = trim((string) ($u->nome ?? ''));
            $doc = trim((string) ($u->numero_identificacao ?? ''));
            if ($doc === '') {
                $doc = trim((string) ($u->email ?? ''));
            }

            $label = $nome !== '' ? $nome : ('Usuario #' . (int) $u->id);
            if ($doc !== '') {
                $label .= ' - ' . $doc;
            }

            $out[(int) $u->id] = $label;
        }

        return $out;
    }

    public function createUser($data)
    {
        $acessoTodasEstacoes = $this->resolveAcessoTodasEstacoes($data);

        if (Schema::hasColumn($this->model->getTable(), 'empresa_id')) {
            $empresaId = app(EmpresaContext::class)->empresaId();
            if ($empresaId) {
                $data['empresa_id'] = $empresaId;
            }
        }

        if (Schema::hasColumn($this->model->getTable(), 'acesso_todas_estacoes')) {
            $data['acesso_todas_estacoes'] = $acessoTodasEstacoes;
        }

        $data['email_verified_at'] = now();
        
        /** @var User $user */
        $user = $this->model->create($data);
        $this->syncEstacoesPermitidas($user, $data, $acessoTodasEstacoes);

        return $user;
    }

    public function updatePassword($id, $data){
        $this->model = $this->applyEmpresaScope($this->model->newQuery())->find($id);
        if (!$this->model) {
            throw new \RuntimeException('Usuario nao encontrado para a empresa atual.');
        }

        if (array_key_exists('forcar_troca_senha', $data) && Schema::hasColumn($this->model->getTable(), 'forcar_troca_senha')) {
            $this->model->forcar_troca_senha = (bool) $data['forcar_troca_senha'];
        }

        if (array_key_exists('password', $data)) {
            $this->model->password = $data['password'];
        }

        $this->model->save();
    }

    public function updateUser($id, array $data): ?User
    {
        $user = $this->applyEmpresaScope($this->model->newQuery())->find($id);
        if (!$user) {
            return null;
        }

        $acessoTodasEstacoes = $this->resolveAcessoTodasEstacoes($data, $user);

        if (Schema::hasColumn($this->model->getTable(), 'acesso_todas_estacoes')) {
            $data['acesso_todas_estacoes'] = $acessoTodasEstacoes;
        }

        $user->update($data);
        $this->syncEstacoesPermitidas($user, $data, $acessoTodasEstacoes);

        return $user;
    }

    private function syncEstacoesPermitidas(User $user, array $data, bool $acessoTodasEstacoes): void
    {
        if (!Schema::hasTable('estacao_user')) {
            return;
        }

        if ($acessoTodasEstacoes) {
            $user->estacoesPermitidas()->detach();
            return;
        }

        $estacoes = collect($data['estacoes'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($estacoes->isEmpty()) {
            $user->estacoesPermitidas()->sync([]);
            return;
        }

        $query = Estacao::query()->whereIn('id', $estacoes->all());
        if (Schema::hasColumn('estacoes', 'empresa_id') && !empty($user->empresa_id)) {
            $query->where('empresa_id', $user->empresa_id);
        }

        $validIds = $query->pluck('id')->map(fn ($id) => (int) $id)->all();

        $user->estacoesPermitidas()->sync($validIds);
    }

    private function resolveAcessoTodasEstacoes(array $data, ?User $user = null): bool
    {
        if (array_key_exists('acesso_todas_estacoes', $data)) {
            return (bool) $data['acesso_todas_estacoes'];
        }

        if ($user !== null) {
            return $user->hasAcessoTodasEstacoes();
        }

        return true;
    }
}
