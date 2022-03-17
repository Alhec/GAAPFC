<?php
/**
 * Created by PhpStorm.
 * User: hector
 * Date: 28/07/20
 * Time: 07:02 PM
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
class IrregularFinalWorks implements FromArray, WithStrictNullComparison, WithTitle,ShouldAutoSize
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
     * Hoja donde están los trabajos de grado que exceden el tiempo reglamentario por el cual deberían haber culminado,
     * y estudiantes que aún no han inscrito proyecto teniendo más de 24 créditos en los periodos escolares dados.
     * @return array
     */
    public function array(): array
    {
        // TODO: Implement array() method.
        return AnnualReportService::getIrregularFinalWorks($this->schoolPeriods,$this->organizationId);
    }

    /**
     * Titulo de la pestaña
     * @return string
     */
    public function title(): string
    {
        // TODO: Implement title() method.
        return 'INSCRIPCION DE TESIS Y PROYECTO';
    }
}
