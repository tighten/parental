<?php

namespace Parental\Tests\Features;

use Parental\Tests\Models\Car;
use Parental\Tests\Models\ChildFromAbstractParent;
use Parental\Tests\Models\Plane;
use Parental\Tests\Models\Vehicle;
use Parental\Tests\TestCase;

class TypeColumnCanBeAliasedTest extends TestCase
{
    /** @test */
    function type_column_values_can_accept_type_aliases()
    {
        Car::create(['type' => 'car']);
        Plane::create(['type' => Plane::class]);

        $vehicles = Vehicle::all();

        $this->assertInstanceOf(Car::class, $vehicles[0]);
        $this->assertInstanceOf(Plane::class, $vehicles[1]);
    }

    /** @test */
    function type_aliases_are_set_on_creation()
    {
        $car = Car::create();

        $this->assertEquals('car', $car->fresh()->type);
    }

    /** @test */
    function type_column_values_can_accept_type_aliases_from_abstract_parent()
    {
        ChildFromAbstractParent::create(['type' => 'ChildFromAbstractParent']);

        $child = ChildFromAbstractParent::all();

        $this->assertInstanceOf(ChildFromAbstractParent::class, $child[0]);
    }
}
