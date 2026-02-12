<?php

namespace Parental\Tests\Unit\HasParent;

use Parental\HasParent;

class ChildModelWithOwnMorphClass extends ParentModel
{
    use HasParent;

    protected bool $returnsChildMorphClass = true;
}
