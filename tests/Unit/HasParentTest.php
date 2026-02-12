<?php

namespace Parental\Tests\Unit;

use Parental\Tests\TestCase;
use Parental\Tests\Unit\HasParent\ChildModel;
use Parental\Tests\Unit\HasParent\ChildModelWithoutTrait;
use Parental\Tests\Unit\HasParent\ChildModelWithOwnMorphClass;
use Parental\Tests\Unit\HasParent\ParentModel;
use Parental\Tests\Unit\HasParent\RelatedModel;
use ReflectionClass;
use TypeError;

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

    /** @test */
    public function child_model_returns_parent_morph_class_by_default()
    {
        $this->assertEquals(ParentModel::class, (new ChildModel)->getMorphClass());
    }

    /** @test */
    public function child_model_with_parent_morph_class_causes_lazy_proxy_error()
    {
        if (PHP_VERSION_ID < 80400) {
            $this->markTestSkipped('ReflectionClass::newLazyProxy requires PHP 8.4+');
        }

        $child = new ChildModel;
        $morphClass = $child->getMorphClass();

        // The morph class returns the parent class
        $this->assertEquals(ParentModel::class, $morphClass);

        // Creating a lazy proxy using the morph class but returning a child instance causes TypeError
        $reflector = new ReflectionClass($morphClass);
        $proxy = $reflector->newLazyProxy(function ($proxy) {
            return new ChildModel;
        });

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('The real instance class');

        // Accessing a property triggers the lazy initialization and causes the error
        $proxy->getTable();
    }

    /** @test */
    public function child_model_with_own_morph_class_works_with_lazy_proxy()
    {
        if (PHP_VERSION_ID < 80400) {
            $this->markTestSkipped('ReflectionClass::newLazyProxy requires PHP 8.4+');
        }

        $child = new ChildModelWithOwnMorphClass;
        $morphClass = $child->getMorphClass();

        // The morph class returns the child class when returnsChildMorphClass is true
        $this->assertEquals(ChildModelWithOwnMorphClass::class, $morphClass);

        // Creating a lazy proxy using the morph class works correctly
        $reflector = new ReflectionClass($morphClass);
        $proxy = $reflector->newLazyProxy(function ($proxy) {
            return new ChildModelWithOwnMorphClass;
        });

        // Accessing a property works without error
        $this->assertEquals(ChildModelWithOwnMorphClass::class, $proxy->getMorphClass());
    }
}
