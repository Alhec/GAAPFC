<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateTeachersTable extends Migration
{
    /**
     * Ejecutar migración Teachers.
     *
     * Descripción: Profesores en el sistema que pertenecen a una organización.
     *
     * Atributos:
     *
     * id: Id del usuario | bigint(20) unsigned | Clave Primaria - Clave foránea(users)(id)
     *
     * teacher_type: CON: contratado, JUB: jubilado, REG: regular, OTH: otro | varchar(3) - String | Longitud 3
     *
     * dedication: MT: medio tiempo, TC: tiempo convencional, EXC: exclusivo, TCO: tiempo completo | varchar(3) - String
     * | Longitud 3
     *
     * category: INS: instructor, ASI: asistente, AGR: agregado, ASO: asociado, TIT: titulado, INV: invitado |
     * varchar(3) - String | Longitud 3
     *
     * home_institute: Universidad de egreso o institución de procedencia  | varchar(100) - String | Null permitido -
     * Longitud 100
     *
     * country: País | varchar(20) - String | Null permitido - Longitud 20
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->unsignedBigInteger('id')
                ->primary();
            $table->string('teacher_type',3);
            $table->string('dedication',3);
            $table->string('category',3);
            $table->string('home_institute',100)
                ->nullable();
            $table->string('country',20)
                ->nullable();
            $table->foreign('id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('teachers');
        Schema::enableForeignKeyConstraints();
    }
}
