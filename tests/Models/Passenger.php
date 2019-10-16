<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;

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
