<?php

namespace Parental\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Parental\HasChildren;

/**
 * Class Company
 * @package Parental\Tests\Models
 * @property mixed is_affiliate
 */
class Company extends Model
{

    use HasChildren;

    protected $casts = [
        'is_affiliate' => 'bool'
    ];

    protected $attributes = [
        'is_affiliate' => false
    ];

    protected $guarded = [];

    public function getChildClass(array $attributes)
    {
        if ($attributes['is_affiliate'] ?? null == true) {
            return AffiliateCompany::class;
        }
        return self::class;
    }

}
