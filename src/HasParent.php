<?php

namespace Parental;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

trait HasParent
{
    /**
     * @var bool
     */
    public $hasParent = true;

    /**
     * @throws ReflectionException
     */
    public static function bootHasParent(): void
    {
        // This adds support for using Parental with standalone Eloquent, outside a normal Laravel app.
        if (static::getEventDispatcher() === null) {
            static::setEventDispatcher(new Dispatcher);
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
                $query->where($query->getModel()->getTable() . '.' . $instance->getInheritanceColumn(), $instance->classToAlias(get_class($instance)));
            }
        });

        
        if (method_exists(static::class, 'resolveObserveAttributes')) {
            $parentObservedBy = (new static)->getParentClass()::resolveObserveAttributes();
            static::observe($parentObservedBy);
        }
    }

    public function parentHasHasChildrenTrait(): bool
    {
        return $this->hasChildren ?? false;
    }

    /**
     * @throws ReflectionException
     */
    public function getTable(): string
    {
        if (! isset($this->table)) {
            return str_replace('\\', '', Str::snake(Str::plural(class_basename($this->getParentClass()))));
        }

        return $this->table;
    }

    /**
     * @throws ReflectionException
     */
    public function getForeignKey(): string
    {
        return Str::snake(class_basename($this->getParentClass())) . '_' . $this->primaryKey;
    }

    /**
     * @param  string  $related
     * @param  null|Model  $instance
     *
     * @throws ReflectionException
     */
    public function joiningTable($related, $instance = null): string
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
     * @throws ReflectionException
     */
    public function getClassNameForRelationships(): string
    {
        return class_basename($this->getParentClass());
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * @throws ReflectionException
     */
    public function getMorphClass(): string
    {
        $parentClass = $this->getParentClass();

        return (new $parentClass)->getMorphClass();
    }

    /**
     * Get the class name for poly-type collections
     *
     * @throws ReflectionException
     */
    public function getQueueableClassName(): string
    {
        return $this->getParentClass();
    }

    /**
     * Merge the fillable attributes for the model with those of its Parent Class
     *
     * @return array<string>
     */
    public function getFillable()
    {
        $parentClass = $this->getParentClass();

        if ((new ReflectionClass($parentClass))->isAbstract()) {

            return $this->fillable;
        }
        $parentFillable = (new $parentClass)->getFillable();

        return array_unique(array_merge($parentFillable, $this->fillable));
    }

    /**
     * Get the class name for Parent Class.
     *
     * @throws ReflectionException
     */
    protected function getParentClass(): string
    {
        static $parentClassName;

        return $parentClassName ?: $parentClassName = (new ReflectionClass($this))->getParentClass()->getName();
    }
}
