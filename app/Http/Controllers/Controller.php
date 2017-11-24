<?php

namespace App\Http\Controllers;

use App\User;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    function __construct()
    {
        $this->airtable = new Airtable([
            'api_key' => env('API_KEY'),
            'base'    => env('BASE_KEY'),
        ]);
        //$this->airtable = $airtable;
    }

    /**
     * Fetches a table from airtable and return the rows
     *
     * @param int $tableName The name of the table to fetch from airtable
     *
     * @return array rows of current table
     */
    private function getTable($tableName)
    {
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
        $response = $this->airtable->getContent($tableName . '/' . $tableId)->getResponse();

        return $response['id'] ? ['id' => $response['id'], 'fields' => $response['fields']] : false;
    }

    private function getRecordByFilter($tableName, $params)
    {
        $response = $this->airtable->getContent($tableName, $params)->getResponse();

        return $response['records'];
    }

    private function getSpaceInfo($space)
    {
        $params = [
            "filterByFormula" => "AND({Space Hash} = '" . $space . "')",
        ];

        return $this->getRecordByFilter('Spaces', $params);
    }

    private function getLoginInfo($space, $password)
    {
        $params = [
            "filterByFormula" => "AND({Space Hash} = '" . $space . "', {Password} = '" . $password . "')",
        ];

        return $this->getRecordByFilter('Spaces', $params);
    }

    private function getAutoLoginInfo($space)
    {
        $params = [
            "filterByFormula" => "AND({Space Hash} = '" . $space . "')",
        ];

        return $this->getRecordByFilter('Spaces', $params);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$space = Space::first();
        //$space->update([
        //    'Name' => 'VillageOffice Uferbao',
        //]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml('hello world');

        // (Optional) Setup the paper size and orientation
        // $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        $output = $dompdf->output();
        $filename = 'asd.pdf';
        $disk = Storage::disk('local');
        $disk->put($filename, $output);

        $path = "/downloads/{$filename}";
        dd([
            'title' => $filename,
            'url' => $path
        ]);
        //$spaces = Space::collect(Space::query()->sortBy('Name', 'asc')->get());
        //
        //return $spaces->transform();
        //$person = Person::fractalGet(20);
        //return response()->json($person->toArray());
        //return response()->json(Spaces::first());
        //$person = Person::first();
        //$check_in = $person->insertCheckIn('rec1LSI8mfytkpOm2', '2017-10-10T00:00:00.000Z');
        //$check_in = $person->insertCheckIn('asd', '2017-10-10T00:00:00.000Z'); // Throws exception
        //return response()->json($check_in); // Also works
        //return response()->json($check_in->transform());

        //$check_ins = Checkins::getUniqueBySpaceId('pZ0q70cQqK8HxJAnusL9');
        //$check_ins = Checkins::find('rec5PhcaV5E5n6kPx');
        //return response()->json(['Status' => '200']);
    }

    public function addCheckIn(Request $request) {
        $personId = $request->input('customer');
        $spaceId = $request->get('id');
        $stringDate = $request->input('date');
        $stringTime = $request->input('time');
        $lastSeen = $request->input('last-seen');

        $date = $stringDate . 'T' . $stringTime . ':00.000Z';

        Log::info('date:' . $stringDate . ' '. $stringTime );
        Log::info($date);
        if ($personId && $spaceId) {
            $Person = Person::getByPersonId($personId);
            $check_in = $Person->insertCheckIn($spaceId, $date, $lastSeen);
            return response()->json($check_in->transform());
        } else {
            return response()->json(['success'=>false, 'reason'=>'You need to fill both customer and date']);
        }
    }

    public function updateSettings(Request $request) {
        $space = Space::getBySpaceId($request->get('id'));

        $space->update([
                'Name'          => $request->input('name'),
                'Public Phone'  => $request->input('publicPhone'),
                'Public Email'  => $request->input('publicEmail'),
                'Address'       => $request->input('address'),
                'Twitter Handle'=> $request->input('twitterHandle'),
                'IBAN'          => $request->input('IBAN'),
                'Password'      => $request->input('password')
            ]);

        return $space->transform();
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
        $people      = CheckIn::getUniqueUserBySpaceId($request->get('id'), 100)->transform();
        $peopleArray = $people->toArray();
        $uniqueArray = $temp = [];
        /*foreach ($peopleArray['data'] as $person) {
            if(!isset($temp[$person['name']])) {
                $uniqueArray[] = $person;
                $temp[$person['name']] = true;
            }
        }*/
        foreach ($peopleArray['data'] as $person) {
            if (isset($temp[$person['name']])) {
                $temp[$person['name']]['count']++;
                if (date("m", time()) == date("m", strtotime($person['date']))) {
                    $temp[$person['name']]['thisMonth']++;
                }
            } else {
                $temp[$person['name']]              = $person;
                $temp[$person['name']]['thisMonth'] =
                    (date("m", time()) == date("m", strtotime($person['date']))) ? 1 : 0;
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
        $Checkins = CheckIn::getBySpaceId($request->get('id'))->transform();
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
            $period['CHF'] = $this->calculateCHF($period['checkins']);
            $result[] = $period;
        }
        return response()->json(['data'=>$result]);
    }

    public function period(Request $request, $year, $month)
    {
        $Checkins = CheckIn::getByPeriod($request->get('id'), $year, $month)->transform();
        $currentCheckins = $Checkins->toArray();
        $periods = $result = $meta = [];
        $result = [];
        $meta['title'] = date("Y F", strtotime($year.'-'.$month.'-02'));
        $meta['totalCheckins'] = 0;
        // HARDCODED for now
        $meta['pdf'] = [];
        $meta['pdf']['title'] = 'VO-0004-June2017.pdf';
        $meta['pdf']['url'] = 'http://someurl.com/VO-0004-June2017.pdf';
        // END of hardcoded
        foreach($currentCheckins['data'] as $checkin) {
            // if date not set, record is wrong, omitting...
            if (isset($checkin['email'])) {
                $idx = $checkin['email'];
                if (isset($periods[$idx])) {
                    $periods[$idx]['checkins'][] = $checkin;
                } else {
                    // current period:
                    $person = [];
                    $person['checkins'] = [$checkin];
                    $person['name'] = $checkin['name'];
                    $person['email'] = $checkin['email'];
                    $person['idx'] = $idx;
                    $periods[$idx] = $person;
                }
            }
        }
        foreach($periods as $person) {
            $person['checkins'] = $this->onlyUniqueCheckins($person['checkins']);
            $meta['totalCheckins'] += count($person['checkins']);
            $person['CHF'] = $this->calculateCHF($person['checkins']);
            $result[] = $person;
        }
        return response()->json([
            'data'=>$result,
            'meta'=>$meta
        ]);
    }

    public function onlyUniqueCheckins($checkins) {
        $temp = array();
        $result = array();
        foreach($checkins as $checkin) {
            if (!isset($temp[$checkin['date']])) {
                $result[$checkin['date']] = $checkin;
                $temp[$checkin['date']] = true;
            }
        }
        ksort($result);
        return array_values($result);
    }

    public function calculateCHF($checkins) {
        $checks = is_array($checkins)? count($checkins): (is_numeric($checkins)? $checkins:0);
        if ($checks < 2) {
            return 15;
        }
        $increment = 0;
        for($i = 1; $i <= ($checks-1); $i++)
        {
            $increment += ($i * 0.50);
        }
        $result = ($checks * 15) + $increment;
        if ($result > 400) {
            $result = 400;
        }
        return $result;
    }

    /**
     * Get the checkins table from airtable
     *
     * @return \Illuminate\Http\Response
     */
    public function checkins($offset = null, Request $request)
    {
        //Log::info('Offset: '. $offset );
        $Checkins = CheckIn::getBySpaceId($request->get('id'))->transform();

        return response()->json($Checkins->toArray());
    }

    public function personCheckins(Request $request, $name)
    {

        $Checkins = CheckIn::getByUserId($request->get('id'), $name, 20)->transform();

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
        $Space = Space::getBySpaceId($request->get('id'))->transform();

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
        $response = ['loggedin' => false];

        $loginInfo = $this->getLoginInfo($request->input('space'), $request->input('pass'));
        if ($loginInfo) {
            $response['loggedin'] = true;
            //encode data:
            $toEnctrypt          = "{$loginInfo[0]->id}|{$loginInfo[0]->fields->Name}|" . str_random(15);
            $response['payload'] = [
                'hash' => Crypt::encrypt($toEnctrypt),
                'name' => $loginInfo[0]->fields->Name,
            ];
        } else {
            $spaceInfo = $this->getSpaceInfo($request->input('space'));
            if ($spaceInfo) {
                $response['reason'] = 'Password is incorrect';
            } else {
                $response['reason'] = 'Space not found';
            }
        }

        return response()->json($response);
    }

    public function autologin(Request $request)
    {
        $response = ['loggedin' => false];

        $loginInfo = $this->getAutoLoginInfo($request->input('space'));
        if ($loginInfo) {
            $response['loggedin'] = true;
            //encode data:
            $toEnctrypt          = "{$loginInfo[0]->id}|{$loginInfo[0]->fields->Name}|" . str_random(15);
            $response['payload'] = [
                'hash' => Crypt::encrypt($toEnctrypt),
                'name' => $loginInfo[0]->fields->Name,
            ];
        } else {
            $spaceInfo = $this->getSpaceInfo($request->input('space'));
            if ($spaceInfo) {
                $response['reason'] = 'Something went wrong, please try again';
            } else {
                $response['reason'] = 'Space not found';
            }
        }

        return response()->json($response);
    }

    public function getPages($table)
    {
        $Checkins = CheckIn::fractalGet(20);

        return response()->json($this->getRecordByid('Ger', 'recDIqYTHo1RHrrxr'));
    }

    private function response($data)
    {
        $response         = [];
        $response['data'] = $data;

        return response()->json($response);
    }
}
