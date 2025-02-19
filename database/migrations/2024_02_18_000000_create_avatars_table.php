<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('avatars')) {
            Schema::create('avatars', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->default('default');
                $table->timestamps();
            });
        } else {
            // Opcional: Actualizar la estructura si es necesario
            Schema::table('avatars', function (Blueprint $table) {
                if (!Schema::hasColumn('avatars', 'type')) {
                    $table->string('type')->default('default')->after('name');
                }
                if (!Schema::hasColumn('avatars', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('avatars');
    }
};
