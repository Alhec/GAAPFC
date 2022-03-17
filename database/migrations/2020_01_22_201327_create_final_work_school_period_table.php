<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateFinalWorkSchoolPeriodTable extends Migration
{
    /**
     * Ejecutar migración FinalWorkSchoolPeriod.
     *
     * Descripción: Tabla relación entre trabajo final y tabla relación periodo escolar estudiante
     *
     * Atributos:
     *
     * id: Id del trabajo de grado inscrita en un periodo escolar | bigint(20) unsigned | Clave Primaria
     *
     * status: status del trabajo de grado en ese periodo escolar: WAITING, APPROVED, PROGRESS, REPROBATE |
     * varchar(20) - String | Longitud: 20 - Default: progress
     *
     * description_status: descripción del status | text - text | Null permitido
     *
     * final_work_id: Id del trabajo de grado | bigint(20) unsigned | Clave foránea(final_works)(id)
     *
     * school_period_student_id: Id de la entidad periodo escolar estudiante | bigint(20) unsigned |
     * Clave foránea(school_period_student)(id)
     *
     * @return void
     */
    public function up()
    {
        Schema::create('final_work_school_period', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('status',10)
                ->default('progress');
            $table->text('description_status')
                ->nullable();
            $table->unsignedBigInteger('final_work_id');
            $table->unsignedBigInteger('school_period_student_id');
            $table->foreign('final_work_id')
                ->references('id')
                ->on('final_works')
                ->onDelete('cascade');
            $table->foreign('school_period_student_id')
                ->references('id')
                ->on('school_period_student')
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
        Schema::dropIfExists('final_work_school_period');
    }
}
