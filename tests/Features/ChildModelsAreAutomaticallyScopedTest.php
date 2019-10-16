<?php

namespace Parental\Tests\Features;

use Parental\Tests\Models\Admin;
use Parental\Tests\Models\Car;
use Parental\Tests\Models\Driver;
use Parental\Tests\Models\Passenger;
use Parental\Tests\Models\Trip;
use Parental\Tests\Models\User;
use Parental\Tests\Models\Vehicle;
use Parental\Tests\TestCase;

class ChildModelsAreAutomaticallyScopedTest extends TestCase
{
    /** @test */
    function child_is_scoped_based_on_type_column()
    {
        Car::create();
        Vehicle::create();

        $this->assertCount(2, Vehicle::all());
        $this->assertCount(1, Car::all());
    }

    /** @test */
    function child_without_type_column_isnt_scoped()
    {
        Admin::create();
        User::create();

        $this->assertCount(2, User::all());
        $this->assertCount(2, Admin::all());
    }

    /** @test */
    function child_is_scoped_when_accessed_from_belongs_to()
    {
        $car = Car::create();
        $vehicle = Vehicle::create();
        $passenger = Passenger::create(['name' => 'joe', 'vehicle_id' => $vehicle->id]);

        $this->assertNull($passenger->car);
        $this->assertNotNull($passenger->vehicle);

        $passenger->update(['vehicle_id' => $car->id]);

        $this->assertNotNull($passenger->fresh()->car);
        $this->assertNotNull($passenger->fresh()->vehicle);
    }

    /** @test */
    function child_is_scoped_when_accessed_from_has_many()
    {
        $driver = Driver::create(['name' => 'joe']);
        Car::create(['driver_id' => $driver->id]);
        Vehicle::create(['driver_id' => $driver->id]);

        $this->assertCount(2, $driver->vehicles);
        $this->assertCount(1, $driver->cars);
    }

    /** @test */
    function child_is_scoped_when_accessed_from_belongs_to_many()
    {
        $car = Car::create();
        $vehicle = Vehicle::create();
        $trip = Trip::create();
        $trip->vehicles()->attach([$car->id, $vehicle->id]);

        $this->assertCount(1, $trip->cars);
        $this->assertCount(2, $trip->vehicles);
    }
}
