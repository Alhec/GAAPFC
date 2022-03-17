<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateDoctoralExamsTable extends Migration
{
    /**
     * Ejecutar migración DoctoralExams.
     *
     * Descripción: Contiene los estatus de los exámenes doctorales de los estudiantes asociado a un semestre inscrito.
     *
     * Atributos:
     *
     * school_period_student_id: Id de la inscripción del estudiante en un periodo escolar | bigint(20) unsigned |
     * Clave foránea(school_period_student)(id)
     *
     * status: Estatus del examen doctoral APPROVED, REPROBATE | varchar(10) - String | Longitud 10
     *
     * created_at: fecha de creación | timestamp
     *
     * update_at: fecha de actualización | timestamp
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctoral_exams', function (Blueprint $table) {
            $table->unsignedBigInteger('school_period_student_id');
            $table->string('status',10);
            $table->timestamps();
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
        Schema::dropIfExists('doctoral_exams');
    }
}
