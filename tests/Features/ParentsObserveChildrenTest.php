<?php

namespace Tightenco\Parental\Tests\Features;

use Tightenco\Parental\Tests\Models\Car;
use Tightenco\Parental\Tests\Models\Train;
use Tightenco\Parental\Tests\Models\Vehicle;
use Tightenco\Parental\Tests\Observers\CarObserver;
use Tightenco\Parental\Tests\Observers\VehicleObserver;
use Tightenco\Parental\Tests\TestCase;

class ParentsObserveChildrenTest extends TestCase
{
    /** @test */
    public function parent_observer_observes_children()
    {
        Vehicle::observe(VehicleObserver::class);

        $car = Car::create();
        $this->assertEquals(1, $car->driver_id);

        $vehicle = Vehicle::create();
        $this->assertEquals(1, $vehicle->driver_id);

        $train = Train::create();
        $this->assertEquals(1, $train->driver_id);
    }

    /** @test */
    public function child_observer_observes_child()
    {
        Car::observe(CarObserver::class);
        $car = Car::create();
        $this->assertEquals(2, $car->driver_id);
    }

    /** @test */
    public function child_observer_doesnt_observe_other_children()
    {
        Car::observe(CarObserver::class);
        $train = Train::create();
        $this->assertEmpty($train->driver_id);
    }

    /** @test */
    public function child_observer_doesnt_observe_parent()
    {
        Car::observe(CarObserver::class);
        $vehicle = Vehicle::create();
        $this->assertEmpty($vehicle->driver_id);
    }

    /** @test */
    public function register_events_on_children_through_parent()
    {
        Vehicle::created(function ($vehicle) {
            $vehicle->driver_id = 3;
        });

        $car = Car::create();
        $this->assertEquals(3, $car->driver_id);

        $vehicle = Vehicle::create();
        $this->assertEquals(3, $vehicle->driver_id);

        $train = Train::create();
        $this->assertEquals(3, $train->driver_id);
    }

    /** @test */
    public function registering_events_on_child_doesnt_affect_parent()
    {
        Car::created(function ($vehicle) {
            $vehicle->driver_id = 3;
        });

        $car = Car::create();
        $this->assertEquals(3, $car->driver_id);

        $vehicle = Vehicle::create();
        $this->assertNull($vehicle->driver_id);

        $train = Train::create();
        $this->assertNull($train->driver_id);
    }

    /**
     * This test works when being run alone, but somehow doesn't when the entire file is being run. The following test
     * includes all the logic of the previous tests, without the assertions, and the assertions work as intended.
     *
     * @test
     */
    public function registering_events_in_parent_boot_only_triggers_once()
    {
        $car = Car::query()->create();
        $this->assertEquals(1, $car->boot_count);
        $this->assertEquals(1, $car->boot_count_two);

        $vehicle = Vehicle::query()->create();
        $this->assertEquals(1, $vehicle->boot_count);
        $this->assertEquals(1, $vehicle->boot_count_two);

        $train = Train::query()->create();
        $this->assertEquals(1, $train->boot_count);
        $this->assertEquals(1, $train->boot_count_two);
    }

    /**
     * This test just includes all the logic from the previous tests, and runs correctly. The other test assertions have
     * been removed.
     *
     * @test
     */
    public function registering_events_in_parent_boot_only_triggers_once_bis()
    {
        Vehicle::observe(VehicleObserver::class);
        $car = Car::create();
        $vehicle = Vehicle::create();
        $train = Train::create();

        Car::observe(CarObserver::class);
        $car = Car::create();

        Car::observe(CarObserver::class);
        $train = Train::create();

        Car::observe(CarObserver::class);
        $vehicle = Vehicle::create();

        Vehicle::created(function ($vehicle) {
            $vehicle->driver_id = 3;
        });

        $car = Car::create();
        $vehicle = Vehicle::create();
        $train = Train::create();

        Car::created(function ($vehicle) {
            $vehicle->driver_id = 3;
        });

        $car = Car::create();
        $vehicle = Vehicle::create();
        $train = Train::create();

        $car = Car::query()->create();
        $this->assertEquals(1, $car->boot_count);
        $this->assertEquals(1, $car->boot_count_two);

        $vehicle = Vehicle::query()->create();
        $this->assertEquals(1, $vehicle->boot_count);
        $this->assertEquals(1, $vehicle->boot_count_two);

        $train = Train::query()->create();
        $this->assertEquals(1, $train->boot_count);
        $this->assertEquals(1, $train->boot_count_two);
    }
}
