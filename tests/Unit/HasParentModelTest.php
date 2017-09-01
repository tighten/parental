<?php

namespace Tightenco\Parental\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Tightenco\Parental\HasParentModel;
use Tightenco\Parental\Tests\TestCase;

class HasParentModelTest extends TestCase
{
    /** @test */
    function child_model_has_table_name_of_parent_model()
    {
        $this->assertEquals('parent_models', (new ParentModel)->getTable());
        $this->assertEquals('parent_models', (new ChildModel)->getTable());
        $this->assertEquals('child_model_without_traits', (new ChildModelWithoutTrait)->getTable());
    }

    /** @test */
    function child_model_has_same_foreign_key_as_parent()
    {
        $this->assertEquals('parent_model_id', (new ParentModel)->getForeignKey());
        $this->assertEquals('parent_model_id', (new ChildModel)->getForeignKey());
        $this->assertEquals('child_model_without_trait_id', (new ChildModelWithoutTrait)->getForeignKey());
    }

    /** @test */
    function child_model_has_same_pivot_table_name_as_parent()
    {
        $related = new RelatedModel;

        $this->assertEquals('parent_model_related_model', (new ParentModel)->joiningTable($related));
        $this->assertEquals('parent_model_related_model', (new ChildModel)->joiningTable($related));
        $this->assertEquals('child_model_without_trait_related_model', (new ChildModelWithoutTrait)->joiningTable($related));
    }
}

class ParentModel extends Model {
   //
}

class ChildModel extends ParentModel {
    use HasParentModel;
}

class ChildModelWithoutTrait extends ParentModel {
    //
}

class RelatedModel extends Model {
    //
}
