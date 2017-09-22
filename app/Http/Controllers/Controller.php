<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use \TANIOS\Airtable\Airtable;

class Controller extends BaseController
{
    // vars
    private $airtable;

    function __construct() {
        $this->airtable = new Airtable(array(
            'api_key'=> env('API_KEY'),
            'base'   => env('BASE_KEY')
        ));
    }

    /**
     * Fetches a table from airtable and return the rows
     *
     * @param int $tableName    The name of the table to fetch from airtable
     * @return array rows of current table
     */
    private function getTable($tableName) {
        $rows = [];
        $request = $this->airtable->getContent( $tableName );
        do {
            $response = $request->getResponse();
            $rows[] = $response[ 'records' ] ;
        }
        while( $request = $response->next() );
        return $rows;
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return response()->json(['hello'=>'world']);
    }

    /**
     * Get the people table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function people()
    {
        return response()->json($this->getTable('People'));
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

}
