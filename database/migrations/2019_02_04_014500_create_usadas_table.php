<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsadasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usadas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_usuario')->unsigned()->index();
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
            $table->integer('id_gestion')->unsigned()->index();
            $table->foreign('id_gestion')->references('id')->on('gestiones')->onDelete('cascade');
            $table->date('desde');
            $table->date('hasta');
            $table->decimal('usadas', 5, 1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('usadas');
    }
}
