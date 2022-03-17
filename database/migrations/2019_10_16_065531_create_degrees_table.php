<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateDegreesTable extends Migration
{
    /**
     * Ejecutar migración Degrees.
     *
     * Descripción: Grados obtenidos y asociados a un estudiante.
     *
     * Atributos:
     *
     * student_id: id del estudiante asociado al grado | bigint(20) unsigned | Clave foránea(students)(id)
     *
     * degrre_obtained: Grado contraído obtenido TSU,TCM,Dr,Esp,Ing,MSc,Lic | varchar(3) - String | Longitud 3
     *
     * degree_name: Nombre del grado obtenido | varchar(50) - String | Longitud 50
     *
     * degree_description: Descripción del grado | varchar(200) - String | Null permitido - Longitud 200
     *
     * university: REG: Universidad donde se obtuvo el grado | varchar(100) - String | Longitud 100
     *
     * @return void
     */
    public function up()
    {
        Schema::create('degrees', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id');
            $table->string('degree_obtained',3);
            $table->string('degree_name',50);
            $table->string('degree_description',200)
                ->nullable();
            $table->string('university',100);
            $table->foreign('student_id')
                ->references('id')
                ->on('students')
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
        Schema::dropIfExists('degrees');
        Schema::enableForeignKeyConstraints();
    }
}
