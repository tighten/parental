<?php

namespace Parental\Tests\Features;

use Parental\Tests\Models\AffiliateCompany;
use Parental\Tests\Models\Company;
use Parental\Tests\Models\NonAffiliateCompany;
use Parental\Tests\TestCase;

class ParentalWithoutExplicitClassColumnTest extends TestCase
{

    /**
     * @test
     */
    function test()
    {
        $company = Company::create()->fresh();
        Company::create([
            'is_affiliate' => 1
        ])->fresh();

        $this->assertNotNull($company);

        $nonAffiliateCompany = Company::find($company->id);
        $this->assertNotNull($nonAffiliateCompany);
        $this->assertInstanceOf(Company::class, $nonAffiliateCompany);

        // update it such that it is affiliate
        $company->is_affiliate = true;
        $company->save();

        $affiliateCompany = Company::find($company->id);
        $this->assertNotNull($affiliateCompany);
        $this->assertInstanceOf(AffiliateCompany::class,  $affiliateCompany);

    }

}
