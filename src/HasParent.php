<?php

namespace Parental;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Events\Dispatcher;

trait HasParent
{
    public $hasParent = true;

    public static function bootHasParent()
    {
        // This adds support for using Parental with standalone Eloquent, outside a normal Laravel app.
        if (static::getEventDispatcher() === null) {
            static::setEventDispatcher(new Dispatcher());
        }

        static::creating(function ($model) {
            if ($model->parentHasHasChildrenTrait()) {
                $model->forceFill(
                    [$model->getInheritanceColumn() => $model->classToAlias(get_class($model))]
                );
            }
        });

        static::addGlobalScope(function ($query) {
            $instance = new static;

            if ($instance->parentHasHasChildrenTrait()) {
                $query->where($instance->getTable().'.'.$instance->getInheritanceColumn(), $instance->classToAlias(get_class($instance)));
            }
        });
    }

    /**
     * @return bool
     */
    public function parentHasHasChildrenTrait()
    {
        return $this->hasChildren ?? false;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getTable()
    {
        if (! isset($this->table)) {
            return str_replace('\\', '', Str::snake(Str::plural(class_basename($this->getParentClass()))));
        }

        return $this->table;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getForeignKey()
    {
        return Str::snake(class_basename($this->getParentClass())).'_'.$this->primaryKey;
    }

    /**
     * @param $related
     * @param null $instance
     * @return string
     * @throws \ReflectionException
     */
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

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getClassNameForRelationships()
    {
        return class_basename($this->getParentClass());
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getMorphClass()
    {
        $parentClass = $this->getParentClass();

        return (new $parentClass)->getMorphClass();
    }

    /**
     * Get the class name for Parent Class.
     *
     * @return string
     * @throws \ReflectionException
     */
    protected function getParentClass()
    {
        static $parentClassName;

        return $parentClassName ?: $parentClassName = (new ReflectionClass($this))->getParentClass()->getName();
    }
}
