<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;

abstract class AbstractParent extends Model
{
    use HasChildren;

    protected $fillable = [
        'type'
    ];

    protected $childTypes = [
        'ChildFromAbstractParent' => ChildFromAbstractParent::class,
    ];

}
