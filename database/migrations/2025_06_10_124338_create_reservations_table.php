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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logement_id')
                ->constrained('logements', 'idL')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('client_id')
                ->constrained('utilisateurs', 'idU')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->date('dateE')->nullable();
            $table->date('dateS')->nullable();
            $table->decimal('montantR', 10, 2);
            $table->string('typeR'); // Possible values: 'court terme', 'long terme'
            $table->string('statutR')->default('en attente'); // Possible values: 'en attente', 'confirmée', 'annulée','rejetée'
            $table->decimal('caution', 10, 2)->nullable(); // Caution pour la réservation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
