<?php

namespace Parental\Tests\Features;

use Parental\Tests\Enums\ToolNames;
use Parental\Tests\Models\Car;
use Parental\Tests\Models\ChildFromAbstractParent;
use Parental\Tests\Models\ClawHammer;
use Parental\Tests\Models\Mallet;
use Parental\Tests\Models\Plane;
use Parental\Tests\Models\SledgeHammer;
use Parental\Tests\Models\Tool;
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

    /** @test */
    function enums_can_be_used_as_type_alias()
    {
        ClawHammer::create();
        Mallet::create();
        SledgeHammer::create();

        $tools = Tool::all();

        $this->assertInstanceOf(ClawHammer::class, $tools[0]);
        $this->assertEquals(ToolNames::ClawHammer->value, $tools[0]->type);
        $this->assertInstanceOf(Mallet::class, $tools[1]);
        $this->assertEquals(ToolNames::Mallet->value, $tools[1]->type);
        $this->assertInstanceOf(SledgeHammer::class, $tools[2]);
        $this->assertEquals(ToolNames::SledgeHammer->value, $tools[2]->type);
    }
}
