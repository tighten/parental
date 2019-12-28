<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $guarded = [];

    public function vehicles()
    {
        return $this->morphedByMany(Vehicle::class, 'partable', 'vehicle_parts');
    }
}
