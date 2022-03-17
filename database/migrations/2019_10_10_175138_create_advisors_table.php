<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @package : Migration
 * @author : Hector Alayon
 * @version : 1.0
 */
class CreateAdvisorsTable extends Migration
{
    /**
     * Ejecutar migración FinalWorks.
     *
     * Descripción: Proyectos y Trabajos de grados.
     *
     * Atributos:
     *
     * id: Id del advisor (auxiliar para soportar postgres) | bigint(20) unsigned | Clave Primaria
     *
     * final_work_id: Id del trabajo de grado| bigint(20) unsigned  | Clave foránea(final_works)(id)
     *
     * teacher_id: Id del tutor | bigint(20) unsigned | Clave foránea(teachers)(id)
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advisors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('final_work_id');
            $table->unsignedBigInteger('teacher_id');
            $table->foreign('final_work_id')
                ->references('id')
                ->on('final_works')
                ->onDelete('cascade');
            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
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
        Schema::dropIfExists('advisors');
    }
}
