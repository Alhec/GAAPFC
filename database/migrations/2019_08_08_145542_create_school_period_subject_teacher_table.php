<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateSchoolPeriodSubjectTeacherTable extends Migration
{
    /**
     * Ejecutar migración SchoolPeriodSubjectTeacher.
     *
     * Descripción: Tabla relación entre profesor, asignatura y periodo escolar en la cual se asocia un profesor a una
     * asignatura en un periodo escolar.
     *
     * Atributos:
     *
     * id: Id de la relación | bigint(20) unsigned | Clave Primaria
     *
     * teacher_id: Id del profesor | bigint(20) unsigned | Clave foránea(teachers)(id)
     *
     * subject_id: Id de la asignatura | bigint(20) unsigned | Clave foránea(subjects)(id)
     *
     * school_period_id: Id del periodo escolar | bigint(20) unsigned | Clave foránear(school_periods)(id)
     *
     * limit: Límite de estudiantes | int(11) - integer
     *
     * enrolled_student: Cantidad de estudiantes inscritos | int(11) - integer
     *
     * duty: Arancel de la asignatura | double(8,2) - float
     *
     * modality: Modalidad en que se dictara la asignatura: Caso Postgrado de Geoquímica posee tres modalidades,
     * REG: Regular que se imparte durante el transcurso del periodo escolar INT: Intensivo, una semana de clases
     * donde se dicta la asignatura, SUF: Examen de suficiencia, una prueba para demostrar conocimiento de la asignatura
     * | varchar(3) - String | Longitud: 3
     *
     * start_date: En caso de que la asignatura no sea de modalidad regular tendrá una fecha durante el periodo escolar
     * donde se impartirá dicha modalidad. | date - date | Longitud 100
     *
     * end_date: En caso de que la modalidad no sea regular debe haber una fecha en que termine la modalidad de INT. |
     * date - date | Null permitido
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_period_subject_teacher', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('school_period_id');
            $table->integer('limit');
            $table->integer('enrolled_students');
            $table->float('duty');
            $table->string('modality',3);
            $table->date('start_date')
                ->nullable();
            $table->date('end_date')
                ->nullable();
            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->onDelete('cascade');
            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
                ->onDelete('cascade');
            $table->foreign('school_period_id')
                ->references('id')
                ->on('school_periods')
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
        Schema::dropIfExists('school_period_subject_teacher');
        Schema::enableForeignKeyConstraints();
    }
}
