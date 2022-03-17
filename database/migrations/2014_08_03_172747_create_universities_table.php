<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateUniversitiesTable extends Migration
{
    /**
     * Ejecutar migración Universities.
     *
     * Descripción: Universidades en la base de datos.
     *
     * Atributos:
     *
     * id: Id de la universidad | varchar(10) - String | Clave Primaria - Longitud 10
     *
     * name: Nombre de la universidad | varchar(100) - String | Único - Longitud 100
     *
     * acronym: Acrónimo de la universidad | varchar(10) - String | Único - Null permitido - Longitud 10
     *
     * @return void
     */
    public function up()
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->string('id',10)
                ->primary();
            $table->string('name',100)
                ->unique();
            $table->string('acronym',10)
                ->nullable()
                ->unique();
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
        Schema::dropIfExists('universities');
        Schema::enableForeignKeyConstraints();
    }
}
