<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVacacionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vacaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_usuario')->unsigned()->index();
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->date('fecha_solicitud');
            $table->date('fecha_inicio');
            $table->integer('dias_solicitados');            
            $table->integer('dias_autorizados');
            $table->integer('dias_otorgados');
            $table->integer('id_gestion');
            $table->date('desde');
            $table->date('hasta');
            $table->date('retorno');
            $table->integer('saldo');
            $table->text('observacion')->nullable();
            $table->integer('id_estado')->unsigned()->index();
            $table->foreign('id_estado')->references('id')->on('estados')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vacaciones');
    }
}
