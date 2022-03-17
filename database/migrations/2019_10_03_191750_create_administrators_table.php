<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateAdministratorsTable extends Migration
{
    /**
     * Ejecutar migración Administrators.
     *
     * Descripción: Administradores en el sistema sobre una organización.
     *
     * Atributos:
     *
     * id: Id del usuario | bigint(20) unsigned | Clave Primaria -Clave foránea(users)(id)
     *
     * rol: Coordinador o secretario  | varchar(11) - String | Longitud 11
     *
     * principal: Coordinador principal | tinyint - Boolean | Default: false
     *
     * @return void
     */
    public function up()
    {
        Schema::create('administrators', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('rol',11);
            $table->boolean('principal')
                ->default(false);
            $table->primary('id');
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
        Schema::dropIfExists('administrators');
    }
}
