<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\Responsavel;
use App\Models\User;
use App\Support\EmpresaContext;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
     * Retorna usuarios para selects.
     * Formato: "Nome - documento" (numero_identificacao) ou "Nome - email".
     */
    public function getAllForSelect($key = 'id', $value = 'nome'): array
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
        // Coluna legada NOT NULL: espelha o nome de exibicao.
        if (empty($data['name']) && ! empty($data['nome'])) {
            $data['name'] = $data['nome'];
        }

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
     * Id do perfil (role) do tenant pelo nome — ex.: 'aluno',
     * 'responsavel_legal'. Garante os perfis padrao da empresa como
     * fallback quando o perfil ainda nao existe.
     */
    public function roleIdPorNome(string $nome): ?int
    {
        $empresaId = $this->currentEmpresaId();
        if (! $empresaId) {
            return null;
        }

        $buscar = fn () => DB::table('roles')
            ->where('empresa_id', $empresaId)
            ->where('guard_name', 'web')
            ->where('name', $nome)
            ->value('id');

        $roleId = $buscar();
        if (! $roleId) {
            (new \Database\Seeders\PerfisEscolaresSeeder)->garantirPerfisParaEmpresa($empresaId);
            $roleId = $buscar();
        }

        return $roleId ? (int) $roleId : null;
    }

    /**
     * Escopo de listagem por tipo de conta (colaborador/aluno/
     * responsavel). Retorna null quando nao ha filtro.
     */
    public function scopeTipoConta(?string $tipo): ?\Closure
    {
        return match ($tipo) {
            User::TIPO_CONTA_ALUNO => fn ($query) => $query->where(fn ($q) => $q
                ->whereHas('aluno')
                ->orWhereHas('regra', fn ($r) => $r->where('name', User::ROLE_ALUNO))),
            User::TIPO_CONTA_RESPONSAVEL => fn ($query) => $query->where(fn ($q) => $q
                ->whereHas('responsavel')
                ->orWhereHas('regra', fn ($r) => $r->whereIn('name', User::ROLES_RESPONSAVEL))),
            User::TIPO_CONTA_COLABORADOR => fn ($query) => $query
                ->whereDoesntHave('aluno')
                ->whereDoesntHave('responsavel')
                ->where(fn ($q) => $q->whereNull('role_id')
                    ->orWhereHas('regra', fn ($r) => $r->whereNotIn('name', User::ROLES_PORTAL))),
            default => null,
        };
    }

    /**
     * Vinculo da conta com aluno/responsavel: limpa vinculos
     * anteriores da conta e grava o novo (quando informado).
     */
    public function sincronizarVinculoConta(User $usuario, ?string $tipo, ?int $vinculoId): void
    {
        Aluno::query()->where('user_id', $usuario->id)->update(['user_id' => null]);
        Responsavel::query()->where('user_id', $usuario->id)->update(['user_id' => null]);

        if ($tipo === User::TIPO_CONTA_ALUNO && $vinculoId) {
            Aluno::query()
                ->where('empresa_id', $usuario->empresa_id)
                ->whereKey($vinculoId)
                ->update(['user_id' => $usuario->id]);
        }

        if ($tipo === User::TIPO_CONTA_RESPONSAVEL && $vinculoId) {
            Responsavel::query()
                ->where('empresa_id', $usuario->empresa_id)
                ->whereKey($vinculoId)
                ->update(['user_id' => $usuario->id]);
        }
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
