<?php

namespace App\Airtable\Models;

use App\Airtable\Builder;
use App\Transformers\CheckinsTransformer;

class CheckIn extends Model
{
    protected static $table = 'check-ins';
    protected static $transformer = CheckinsTransformer::class;

    public static function getBySpaceName($space_name, $page_size = Builder::PAGE_SIZE)
    {
        $query = static::query();
        $query->where("{Space} = '{$space_name}'");
        $page   = $query->get($page_size);
        $offset = $query->getOffset();

        return static::collect($page, $offset);
    }

    public static function getUniqueUserBySpaceName($space_name, $page_size = Builder::PAGE_SIZE)
    {
        $query = static::query();
        $query->where("{Space} = '{$space_name}'");
        $query->view("unique");
        $page   = $query->get($page_size);
        $offset = $query->getOffset();

        return static::collect($page, $offset);
    }

    public static function getByUserId($space_name, $user_id, $page_size = Builder::PAGE_SIZE)
    {
        $query = static::query();

        $query->where("AND({CustomerId} = '{$user_id}', {Space} = '{$space_name}')");

        $page   = $query->get($page_size);
        $offset = $query->getOffset();

        return static::collect($page, $offset);
    }
}
