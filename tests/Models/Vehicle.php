<?php

namespace Tightenco\Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Tightenco\Parental\HasChildren;

class Vehicle extends Model
{
    use HasChildren;

    protected $fillable = [
        'type', 'driver_id'
    ];

    protected $childTypes = [
        'car' => Car::class
    ];

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $model->boot_count = $model->boot_count ? $model->boot_count + 1 : 1;
        });
    }

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

    public function parts()
    {
        return $this->morphToMany(Part::class, 'partable', 'vehicle_parts');
    }
}
