<?php

namespace Parental\Tests\Models;

use Parental\HasParent;

class Conference extends Event
{
    use HasParent;

    protected $fillable = ['industry'];
}
