<?php
/**
 * Created by PhpStorm.
 * User: hector
 * Date: 29/07/20
 * Time: 09:43 AM
 */

namespace App\Exports\AnnualReportSheets;


use App\Services\AnnualReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * @package : Exports
 * @author : Hector Alayon
 * @version : 1.0
 */
class ApprovedFinalWorks implements FromArray, WithStrictNullComparison, WithTitle,ShouldAutoSize
{
    /**
     * Variable que contiene los periodos escolares
     *
     */
    protected $schoolPeriods;

    /**
     * Variable que contiene la organizacion
     *
     */
    protected $organizationId;

    /**
     * Constructor inicializando las variables protegidas
     * @param array $schoolPeriods  Rango de periodos escolares a consultar
     * @param string $organizationId Id de la organiación
     */
    public function __construct($schoolPeriods,$organizationId)
    {
        $this->schoolPeriods = $schoolPeriods;
        $this->organizationId=$organizationId;
    }
    /**
     * Hoja donde se presentan los trabajos especiales de grado que han sido aprobados en los periodos escolares
     * consultados.
     * @return array
     */
    public function array(): array
    {
        // TODO: Implement array() method.
        return AnnualReportService::getApprovedFinalWorks($this->schoolPeriods,$this->organizationId);
    }

    /**
     * Titulo de la pestaña
     * @return string
     */
    public function title(): string
    {
        // TODO: Implement title() method.
        return 'TESIS Y TRABAJOS DE GRADO APROB';
    }
}
