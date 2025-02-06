<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Si "username" difiere de "alias" y quieres tener ambos
            $table->string('username')->nullable()->unique(); 
            
            // Ojo: si NO quieres username separado y decides unificarlo con alias,
            // omite este campo y deja 'alias' como único "username".

            $table->string('nationality')->nullable();
            $table->date('register_date')->nullable();
        });
    }

    public function down()  
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
            $table->dropColumn('nationality');
            $table->dropColumn('register_date');
        });
    }
};
