<?php

namespace App\Transformers;

use App\Airtable\Models\Model;

class CheckinsTransformer extends AbstractTransformer
{
    const ORIGINAL_ATTRIBUTES = [
        'id'            => 'id',
        'customer'      => 'fields->Customer',
        'name'          => 'fields->CustomerName',
        'email'         => 'fields->CustomerEmail',
        'phone'         => 'fields->CustomerPhone',
        'organization'  => 'fields->CustomerOrganization',
        'space'         => 'fields->SpaceName',
        'type'          => 'fields->Type',
        'checkin'       => 'fields->Check-in',
        'timestamp'     => 'fields->Timestamp',
        'lastSeen'      => 'fields->Last seen',
        'month'         => 'fields->Month',
        'date'          => 'fields->Date'
    ];

    public function transform(Model $resource)
    {
        $transformation = parent::transform($resource); // This is an array, modify it as needed

        return $transformation;
    }
}
