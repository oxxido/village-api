<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \TANIOS\Airtable\Airtable;
use App\Airtable\Models\Person;
use App\Airtable\Models\Organization;
use App\Airtable\Models\Checkins;
use App\Airtable\Models\Spaces;

class Controller extends BaseController
{
    // vars
    private $airtable;

    function __construct() {
        $this->airtable = new Airtable([
            'api_key'=> env('API_KEY'),
            'base'   => env('BASE_KEY')
        ]);
        //$this->airtable = $airtable;
    }

    /**
     * Fetches a table from airtable and return the rows
     *
     * @param int $tableName    The name of the table to fetch from airtable
     * @return array rows of current table
     */
    private function getTable($tableName) {
        /*$rows = [];
        $request = $this->airtable->getContent($tableName);
        do {
            $response = $request->getResponse();
            $rows[] = $response['records'] ;
        }
        while ($request = $response->next());
        return $rows;*/

        $response = $this->airtable->getContent($tableName)->getResponse();
        return $response['records'];
    }

    private function getRecordById($tableName, $tableId)
    {
        $response = $this->airtable->getContent($tableName.'/'.$tableId)->getResponse();
        return $response['id']?['id' => $response['id'], 'fields'=>$response['fields']]:false;
    }

    private function getRecordByFilter($tableName, $params)
    {
        $response = $this->airtable->getContent($tableName, $params)->getResponse();
        return $response['records'];
    }

    private function getLoginInfo($username, $password)
    {
        $params =  array(
            "filterByFormula"=>"AND({User} = '".$username."', {Password} = '".$password."')"
        );
        return $this->getRecordByFilter('Admins', $params);
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$person = Person::fractalGet(20);
        //return response()->json($person->toArray());
        //$check_ins = Checkins::get(20);
        $check_ins = Checkins::getUniqueBySpaceId('pZ0q70cQqK8HxJAnusL9');
        $check_ins = Checkins::find('rec5PhcaV5E5n6kPx');
        return response()->json($check_ins->transform());
        return response()->json(['Status' => '200']);
    }

    /**
     * Get the people table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function people($offset = null)
    {
        //$offset = $request->get('offset')?$request->get('offset'):null;
        $person = Person::fractalGet(20, $offset);
        return response()->json($person->toArray());
    }

    /**
     * Get the organizations table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function organizations($offset = null)
    {
        $Organizations = Organization::fractalGet(20, $offset);
        return response()->json($Organizations->toArray());
        //return response()->json($this->getTable('Organizations'));
    }

    /**
     * Get the checkins table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function checkins($offset = null)
    {
        $Checkins = Checkins::fractalGet(20, $offset);
        return response()->json($Checkins->toArray());
    }

    /**
     * Get the checkins table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function spaces($offset = null)
    {
        $Spaces = Spaces::fractalGet(20, $offset);
        return response()->json($Spaces->toArray());
    }

    /**
     * Get the checkins table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function space($id)
    {
        $Space = Spaces::fractalGet(20, $id);
        return response()->json($Space->toArray());
    }

    /**
     * Get the organizations table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function plans()
    {
        return response()->json($this->getTable('Plans'));
    }

    /**
     * Get the admin table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function admins()
    {
        echo "hola";
        return response()->json($this->getLoginInfo('Ger', 'holapolola'));
        //recDIqYTHo1RHrrxr
        //return response()->json($this->getRecordByid('Ger', 'recDIqYTHo1RHrrxr'));
    }
    public function admin()
    {
        return response()->json($this->getRecordByid('Ger', 'recDIqYTHo1RHrrxr'));
    }
    public function getPages($table)
    {
        $Checkins = Checkins::fractalGet(20);
        return response()->json($this->getRecordByid('Ger', 'recDIqYTHo1RHrrxr'));
    }

    private function response($data)
    {
        $response = [];
        $response['data'] = $data;
        return response()->json($response);
    }
}
