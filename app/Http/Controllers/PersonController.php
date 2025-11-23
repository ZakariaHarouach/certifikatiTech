<?php

namespace App\Http\Controllers;

use App\Models\PersonModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PersonController extends Controller
{
    /**
     * Liste toutes les personnes.
     */
    public function index(): JsonResponse
    {
        $people = PersonModel::with('etudiant')->get();

        return response()->json($people);
    }

    /**
     * Affiche les détails d'une personne.
     */
    public function show(PersonModel $personne): JsonResponse
    {
        return response()->json(
            $personne->load('etudiant.certificatsMedicaux')
        );
    }

    /**
     * Met à jour une personne existante.
     */
    public function update(Request $request, PersonModel $personne): JsonResponse
    {
        $validated = $request->validate([
            'prenom' => ['sometimes', 'string', 'max:255'],
            'nom' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('personnes', 'email')->ignore($personne->cin, 'cin'),
            ],
            'telephone' => ['nullable', 'string', 'max:20'],
            'mot_de_passe' => ['sometimes', 'string', 'min:8'],
        ]);

        if (array_key_exists('mot_de_passe', $validated)) {
            $validated['mot_de_passe'] = Hash::make($validated['mot_de_passe']);
        }

        $personne->update($validated);

        return response()->json(
            $personne->refresh()->load('etudiant.certificatsMedicaux')
        );
    }

    /**
     * Supprime une personne.
     */
    public function destroy(string $cin): JsonResponse
    {
        $personne = PersonModel::where('cin', $cin)->first();

        if (!$personne) {
            return response()->json([
                'message' => 'Personne introuvable avec ce CIN.'
            ], 404);
        }
        
        $personne->delete();

        return response()->json([
            'message' => 'Personne supprimée avec succès.'
        ], 200);
    }
}
