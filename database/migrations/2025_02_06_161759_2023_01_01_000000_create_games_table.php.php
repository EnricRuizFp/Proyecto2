<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            // Campo para el código de 4 caracteres, único.
            $table->char('code', 4)->unique();
            // Usamos CURRENT_TIMESTAMP para que sea datetime.
            $table->dateTime('creation_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('start_date')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_finished')->default(false);
            $table->dateTime('end_date')->nullable();
            $table->unsignedBigInteger('winner')->nullable();
            $table->unsignedBigInteger('created_by');
            
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('winner')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('games');
    }
};
    