<?php

namespace App\Services;

use App\Models\User;
use App\Support\EmpresaContext;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Model;

class UsuarioService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(User $usuario)
    {
        $this->model = $usuario;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof User) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\User');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Contas superadmin são de plataforma e só podem ser configuradas
     * diretamente no banco (tipo_usuario_id = 1). Elas nunca aparecem
     * nem podem ser alteradas pelos fluxos de gestão de usuários.
     */
    protected function applyEmpresaScope($query)
    {
        return parent::applyEmpresaScope($query)->where(function ($q) {
            $q->whereNull('tipo_usuario_id')->orWhere('tipo_usuario_id', '!=', 1);
        });
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

            $label = $nome !== '' ? $nome : ('Usuario #'.(int) $u->id);
            if ($doc !== '') {
                $label .= ' - '.$doc;
            }

            $out[(int) $u->id] = $label;
        }

        return $out;
    }

    public function createUser($data)
    {
        if (SchemaCache::hasColumn($this->model->getTable(), 'empresa_id')) {
            $empresaId = app(EmpresaContext::class)->empresaId();
            if ($empresaId) {
                $data['empresa_id'] = $empresaId;
            }
        }

        $data['email_verified_at'] = now();

        /** @var User $user */
        $user = $this->model->create($this->apenasColunasExistentes($data));

        return $user;
    }

    public function updatePassword($id, $data)
    {
        $this->model = $this->applyEmpresaScope($this->model->newQuery())->find($id);
        if (! $this->model) {
            throw new \RuntimeException('Usuario nao encontrado para a empresa atual.');
        }

        if (array_key_exists('forcar_troca_senha', $data) && SchemaCache::hasColumn($this->model->getTable(), 'forcar_troca_senha')) {
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
        if (! $user) {
            return null;
        }

        $user->update($this->apenasColunasExistentes($data));

        return $user;
    }

    /**
     * Filtra o payload para colunas fillable que existem no schema atual —
     * evita erro 42S22 quando o banco ainda nao recebeu uma migration
     * (ex.: deploy sem migrate). Chaves que nao sao colunas (_token etc.)
     * sao descartadas, como o mass assignment ja faria.
     */
    private function apenasColunasExistentes(array $data): array
    {
        $table = $this->model->getTable();
        $attrs = [];

        foreach ($this->model->getFillable() as $column) {
            if (array_key_exists($column, $data) && SchemaCache::hasColumn($table, $column)) {
                $attrs[$column] = $data[$column];
            }
        }

        return $attrs;
    }
}
