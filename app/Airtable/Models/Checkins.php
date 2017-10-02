<?php

namespace App\Airtable\Models;

use App\Transformers\CheckinsTransformer;

class Checkins extends Model
{
    protected static $table = 'check-ins';
    protected static $transformer = CheckinsTransformer::class;
}
