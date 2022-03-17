<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateSchoolPeriodStudentTable extends Migration
{
    /**
     * Ejecutar migración SchoolPeriodStudent.
     *
     * Descripción: Tabla relación entre estudiante y periodo escolar.
     *
     * Atributos:
     *
     * id: Id de la asociación | bigint(20) unsigned | Clave Primaria
     *
     * student_id: Id del estudiante | bigint(20) unsigned | Clave foránea(students)(id)
     *
     * school_period_id: Id del periodo escolar | bigint(20) unsigned | Clave foránear(school_periods)(id)
     *
     * status: Estatus del estudiante en el periodo escolar RET-A, RET-B, DES-A, DES-B, RIN-A Reingreso, RIN-B, REI-A
     * Reincorporación, REI-B, REG | varchar(3) - String | Longitud 3
     *
     * financing: Hay 4 maneras de financiar el periodo escolar EXO exonerado, FUN Fundación, SFI financiamiento propio,
     * ScS Beca escolar | varchar(3) - String | Longitud 3 - Null permitido
     *
     * financing_description: Descripción del financiamiento de ser necesario | text - text | Null permitido
     *
     * pay_ref: Referencia de pago | varchar(50) - String | Longitud: 50 - Null permitido
     *
     * amount_paid: Cantidad total de pago | double(8,2) - float | Null permitido
     *
     * inscription_date: Dia en que realizó la inscripción | date - date | Default: fecha de inserción
     *
     * test_period: Flag que muestra periodos escolares donde el estudiante está en periodo de evaluación de continuidad
     * en el postgrado | tinyint - boolean | Default: false
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_period_student', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('school_period_id');
            $table->string('status',5);
            $table->string('financing',3)
                ->nullable();//EXO exonerated, FUN Funded, SFI Self-financing, ScS Scholarship
            $table->text('financing_description')
                ->nullable();
            $table->string('pay_ref',50)
                ->nullable();
            $table->float('amount_paid')
                ->default(0)
                ->nullable();
            $table->date('inscription_date')
                ->default(now());
            $table->boolean('test_period')
                ->default(false)
                ->nullable();
            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->onDelete('cascade');
            $table->foreign('school_period_id')
                ->references('id')
                ->on('school_periods')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('school_period_student');
        Schema::enableForeignKeyConstraints();

    }
}
