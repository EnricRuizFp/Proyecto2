<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientosTable extends Migration
{
    public function up()
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id(); // ID
            $table->foreignId('partida_id')->constrained('partidas')->onDelete('cascade');
            $table->foreignId('jugador_partida_id')->constrained('jugadores_partidas')->onDelete('cascade');
            $table->string('coordenada', 5);
            $table->enum('resultado', ['agua', 'tocado', 'hundido']);
            $table->timestamp('realizado_en')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('movimientos');
    }
}
