<?php

namespace Parental\Tests\Unit\HasChildren;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;

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
