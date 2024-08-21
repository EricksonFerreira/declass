<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use Illuminate\Support\Facades\Hash;

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

    public function createUser($data){
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $this->model->create($data);
    }

    public function updatePassword($id, $data){
        $this->model = $this->model->find($id);
        $this->model->password = Hash::make($data['password']);
        $this->model->save();
    }
}
