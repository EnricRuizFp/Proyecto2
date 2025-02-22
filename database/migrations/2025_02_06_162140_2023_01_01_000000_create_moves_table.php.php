<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('moves', function (Blueprint $table) {
            $table->id(); // ID
            $table->unsignedBigInteger('game_id');
            $table->unsignedBigInteger('game_player_id');
             $table->string('coordinate', 5)->nullable(); 
             $table->enum('result', ['hit', 'miss', 'unknown'])->default('unknown');

            $table->foreign('game_id')->references('id')->on('games');
            $table->foreign('game_player_id')->references('id')->on('game_players');

            // $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('moves');
    }
};
