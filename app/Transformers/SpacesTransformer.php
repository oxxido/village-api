<?php

namespace App\Transformers;

use App\Airtable\Models\Model;

class SpacesTransformer extends AbstractTransformer
{
    const ORIGINAL_ATTRIBUTES = [
        'id'         => 'id',
        'vid'        => 'fields->VillageOffice ID',
        'name'       => 'fields->Name',
        'address'    => 'fields->Address',
        'accountant' => 'fields->Key Acount Coworking Spaces->name',
        'accountantEmail' => 'fields->Key Acount Coworking Spaces->email',
        'accountantPhoto' => 'fields->Key Acount Coworking Spaces->photo',
        'publicEmail'=> 'fields->Public Email',
        'publicPhone'=> 'fields->Public Phone',
        'address'    => 'fields->Address',
    ];

    public function transform(Model $resource)
    {
        $transformation = parent::transform($resource); // This is an array, modify it as needed

        return $transformation;
    }
}
