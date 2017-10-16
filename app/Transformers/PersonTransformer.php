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
        'phone'        => 'fields->Phone',
        'customerTeam' => 'fields->Customer Team',
        'organizationId' => 'fields->Organization',
        'organization' => 'fields->OrganizationName',
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
        $transformation['photo'] = array_shift($resource->fields->Photo);
        return $transformation;
    }
}
