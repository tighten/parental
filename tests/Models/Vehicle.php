<?php

namespace Tightenco\Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Tightenco\Parental\ReturnsChildModels;

class Vehicle extends Model
{
    use ReturnsChildModels;

    protected $fillable = [
        'type', 'driver_id'
    ];

    protected $childTypeAliases = [
        'car' => Car::class
    ];

    protected $guarded = [];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function passengers()
    {
        return $this->hasMany(Passenger::class);
    }

    public function trips()
    {
        return $this->belongsToMany(Trip::class);
    }
}
