<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateSubjectsTable extends Migration
{
    /**
     * Ejecutar migración Subjects.
     *
     * Descripción: Asignaturas en el sistema asociadas a un programa escolar.
     *
     * Atributos:
     *
     * id: Id de la asignatura | bigint(20) unsigned | Clave Primaria
     *
     * code: código de la asignatura  | varchar(10) - String | Longitud 10
     *
     * name: nombre de la asignatura | varchar(100) - String | Longitud 100
     *
     * uc: unidades de crédito | int(11) - integer
     *
     * is_final_subject: ¿Es un trabajo de grado? | tinyint - boolean | Default: false
     *
     * is_project_subject: ¿Es un proyecto? | tinyint - boolean | Default: false
     *
     * theoretical_hours: Horas teóricas | int(11) - integer | Default: 0
     *
     * practical_hours: Horas de práctica | int(11) - integer | Default: 0
     *
     * laboratory_hours: Horas de laboratorio | int(11) - integer | Default: 0
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code',10);
            $table->string('name',100);
            $table->integer('uc');
            $table->boolean('is_final_subject')
                ->default(false);
            $table->boolean('is_project_subject')
                ->default(false);
            $table->integer('theoretical_hours')
                ->default(0);
            $table->integer('practical_hours')
                ->default(0);
            $table->integer('laboratory_hours')
                ->default(0);
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
        Schema::dropIfExists('subjects');
        Schema::enableForeignKeyConstraints();
    }
}
