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
        Schema::create('favoris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logement_id')
                ->constrained('logements', 'idL')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('client_id')
                ->constrained('utilisateurs', 'idU')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->string('status')->default('active'); // Possible values: 'active', 'inactive'
            $table->softDeletes(); // Allows for soft deletion of favorites
            $table->date('date_ajout')->nullable(); // Date when the favorite was added
            $table->unique(['logement_id', 'client_id'], 'unique_favoris'); // Ensure a client can only favorite a logement once
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favoris');
    }
};
