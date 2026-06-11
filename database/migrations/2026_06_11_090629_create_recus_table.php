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
        Schema::create('recus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('texte_brut')->nullable;
            $table->string('image_path')->nullable();
            $table->string('statut')->default('en_ettente');
            $table->text('erreur_traitement')->nullable();
            $table->json('payload_brut')->nullable();
            $table->decimal('total_estime', 8, 2)->nullable();
            $table->string('devise', 10)->default('MAD');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recus');
    }
};
