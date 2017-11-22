<?php

namespace App\Http\Controllers;

use App\Airtable\Models\CheckIn;
use Laravel\Lumen\Routing\Controller as BaseController;
use Dompdf\Dompdf;

class PdfController extends BaseController
{
    private static $CHFSharesLimit = 500;
    private static $CHFUserLimit = 400;
    private static $CHFMoneyValue = 15;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get all the active spaces on Space table
     *
     * @return \Illuminate\Http\Response
     */
    public function spaces()
    {
        //$Spaces = Space::fractalGet(200, $offset);
        $Spaces = Space::getActive($offset)->transform();

        return response()->json($Spaces->toArray());
    }

    public function createReports()
    {
        $Spaces = Space::getActive($offset)->transform();
        $SpacesArray = $Spaces->toArray();
        $currentYear = date("Y");
        $currentMonth = date("m");
        foreach($SpacesArray as $space) {
            $this->createSpaceReport($space['id'], $currentYear, $currentMonth);
        }
    }

    /**
     * Create a report for an specific month, year and space
     * It gets all checkings from a space, calculate their CHF (Shares and cash)
     * and converts the result into a pdf, then returns an array with this info:
     * Space, CHF share, chf cash, pdf's url.
     *
     * @return Array
     */
    public function createSpaceReport($spaceId, $year, $month)
    {
        // using no auth, so we base everything with parameters
        // Get all checkins in current month
        $Checkins = CheckIn::getByPeriod($spaceId, $year, $month)->transform();
        $currentCheckins = $Checkins->toArray();
        $periods = $result = $meta = [];
        $meta['period']   = $year.$month;
        $meta['space']    = $spaceId;
        $meta['checkins'] = 0;
        $meta['shares']   = 0;
        $meta['cash']     = 0;
        $meta['pdf']      = [];
        $payout = 0;
        $PDFName = 'invoice_'.$spaceId.'_'.$meta['period'].'.pdf';
        // $meta['pdf']['title'] = 'VO-0004-June2017.pdf';
        // $meta['pdf']['url'] = 'http://someurl.com/VO-0004-June2017.pdf';
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
            $person['checkins'] =
                $this->onlyUniqueCheckins($person['checkins']);
            $person['CHFShares'] = $this->calculateCHF($person['checkins']);
            $meta['checkins'] += count($person['checkins']);
            $payout += $person['CHFShares'];
            $result[] = $person;
        }
        // calculate chf shares and cash
        if ($payout < (self::$CHFSharesLimit + 1)) {
            $meta['shares'] = $payout;
        } else {
            $meta['shares'] = self::$CHFSharesLimit;
            $meta['cash'] = $payout - $meta['shares'];
        }

        $currentPdfView = view('pdf', ['data' => $result]);

        $meta['pdf'] = $this->generatePDF($currentPdfView, $PDFName);

        print_r($meta);

        return $meta;
    }

    private function generatePDF($htmlData, $filename) {
        $dompdf = new Dompdf();
        $dompdf->loadHtml('hello world');

        // (Optional) Setup the paper size and orientation
        // $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        $output = $dompdf->output();
        file_put_contents('storage/app/public/'.$filename, $output);
        return [
            'title' => $filename,
            'url' => '/'.$filename
        ];
    }

    public function calculateCHF($checkins) {
        $checks = is_array($checkins) ?
            count($checkins) :
            (is_numeric($checkins)? $checkins:0);
        if ($checks < 2) {
            return 15;
        }
        $increment = 0;
        for($i = 1; $i <= ($checks-1); $i++)
        {
            $increment += ($i * 0.50);
        }
        $result = ($checks * 15) + $increment;
        if ($result > self::$CHFUserLimit) {
            $result = self::$CHFUserLimit;
        }
        return $result;
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
}
