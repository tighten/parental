<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Parental\Tests\Models\ChildFromAbstractParent;

class ChildFromAbstractParentFactory extends Factory
{
    protected $model = ChildFromAbstractParent::class;


    public function definition()
    {
        return [];
    }
}
