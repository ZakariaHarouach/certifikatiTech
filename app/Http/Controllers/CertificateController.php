<?php

namespace App\Http\Controllers;

use App\Models\CertificateModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    /**
     * Liste tous les certificats médicaux.
     */
    public function index(): JsonResponse
    {
        $certificates = CertificateModel::with('etudiant.personne')->get();

        return response()->json($certificates);
    }

    /**
     * Crée un certificat médical.
     */
    public function store(Request $request): JsonResponse
    {
        // Get authenticated user
        $user = $request->user();
        
        // Verify that the authenticated user is a student
        if (!$user->etudiant) {
            return response()->json([
                'message' => 'Seuls les étudiants peuvent ajouter des certificats.',
            ], 403);
        }

        $validated = $request->validate([
            'image_certificat' => ['required', 'image', 'mimes:png,jpeg,jpg', 'max:10240'], // Max 10MB
            'date_emission' => ['required', 'date'],
        ]);

        // Handle file upload
        $file = $request->file('image_certificat');
        $originalExtension = $file->getClientOriginalExtension();
        
        // Generate GUID for filename
        $guid = Str::uuid()->toString();
        $newFileName = $guid . '.' . $originalExtension;
        
        // Store file in certificate folder
        $filePath = $file->storeAs('certificate', $newFileName, 'public');
        
        // Create certificate record with pending status
        // Use the authenticated user's CIN as cin_etudiant
        $certificate = CertificateModel::create([
            'cin_etudiant' => $user->cin,
            'image_certificat' => $filePath,
            'date_emission' => $validated['date_emission'],
            'statut_certificat' => 'pending',
        ]);

        return response()->json($certificate->load('etudiant.personne'), 201);
    }

    /**
     * Affiche un certificat médical.
     */
    public function show(CertificateModel $certificat): JsonResponse
    {
        return response()->json($certificat->load('etudiant.personne'));
    }

    /**
     * Met à jour un certificat médical.
     */
    public function update(Request $request, CertificateModel $certificat): JsonResponse
    {
        $validated = $request->validate([
            'image_certificat' => ['sometimes', 'string', 'max:255'],
            'date_emission' => ['sometimes', 'date'],
            'statut_certificat' => ['sometimes', 'string', 'max:100'],
        ]);

        $certificat->update($validated);

        return response()->json($certificat->refresh()->load('etudiant.personne'));
    }

    /**
     * Supprime un certificat médical.
     */
    public function destroy(CertificateModel $certificat): JsonResponse
    {
        $certificat->delete();

        return response()->json([
            'message' => 'Certificat supprimé avec succès.',
        ], 200);
    }
}

