<?php
/**
 * Created by PhpStorm.
 * User: hector
 * Date: 27/07/20
 * Time: 05:45 PM
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
class IrregularStudents implements FromArray, WithStrictNullComparison, WithTitle,ShouldAutoSize
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
     * Hoja donde se presentan los estudiantes que han estado en un estatus diferente al regular en los periodos
     * escolares a consultar.
     * @return array
     */
    public function array(): array
    {
        // TODO: Implement array() method.
        return AnnualReportService::getIrregularStudents($this->schoolPeriods,$this->organizationId);
    }

    /**
     * Titulo de la pestaña
     * @return string
     */
    public function title(): string
    {
        // TODO: Implement title() method.
        return 'ESTUD. DESINC., REINC., RETIROS';
    }
}
