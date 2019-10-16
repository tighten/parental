<?php

namespace Parental\Tests\Features;

use Parental\Tests\Models\Car;
use Parental\Tests\Models\Part;
use Parental\Tests\Models\Passenger;
use Parental\Tests\Models\Vehicle;
use Parental\Tests\TestCase;

class PolymorphismTest extends TestCase
{
    /** @test */
    public function parts_can_access_vehicles_with_morphed_by_many()
    {
        Vehicle::create()->parts()->create();
        Car::create()->parts()->create();

        $parts = Part::all();

        $vehicle = $parts->first()->vehicles->first();
        $car = $parts->last()->vehicles->first();

        $this->assertInstanceOf(Vehicle::class, $vehicle);
        $this->assertInstanceOf(Car::class, $car);

        $part = Part::create();
        $part->vehicles()->attach($vehicle);
        $part->vehicles()->attach($car);

        $part->refresh();

        $this->assertTrue($vehicle->is($part->vehicles()->first()));
        $this->assertTrue($car->is($part->vehicles()->get()->pop()));
        $this->assertInstanceOf(Vehicle::class, $part->vehicles()->first());
        $this->assertInstanceOf(Car::class, $part->vehicles()->get()->pop());
    }

    /** @test */
    public function can_query_where_has_from_child_to_morphed()
    {
        $notCar = Vehicle::create();
        $car = Car::create();

        $notCar->parts()->attach(Part::create(['type' => 'wing']));
        $car->parts()->attach(Part::create(['type' => 'tire']));

        $shouldNotBeCar = Car::query()->whereHas('parts', function ($query) {
            $query->where('type', 'wing');
        })->first();

        $shouldBeCar = Vehicle::query()->whereHas('parts', function ($query) {
            $query->where('type', 'tire');
        })->first();

        $this->assertNull($shouldNotBeCar);
        $this->assertTrue($car->is($shouldBeCar));
        $this->assertInstanceOf(Car::class, $shouldBeCar);
    }

    /** @test */
    public function can_query_deeply_from_morphed_to_parental_models_via_where_has()
    {
        Part::create()->vehicles()->attach($car = Car::create());
        $car->passengers()->create(['name' => 'Robert']);

        $part = Part::whereHas('vehicles.passengers', function ($query) {
            $query->where('name', 'Robert');
        })->first();

        $this->assertTrue($car->is($part->vehicles()->first()));
        $this->assertEquals('Robert', $part->vehicles()->first()->passengers()->value('name'));
    }

    /** @test */
    public function can_query_deeply_from_parental_models_to_morphed_via_where_has()
    {
        $car = Car::create();
        $car->passengers()->create(['name' => 'joe']);
        $car->parts()->create(['type' => 'tire']);

        $passenger = Passenger::query()->whereHas('vehicle.parts', function ($query) {
            $query->where('type', 'tire');
        })->first();

        $this->assertTrue($passenger->is($passenger));
        $this->assertTrue($car->is($passenger->vehicle));
        $this->assertInstanceOf(Car::class, $passenger->vehicle);
    }
}
