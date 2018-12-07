<?php

namespace Tightenco\Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Tightenco\Parental\HasParent;

class InternationalTrip extends Trip
{
    use HasParent;
}
