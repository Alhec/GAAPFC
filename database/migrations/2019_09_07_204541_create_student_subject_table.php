<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateStudentSubjectTable extends Migration
{
    /**
     * Ejecutar migración StudentSubject.
     *
     * Descripción: Tabla relación entre estudiante y periodo escolar, representa la inscripción de un estudiante en una
     * asignatura del periodo escolar.
     *
     * Atributos:
     *
     * id: Id de la asociación | bigint(20) unsigned | Clave Primaria
     *
     * school_period_student_id: Id de la tabla relación estudiante y periodo escolar | bigint(20) unsigned
     * | Clave foránea(school_period_student)(id)
     *
     * school_period_subject_teacher_id: Id de la tabla relacion periodo escolar, asignatura y profesor |
     * bigint(20) unsigned | Clave foránea(school_period_subject_teacher)(id)
     *
     * qualification: Nota de la asignatura | int(11) - integer | Null permitido
     *
     * status: Estatus en que se encuentra el estudiante sobre una asignatura en un periodo escolar CUR cursando,
     * APR aprobado, REP reprobado, RET retirado | varchar(3) - String | Longitud 3
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_subject', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('school_period_student_id');
            $table->unsignedBigInteger('school_period_subject_teacher_id');
            $table->integer('qualification')
                ->nullable();
            $table->string('status',3);
            $table->foreign('school_period_student_id')
                ->references('id')
                ->on('school_period_student')
                ->onDelete('cascade');
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
        Schema::dropIfExists('student_subject');
        Schema::enableForeignKeyConstraints();

    }
}
