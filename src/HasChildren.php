<?php

namespace Tightenco\Parental;

use Illuminate\Support\Str;

trait HasChildren
{
    protected $hasChildren = true;

    protected static function registerModelEvent($event, $callback)
    {
        parent::registerModelEvent($event, $callback);

        if (static::class === self::class && property_exists(self::class, 'childTypes')) {
            foreach ((new self)->childTypes as $childClass) {
                $childClass::registerModelEvent($event, $callback);
            }
        }
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
        return $this->childColumn ?: 'type';
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
            if (isset($this->childTypes[$aliasOrClass])) {
                return $this->childTypes[$aliasOrClass];
            }
        }

        return $aliasOrClass;
    }

    public function classToAlias($className)
    {
        if (property_exists($this, 'childTypes')) {
            if (in_array($className, $this->childTypes)) {
                return array_search($className, $this->childTypes);
            }
        }

        return $className;
    }
}
