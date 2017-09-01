<?php

namespace Tightenco\Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Tightenco\Parental\HasParentModel;

class InternationalTrip extends Trip
{
    use HasParentModel;
}
