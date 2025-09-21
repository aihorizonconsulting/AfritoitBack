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
        Schema::table('message', function (Blueprint $table) {
            $table->string('emailU')->nullable()->after('nomU');
            $table->string('telphoneU')->nullable()->after('emailU');
            $table->string('profilU')->nullable()->after('telphoneU');
            $table->string('urlPhotoU')->nullable()->after('profilU');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message', function (Blueprint $table) {
            //
        });
    }
};
