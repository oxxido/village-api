<?php

namespace App\Transformers;

use App\Airtable\Models\Model;

class PersonTransformer extends AbstractTransformer
{
    const ORIGINAL_ATTRIBUTES = [
        'id'           => 'id',
        'firstName'    => 'fields->First Name',
        'lastName'     => 'fields->Last Name',
        'email'        => 'fields->Email',
        'customerTeam' => 'fields->Customer Team',
        'organization' => 'fields->Organization',
        'type'         => 'fields->Type',
        'program'      => 'fields->Program',
        'zipOffice'    => 'fields->ZIP Office',
        'name'         => 'fields->Name',
        'orgAbbr'      => 'fields->Org. abbr.',
        'created'      => 'createdTime',
    ];

    public function transform(Model $resource)
    {
        $transformation = parent::transform($resource); // This is an array, modify it as needed

        return $transformation;
    }
}