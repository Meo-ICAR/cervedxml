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
    Schema::create('api_logs', function (Blueprint $table) {
        $table->id();
        $table->string('direction'); // 'IN' (Incoming) o 'OUT' (Outgoing)
        $table->string('method');    // GET, POST, etc.
        $table->string('url');
        $table->integer('status_code')->nullable();
        $table->json('payload')->nullable();
        $table->json('response')->nullable();
        $table->string('ip_address')->nullable();
        $table->integer('duration_ms')->nullable(); // Durata in millisecondi
        $table->timestamps();
        
        // Indici per velocizzare la ricerca in Filament
        $table->index(['created_at', 'direction']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
