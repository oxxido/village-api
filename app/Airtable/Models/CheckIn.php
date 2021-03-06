<?php

namespace App\Airtable\Models;

use App\Airtable\Builder;
use App\Transformers\CheckinsTransformer;

class CheckIn extends Model
{
    protected static $table = 'check-ins';
    protected static $transformer = CheckinsTransformer::class;

    public static function getBySpaceId($space_id)
    {
        $query = static::query();
        $query->where("{SpaceId} = '{$space_id}'");
        $query->view("unique");
        return static::collect($query->all(), $query->getOffset());
    }

    public static function getUniqueUserBySpaceId($space_id, $page_size = Builder::PAGE_SIZE)
    {
        $query = static::query();
        $query->where("{SpaceId} = '{$space_id}'");
        $query->view("unique");
        $page   = $query->get($page_size);
        $offset = $query->getOffset();

        return static::collect($page, $offset);
    }
    public static function getPaginatedBySpaceId($space_id, $page_size = Builder::PAGE_SIZE, $offset = null)
    {
        $query = static::query();
        $query->where("{SpaceId} = '{$space_id}'");
        // $query->sortBy("Timestamp", "asc");
        // $query->view("unique");
        $query->setOffset($offset);
        $page   = $query->get($page_size);
        $offset = $query->getOffset();

        return static::collect($page, $offset);
    }

    public static function getByUserId($space_id, $user_id, $page_size = Builder::PAGE_SIZE)
    {
        $query = static::query();

        $query->where("AND({CustomerId} = '{$user_id}', {SpaceId} = '{$space_id}')");

        $page   = $query->get($page_size);
        $offset = $query->getOffset();

        return static::collect($page, $offset);
    }

    public static function getByPeriod($space_id, $year, $month)
    {
        $query = static::query();

        $query->where("AND(MONTH({date}) = {$month}, YEAR({date}) = {$year}, {SpaceId} = '{$space_id}')");

        return static::collect($query->all(), $query->getOffset());
    }
}
