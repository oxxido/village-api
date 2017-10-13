<?php

namespace App\Airtable\Models;

use Exception;
use stdClass;
use App\Transformers\PersonTransformer;

class Person extends Model
{
    protected static $table = 'people';
    protected static $transformer = PersonTransformer::class;

    public function insertCheckIn($space_id, $date_time)
    {
        $fields = [
            'Timestamp' => $date_time,
            'Customer'  => [$this->id],
            'Space'     => [$space_id],
        ];

        $response  = $this->getQuery()->post($fields, with(new CheckIn(new stdClass()))->getTable());

        $decoded = json_decode($response);

        if ($error = ($decoded->error?? null)) {
            throw new Exception($error->type . ': ' . $error->message);
        }

        return new CheckIn($decoded);
    }
    public static function getByPersonId($person_id)
    {
        $query = static::query();
        $query->where("{id} = '{$person_id}'");
        $obj   = new static($query->first());
        return $obj;
    }
}
