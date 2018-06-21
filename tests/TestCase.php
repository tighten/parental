<?php

namespace Tightenco\Parental\Tests;

use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function setUp()
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
    }
}
