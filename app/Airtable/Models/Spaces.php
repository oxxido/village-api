<?php

namespace App\Airtable\Models;

use App\Airtable\Builder;
use App\Transformers\SpacesTransformer;

class Spaces extends Model
{
    protected static $table = 'spaces';
    protected static $transformer = SpacesTransformer::class;

    public function checkIns($page_size = Builder::PAGE_SIZE)
    {
        return Checkins::getUniqueBySpaceId($this->id, $page_size);
    }
}
