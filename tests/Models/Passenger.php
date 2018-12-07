<?php

namespace Tightenco\Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Tightenco\Parental\HasChildren;

class Passenger extends Model
{
    use HasChildren;

    protected $guarded = [];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
