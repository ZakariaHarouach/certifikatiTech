<?php

namespace App\Http\Controllers;

use App\Models\PersonModel;
use App\Models\StudentModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Liste tous les étudiants.
     */
    public function index(): JsonResponse
    {
        $students = StudentModel::with(['personne', 'certificatsMedicaux'])->get();

        return response()->json($students);
    }

    /**
     * Crée un nouvel étudiant et la personne associée.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cin_personne' => ['required', 'string', 'max:20', 'unique:personnes,cin', 'unique:etudiants,cin_personne'],
            'prenom' => ['required', 'string', 'max:255'],
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:personnes,email'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'mot_de_passe' => ['required', 'string', 'min:8'],
            'groupe' => ['required', 'string', 'max:50'],
            'niveau_etudiant' => ['required', 'string', 'max:100'],
            'specialite' => ['required', 'string', 'max:100'],
        ]);

        $student = DB::transaction(function () use ($validated) {
            PersonModel::create([
                'cin' => $validated['cin_personne'],
                'prenom' => $validated['prenom'],
                'nom' => $validated['nom'],
                'email' => $validated['email'],
                'telephone' => $validated['telephone'] ?? null,
                'mot_de_passe' => Hash::make($validated['mot_de_passe']),
            ]);

            return StudentModel::create([
                'cin_personne' => $validated['cin_personne'],
                'groupe' => $validated['groupe'],
                'niveau_etudiant' => $validated['niveau_etudiant'],
                'specialite' => $validated['specialite'],
            ]);
        });

        return response()->json($student->load(['personne']), 201);
    }

    /**
     * Affiche les détails d'un étudiant.
     */
    public function show(StudentModel $etudiant): JsonResponse
    {
        return response()->json($etudiant->load(['personne', 'certificatsMedicaux']));
    }

    /**
     * Met à jour un étudiant.
     */
    public function update(Request $request, StudentModel $etudiant): JsonResponse
    {
        $validated = $request->validate([
            'groupe' => ['sometimes', 'string', 'max:50'],
            'niveau_etudiant' => ['sometimes', 'string', 'max:100'],
            'specialite' => ['sometimes', 'string', 'max:100'],
        ]);

        $etudiant->update($validated);

        return response()->json($etudiant->refresh()->load(['personne', 'certificatsMedicaux']));
    }

    /**
     * Supprime un étudiant.
     */
    public function destroy(string $cin_personne): JsonResponse
    {
        $etudiant = StudentModel::where('cin_personne', $cin_personne)->first();
        if (!$etudiant) {
            return response()->json([
                'message' => 'Étudiant non trouvé.',
            ], 404);
        }
        $etudiant->delete();

        return response()->json([
            'message' => 'Étudiant supprimé avec succès.',
        ], 200);
    }
}

