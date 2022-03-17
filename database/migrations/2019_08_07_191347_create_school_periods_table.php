<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateSchoolPeriodsTable extends Migration
{
    /**
     * Ejecutar migración SchoolPeriods.
     *
     * Descripción: Periodos escolares en el sistema.
     *
     * Atributos:
     *
     * id: Id del programa escolar | bigint(20) unsigned | Clave Primaria
     *
     * organization_id: Id de la organización a la que perteneces.  | varchar(10) - String |
     * Clave foránea(organizations)(id)
     *
     * cod_school_period: Corte o código asociado al periodo escolar | varchar(10) - String | Longitud 10
     *
     * start_date: Fecha inicio del periodo escolar | date - date
     *
     * end_date: Fecha fin del periodo escolar | date - date
     *
     * withdrawal_deadline: Fecha límite de retiro | date - date
     *
     * load_notes: Bandera para permitir cargar notas | tinyint - boolean | Default: false
     *
     * inscription_start_date: Fecha de inicio de inscripción  | date - date
     *
     * inscription_visible: Bandera para permitir o no inscribir  | tinyint - boolean | Default: false
     *
     * project_duty: Arancel de los proyectos | double(8,2) - float
     *
     * final_work_duty: Arancel de los trabajos especiales de grado | double(8,2) - float
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_periods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('organization_id',10);
            $table->string('cod_school_period',10);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('withdrawal_deadline');
            $table->boolean('load_notes')
                ->default(false);
            $table->date('inscription_start_date');
            $table->boolean('inscription_visible')
                ->default(false);
            $table->float('project_duty');
            $table->float('final_work_duty');
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('cascade');
        });
    }

    /**
     * Revertir migración.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('school_periods');
        Schema::enableForeignKeyConstraints();
    }
}
