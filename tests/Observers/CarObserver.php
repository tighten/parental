<?php

namespace Tightenco\Parental\Tests\Observers;

use Tightenco\Parental\Tests\Models\Car;

class CarObserver
{
    public function creating(Car $car)
    {
        $car->driver_id = 2;
    }
}
