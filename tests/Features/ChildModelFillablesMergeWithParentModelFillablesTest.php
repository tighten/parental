<?php

namespace Parental\Tests\Features;

use Parental\Tests\Models\Event;
use Parental\Tests\Models\Workshop;
use Parental\Tests\TestCase;

class ChildModelFillablesMergeWithParentModelFillablesTest extends TestCase
{
    /** @test */
    function child_fillables_are_merged_with_parent_fillables()
    {
        $workshop = Workshop::create([
            'name' => 'Scaling Laravel',
            'industry' => 'Technology',
            'skill_level' => 'Advanced',
        ]);

        $event = Event::first();

        $this->assertEquals($event->name, $workshop->name);
    }
}
