<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_avatars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('avatar_id');
            $table->date('updated')->default(now());
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('avatar_id')->references('id')->on('avatars')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_avatars');
    }
};
