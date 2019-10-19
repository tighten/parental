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
        'is_affiliate' => 'bool',
        'is_special' => 'bool',
    ];

    protected static $requiredByParental = [
        'is_affiliate',
        'is_special'
    ];

    protected $guarded = [];

    public function getChildClass(array $attributes)
    {
        if ($attributes['is_affiliate'] ?? null == true) {
            return AffiliateCompany::class;
        }

        if ($attributes['is_special'] ?? null == true) {
            return SpecialCompany::class;
        }

        return self::class;
    }

}
