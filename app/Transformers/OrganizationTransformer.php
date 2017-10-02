<?php

namespace App\Transformers;

use App\Airtable\Models\Model;

class OrganizationTransformer extends AbstractTransformer
{
    const ORIGINAL_ATTRIBUTES = [
        'id'            => 'id',
        'name'          => 'fields->Name',
        'participants'  => 'fields->Participants',
        'abbreviation'  => 'fields->Abbreviation',
        'accountManager'=> 'fields->Key Account Manager->name',
        'accountManagerEmail'=> 'fields->Key Account Manager->email',
        'created'       => 'createdTime',
    ];

    public function transform(Model $resource)
    {
        $transformation = parent::transform($resource); // This is an array, modify it as needed

        return $transformation;
    }
}
