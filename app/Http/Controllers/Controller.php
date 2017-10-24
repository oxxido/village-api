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

    public function addCheckIn(Request $request) {
        $personId = $request->input('customer');
        $spaceId = $request->get('id');
        $stringDate = $request->input('date');
        if(!$stringDate) {
            $stringDate = date('Y-m-d\TH:i:s\.000\Z');
        }
        // first test: override person and space id:
        // $personId = 'recLTLOltJ1jOesU5';
        // $spaceId = 'recNGytQ66mhRPUPp';
        // $stringDate = date('Y-m-d\TH:i:s\.000\Z');
        $Person = Person::getByPersonId($personId);
        $check_in = $Person->insertCheckIn($spaceId, $stringDate);
        return response()->json($check_in->transform());
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
        /*foreach ($peopleArray['data'] as $person) {
            if(!isset($temp[$person['name']])) {
                $uniqueArray[] = $person;
                $temp[$person['name']] = true;
            }
        }*/
        foreach ($peopleArray['data'] as $person) {
            if(isset($temp[$person['name']])) {
                $temp[$person['name']]['count']++;
            } else {
                $temp[$person['name']] = $person;
                $temp[$person['name']]['count'] = 1;
            }
        }
        foreach ($temp as $person) {
            $uniqueArray[] = $person;
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
     * Get all rows on the checkins table from airtable
     * and calculate the billing part
     *
     * @return \Illuminate\Http\Response
     */
    public function billing($offset = null, Request $request)
    {
        $Checkins = CheckIn::getBySpaceName($request->get('name'), 100)->transform();
        $currentCheckins = $Checkins->toArray();
        $periods = [];
        foreach($currentCheckins['data'] as $checkin) {
            // if date not set, record is wrong, omitting...
            if (isset($checkin['date'])) {
                $idx = intval(date("ym", strtotime($checkin['date'])));
                if (isset($periods[$idx])) {
                    $periods[$idx]['checkins']++;
                    $periods[$idx]['cash'] += 100;
                    $periods[$idx]['shares'] += 50;
                } else {
                    // current period:
                    $period = [];
                    $period['month'] = date("Y F", strtotime($checkin['date']));
                    $period['checkins'] = 1;
                    $period['cash'] = 100;
                    $period['shares'] = 50;
                    $period['idx'] = $idx;
                    $period['link'] = date("Y/m", strtotime($checkin['date']));
                    $periods[$idx] = $period;
                }
            }
        }
        krsort($periods);
        $result = [];
        foreach($periods as $period) {
            $result[] = $period;
        }
        return response()->json(['data'=>$result]);
    }

    public function period(Request $request, $year, $month)
    {
        $Checkins = CheckIn::getByPeriod($request->get('name'), $year, $month, 100)->transform();
        $currentCheckins = $Checkins->toArray();
        $periods = $result = $meta = [];
        $result = [];
        $meta['title'] = date("Y F", strtotime($year.'-'.$month.'-02'));
        $meta['totalCheckins'] = 0;
        foreach($currentCheckins['data'] as $checkin) {
            // if date not set, record is wrong, omitting...
            if (isset($checkin['email'])) {
                $idx = $checkin['email'];
                if (isset($periods[$idx])) {
                    $periods[$idx]['checkins'][] = $checkin;
                } else {
                    // current period:
                    $period = [];
                    $period['checkins'] = [$checkin];
                    $period['name'] = $checkin['name'];
                    $period['email'] = $checkin['email'];
                    $period['idx'] = $idx;
                    $periods[$idx] = $period;
                }
                $meta['totalCheckins']++;
            }
        }
        foreach($periods as $period) {
            $result[] = $period;
        }
        return response()->json([
            'data'=>$result,
            'meta'=>$meta
        ]);
    }

    /**
     * Get the checkins table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function checkins($offset = null, Request $request)
    {
        //Log::info('space id: '.$request->get('id'));
        $Checkins = CheckIn::getBySpaceName($request->get('name'), 20)->transform();
        return response()->json($Checkins->toArray());
    }

    public function personCheckins(Request $request, $name)
    {
        $Checkins = CheckIn::getByUserId($request->get('name'), $name, 20)->transform();
        return response()->json($Checkins->toArray());
    }

    /**
     * Get the checkins table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function spaces($offset = null)
    {
        //$Spaces = Space::fractalGet(200, $offset);
        $Spaces = Space::getActive($offset)->transform();
        return response()->json($Spaces->toArray());
    }

    /**
     * Get the checkins table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function space(Request $request)
    {
        // $Space = Space::fractalGet(20, $id);
        $Space = Space::getBySpaceName($request->get('name'))->transform();
        return response()->json($Space);
    }

    public function person(Request $request, $id_person)
    {
        $Person = Person::getByPersonId($id_person)->transform();
        return response()->json($Person);
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
