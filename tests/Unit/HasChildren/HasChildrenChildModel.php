<?php

namespace Parental\Tests\Unit\HasChildren;

class HasChildrenChildModel extends HasChildrenParentModel
{
    public $mutatorWasCalled = false;

    public function setTestAttribute()
    {
        $this->mutatorWasCalled = true;
    }
}
