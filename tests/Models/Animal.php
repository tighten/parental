<?php

namespace Parental\Tests\Models;

use Parental\HasChildren;

class Animal extends CountedModel
{
    use HasChildren;

    protected $childTypes = [
        'cat' => Cat::class,
        'dog' => Dog::class,
    ];
}
