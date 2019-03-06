<?php

namespace Tightenco\Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $guarded = [];

    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
