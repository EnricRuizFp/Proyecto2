<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosAvataresTable extends Migration
{
    public function up()
    {
        Schema::create('usuarios_avatares', function (Blueprint $table) {
            $table->id(); // ID
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('avatar_id')->constrained('avatares')->onDelete('cascade');
            $table->timestamp('actualizado')->useCurrent();
            $table->timestamps();

            $table->unique(['usuario_id', 'avatar_id']); // Evita duplicados
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuarios_avatares');
    }
}
