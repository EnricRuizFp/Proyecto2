<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rankings', function (Blueprint $table) {
            $table->id('ranking_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['global', 'nacional'])->default('global');
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('draws')->default(0);
            $table->integer('points')->default(0);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->useCurrentOnUpdate();

            $table->foreign('user_id')->references('id')->on('users');

            // $table->timestamps(); // no es necesario si usas 'updated_at' custom
        });
    }

    public function down()
    {
        Schema::dropIfExists('rankings');
    }
};

