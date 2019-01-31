<?php

namespace Tightenco\Parental\Tests\Features;

use Tightenco\Parental\Tests\Models\Car;
use Tightenco\Parental\Tests\Models\Part;
use Tightenco\Parental\Tests\Models\Passenger;
use Tightenco\Parental\Tests\Models\Vehicle;
use Tightenco\Parental\Tests\TestCase;

class PolymorphismTest extends TestCase
{
    /** @test */
    public function morph_by_many()
    {
        Vehicle::create()->parts()->create([]);
        Car::create()->parts()->create([]);

        $parts = Part::with('vehicles')->get();

        $vehicle = $parts->first()->vehicles->first();
        $this->assertInstanceOf(Vehicle::class, $vehicle);

        $car = $parts->last()->vehicles->first();
        $this->assertInstanceOf(Car::class, $car);

        /** @var Part $part */
        $part = Part::create();
        $part->vehicles()->attach($vehicle);
        $part->vehicles()->attach($car);

        $part = Part::find($part->getKey());

        $this->assertTrue($vehicle->is($part->vehicles()->first()));
        $this->assertInstanceOf(Vehicle::class, $part->vehicles()->first());

        $this->assertTrue($car->is($part->vehicles()->get()->pop()));
        $this->assertInstanceOf(Car::class, $part->vehicles()->get()->pop());
    }

    /** @test */
    public function can_query_where_has_one()
    {
        $_1 = Part::create(['type' => 'tire']);
        $_2 = Part::create(['type' => 'wing']);
        $_3 = Part::create(['type' => 'engine']);
        $_4 = Part::create(['type' => 'seat']);
        $_5 = Part::create(['type' => 'some metal thing']);
        $_6 = Part::create(['type' => 'i dont know car parts w/e']);

        /** @var Car $car */
        $car = Car::create([]);
        /** @var Vehicle $vehicle */
        $vehicle = Vehicle::create([]);

        $car->parts()->attach($_1);
        $car->parts()->attach($_2);
        $car->parts()->attach($_3);

        $vehicle->parts()->attach($_4);
        $vehicle->parts()->attach($_5);
        $vehicle->parts()->attach($_6);

        $this->assertNull(Car::query()->whereHas('parts', function ($query) {
            $query->where('type', 'seat');
        })->first());

        $checker = Vehicle::query()->whereHas('parts', function ($query) {
            $query->where('type', 'tire');
        })->first();

        $this->assertNotNull($checker);
        $this->assertTrue($car->is($checker));
        $this->assertInstanceOf(Car::class, $checker);
    }

    /** @test */
    public function can_query_where_has_two()
    {
        /** @var Part $part */
        Part::create();
        Part::create();
        $part = Part::create();
        Part::create();
        $otherPart = Part::create();
        Part::create();
        /** @var Car $car */
        $car = Car::create([]);
        /** @var Vehicle $vehicle */
        $vehicle = Vehicle::create([]);
        $part->vehicles()->create([]);
        $part->vehicles()->attach($vehicle);
        $part->vehicles()->attach($car);

        $otherPart->vehicles()->create([]);
        $otherPart->vehicles()->create([]);
        $otherPart->vehicles()->create([]);
        $otherPart->vehicles()->attach($vehicle);

        $car->passengers()->create(['name' => 'Robert']);
        $car->passengers()->create(['name' => 'Joe']);
        $car->passengers()->create(['name' => 'John']);
        $vehicle->passengers()->create(['name' => 'Bob']);
        $vehicle->passengers()->create(['name' => 'Karl']);

        $checker = Part::query()
            ->whereHas('vehicles.passengers', function ($query) {
                $query->where('name', 'Robert');
            })
            ->first();

        $this->assertTrue($part->is($checker));

        $checker = Part::query()
            ->whereHas('vehicles.passengers', function ($query) {
                $query->where('name', 'Robert');
            })
            ->with(['vehicles.passengers' => function ($query) {
                $query->where('name', 'Robert');
            }])
            ->first();

        $this->assertTrue($car->is($checker->vehicles()->first()));
        $this->assertEquals('Robert', $checker->vehicles()->first()->passengers()->first()->name);
    }

    /** @test */
    public function can_query_where_has_three()
    {
        /** @var Car $car */
        $car = Car::create();
        /** @var Vehicle $vehicle */
        $vehicle = Vehicle::create();

        $joe = Passenger::create(['name' => 'joe', 'vehicle_id' => $car->id]);
        Passenger::create(['name' => 'john', 'vehicle_id' => $vehicle->id]);
        Passenger::create(['name' => 'carl', 'vehicle_id' => $car->id]);
        Passenger::create(['name' => 'tony', 'vehicle_id' => $vehicle->id]);
        Passenger::create(['name' => 'robert', 'vehicle_id' => $car->id]);

        $car->parts()->create(['type' => 'tire']);
        $car->parts()->create(['type' => 'some']);
        $car->parts()->create(['type' => 'type']);
        $vehicle->parts()->create(['type' => 'and']);
        $vehicle->parts()->create(['type' => 'some']);
        $vehicle->parts()->create(['type' => 'other']);

        // This already works today, but it's pretty ugly
        $passenger = Passenger::query()->where(function ($query) {
            $query->orWhereHas('car.parts', function ($query) {
                $query->where('type', 'tire');
            })->orWhereHas('vehicle.parts', function ($query) {
                $query->where('type', 'tire');
            });
        })->first();

        $this->assertNotNull($passenger);
        $this->assertTrue($joe->is($passenger));
        $this->assertTrue($car->is($passenger->vehicle));
        $this->assertInstanceOf(Car::class, $passenger->vehicle);

        unset($passenger);

        // This works with the new version
        $passenger = Passenger::query()->whereHas('vehicle.parts', function ($query) {
            $query->where('type', 'tire');
        })->first();

        $this->assertNotNull($passenger);
        $this->assertTrue($joe->is($passenger));
        $this->assertTrue($car->is($passenger->vehicle));
        $this->assertInstanceOf(Car::class, $passenger->vehicle);
    }
}
