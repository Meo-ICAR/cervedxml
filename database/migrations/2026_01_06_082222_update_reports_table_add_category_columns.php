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
        Schema::table('reports', function (Blueprint $table) {
            $table->renameColumn('idsoggetto', 'id_soggetto');
            $table->string('categoria_codice', 20)->nullable()->after('valore');
            $table->string('categoria_descrizione', 255)->nullable()->after('categoria_codice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->renameColumn('id_soggetto', 'idsoggetto');
            $table->dropColumn(['categoria_codice', 'categoria_descrizione']);
        });
    }
};
