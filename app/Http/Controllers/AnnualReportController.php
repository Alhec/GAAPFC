<?php
/**
 * Created by PhpStorm.
 * User: hector
 * Date: 25/07/20
 * Time: 06:51 PM
 */
namespace App\Http\Controllers;

use App\Services\AnnualReportService;
use Excel;
use Illuminate\Http\Request;

/**
 * @package : Controller
 * @author : Hector Alayon
 * @version : 1.0
 */
class AnnualReportController extends Controller
{
    /**
     * Invoca el servicio que genera el reporte anual con el metodo
     * AnnualReportService::exportAnnualReport($firstSchoolPeriodId,$lastSchoolPeriodId,$organizationId)
     * @param  \Illuminate\Http\Request  $request
     * @return array|Excel|object
     */
    public static function exportAnnualReport(Request $request)
    {
        $organizationId = $request->header('Organization-Key');
        $firstSchoolPeriodId = $request->input('first_school_period_id');
        $lastSchoolPeriodId = $request->input('last_school_period_id');
        return AnnualReportService::exportAnnualReport($firstSchoolPeriodId,$lastSchoolPeriodId,$organizationId);
    }
}
