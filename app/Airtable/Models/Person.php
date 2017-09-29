<?php

namespace App\Airtable\Models;

use App\Transformers\PersonTransformer;

class Person extends Model
{
    protected static $table = 'people';
    protected static $transformer = PersonTransformer::class;
}
