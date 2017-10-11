<?php

namespace App\Airtable\Models;

use App\Airtable\Builder;
use App\Transformers\SpacesTransformer;

class Space extends Model
{
    protected static $table = 'spaces';
    protected static $transformer = SpacesTransformer::class;

    public function checkIns($page_size = Builder::PAGE_SIZE)
    {
        return CheckIn::getUniqueBySpaceId($this->id, $page_size);
    }

    public static function getBySpaceName($space_name)
    {
        $query = static::query();
        $query->where("{Name} = '{$space_name}'");
        $obj   = new static($query->first());
        //$offset = $query->getOffset();

        //return static::collect($page, $offset);
        return $obj;
    }
}
