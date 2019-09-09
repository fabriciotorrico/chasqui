<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         

         Schema::table('users', function ($table) {
            $table->string('ci', 20);
             $table->string('nombre', 60);
             $table->string('paterno', 60);
             $table->string('materno', 60);
             $table->string('telefono', 60);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
