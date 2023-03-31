<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Parental\HasParent;

class ChildFromAbstractParent extends AbstractParent
{
    use HasParent;
    use HasFactory;
}
