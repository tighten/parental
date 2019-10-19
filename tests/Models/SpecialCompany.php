<?php

namespace Parental\Tests\Models;

use Parental\HasParent;

class SpecialCompany extends Company
{
    use HasParent;

    public function SomethingThatOnlySpecialCompanyCanDo()
    {
        return true;
    }

    /**
     * Override this for custom design
     * @return array array of field_name => field_value
     */
    protected function getParentalAttributes()
    {
        return [
            'is_special' => true
        ];
    }

}
