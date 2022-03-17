<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateLogsTable extends Migration
{
    /**
     * Ejecutar migración Logs.
     *
     * Descripción: Logs para documentar actividad del usuario.
     *
     * Atributos:
     *
     * id: Id del log | bigint(20) unsigned | Clave Primaria
     *
     * user_id: Id del usuario | bigint(20) unsigned | Clave foránea(users)(id)
     *
     * log_description: Descripción de la actividad | varchar(200) - String | Longitud 200
     *
     * created_at: fecha de creación | timestamp
     *
     * update_at: fecha de actualización | timestamp
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('log_description',200);
            $table->timestamps();
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
        Schema::dropIfExists('logs');
    }
}
