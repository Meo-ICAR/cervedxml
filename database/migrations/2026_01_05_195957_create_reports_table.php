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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('piva', 11)->nullable();
            $table->boolean('is_racese')->default(false);
            $table->text('annotation')->nullable();
            $table->string('id_soggetto', 20)->nullable();
            $table->string('codice_score', 20)->nullable();
            $table->string('descrizione_score', 255)->nullable();
            $table->decimal('valore', 10, 2)->nullable();
            $table->string('status')->default('draft')->nullable();
             $table->string('categoria_codice', 20)->nullable();
            $table->string('categoria_descrizione', 255)->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
