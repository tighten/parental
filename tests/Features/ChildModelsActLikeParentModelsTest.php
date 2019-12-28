<?php

namespace Parental\Tests\Features;

use Parental\Tests\Models\Car;
use Parental\Tests\Models\Driver;
use Parental\Tests\Models\Passenger;
use Parental\Tests\Models\Trip;
use Parental\Tests\Models\Vehicle;
use Parental\Tests\TestCase;

class ChildModelsActLikeParentModelsTest extends TestCase
{
    /** @test */
    function vehicle_can_access_belongs_to_relationship_on_car_model()
    {
        $car = Car::create([
            'driver_id' => Driver::create(['name' => 'Joe'])->id,
        ]);

        $vehicle = Vehicle::find($car->id);

        $this->assertEquals($vehicle->driver->id, $car->driver->id);
    }

    /** @test */
    function vehicle_can_access_has_many_relationship_on_car_model()
    {
        $car = Car::create();

        Passenger::create(['name' => 'Jack', 'vehicle_id' => $car->id]);
        Passenger::create(['name' => 'Jill', 'vehicle_id' => $car->id]);

        $vehicle = Vehicle::find($car->id);

        $this->assertEquals($vehicle->passengers->pluck('id'), $car->passengers->pluck('id'));
    }

    /** @test */
    function vehicle_can_access_many_to_many_relationship_on_car_model()
    {
        $car = Car::create();

        $car->trips()->create([]);

        $vehicle = Vehicle::find($car->id);

        $this->assertEquals($vehicle->fresh()->trips->pluck('id'), $car->fresh()->trips->pluck('id'));
    }

    /** @test */
    public function guarded_or_fillable_models_have_raw_attributes_like_timestamps()
    {
        $vehicle = Vehicle::create()->fresh();

        $this->assertNotNull($vehicle->id);
        $this->assertNotNull($vehicle->created_at);
        $this->assertNotNull($vehicle->updated_at);
    }
}
