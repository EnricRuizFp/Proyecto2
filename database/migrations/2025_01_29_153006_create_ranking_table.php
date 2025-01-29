<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRankingTable extends Migration
{
    public function up()
    {
        Schema::create('ranking', function (Blueprint $table) {
            $table->id('ranking_id'); // ID
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->enum('tipo', ['global', 'nacional'])->default('global');
            $table->integer('partidas_ganadas')->default(0);
            $table->integer('partidas_perdidas')->default(0);
            $table->integer('partidas_empatadas')->default(0);
            $table->integer('puntos')->default(0);
            $table->timestamp('actualizado_en')->useCurrent()->useCurrentOnUpdate();
            $table->timestamps();

            $table->unique(['usuario_id', 'tipo']); // Un registro por tipo por usuario
        });
    }

    public function down()
    {
        Schema::dropIfExists('ranking');
    }
}
