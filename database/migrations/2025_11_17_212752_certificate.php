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
        Schema::create('certificats_medicaux', function (Blueprint $table) {
            // Clé Primaire : Id du Certificat Médical
            $table->bigIncrements('id_certificat_medical');

            // Clé Étrangère : Relie le certificat à l'étudiant (StudentCIN)
            $table->string('cin_etudiant');
            $table->foreign('cin_etudiant')->references('cin_personne')->on('etudiants')->onDelete('cascade');

            // Champs spécifiques au certificat
            $table->string('image_certificat'); // Chemin ou nom du fichier image du certificat
            $table->date('date_emission');
            $table->string('statut_certificat');

            $table->timestamps();
        });
    }

    /**
     * Annule les migrations (DOWN).
     */
    public function down(): void
    {
        Schema::dropIfExists('certificats_medicaux');
    }
};