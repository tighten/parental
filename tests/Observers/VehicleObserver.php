<?php

namespace Parental\Tests\Observers;

use Parental\Tests\Models\Vehicle;

class VehicleObserver
{
    public function creating(Vehicle $vehicle)
    {
        $vehicle->driver_id = 1;
    }
}
