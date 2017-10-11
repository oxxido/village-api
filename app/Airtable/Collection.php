<?php

namespace App\Airtable;

use App\Airtable\Models\Model;
use Illuminate\Support\Collection as BaseCollection;

class Collection extends BaseCollection
{
    protected $modelClass;
    protected $transformerClass;
    protected $offset;

    public function setModelClass($model)
    {
        if ($model instanceof Model) {
            $model = get_class($model);

        }

        if (is_string($model) && class_exists($model)) {
            $this->modelClass = $model;
            $this->transformerClass = $model::transformerClass();
        }

        return $this;
    }

    public function getModelClass()
    {
        return $this->modelClass;
    }

    public function getTransformerClass()
    {
        return $this->transformerClass;
    }

    public function transform(callable $callback = null)
    {
        if (null === $callback && !empty($this->transformerClass)) {
            $transformer_class = $this->getTransformerClass();
            $fractal = fractal($this, new $transformer_class());
            if ($offset = $this->getOffset()) {
                $fractal->addMeta(['offset' => $offset]);
            }

            return $fractal;
        }

        return parent::transform($callback); // TODO: Change the autogenerated stub
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    public function getOffset()
    {
        return $this->offset;
    }
}
