<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Exécute les migrations (UP).
     */
    public function up(): void
    {
        Schema::create('personnes', function (Blueprint $table) {
            // Clé Primaire : CIN (Code d'Identification Nationale)
            $table->string('cin')->primary();

            // Champs de l'entité Personne
            $table->string('prenom');
            $table->string('nom');
            $table->string('email')->unique(); // L'adresse email doit être unique
            $table->string('telephone')->nullable();
            $table->string('mot_de_passe'); // Stocke le hash du mot de passe

            $table->timestamps(); // Champs created_at et updated_at
        });
    }

    /**
     * Annule les migrations (DOWN).
     */
    public function down(): void
    {
        Schema::dropIfExists('personnes');
    }
};