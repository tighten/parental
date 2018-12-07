<?php

namespace Tightenco\Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Tightenco\Parental\HasChildren;

class Driver extends Model
{
    use HasChildren;

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
