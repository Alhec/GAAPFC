<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateSchedulesTable extends Migration
{
    /**
     * Ejecutar migración Schedules.
     *
     * Descripción: Horarios de las asignaturas en el periodo escolar.
     *
     * Atributos:
     *
     * school_period_subject_teacher_id: Id de la relación entre asignatura, periodo escolar y profesor. |
     * bigint(20) unsigned | Clave foránea (school_period_subject_teacher)(id)
     *
     * day: Dia de la semana del 1 al 7 empezando por lunes. | varchar(1) - String| Longitud 1
     *
     * classroom: Identificador del aula | varchar(40) - String | Null permitido - Longitud 40
     *
     * start_hour: Hora de inicio | time - time
     *
     * end_hour: Hora fin | time - time
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('school_period_subject_teacher_id');
            $table->string('day',1);
            $table->string('classroom',40);
            $table->time('start_hour');
            $table->time('end_hour');
            $table->foreign('school_period_subject_teacher_id')
                ->references('id')
                ->on('school_period_subject_teacher')
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
        Schema::dropIfExists('schedules');
        Schema::enableForeignKeyConstraints();
    }
}
