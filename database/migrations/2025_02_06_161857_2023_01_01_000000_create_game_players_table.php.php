<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('game_players', function (Blueprint $table) {
            $table->id(); // ID
            $table->unsignedBigInteger('game_id');
            $table->unsignedBigInteger('user_id');
            $table->date('joined')->default(DB::raw('(CURDATE())'));

            $table->foreign('game_id')->references('id')->on('games');
            $table->foreign('user_id')->references('id')->on('users');
            
            // $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('game_players');
    }
};
