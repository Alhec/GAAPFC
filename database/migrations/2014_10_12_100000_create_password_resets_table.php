<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreatePasswordResetsTable extends Migration
{
    /**
     * Ejecutar migración PasswordResets.
     *
     * Descripción: Solicitudes de recuperación de contraseña.
     *
     * Atributos:
     *
     * email: Email de recuperación | varchar(191) - String | indice - Longitud 191
     *
     * token: Token de recuperación | varchar(191) - String | Longitud 191
     *
     * created_at: Fecha de solicitud | timestamp
     *
     * @return void
     */
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
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
        Schema::dropIfExists('password_resets');
        Schema::enableForeignKeyConstraints();

    }
}
