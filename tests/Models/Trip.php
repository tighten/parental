<?php

namespace Tightenco\Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Tightenco\Parental\HasChildren;

class Trip extends Model
{
    use HasChildren;

    protected $parentType = 'trip';
    protected $childColumn = 'trip_type';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('verification', function ($query) {
            $query->whereNotNull('trips.id');
        });
    }

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class);
    }

    public function cars()
    {
        return $this->belongsToMany(Car::class, 'trip_vehicle', 'trip_id', 'vehicle_id');
    }
}
