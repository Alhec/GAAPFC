<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateStudentsTable extends Migration
{
    /**
     * Ejecutar migración Students.
     *
     * Descripción: Estudiantes en la base de datos asociados a una organización.
     *
     * Atributos:
     *
     * id: Id del estudiante | bigint(20) unsigned | Clave Primaria
     *
     * school_program_id: Id del programa escolar al que pertenezco | bigint(20) unsigned | Clave foránea
     * (school_programs)(id)
     *
     * user_id: Id del usuario asociado al estudiante | bigint(20) unsigned | Clave foránea(users)(id)
     *
     * guide_teacher_id: Id del profesor guia | bigint(20) unsigned | Clave foránea(teachers)(id) - Null permitido
     *
     * student_type: REG: Regular (Programas conducentes a grado), EXT: Extensión, AMP: Ampliación,
     * PER: Perfeccionamiento, PDO: Post doctoral, ACT:Actualización | varchar(3) - String | Longitud 3
     *
     * home_university: Universidad de egreso | varchar(100) - String | Longitud 100
     *
     * current_postgraduate: Si proviene de otro postgrado, decir cual postgrado | varchar(100) - String | Null
     * permitido - Longitud 100
     *
     * type_income: Tipo de ingreso | varchar(30) - String | Null permitido - Longitud 30
     *
     * is_ucv_teacher: ¿es profesor de la universidad? | tinyint - boolean | Default: false
     *
     * is_available_final_work: ¿tiene habilitado presentar proyecto? | tinyint - boolean | Default: false
     *
     * credits_granted: Créditos asignados por equivalencia | int(11) - integer | Default: 0 - Null permitido
     *
     * with_work: ¿tiene trabajo? | tinyint - boolean | Default: false - Null permitido
     *
     * end_program: Finalizó el programa escolar | tinyint - boolean | Default: false
     *
     * test_period: En periodo de prueba | tinyint - boolean | Default: false
     *
     * current_status: Estatus del estudiante en su último periodo escolar inscrito RET-A (Retirado), RET-B, DES-A
     * (Desincorporado), DES-B, RIN-A (Reingreso), RIN-B, REI-A (Reincorporación), REI-B, REG (Regular), ENDED
     * (graduado) | varchar(5) - String | Default: REG - Longitud 5
     *
     * allow_post_inscription: Habilitar a un estudiante la inscripción posterior a la fecha de inscripción | tinyint -
     * boolean | Default: false
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('school_program_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('guide_teacher_id')
                ->nullable();
            $table->string('student_type',3);
            $table->string('home_university',100);
            $table->string('current_postgraduate',100)
                ->nullable();
            $table->string('type_income',30)
                ->nullable();
            $table->boolean('is_ucv_teacher')
                ->default(false);
            $table->boolean('is_available_final_work')
                ->default(false);
            $table->integer('credits_granted')
                ->default(0)
                ->nullable();
            $table->boolean('with_work')
                ->default(false)
                ->nullable();
            $table->boolean('end_program')
                ->default(false);
            $table->boolean('test_period')
                ->default(false);
            $table->string('current_status',5)
                ->default('REG');
            $table->boolean('allow_post_inscription')
                ->default(false);
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('guide_teacher_id')
                ->references('id')
                ->on('teachers')
                ->onDelete('SET NULL');
            $table->foreign('school_program_id')
                ->references('id')
                ->on('school_programs')
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
        Schema::dropIfExists('students');
        Schema::enableForeignKeyConstraints();
    }
}
