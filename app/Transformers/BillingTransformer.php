<?php

namespace App\Transformers;

use App\Airtable\Models\Model;

class BillingTransformer extends AbstractTransformer
{
    const ORIGINAL_ATTRIBUTES = [
        'id'        => 'id',
        'period'    => 'fields->Period',
        'space'     => 'fields->Space',
        'spaceName' => 'fields->SpaceName',
        'checkins'  => 'fields->Checkins',
        'CHFCash'   => 'fields->CHF Cash',
        'CHFShares' => 'fields->CHF Shares',
        'created'   => 'createdTime',
    ];

    public function transform(Model $resource)
    {
        $transformation = parent::transform($resource); // This is an array, modify it as needed

        return $transformation;
    }
}

