<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use \TANIOS\Airtable\Airtable;

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
        //$person = Person::fractalFirst();
        //return response($person->toJson());
        return response()->json(['hello' => 'world']);
    }

    /**
     * Get the people table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function people()
    {
        //return response()->json($this->getTable('People'));
        return $this->response($this->getTable('People'));
    }

    /**
     * Get the organizations table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function organizations()
    {
        return response()->json($this->getTable('Organizations'));
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

    private function response($data)
    {
        $response = [];
        $response['data'] = $data;
        return response()->json($response);
    }
}
