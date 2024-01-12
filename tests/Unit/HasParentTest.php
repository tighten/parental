<?php

namespace Parental\Tests\Unit;

use Parental\Tests\TestCase;
use Parental\Tests\Unit\HasParent\ChildModel;
use Parental\Tests\Unit\HasParent\ChildModelWithoutTrait;
use Parental\Tests\Unit\HasParent\ParentModel;
use Parental\Tests\Unit\HasParent\RelatedModel;

class HasParentTest extends TestCase
{
    /** @test */
    public function child_model_has_table_name_of_parent_model()
    {
        $this->assertEquals('parent_models', (new ParentModel)->getTable());
        $this->assertEquals('parent_models', (new ChildModel)->getTable());
        $this->assertEquals('child_model_without_traits', (new ChildModelWithoutTrait)->getTable());
    }

    /** @test */
    public function child_model_has_same_foreign_key_as_parent()
    {
        $this->assertEquals('parent_model_id', (new ParentModel)->getForeignKey());
        $this->assertEquals('parent_model_id', (new ChildModel)->getForeignKey());
        $this->assertEquals('child_model_without_trait_id', (new ChildModelWithoutTrait)->getForeignKey());
    }

    /** @test */
    public function child_model_has_same_pivot_table_name_as_parent()
    {
        $related = new RelatedModel;

        $this->assertEquals('parent_model_related_model', (new ParentModel)->joiningTable($related));
        $this->assertEquals('parent_model_related_model', (new ChildModel)->joiningTable($related));
        $this->assertEquals('child_model_without_trait_related_model', (new ChildModelWithoutTrait)->joiningTable($related));
    }
}
