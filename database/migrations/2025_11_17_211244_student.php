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
        Schema::create('etudiants', function (Blueprint $table) {
            // Clé Primaire et Étrangère : Relie l'étudiant à une personne (Relation 1 à 1)
            $table->string('cin_personne')->primary();
            $table->foreign('cin_personne')->references('cin')->on('personnes')->onDelete('cascade');

            // Champs spécifiques à l'étudiant
            $table->string('groupe');
            $table->string('niveau_etudiant');
            $table->string('specialite');

            $table->timestamps();
        });
    }

    /**
     * Annule les migrations (DOWN).
     */
    public function down(): void
    {
        Schema::dropIfExists('etudiants');
    }
};