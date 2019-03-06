<?php

namespace Tightenco\Parental\Tests\Features;

use Tightenco\Parental\Tests\Models\Car;
use Tightenco\Parental\Tests\Models\Trip;
use Tightenco\Parental\Tests\Models\Vehicle;
use Tightenco\Parental\Tests\TestCase;

class ParentCanHaveOwnAlias extends TestCase
{
    /** @test */
    public function parent_alias_is_null_if_not_defined()
    {
        $vehicle = Vehicle::create();
        $this->assertNull($vehicle->type);
    }

    /** @test */
    public function parent_alias_is_set_on_creating_if_defined()
    {
        $trip = Trip::create();
        $this->assertEquals('trip', $trip->type);
    }

    /** @test */
    public function alias_doesnt_get_overridden_if_already_defined()
    {
        $car = Vehicle::create(['type' => 'car']);
        $this->assertInstanceOf(Car::class, $car);
        $this->assertEquals('car', $car->type);

        $carTwo = new Vehicle;
        $carTwo->type = 'car';
        $carTwo->save();
        $this->assertInstanceOf(Vehicle::class, $car);
        $this->assertEquals('car', $car->type);
    }
}
