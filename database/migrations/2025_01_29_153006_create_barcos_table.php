<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarcosTable extends Migration
{
    public function up()
    {
        Schema::create('barcos', function (Blueprint $table) {
            $table->id(); // ID
            $table->string('nombre', 50);
            $table->integer('tamaño');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('barcos');
    }
}
