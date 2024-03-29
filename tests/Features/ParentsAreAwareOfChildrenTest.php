<?php

namespace Parental\Tests\Features;

use Parental\Tests\Models\Car;
use Parental\Tests\Models\Driver;
use Parental\Tests\Models\Passenger;
use Parental\Tests\Models\Plane;
use Parental\Tests\Models\Vehicle;
use Parental\Tests\TestCase;

class ParentsAreAwareOfChildrenTest extends TestCase
{
    /** @test */
    public function vehicle_all_method_returns_child_models()
    {
        Car::create(['type' => Car::class]);
        Plane::create(['type' => Plane::class]);

        $vehicles = Vehicle::all();

        $this->assertInstanceOf(Car::class, $vehicles[0]);
        $this->assertInstanceOf(Plane::class, $vehicles[1]);
    }

    /** @test */
    public function type_column_values_can_accept_type_aliases()
    {
        // Looks for "childTypes" property on Vehicle class.
        Car::create(['type' => 'car']);
        Plane::create(['type' => Plane::class]);

        $vehicles = Vehicle::all();

        $this->assertInstanceOf(Car::class, $vehicles[0]);
        $this->assertInstanceOf(Plane::class, $vehicles[1]);
    }

    /** @test */
    public function vehicle_query_builder_get_method_returns_child_models()
    {
        Car::create(['type' => Car::class]);
        Plane::create(['type' => Plane::class]);
        Vehicle::create();

        $vehicles = Vehicle::query()->get();

        $this->assertInstanceOf(Car::class, $vehicles[0]);
        $this->assertInstanceOf(Plane::class, $vehicles[1]);
        $this->assertInstanceOf(Vehicle::class, $vehicles[2]);
    }

    /** @test */
    public function has_many_returns_child_models()
    {
        $driver = Driver::create(['name' => 'Joe']);
        Car::create([
            'type' => Car::class,
            'driver_id' => $driver->id,
        ]);

        $vehicleA = $driver->vehicles()->first();
        $vehicleB = $driver->vehicles->first();

        $this->assertInstanceOf(Car::class, $vehicleA);
        $this->assertInstanceOf(Car::class, $vehicleB);
    }

    /** @test */
    public function belongs_to_returns_child_models()
    {
        $car = Car::create(['type' => Car::class]);
        $passenger = Passenger::create([
            'name' => 'Joe',
            'vehicle_id' => $car->id,
        ]);

        $vehicle = $passenger->vehicle;

        $this->assertInstanceOf(Car::class, $vehicle);
    }

    /** @test */
    public function many_to_many_returns_child_models()
    {
        $car = Car::create(['type' => Car::class]);
        $trip = $car->trips()->create([]);

        $vehicleA = $trip->vehicles()->first();
        $vehicleB = $trip->vehicles->first();

        $this->assertInstanceOf(Car::class, $vehicleA);
        $this->assertInstanceOf(Car::class, $vehicleB);
    }
}
