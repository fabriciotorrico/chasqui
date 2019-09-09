<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->increments('idarea');
            $table->integer('idunidad')->unsigned()->index();
            $table->foreign('idunidad')->references('id')->on('unidades')->onDelete('cascade');

            $table->integer('iddireccion')->unsigned()->index();
            $table->foreign('iddireccion')->references('id')->on('direcciones')->onDelete('cascade');

            $table->integer('idmin')->unsigned()->index();
            $table->foreign('idmin')->references('id')->on('ministerios')->onDelete('cascade');
            $table->integer('idunidad_ejec');
            $table->string('sigla');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('areas');
    }
}
