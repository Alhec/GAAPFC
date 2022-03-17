<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateRolesTable extends Migration
{
    /**
     * Ejecutar migración Logs.
     *
     * Descripción: Logs para documentar actividad del usuario.
     *
     * Atributos:
     *
     * id: Id de la asociación | bigint(20) unsigned | Clave Primaria
     *
     * user_id: Id del usuario | bigint(20) unsigned | Clave foránea(users)(id)
     *
     * user_type: Tipo de usuario: S: estudiante, A: administrador, T: profesor | varchar(1) - String | Longitud 1
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('user_type',1);
            $table->foreign('user_id')
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
        Schema::dropIfExists('roles');
    }
}
