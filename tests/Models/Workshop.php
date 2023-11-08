<?php

namespace Parental\Tests\Models;

use Parental\HasParent;

class Workshop extends Event
{
    use HasParent;
    protected $fillable = ['industry', 'skill_level'];
}
