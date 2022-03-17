<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateFacultiesTable extends Migration
{
    /**
     * Ejecutar migración Faculties.
     *
     * Descripción: Facultades en la base de datos.
     *
     * Atributos:
     *
     * id: Id de la facultad | varchar(10) - String | Clave Primaria - Longitud 10
     *
     * name: Nombre de la facultad | varchar(100) - String | Longitud 100
     *
     * acronym: Acrónimo de la facultad | varchar(10) - String | Null permitido - Longitud 10
     *
     * university_id: Id de la universidad | varchar(10) - String | Clave foránea (universities)(id)
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faculties', function (Blueprint $table) {
            $table->String('id',10)
                ->primary();
            $table->string('university_id',10);
            $table->string('name',100);
            $table->string('acronym',10)
                ->nullable();
            $table->foreign('university_id')
                ->references('id')
                ->on('universities')
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
        Schema::dropIfExists('faculties');
        Schema::enableForeignKeyConstraints();
    }
}
