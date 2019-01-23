<?php

namespace Tightenco\Parental\Tests\Features;

use Tightenco\Parental\Tests\Models\Car;
use Tightenco\Parental\Tests\Models\Part;
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
}
