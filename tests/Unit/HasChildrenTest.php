<?php

namespace Parental\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;
use Parental\Tests\TestCase;

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

class HasChildrenParentModel extends Model
{
    use HasChildren;

    protected $fillable = ['type', 'test'];
}

class HasChildrenChildModel extends HasChildrenParentModel
{
    public $mutatorWasCalled = false;

    public function setTestAttribute()
    {
        $this->mutatorWasCalled = true;
    }
}

class HasChildrenParentModelWithMethodTypes extends Model
{
    use HasChildren;

    public function getChildTypes()
    {
        return [
            'foo' => Foo::class,
            'bar' => Bar::class,
        ];
    }
}
