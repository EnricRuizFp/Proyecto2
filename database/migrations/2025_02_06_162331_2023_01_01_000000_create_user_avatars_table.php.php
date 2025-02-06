<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_avatars', function (Blueprint $table) {
            $table->id(); // ID
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('avatar_id');
            $table->date('updated')->default(DB::raw('(CURDATE())'));

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('avatar_id')->references('id')->on('avatars');

            // $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_avatars');
    }
};

