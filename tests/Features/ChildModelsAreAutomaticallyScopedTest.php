<?php

namespace Parental\Tests\Features;

use Parental\Tests\Models\Admin;
use Parental\Tests\Models\Car;
use Parental\Tests\Models\ChildNode;
use Parental\Tests\Models\Driver;
use Parental\Tests\Models\NodeEdge;
use Parental\Tests\Models\ParentNode;
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

    /** @test */
    function child_is_scoped_when_accessed_from_has_one_through()
    {
        // Create root with children
        $rootA = ParentNode::create(['name' => 'Root A']);
        $childA = ChildNode::create(['name' => 'Child 1']);
        $childB = ChildNode::create(['name' => 'Child 2']);
        $childC = ChildNode::create(['name' => 'Child 3']);
        NodeEdge::create(['parent_node_id' => $rootA->id, 'child_node_id' => $childA->id]);
        NodeEdge::create(['parent_node_id' => $rootA->id, 'child_node_id' => $childB->id]);

        $this->assertInstanceOf(ParentNode::class, $childA->parent);
        $this->assertInstanceOf(ParentNode::class, $childB->parent);
        $this->assertNull($childC->parent);

        $this->assertCount(2, ChildNode::whereHas('parent')->get());
        $this->assertTrue(ChildNode::whereId($childA->id)->whereHas('parent')->exists());
        $this->assertTrue(ChildNode::whereId($childB->id)->whereHas('parent')->exists());
        $this->assertFalse(ChildNode::whereId($childC->id)->whereHas('parent')->exists());
    }

    /** @test */
    function child_is_scoped_when_accessed_from_has_many_through()
    {
        // Create root with children
        $rootA = ParentNode::create(['name' => 'Root A']);
        $childA = ChildNode::create(['name' => 'Child 1']);
        $childB = ChildNode::create(['name' => 'Child 2']);
        $childC = ChildNode::create(['name' => 'Child 3']);
        NodeEdge::create(['parent_node_id' => $rootA->id, 'child_node_id' => $childA->id]);
        NodeEdge::create(['parent_node_id' => $rootA->id, 'child_node_id' => $childB->id]);
        NodeEdge::create(['parent_node_id' => $rootA->id, 'child_node_id' => $childC->id]);

        // Create different root with different children
        $rootB = ParentNode::create(['name' => 'Root B']);
        $childX = ChildNode::create(['name' => 'Child X']);
        NodeEdge::create(['parent_node_id' => $rootB->id, 'child_node_id' => $childX->id]);

        // Create different root children any children
        $rootC = ParentNode::create(['name' => 'Root C']);

        $this->assertCount(3, $rootA->children);
        $this->assertContainsOnlyInstancesOf(ChildNode::class, $rootA->children);

        $this->assertCount(1, $rootB->children);
        $this->assertContainsOnlyInstancesOf(ChildNode::class, $rootB->children);

        $this->assertCount(2, ParentNode::whereHas('children')->get());
        $this->assertTrue(ParentNode::whereId($rootA->id)->whereHas('children')->exists());
        $this->assertTrue(ParentNode::whereId($rootB->id)->whereHas('children')->exists());
        $this->assertFalse(ParentNode::whereId($rootC->id)->whereHas('children')->exists());
    }
}
