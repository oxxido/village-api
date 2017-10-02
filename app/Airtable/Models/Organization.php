<?php

namespace App\Airtable\Models;

use App\Transformers\OrganizationTransformer;

class Organization extends Model
{
    protected static $table = 'organizations';
    protected static $transformer = OrganizationTransformer::class;
}
