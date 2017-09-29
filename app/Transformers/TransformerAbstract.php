<?php

namespace App\Transformers;

use App\Airtable\Models\Model;
use League\Fractal\TransformerAbstract;

class AbstractTransformer extends TransformerAbstract
{
    const ORIGINAL_ATTRIBUTES = [];

    public static function originalAttributes()
    {
        return static::ORIGINAL_ATTRIBUTES;
    }

    public static function attributes()
    {
        return array_flip(static::originalAttributes());
    }

    public function transform(Model $model)
    {
        // Makes it all array
        $resource = $model->toArray();
        $fields = array_map(function ($source) use ($resource) {
            return $this->asd($resource, $source);
        },
            static::ORIGINAL_ATTRIBUTES);

        return array_filter($fields,
            function ($field) {
                return null !== $field;
            });
    }

    protected function asd($resource, $source)
    {
        $sub_resource = $resource;
        $fields       = explode('->', $source);

        do {
            $field        = array_shift($fields);
            $sub_resource = $sub_resource[$field] ?? null;

            if (null === $sub_resource) {
                return null;
            }

            if (empty($fields) && is_array($sub_resource)) {
                return implode(', ', $sub_resource);
            }
        } while (null !== $sub_resource && !is_string($sub_resource));

        return $sub_resource;
    }
}
