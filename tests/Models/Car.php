<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Parental\HasParent;

class Car extends Vehicle
{
    use HasParent;
    use HasFactory;
}
