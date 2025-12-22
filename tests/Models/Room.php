<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}

