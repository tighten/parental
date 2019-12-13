<?php

namespace Parental\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;
use Parental\Tests\TestCase;

class HasChildrenTest extends TestCase
{
    /** @test */
    function child_model_mutators_are_not_instigated()
    {
        $model = (new HasChildrenParentModel)->newFromBuilder([
            'type' => HasChildrenChildModel::class,
            'test' => 'value'
        ]);

        $this->assertEquals($model->mutatorWasCalled, false);
    }
}

class HasChildrenParentModel extends Model {
    use HasChildren;

    protected $fillable = ['type', 'test'];
}

class HasChildrenChildModel extends HasChildrenParentModel {
    public $mutatorWasCalled = false;

    public function setTestAttribute()
    {
        $this->mutatorWasCalled = true;
    }
}
