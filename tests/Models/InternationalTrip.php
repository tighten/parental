<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\HasParent;

class InternationalTrip extends Trip
{
    use HasParent;
}
