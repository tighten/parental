<?php

namespace Parental\Tests\Observers;

use Parental\Tests\Models\Car;

class CarObserver
{
    public function creating(Car $car)
    {
        $car->driver_id = 2;
    }
}
