<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateUsersTable extends Migration
{
    /**
     * Ejecutar migración Users.
     *
     * Descripción: Usuarios en la base de datos.
     *
     * Atributos:
     *
     * id: Id del usuario | bigint(20) unsigned | Clave Primaria
     *
     * identification: identificación del usuario  | varchar(20) - String | Longitud 20
     *
     * first_name: Primer nombre | varchar(20) - String | Longitud 20
     *
     * second_name: Segundo nombre | varchar(20) - String | Null permitido - Longitud 20
     *
     * first_surname: Primer apellido | varchar(20) - String | Longitud 20
     *
     * second_surname: Segundo apellido | varchar(20) - String | Null permitido - Longitud 20
     *
     * telephone: Teléfono local | varchar(15) - String | Null permitido - Longitud 15
     *
     * mobile: Descripción de la actividad | varchar(15) - String | Longitud 15
     *
     * work_phone: fecha de creación | varchar(15) - String | Null permitido - Longitud 15
     *
     * email: Correo electrónico | varchar(30) - String | Longitud 30
     *
     * email_verified_at: Fecha de correo verificado | timestamp | Null permitido
     *
     * password: Contraseña encriptada | varchar(250) - String | Longitud 250
     *
     * level_instruction: Nivel de instrucción abreviado | varchar(3) - String | Longitud 3
     *
     * active: Activo | tinyint - Boolean | Default: true
     *
     * with_disabilities: Con discapacida | tinyint - Boolean | Default: false
     *
     * sex: Sexo | varchar(1) - String | Longitud 1
     *
     * nationality: Nacionalidad | varchar(1) - String | Longitud 1
     *
     * organization_id: Id de la organización a la que perteneces | varchar(10) - String | Clave foránea(organizations)
     * (id)
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('identification',20);
            $table->string('first_name',20);
            $table->string('second_name',20)
                ->nullable();
            $table->string('first_surname',20);
            $table->string('second_surname',20)
                ->nullable();
            $table->string('telephone',15)
                ->nullable();
            $table->string('mobile',15);
            $table->string('work_phone',15)
                ->nullable();
            $table->string('email',30);
            $table->timestamp('email_verified_at')
                ->nullable();
            $table->string('password',250);
            $table->string('level_instruction',3);
            $table->boolean('active')
                ->default(true);
            $table->boolean('with_disabilities')
                ->default(false);
            $table->string('sex',1);
            $table->string('nationality',1);
            $table->string('organization_id',10);
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('cascade');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();
    }
}
