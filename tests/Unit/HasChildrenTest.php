<?php

namespace Parental\Tests\Unit;

use Parental\Tests\TestCase;
use Parental\Tests\Unit\HasChildren\Bar;
use Parental\Tests\Unit\HasChildren\Foo;
use Parental\Tests\Unit\HasChildren\HasChildrenChildModel;
use Parental\Tests\Unit\HasChildren\HasChildrenParentModel;
use Parental\Tests\Unit\HasChildren\HasChildrenParentModelWithMethodTypes;

class HasChildrenTest extends TestCase
{
    /** @test */
    public function child_model_mutators_are_not_instigated()
    {
        $model = (new HasChildrenParentModel)->newFromBuilder([
            'type' => HasChildrenChildModel::class,
            'test' => 'value',
        ]);

        $this->assertEquals($model->mutatorWasCalled, false);
    }

    /** @test */
    public function child_model_types_can_be_set_via_method()
    {
        $types = (new HasChildrenParentModelWithMethodTypes)->getChildTypes();

        $this->assertEquals([
            'foo' => Foo::class,
            'bar' => Bar::class,
        ], $types);
    }
}
