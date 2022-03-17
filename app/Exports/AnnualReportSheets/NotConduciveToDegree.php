<?php
/**
 * Created by PhpStorm.
 * User: hector
 * Date: 28/07/20
 * Time: 09:22 AM
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
class NotConduciveToDegree implements FromArray, WithStrictNullComparison, WithTitle,ShouldAutoSize
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
     * Hoja donde se encuentra la información de los programas escolares no conducentes a grado en los periodos
     * escolares dados.
     * @return array
     */
    public function array(): array
    {
        // TODO: Implement array() method.
        return AnnualReportService::getNotConduciveToDegree($this->schoolPeriods,$this->organizationId);
    }

    /**
     * Titulo de la pestaña
     * @return string
     */
    public function title(): string
    {
        // TODO: Implement title() method.
        return 'CURSOS DE AMPLIACIÓN Y PERFECC.';
    }
}
