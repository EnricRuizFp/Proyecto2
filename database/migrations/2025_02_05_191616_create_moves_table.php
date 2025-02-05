<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('moves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->unsignedBigInteger('player_game_id');
            // Agrega aquí otras columnas que necesites
            $table->timestamps();

            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
            $table->foreign('player_game_id')->references('id')->on('player_games')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('moves');
    }
};
