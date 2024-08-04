<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class BookingService extends AbstractBaseService
{
    protected $model;
    public function __construct(Booking $booking)
    {
        $this->model = $booking;
    }

    public function setModel(Model $model): void {
        if (!$model instanceof Booking) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Booking");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
