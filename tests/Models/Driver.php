<?php

namespace Tightenco\Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Tightenco\Parental\ReturnsChildModels;

class Driver extends Model
{
    use ReturnsChildModels;

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
