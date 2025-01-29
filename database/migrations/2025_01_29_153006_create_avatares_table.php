<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvataresTable extends Migration
{
    public function up()
    {
        Schema::create('avatares', function (Blueprint $table) {
            $table->id(); // ID
            $table->string('nombre', 50);
            $table->string('ruta_imagen', 255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('avatares');
    }
}
