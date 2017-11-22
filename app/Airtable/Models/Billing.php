<?php

namespace App\Airtable\Models;

use App\Transformers\OrganizationTransformer;

class Billing extends Model
{
    protected static $table = 'billing';
    protected static $transformer = BillingTransformer::class;
}
