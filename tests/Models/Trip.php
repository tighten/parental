<?php

namespace Tightenco\Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Tightenco\Parental\ReturnsChildModels;

class Trip extends Model
{
    use ReturnsChildModels;

    protected $childTypeColumn = 'trip_type';

    protected $guarded = [];

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class);
    }

    public function cars()
    {
        return $this->belongsToMany(Car::class);
    }
}
