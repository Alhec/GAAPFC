<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateSchoolProgramTable extends Migration
{
    /**
     * Ejecutar migración SchoolPrograms.
     *
     * Descripción: Programas escolares en el sistema sobre una organización.
     *
     * Atributos:
     *
     * id: Id del programa escolar | bigint(20) unsigned | Clave Primaria
     *
     * organization_id: Id de la organización a la que perteneces | varchar(10) - String | Clave foránea(organizations)
     * (id)
     *
     * school_program_name: Nombre del programa | varchar(100) - String | Longitud 100
     *
     * num_cu: Cantidad de créditos para aprobar, si es conducente a grado | int(11) - integer | Null permitido
     *
     * min_num_cu_final_work: Cantidad mínima para presentar proyecto, si es conducente a grado (semestre) | int(11) -
     * integer | Null permitido
     *
     * duration: Duración del programa escolar, si es conducente a grado (semestre) | int(11) - integer | Null permitido
     *
     * min_duration: Duración mínima del programa escolar,  si es conducente a grado (semestre) | int(11) - integer |
     * Null permitido
     *
     * grant_certificate: Otorga certificados | tinyint - boolean | Default: false
     *
     * conducive_to_degree: Conducente a grado académico | tinyint - boolean | Default: true
     *
     * doctoral_exam: Examen doctoral necesario | tinyint - boolean | Default: false
     *
     * min_cu_to_doctoral_exam: Cantidad mínima de créditos para presentar el examen doctoral | int(11) - integer | Null
     * permitido
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_programs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('organization_id',10);
            $table->string('school_program_name',100);
            $table->integer('num_cu')
                ->nullable();
            $table->integer('min_num_cu_final_work')
                ->nullable();
            $table->integer('duration')
                ->nullable();
            $table->integer('min_duration')
                ->nullable();
            $table->boolean('grant_certificate')
                ->default(false);
            $table->boolean('conducive_to_degree')
                ->default(true);
            $table->boolean('doctoral_exam')
                ->default(false);
            $table->integer('min_cu_to_doctoral_exam')
                ->nullable();
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
        Schema::dropIfExists('school_programs');
        Schema::enableForeignKeyConstraints();

    }
}
