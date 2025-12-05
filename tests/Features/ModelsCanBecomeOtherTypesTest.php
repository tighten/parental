<?php

namespace Parental\Tests\Features;

use Illuminate\Support\Facades\Event;
use Parental\Tests\Models\Car;
use Parental\Tests\Models\ClawHammer;
use Parental\Tests\Models\Mallet;
use Parental\Tests\Models\SledgeHammer;
use Parental\Tests\Models\Vehicle;
use Parental\Tests\TestCase;

class ModelsCanBecomeOtherTypesTest extends TestCase
{
    /** @test */
    public function child_model_can_become_another_child_type()
    {
        $car = Car::create(['driver_id' => 1]);

        $vehicle = $car->become(Vehicle::class);

        $this->assertEquals($car->id, $vehicle->id);
        $this->assertEquals($car->driver_id, $vehicle->driver_id);
        $this->assertEquals($car->created_at, $vehicle->created_at);
        $this->assertEquals($car->updated_at, $vehicle->updated_at);
    }

    /** @test */
    public function become_marks_the_instance_as_existing()
    {
        $clawHammer = ClawHammer::create();

        $sledgeHammer = $clawHammer->become(SledgeHammer::class);

        $this->assertTrue($sledgeHammer->exists);
    }

    /** @test */
    public function become_preserves_relationships()
    {
        $car = Car::create(['driver_id' => 1]);
        $car->load('driver');

        $vehicle = $car->become(Vehicle::class);

        $this->assertTrue($vehicle->relationLoaded('driver'));
    }

    /** @test */
    public function become_preserves_connection()
    {
        $clawHammer = ClawHammer::create();
        $connectionName = $clawHammer->getConnectionName();

        $sledgeHammer = $clawHammer->become(SledgeHammer::class);

        $this->assertEquals($connectionName, $sledgeHammer->getConnectionName());
    }

    /** @test */
    public function become_can_transform_parent_to_child()
    {
        $vehicle = Vehicle::create(['type' => 'truck']);

        $this->assertInstanceOf(Vehicle::class, $vehicle);
        $this->assertEquals('truck', $vehicle->type);

        $car = $vehicle->become(Car::class);

        $this->assertInstanceOf(Car::class, $car);
        $this->assertEquals('car', $car->type);
        $this->assertEquals($vehicle->id, $car->id);
    }

    /** @test */
    public function become_sets_the_correct_type_alias()
    {
        $clawHammer = ClawHammer::create();

        $mallet = $clawHammer->become(Mallet::class);

        $this->assertEquals('mallet', $mallet->type);
    }

    /** @test */
    public function come_calls_model_events_on_specified_model(): void
    {
        $carEventCalled = false;

        Car::saved(function () use (&$carEventCalled) {
            $carEventCalled = true;
        });

        $vehicle = Vehicle::create(['type' => 'truck']);

        $this->assertFalse($carEventCalled);

        $car = $vehicle->become(Car::class);
        $car->save();

        $this->assertInstanceOf(Car::class, $car);
        $this->assertEquals('car', $car->type);
        $this->assertEquals($vehicle->id, $car->id);

        $this->assertTrue($carEventCalled);
    }

    /** @test */
    public function fires_becoming_model_event(): void
    {
        $becomingEventCalledCount = 0;

        Event::listen('eloquent.becoming: ' . Car::class, function () use (&$becomingEventCalledCount) {
            $becomingEventCalledCount++;
        });

        Car::becoming(function () use (&$becomingEventCalledCount) {
            $becomingEventCalledCount++;
        });

        $vehicle = Vehicle::create(['type' => 'truck']);

        $this->assertEquals(0, $becomingEventCalledCount);

        $car = $vehicle->become(Car::class);
        $car->save();

        $this->assertInstanceOf(Car::class, $car);
        $this->assertEquals('car', $car->type);
        $this->assertEquals($vehicle->id, $car->id);

        $this->assertEquals(2, $becomingEventCalledCount);
    }
}
