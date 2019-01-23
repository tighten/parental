<?php

namespace Tightenco\Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    public function vehicles()
    {
        return $this->morphedByMany(Vehicle::class, 'partable', 'vehicle_parts');
    }
}
