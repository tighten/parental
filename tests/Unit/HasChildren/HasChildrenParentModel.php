<?php

namespace Parental\Tests\Unit\HasChildren;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;

class HasChildrenParentModel extends Model
{
    use HasChildren;

    protected $fillable = ['type', 'test'];
}
