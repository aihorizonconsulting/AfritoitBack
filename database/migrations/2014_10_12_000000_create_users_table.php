<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id('idU');
            $table->string('nomU');
            $table->string('prenomU');
            $table->string('telphoneU')->unique();
            $table->string('profilU');
            $table->string('statutU')->default('actif');
            $table->string('emailU')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mdpU');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->string('urlPhotoU')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
