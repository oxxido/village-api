<?php

namespace App\Airtable\Models;

use App\Transformers\SpacesTransformer;

class Spaces extends Model
{
    protected static $table = 'spaces';
    protected static $transformer = SpacesTransformer::class;
}
