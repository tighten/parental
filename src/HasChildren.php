<?php

namespace Tightenco\Parental;

use Illuminate\Support\Str;

trait HasChildren
{
    protected static $parentBootMethods;

    protected $hasChildren = true;

    protected static function registerModelEvent($event, $callback)
    {
        parent::registerModelEvent($event, $callback);

        $childTypes = (new self)->getChildTypes();

        if (static::class === self::class && count($childTypes)) {
            // We don't want to register the callbacks that happen in the boot method of the parent, as they'll be called
            // from the child's boot method as well.
            if (! self::parentIsBooting()) {
                foreach ($childTypes as $childClass) {
                    if ($childClass !== self::class) {
                        $childClass::registerModelEvent($event, $callback);
                    }
                }
            }
        }
    }

    protected static function parentIsBooting()
    {
        if (! isset(self::$parentBootMethods)) {
            self::$parentBootMethods[] = 'boot';

            foreach (class_uses_recursive(self::class) as $trait) {
                self::$parentBootMethods[] = 'boot'.class_basename($trait);
            }

            self::$parentBootMethods = array_flip(self::$parentBootMethods);
        }

        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $trace) {
            $class = isset($trace['class']) ? $trace['class'] : null;
            $function = isset($trace['function']) ? $trace['function'] : '';

            if ($class === self::class && isset(self::$parentBootMethods[$function])) {
                return true;
            }
        }

        return false;
    }

    public function newInstance($attributes = [], $exists = false)
    {
        $model = isset($attributes[$this->getInheritanceColumn()])
            ? $this->getChildModel($attributes)
            : new static(((array) $attributes));

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        return $model;
    }

    public function newFromBuilder($attributes = [], $connection = null)
    {
        $model = $this->newInstance((array) $attributes, true);

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection($connection ?: $this->getConnectionName());

        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        $instance = $this->newRelatedInstance($related);

        if (is_null($foreignKey) && $instance->hasParent) {
            $foreignKey = Str::snake($instance->getClassNameForRelationships()).'_'.$instance->getKeyName();
        }

        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        return parent::belongsTo($related, $foreignKey, $ownerKey, $relation);
    }

    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        return parent::hasMany($related, $foreignKey, $localKey);
    }

    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null)
    {
        $instance = $this->newRelatedInstance($related);

        if (is_null($table) && $instance->hasParent) {
            $table = $this->joiningTable($instance->getClassNameForRelationships());
        }

        return parent::belongsToMany($related, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relation);
    }

    public function getClassNameForRelationships()
    {
        return class_basename($this);
    }

    public function getInheritanceColumn()
    {
        return property_exists($this, 'childColumn') ? $this->childColumn : 'type';
    }

    protected function getChildModel(array $attributes)
    {
        $className = $this->classFromAlias(
            $attributes[$this->getInheritanceColumn()]
        );

        return new $className((array)$attributes);
    }

    public function classFromAlias($aliasOrClass)
    {
        if (property_exists($this, 'childTypes')) {
            if (isset($this->getChildTypes()[$aliasOrClass])) {
                return $this->getChildTypes()[$aliasOrClass];
            }
        }

        return $aliasOrClass;
    }

    public function classToAlias($className)
    {
        if (property_exists($this, 'childTypes')) {
            if (in_array($className, $this->getChildTypes())) {
                return array_search($className, $this->getChildTypes());
            }
        }

        return $className;
    }

    public function getChildTypes()
    {
        return property_exists($this, 'childTypes') ? $this->childTypes : [];
    }
}
