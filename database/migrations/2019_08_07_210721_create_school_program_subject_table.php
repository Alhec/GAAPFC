<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateSchoolProgramSubjectTable extends Migration
{
    /**
     * Ejecutar migración SchoolProgramSubject.
     *
     * Descripción: Tabla relación entre programas escolares y asignaturas.
     *
     * Atributos:
     *
     * id: Id de la relación | bigint(20) unsigned | Clave Primaria
     *
     * school_program_id: Id del programa escolar | varchar(10) - String | Longitud 10
     *
     * subject_id: Id de la asignatura | varchar(100) - String | Longitud 100
     *
     * type: Tipo de asignatura: EL: electiva, OP: optativa, OB: Obligatoria | varchar(2) - String | Longitud: 2
     * Null permitido
     *
     * subject_group: Grupo al que pertenece la asignatura (Esto se usa para agrupar las asignaturas que se deben
     * inscribir juntas por primera vez ejemplo en el postgrado de geoquímica proyecto y seminario son dos asignaturas
     * distintas que deben inscribirse al mismo tiempo) | int(11) - integer | Null permitido
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_program_subject', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('school_program_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('type',2)
                ->nullable();
            $table->integer('subject_group')
                ->nullable();
            $table->foreign('school_program_id')
                ->references('id')
                ->on('school_programs')
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
        Schema::dropIfExists('school_program_subject');
        Schema::enableForeignKeyConstraints();
    }
}
