<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Parental\HasParent;

class SledgeHammer extends Tool
{
    use HasFactory;
    use HasParent;
}
