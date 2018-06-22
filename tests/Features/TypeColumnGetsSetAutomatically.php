<?php

namespace Tightenco\Parental\Tests\Features;

use Tightenco\Parental\Tests\Models\Car;
use Tightenco\Parental\Tests\Models\Driver;
use Tightenco\Parental\Tests\Models\InternationalTrip;
use Tightenco\Parental\Tests\Models\Trip;
use Tightenco\Parental\Tests\TestCase;

class TypeColumnGetsSetAutomatically extends TestCase
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
        $car = factory(Car::class)->create();

        $this->assertNotNull($car->type);
    }

    /** @test */
    function custom_type_column_gets_used()
    {
        $internationalTrip = InternationalTrip::create();

        $this->assertNotNull($internationalTrip->fresh()->trip_type);
    }
}
