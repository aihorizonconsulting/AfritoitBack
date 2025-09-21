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
         Schema::table('produits', function (Blueprint $table) {
            $table->renameColumn('disponibilte', 'disponibilite');
            $table->unsignedBigInteger('utilisateur_id')->nullable()->after('categorie_id');
            $table->foreign('utilisateur_id')->references('idU')->on('utilisateurs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
