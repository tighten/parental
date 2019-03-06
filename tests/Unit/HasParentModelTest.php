<?php

namespace Tightenco\Parental\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Tightenco\Parental\HasParent;
use Tightenco\Parental\Tests\TestCase;

class HasParentTest extends TestCase
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
    function child_model_does_not_have_same_pivot_table_name_as_parent()
    {
        $related = new RelatedModel;

        $this->assertEquals('parent_model_related_model', (new ParentModel)->joiningTable($related));
        $this->assertEquals('child_model_related_model', (new ChildModel)->joiningTable($related));
        $this->assertEquals('child_model_without_trait_related_model', (new ChildModelWithoutTrait)->joiningTable($related));
    }

    /** @test */
    public function child_model_uses_own_table_if_belongs_to_many_defined_on_child_only()
    {
        $this->assertEquals('child_model_related_model', (new ChildModel)->c()->getTable());
    }

    /** @test */
    public function models_use_relationship_table_if_defined()
    {
        $this->assertEquals('test_b', (new ChildModel)->b()->getTable());
        $this->assertEquals('test_b', (new ParentModel)->b()->getTable());

        $this->assertEquals('test_d', (new ChildModel)->d()->getTable());
    }

    /** @test */
    public function child_model_uses_parent_table_if_belongs_to_many_defined_on_parent()
    {
        $this->assertEquals('parent_model_related_model', (new ChildModel)->a()->getTable());
        $this->assertEquals('parent_model_related_model', (new ParentModel)->a()->getTable());
    }
}

class ParentModel extends Model {
    public function a()
    {
        return $this->belongsToMany(RelatedModel::class);
    }

    public function b()
    {
        return $this->belongsToMany(RelatedModel::class, 'test_b');
    }
}

class ChildModel extends ParentModel {
    use HasParent;

    public function c()
    {
        return $this->belongsToMany(RelatedModel::class);
    }

    public function d()
    {
        return $this->belongsToMany(RelatedModel::class, 'test_d');
    }
}

class ChildModelWithoutTrait extends ParentModel {
    //
}

class RelatedModel extends Model {
    //
}
