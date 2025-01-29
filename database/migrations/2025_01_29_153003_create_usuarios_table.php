<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosTable extends Migration
{
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id(); // ID: INT, AUTO_INCREMENT, PRIMARY KEY
            $table->string('username', 50)->unique();
            $table->string('name', 50)->nullable();
            $table->string('surname', 50)->nullable();
            $table->string('email', 100)->unique()->nullable();
            $table->string('password', 255);
            $table->string('nacionalidad', 50)->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
}
