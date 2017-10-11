<?php

namespace App\Airtable\Models;

use stdClass;
use App\Airtable\Builder;
use App\Airtable\Collection;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

abstract class Model implements Arrayable, Jsonable
{
    protected static $table;
    protected static $transformer;
    protected $query;
    protected $attributes;
    public $exists = false;

    public static function query()
    {
        return new Builder(static::$table);
    }

    public static function get($page_size = Builder::PAGE_SIZE, Builder $query = null)
    {
        $query = $query ?? static::query();

        return new Collection(array_map(function ($record) {
            return new static($record);
        }, $query->get($page_size)));
    }

    public static function all()
    {
        return self::get(Builder::PAGE_SIZE);
    }

    public static function first(Builder $query = null)
    {
        $query = $query ?? static::query();

        return new static($query->first());
    }

    public static function transformer()
    {
        return new static::$transformer();
    }

    public static function transformerClass()
    {
        return static::$transformer;
    }

    public static function fractalGet($page_size = Builder::PAGE_SIZE, $offset = null)
    {
        $query = static::query();
        if ($offset) {
            $query->offset($offset);
        }

        $fractal = fractal(static::get($page_size, $query), static::transformer());

        if ($offset = $query->getOffset()) {
            $fractal->addMeta(['offset' => $offset]);
        }

        return $fractal;
    }

    public static function fractalFirst()
    {
        $query = static::query();

        $fractal = fractal(static::first($query), static::transformer());

        return $fractal;
    }

    public static function where($filter)
    {
        return static::query()->where($filter);
    }

    public static function sortBy($field, $direction = 'asc')
    {
        return static::query()->sortBy($field, $direction);
    }

    public static function select($field)
    {
        return static::query()->select($field);
    }

    public static function offset($offset)
    {
        return static::query()->setOffset($offset);
    }

    public function __construct(stdClass $attributes)
    {
        $this->query      = self::query();
        $this->attributes = $attributes;
    }

    public function setQuery(Builder $query)
    {
        $this->query = $query;

        return $this;
    }

    public function getQuery($force_new = false)
    {
        if (!$this->query) {
            return $this->query = new Builder(static::$table);
        }

        return $this->query = !$force_new ? $this->query : new Builder(static::$table);
    }

    public function getTable()
    {
        return static::$table;
    }

    public function toArray()
    {
        return json_decode($this->toJson(), true);
    }

    public function toJson($options = 0)
    {
        return json_encode($this->attributes, $options);
    }

    public function __toString()
    {
        return $this->toJson();
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        if (!$key) {
            return;
        }

        return $this->attributes->$key ?? null;
    }

    public function setAttribute($key, $value)
    {
        $this->attributes->$key = $value;

        return $this;
    }

    public static function collect(array $raw, $offset = null)
    {
        $collection = new Collection(array_map(function ($record) {
            return new static($record);
        }, $raw));

        $collection->setModelClass(static::class);
        $collection->setOffset($offset);

        return $collection;
    }

    public function newInstance($attributes = [], $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static((object) $attributes);

        $model->exists = $exists;

        return $model;
    }

    public function newModelInstance($attributes = [])
    {
        return $this->newInstance($attributes);
    }

    public function create(array $attributes = [])
    {
        return tap($this->newModelInstance($attributes), function ($instance) {
            $instance->save();
        });
    }

    public function make(array $attributes)
    {
        return $this->newModelInstance($attributes);
    }

    public function transform()
    {
        $fractal = fractal($this, static::transformer());

        return $fractal->toArray();
    }
}
