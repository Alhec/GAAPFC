<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateOrganizationsTable extends Migration
{
    /**
     * Ejecutar migración Organizations.
     *
     * Descripción: Organizaciones (Escuelas o Institutos) en la base de datos.
     *
     * Atributos:
     *
     * id: Id de la organización | varchar(10) - String | Clave Primaria - Longitud 10
     *
     * name: Nombre de la organización | varchar(100) - String | Longitud 100
     *
     * faculty_id: Id de la facultad | varchar(10) - String | Clave foránea (faculties)(id) - Null permitido
     *
     * organization_id: Id de la organización a la que perteneces | varchar(10) - String | Clave foránea (organization)
     * (id) - Null permitido
     *
     * website: Sitio web de la organización | varchar(50) - String | Longitud 50
     *
     * address: Dirección de la organización | text - text | Null permitido
     *
     * @return void
     */
    public function up()
    {

        Schema::create('organizations', function (Blueprint $table) {
            $table->string('id',10);
            $table->string('name',100);
            $table->string('faculty_id',10);
            $table->string('organization_id',10)
                ->nullable();
            $table->string('website',50);
            $table->text('address')
                ->nullable();
            $table->primary('id');
            $table->foreign('faculty_id')
                ->references('id')
                ->on('faculties')
                ->onDelete('cascade');
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations');
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
        Schema::dropIfExists('organizations');
        Schema::enableForeignKeyConstraints();
    }
}
