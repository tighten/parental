<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;

class Node extends Model
{
    use HasChildren;

    protected $guarded = [];
}
