<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateFinalWorksTable extends Migration
{
    /**
     * Ejecutar migración FinalWorks.
     *
     * Descripción: Proyectos y Trabajos de grados.
     *
     * Atributos:
     *
     * id: Id del finalWork | bigint(20) unsigned | Clave Primaria
     *
     * title: Título del proyecto o trabajo de grado | varchar(100) - String | Longitud 100
     *
     * student_id: Id del estudiante | bigint(20) unsigned | Clave foránea(students)(id)
     *
     * subject_id: Id de la asignatura de trabajo de grado  o proyecto |bigint(20) unsigned |Clave foránea(subjects)(id)
     *
     * project_id: En caso de ser un trabajo de grado lleva el id del proyecto que lo precede | bigint(20) unsigned |
     * Clave foránea(subjects)(id)
     *
     * is_project: Flag para identificar si es un proyecto o trabajo de grado | tinyint - boolean | Default: false
     *
     * approval_date: Fecha de aprobación | date - date | Null permitido
     *
     * @return void
     */
    public function up()
    {
        Schema::create('final_works', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title',100);
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('project_id')
                ->nullable();
            $table->boolean('is_project')
                ->default(false);
            $table->date('approval_date')
                ->nullable();
            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->onDelete('cascade');
            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
                ->onDelete('cascade');
            $table->foreign('project_id')
                ->references('id')
                ->on('final_works')
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
        Schema::dropIfExists('final_works');
    }
}
