<?php

namespace App\Http\Controllers;

use App\Models\PersonModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Authentifie une personne et génère un token Sanctum.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'mot_de_passe' => ['required', 'string'],
        ]);

        // Check both main email and spare email
        $personne = PersonModel::where(function ($query) use ($credentials) {
            $query->where('email', $credentials['email'])
                  ->orWhere('spare_email', $credentials['email']);
        })->first();

        if (!$personne || !Hash::check($credentials['mot_de_passe'], $personne->mot_de_passe)) {
            return response()->json([
                'message' => 'Les identifiants fournis sont incorrects.',
            ], 401);
        }

        $token = $personne->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'personne' => $personne->load('etudiant'),
        ], 201);
    }
}

