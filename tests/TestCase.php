<?php

namespace Parental\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();

        Factory::guessFactoryNamesUsing(static function (string $modelName) {
            return sprintf('Database\\Factories\\%sFactory', class_basename($modelName));
        });
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

        Schema::create('nodes', function ($table) {
            $table->increments('id');
            $table->string('type');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('node_edges', function ($table) {
            $table->increments('id');
            $table->string('parent_node_id');
            $table->string('child_node_id');
            $table->timestamps();
        });

        Schema::create('abstract_parents', function ($table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('tools', function ($table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('events', function ($table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->string('name');
            $table->string('industry')->nullable();
            $table->string('skill_level')->nullable();
            $table->timestamps();
        });

        Schema::create('animals', function ($table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
