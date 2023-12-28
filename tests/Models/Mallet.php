<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Parental\HasParent;

class Mallet extends Tool
{
    use HasParent;
    use HasFactory;
}
