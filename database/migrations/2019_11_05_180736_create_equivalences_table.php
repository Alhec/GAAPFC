<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateEquivalencesTable extends Migration
{
    /**
     * Ejecutar migración Equivalences.
     *
     * Descripción: Equivalencias de asignaturas a estudiante.
     *
     * Atributos:
     *
     * id: Id de la equivalencia (auxiliar para soportar postgres) | bigint(20) unsigned | Clave Primaria
     *
     * student_id: Id del estudiante asociado a la equivalencia | bigint(20) unsigned | Clave foránea(students)(id)
     *
     * subject_id: Id de la asignatura que se le asigna equivalencia | bigint(20) unsigned | Clave foránea(subjects)(id)
     *
     * qualification: Nota de la asignatura con equivalencia | int(11) - integer
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equivalences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');
            $table->integer('qualification');
            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->onDelete('cascade');
            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
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
        Schema::dropIfExists('equivalences');
        Schema::enableForeignKeyConstraints();
    }
}
