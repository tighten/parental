<?php

namespace Tightenco\Parental\Tests\Features;

use Tightenco\Parental\Tests\Models\InternationalTrip;
use Tightenco\Parental\Tests\Models\LocalTrip;
use Tightenco\Parental\Tests\Models\Trip;
use Tightenco\Parental\Tests\TestCase;

class ParentModelInheritsChildrenScopes extends TestCase
{
    /** @test */
    public function simple_scope_inheritance_check()
    {
        $this->createTenTrips();

        $this->assertEquals(10, Trip::query()->count());
        $this->assertCount(2, (new Trip)->getGlobalScopes());
    }

    /** @test */
    public function global_scopes_can_be_added_on_the_fly()
    {
        $this->createTenTrips();

        LocalTrip::addGlobalScope(function ($query) {
            // this doesn't actually do anything to the query
            $query->whereNotNull((new LocalTrip)->getInheritanceColumn());
        });

        $this->assertEquals(10, Trip::query()->count());
        $this->assertCount(2, (new Trip)->getGlobalScopes());
        $this->assertCount(3, (new LocalTrip)->getGlobalScopes());
    }

    /** @test */
    public function child_scopes_apply_on_parent_queries()
    {
        $this->createTenTrips();

        LocalTrip::addGlobalScope(function ($q) {
            $q->whereKey(9);
        });

        $this->assertEquals(8, Trip::query()->count());
        $this->assertCount(2, (new Trip)->getGlobalScopes());
        $this->assertCount(3, (new LocalTrip)->getGlobalScopes());

        $localTrip = LocalTrip::query()->first();

        $this->assertEquals(9, $localTrip->getKey());
    }

    /** @test */
    public function can_modify_query()
    {
        $this->createTenTrips();

        LocalTrip::addGlobalScope(function ($q) {
            $q->whereKey(9);
        });

        $trips = Trip::query()->where('duration', '>=', 3)->get();

        $this->assertCount(3, $trips);
        $this->assertInstanceOf(Trip::class, $trips->shift());
        $this->assertInstanceOf(Trip::class, $trips->shift());
        $this->assertInstanceOf(InternationalTrip::class, $trips->shift());
    }

    private function createTenTrips()
    {
        Trip::query()->create(['duration' => 1]);
        Trip::query()->create(['duration' => 2]);
        Trip::query()->create(['duration' => 3]);
        Trip::query()->create(['duration' => 4]);

        InternationalTrip::query()->create(['duration' => 1]);
        InternationalTrip::query()->create(['duration' => 2]);
        InternationalTrip::query()->create(['duration' => 3]);

        LocalTrip::query()->create(['duration' => 1]);
        LocalTrip::query()->create(['duration' => 2]);
        LocalTrip::query()->create(['duration' => 3]);
    }
}
