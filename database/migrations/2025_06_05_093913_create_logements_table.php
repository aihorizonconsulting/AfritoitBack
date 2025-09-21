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
        Schema::create('logements', function (Blueprint $table) {
            $table->id("idL");
            $table->string("libelleL");
            $table->string("descriptionL")->nullable();
            $table->string("adresseL");
            $table->integer("nombrePieces")->nullable(true);
            $table->string("typePeriode");
            $table->string("typeLogement");
            $table->decimal("coutLoyer", 10, 2);
            $table->string("statutL")->default("disponible");
            $table->decimal("surface", 8, 2)->nullable();
            $table->foreignId('idU')
            ->constrained('utilisateurs', 'idU')
            ->onDelete('restrict')
            ->onUpdate('cascade'); 
            $table->softDeletes()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logements');
    }
};
