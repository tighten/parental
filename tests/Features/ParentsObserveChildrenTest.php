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
}
