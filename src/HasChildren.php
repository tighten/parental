<?php

namespace Tightenco\Parental;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasChildren
{
    private static $parentBootMethods;
    private static $discoveredChildren;

    protected $hasChildren = true;

    public static function bootHasChildren()
    {
        if (static::class === self::class) {
            foreach ((new self)->getChildTypes() as $childClass) {
                // Just booting all the child classes to make sure their base global scopes get registered
                new $childClass;
            }

            static::addGlobalScope(new ParentScope);
        }
    }

    protected static function registerModelEvent($event, $callback)
    {
        parent::registerModelEvent($event, $callback);

        // We don't want to register the callbacks that happen in the boot method of the parent, as they'll be called
        // from the child's boot method as well.
        if (static::class === self::class && ! self::parentIsBooting()) {
            foreach ((new self)->getChildTypes() as $childClass) {
                $childClass::registerModelEvent($event, $callback);
            }
        }
    }

    private static function parentIsBooting()
    {
        if (! isset(self::$parentBootMethods)) {
            self::$parentBootMethods[] = 'boot';

            foreach (class_uses_recursive(self::class) as $trait) {
                self::$parentBootMethods[] = 'boot'.class_basename($trait);
            }

            self::$parentBootMethods = array_flip(self::$parentBootMethods);
        }

        // Limit to 32 as I don't think we need to go any deeper (even 10 is probably enough)
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 32) as $trace) {
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

        if ($foreignKey === null && $instance->hasParent) {
            $foreignKey = Str::snake($instance->getClassNameForRelationships()).'_'.$instance->getKeyName();
        }

        if ($relation === null) {
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

        if ($table === null && $instance->hasParent) {
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

        return new $className((array) $attributes);
    }

    public function classFromAlias($aliasOrClass)
    {
        $types = $this->getChildTypes();
        if (isset($types[$aliasOrClass])) {
            return $types[$aliasOrClass];
        }

        return $aliasOrClass;
    }

    public function classToAlias($className)
    {
        if (in_array($className, $this->getChildTypes())) {
            return array_search($className, $this->getChildTypes());
        }

        return $className;
    }

    public function getChildTypes()
    {
        return array_flip(array_merge(
            $this->getDiscoveredChildren(),
            array_flip(property_exists($this, 'childTypes') ? $this->childTypes : [])
        ));
    }

    private function getDiscoveredChildren() : array
    {
        if (! isset(self::$discoveredChildren)) {
            self::$discoveredChildren = Arr::get(require __DIR__.'/../discovered-children.php', self::class, []);
        }

        return self::$discoveredChildren;
    }
}
