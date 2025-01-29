<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObservadoresPartidasTable extends Migration
{
    public function up()
    {
        Schema::create('observadores_partidas', function (Blueprint $table) {
            $table->id(); // ID
            $table->foreignId('partida_id')->constrained('partidas')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->timestamp('unido')->useCurrent();
            $table->timestamps();

            $table->unique(['partida_id', 'usuario_id']); // Evita duplicados
        });
    }

    public function down()
    {
        Schema::dropIfExists('observadores_partidas');
    }
}
