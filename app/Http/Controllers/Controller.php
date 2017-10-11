<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \TANIOS\Airtable\Airtable;
use App\Airtable\Models\Person;
use App\Airtable\Models\Organization;
use App\Airtable\Models\CheckIn;
use App\Airtable\Models\Space;
use Illuminate\Support\Facades\Crypt;
use Log;

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

    private function getSpaceInfo($space)
    {
        $params =  array(
            "filterByFormula"=>"AND({Space Hash} = '".$space."')"
        );
        return $this->getRecordByFilter('Spaces', $params);
    }

    private function getLoginInfo($space, $password)
    {
        $params =  array(
            "filterByFormula"=>"AND({Space Hash} = '".$space."', {Password} = '".$password."')"
        );
        return $this->getRecordByFilter('Spaces', $params);
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
        //return response()->json(Spaces::first());
        $person = Person::first();
        $check_in = $person->insertCheckIn('rec1LSI8mfytkpOm2', '2017-10-10T00:00:00.000Z');
        //$check_in = $person->insertCheckIn('asd', '2017-10-10T00:00:00.000Z'); // Throws exception
        //return response()->json($check_in); // Also works
        return response()->json($check_in->transform());

        //$check_ins = Checkins::getUniqueBySpaceId('pZ0q70cQqK8HxJAnusL9');
        //$check_ins = Checkins::find('rec5PhcaV5E5n6kPx');
        //return response()->json(['Status' => '200']);
    }

    /**
     * Get the people table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function people($offset = null, Request $request)
    {
        //$t = [ $request->get('id'), $request->get('name')];
        //return response()->json($t);
        //$offset = $request->get('offset')?$request->get('offset'):null;

        // $person = Person::fractalGet(20, $offset);
        $people = CheckIn::getUniqueUserBySpaceName($request->get('name'), 20)->transform();
        $peopleArray = $people->toArray();
        $uniqueArray = $temp = [];
        foreach ($peopleArray['data'] as $person) {
            if(!isset($temp[$person['customer']])) {
                $uniqueArray[] = $person;
                $temp[$person['customer']] = true;
            }
        }
        return response()->json(['data' => $uniqueArray]);

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
    public function checkins($offset = null, Request $request)
    {
        //$Checkins = CheckIn::fractalGet(20, $offset);
        Log::info('space id: '.$request->get('id'));
        $Checkins = CheckIn::getBySpaceName($request->get('name'), 20)->transform();
        //$Checkins = CheckIn::getUniqueBySpaceId('recxNbj8oGnzIioEj')->transform();
        return response()->json($Checkins->toArray());
    }

    /**
     * Get the checkins table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function spaces($offset = null)
    {
        $Spaces = Space::fractalGet(20, $offset);
        return response()->json($Spaces->toArray());
    }

    /**
     * Get the checkins table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function space($id)
    {
        $Space = Space::fractalGet(20, $id);
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
        return response()->json($this->getTable('Admins'));
        //echo "hola";
        //return response()->json($this->getLoginInfo('Ger', 'holapolola'));
        //recDIqYTHo1RHrrxr
        //return response()->json($this->getRecordByid('Ger', 'recDIqYTHo1RHrrxr'));
    }
    public function admin()
    {
        return response()->json($this->getRecordByid('Ger', 'recDIqYTHo1RHrrxr'));
    }

    public function login(Request $request)
    {
        $response = [ 'loggedin' => false ];

        $loginInfo = $this->getLoginInfo(
                        $request->input('space'),
                        $request->input('pass')
                        );
        if ($loginInfo) {
            $response['loggedin'] = true;
            //encode data:
            $toEnctrypt = "{$loginInfo[0]->id}|{$loginInfo[0]->fields->Name}|".str_random(15);
            $response['payload'] = [
                'hash' => Crypt::encrypt($toEnctrypt),
                'name' => $loginInfo[0]->fields->Name
            ];
        } else {
            $spaceInfo = $this->getSpaceInfo(
                        $request->input('space')
                        );
            if ($spaceInfo) {
                $response['reason'] = 'Password is incorrect';
            } else {
                $response['reason'] = 'Space not found';
            }
        }
        return response()->json( $response );
    }

    public function getPages($table)
    {
        $Checkins = CheckIn::fractalGet(20);
        return response()->json($this->getRecordByid('Ger', 'recDIqYTHo1RHrrxr'));
    }

    private function response($data)
    {
        $response = [];
        $response['data'] = $data;
        return response()->json($response);
    }
}
