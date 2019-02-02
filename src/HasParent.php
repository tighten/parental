<?php

namespace Tightenco\Parental;

use Illuminate\Support\Str;
use ReflectionClass;

trait HasParent
{
    public $hasParent = true;

    public static function bootHasParent()
    {
        static::creating(function ($model) {
            if ($model->parentHasHasChildrenTrait()) {
                $model->forceFill(
                    [$model->getInheritanceColumn() => $model->classToAlias(get_class($model))]
                );
            }
        });

        static::addGlobalScope('parental', function ($query) {
            $instance = new static;

            if ($instance->parentHasHasChildrenTrait()) {
                $query->where($instance->getInheritanceColumn(), $instance->classToAlias(get_class($instance)));
            }
        });
    }

    public static function addGlobalScope($scope, \Closure $implementation = null)
    {
        $implementation = parent::addGlobalScope($scope, $implementation);
        $key = array_search($implementation, static::$globalScopes[static::class]);
        $child = new static;

        if ($scope !== 'parental' && $child->parentHasHasChildrenTrait()) {
            $parent = $child->getParentClass();
            $parent::addGlobalScope(static::class.':'.$key, ParentScope::passToParent($parent, $child, $implementation));
        }

        return $implementation;
    }

    public function parentHasHasChildrenTrait()
    {
        return $this->hasChildren ?? false;
    }

    public function getTable()
    {
        if (! isset($this->table)) {
            return str_replace('\\', '', Str::snake(Str::plural(class_basename($this->getParentClass()))));
        }

        return $this->table;
    }

    public function getForeignKey()
    {
        return Str::snake(class_basename($this->getParentClass())).'_'.$this->primaryKey;
    }

    public function joiningTable($related, $instance = null)
    {
        $relatedClassName = method_exists((new $related), 'getClassNameForRelationships')
            ? (new $related)->getClassNameForRelationships()
            : class_basename($related);

        $models = [
            Str::snake($relatedClassName),
            Str::snake($this->getClassNameForRelationships()),
        ];

        sort($models);

        return strtolower(implode('_', $models));
    }

    public function getClassNameForRelationships()
    {
        return class_basename($this->getParentClass());
    }

    public function getMorphClass()
    {
        if ($this->parentHasHasChildrenTrait()) {
            $parentClass = $this->getParentClass();
            return (new $parentClass)->getMorphClass();
        }

        return parent::getMorphClass();
    }

    protected function getParentClass()
    {
        static $parentClassName;

        return $parentClassName ?: $parentClassName = (new ReflectionClass($this))->getParentClass()->getName();
    }
}
