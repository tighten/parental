<?php

namespace Tightenco\Parental\Tests\Observers;

use Tightenco\Parental\Tests\Models\Vehicle;

class VehicleObserver
{
    public function creating(Vehicle $vehicle)
    {
        $vehicle->driver_id = 1;
    }
}
