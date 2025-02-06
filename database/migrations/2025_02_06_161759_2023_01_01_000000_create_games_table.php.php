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
            $table->date('creation_date')->default(DB::raw('(CURDATE())'));
            $table->boolean('is_public')->default(true);
            $table->boolean('is_finished')->default(false);
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('created_by');
            
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('games');
    }
};
