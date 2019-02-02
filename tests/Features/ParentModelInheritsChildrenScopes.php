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
        $this->assertCount(3, (new Trip)->getGlobalScopes());
    }

    /** @test */
    public function child_scopes_apply_on_parent_queries_for_given_child()
    {
        $this->createTenTrips();

        LocalTrip::addGlobalScope(function ($q) {
            $q->whereKey(9);
        });

        $this->assertEquals(8, Trip::query()->count());
        $this->assertCount(3, (new Trip)->getGlobalScopes());

        $localTrip = LocalTrip::query()->first();

        $this->assertEquals(9, $localTrip->getKey());
    }

    private function createTenTrips()
    {
        Trip::query()->create();
        Trip::query()->create();
        Trip::query()->create();
        Trip::query()->create();

        InternationalTrip::query()->create();
        InternationalTrip::query()->create();
        InternationalTrip::query()->create();

        LocalTrip::query()->create();
        LocalTrip::query()->create();
        LocalTrip::query()->create();
    }
}
