<?php

namespace Tightenco\Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Tightenco\Parental\HasChildren;

class GuardedParent extends Model
{
    use HasChildren;

    protected $fillable = [];

    protected $childTypes = ['child' => GuardedChild::class];
}
