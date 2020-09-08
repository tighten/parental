<?php

namespace Parental\Tests\Features;

use Parental\Tests\Models\Car;
use Parental\Tests\Models\Driver;
use Parental\Tests\Models\InternationalTrip;
use Parental\Tests\Models\Trip;
use Parental\Tests\TestCase;

class TypeColumnGetsSetAutomaticallyTest extends TestCase
{
    /** @test */
    function type_column_gets_set_on_creation()
    {
        $car = Car::create();

        $this->assertNotNull($car->fresh()->type);
    }

    /** @test */
    function type_column_gets_set_on_creation_from_many_to_many_relationship()
    {
        $trip = Trip::create();
        $car = $trip->cars()->create([]);

        $this->assertNotNull($car->fresh()->type);
    }

    /** @test */
    function type_column_gets_set_on_creation_from_has_many_relationship()
    {
        $driver = Driver::create(['name' => 'Joe']);
        $car = $driver->cars()->create([]);

        $this->assertNotNull($car->fresh()->type);
    }

    /** @test */
    function type_column_gets_set_on_saving_from_has_many_relationship()
    {
        $driver = Driver::create(['name' => 'Joe']);
        $car = $driver->cars()->save(new Car);

        $this->assertNotNull($car->fresh()->type);
    }

    /** @test */
    function type_column_gets_set_on_creation_from_a_model_factory()
    {
        $car = Car::factory()->create();

        $this->assertNotNull($car->type);
    }

    /** @test */
    function custom_type_column_gets_used()
    {
        $internationalTrip = InternationalTrip::create();

        $this->assertNotNull($internationalTrip->fresh()->trip_type);
    }
}
