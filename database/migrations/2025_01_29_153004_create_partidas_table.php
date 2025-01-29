<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartidasTable extends Migration
{
    public function up()
    {
        Schema::create('partidas', function (Blueprint $table) {
            $table->id(); // ID
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->boolean('publica')->default(true);
            $table->boolean('finalizada')->default(false);
            $table->timestamp('fecha_finalizacion')->nullable();
            $table->foreignId('creada_por')->constrained('usuarios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partidas');
    }
}
