<?php

namespace App\Airtable\Models;

use App\Airtable\Builder;
use App\Transformers\CheckinsTransformer;

class Checkins extends Model
{
    protected static $table = 'check-ins';
    protected static $transformer = CheckinsTransformer::class;

    public static function getUniqueBySpaceId($space_id, $page_size = Builder::PAGE_SIZE)
    {
        $query = static::query();
        $query->where("{SpaceId} = '{$space_id}'");
        $page   = $query->get($page_size);
        $offset = $query->getOffset();

        return static::collect($page, $offset);
    }
}
