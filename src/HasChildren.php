<?php

namespace Parental;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

trait HasChildren
{
    protected static $parentBootMethods;

    protected $hasChildren = true;

    /**
     *
     */
    public static function bootHasChildren()
    {

        static::addGlobalScope(function (Builder $builder) {

            if (property_exists(get_called_class(), 'requiredByParental')) {

                if (!is_null($builder->getQuery()->columns)) {

                    //columns specified
                    $builder->addSelect(static::$requiredByParental);

                }


            }

        });

    }

    /**
     * @param $event
     * @param $callback
     */
    protected static function registerModelEvent($event, $callback)
    {
        parent::registerModelEvent($event, $callback);

        if (static::class === self::class && property_exists(self::class, 'childTypes')) {
            // We don't want to register the callbacks that happen in the boot method of the parent, as they'll be called
            // from the child's boot method as well.
            if (!self::parentIsBooting()) {
                foreach ((new self)->childTypes as $childClass) {
                    if ($childClass !== self::class) {
                        $childClass::registerModelEvent($event, $callback);
                    }
                }
            }
        }
    }

    /**
     * @return bool
     */
    protected static function parentIsBooting()
    {
        if (!isset(self::$parentBootMethods)) {
            self::$parentBootMethods[] = 'boot';

            foreach (class_uses_recursive(self::class) as $trait) {
                self::$parentBootMethods[] = 'boot' . class_basename($trait);
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

    /**
     * @param array $attributes
     * @param bool $exists
     * @return mixed
     */
    public function newInstance($attributes = [], $exists = false)
    {
        $model = $this->getChildModel($attributes);

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        return $model;
    }

    /**
     * @param array $attributes
     * @param null $connection
     * @return $this
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $attributes = (array) $attributes;

        $inheritanceAttributes = [];
        $inheritanceColumn = $this->getInheritanceColumn();

        if (isset($attributes[$inheritanceColumn])) {
            $inheritanceAttributes[$inheritanceColumn] = $attributes[$inheritanceColumn];
        }

        $model = $this->newInstance($inheritanceAttributes, true);

        $model->setRawAttributes($attributes, true);


        $model->setConnection($connection ?: $this->getConnectionName());

        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $ownerKey
     * @param  string  $relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
    {
        $instance = $this->newRelatedInstance($related);

        if (is_null($foreignKey) && $instance->hasParent) {
            $foreignKey = Str::snake($instance->getClassNameForRelationships()) . '_' . $instance->getKeyName();
        }

        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        return parent::belongsTo($related, $foreignKey, $ownerKey, $relation);
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        return parent::hasMany($related, $foreignKey, $localKey);
    }

    /**
     * Define a many-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $table
     * @param  string  $foreignPivotKey
     * @param  string  $relatedPivotKey
     * @param  string  $parentKey
     * @param  string  $relatedKey
     * @param  string  $relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null)
    {
        $instance = $this->newRelatedInstance($related);

        if (is_null($table) && $instance->hasParent) {
            $table = $this->joiningTable($instance->getClassNameForRelationships());
        }

        return parent::belongsToMany($related, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relation);
    }

    /**
     * @return string
     */
    public function getClassNameForRelationships()
    {
        return class_basename($this);
    }

    /**
     * @return string
     */
    public function getInheritanceColumn()
    {
        return property_exists($this, 'childColumn') ? $this->childColumn : 'type';
    }

    /**
     * Override this in order to get the custom class in a custom way, not reading the InheritanceColumn attribute
     * @param array $attributes
     * @return mixed
     */
    public function getChildClass(array $attributes)
    {

        if (!isset($attributes[$this->getInheritanceColumn()])) {
            return static::class;
        };

        return $this->classFromAlias(
            $attributes[$this->getInheritanceColumn()]
        );
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    protected function getChildModel(array $attributes)
    {
        $className = $this->getChildClass($attributes);

        return new $className((array)$attributes);
    }

    /**
     * @param $aliasOrClass
     * @return mixed
     */
    public function classFromAlias($aliasOrClass)
    {
        if (property_exists($this, 'childTypes')) {
            if (isset($this->childTypes[$aliasOrClass])) {
                return $this->childTypes[$aliasOrClass];
            }
        }

        return $aliasOrClass;
    }

    /**
     * @param $className
     * @return false|int|string
     */
    public function classToAlias($className)
    {
        if (property_exists($this, 'childTypes')) {
            if (in_array($className, $this->childTypes)) {
                return array_search($className, $this->childTypes);
            }
        }

        return $className;
    }

    /**
     * @return array
     */
    public function getChildTypes()
    {
        return property_exists($this, 'childTypes') ? $this->childTypes : [];
    }

}
