<?php

namespace Tightenco\Parental\Tests;

use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Tightenco\Parental\Commands\DiscoverChildren;

class TestCase extends BaseTestCase
{
    public function setUp() : void
    {
        parent::setUp();

        $this->runMigrations();

        $this->withFactories(__DIR__ . '/factories');
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        config()->set('parental.model_directories', __DIR__.'/Models');
        $children = $app->make(DiscoverChildren::class)->findChildren();
        config()->set('parental.discovered_children', array_merge(config('parental.discovered_children', []), $children));
    }

    public function runMigrations()
    {
        Schema::create('drivers', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('passengers', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('vehicle_id');
            $table->timestamps();
        });

        Schema::create('trips', function ($table) {
            $table->increments('id');
            $table->integer('trip_type')->nullable();
            $table->integer('duration')->default(1);
            $table->timestamps();
        });

        Schema::create('trip_vehicle', function ($table) {
            $table->integer('trip_id');
            $table->integer('vehicle_id');
            $table->timestamps();
        });

        Schema::create('vehicles', function ($table) {
            $table->increments('id');
            $table->integer('seats')->nullable();
            $table->integer('driver_id')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function ($table) {
            $table->increments('id');
            $table->timestamps();
        });

        Schema::create('parts', function ($table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_parts', function ($table) {
            $table->increments('id');
            $table->integer('part_id');
            $table->morphs('partable');
            $table->timestamps();
        });
    }
}
