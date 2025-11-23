<?php

namespace App\Http\Controllers;

use App\Models\CertificateModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateAdminController extends Controller
{
    /**
     * Get all certificates with their status and ID (API Key protected).
     */
    public function getAllCertificates(): JsonResponse
    {
        $certificates = CertificateModel::select('id_certificat_medical', 'statut_certificat', 'date_emission', 'cin_etudiant')
            ->with('etudiant.personne')
            ->get()
            ->map(function ($certificate) {
                $etudiant = $certificate->etudiant;

                return [
                    'id' => $certificate->id_certificat_medical,
                    'status' => $certificate->statut_certificat,
                    'date_emission' => $certificate->date_emission,
                    'student_cin' => $certificate->cin_etudiant,
                    'student_name' => $etudiant && $etudiant->personne ? $etudiant->personne->prenom . ' ' . $etudiant->personne->nom : null,
                    'student_group' => $etudiant ? $etudiant->groupe : null,
                    'niveau_etudiant' => $etudiant ? $etudiant->niveau_etudiant : null,
                    'specialite' => $etudiant ? $etudiant->specialite : null,
                ];
            });

        return response()->json([
            'certificates' => $certificates,
            'total' => $certificates->count(),
        ]);
    }

    /**
     * Get certificate image by ID (API Key protected).
     */
    public function getCertificateImage(Request $request)
    {
        $validated = $request->validate([
            'id_certificat_medical' => ['required', 'integer', 'exists:certificats_medicaux,id_certificat_medical'],
        ]);

        $certificate = CertificateModel::find($validated['id_certificat_medical']);

        if (!$certificate || !$certificate->image_certificat) {
            return response()->json([
                'message' => 'Certificate image not found.',
            ], 404);
        }

        if (!Storage::disk('public')->exists($certificate->image_certificat)) {
            return response()->json([
                'message' => 'Image file not found on server.',
            ], 404);
        }

        $imagePath = Storage::disk('public')->path($certificate->image_certificat);
        $imageMimeType = mime_content_type($imagePath);

        return response()->file($imagePath, [
            'Content-Type' => $imageMimeType,
        ]);
    }

    /**
     * Get all pending certificates (API Key protected).
     */
    public function getPendingCertificates(): JsonResponse
    {
        $certificates = CertificateModel::where('statut_certificat', 'pending')
            ->select('id_certificat_medical', 'statut_certificat', 'date_emission', 'cin_etudiant')
            ->with('etudiant.personne')
            ->get()
            ->map(function ($certificate) {
                $etudiant = $certificate->etudiant;

                return [
                    'id' => $certificate->id_certificat_medical,
                    'status' => $certificate->statut_certificat,
                    'date_emission' => $certificate->date_emission,
                    'student_cin' => $certificate->cin_etudiant,
                    'student_name' => $etudiant && $etudiant->personne ? $etudiant->personne->prenom . ' ' . $etudiant->personne->nom : null,
                    'student_group' => $etudiant ? $etudiant->groupe : null,
                    'niveau_etudiant' => $etudiant ? $etudiant->niveau_etudiant : null,
                    'specialite' => $etudiant ? $etudiant->specialite : null,
                ];
            });

        return response()->json([
            'certificates' => $certificates,
            'total' => $certificates->count(),
        ]);
    }

    /**
     * Update certificate status (API Key protected).
     */
    public function updateCertificateStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'statut_certificat' => ['required', 'string', 'in:accepted,rejected'],
        ]);

        $certificate = CertificateModel::find($id);

        if (!$certificate) {
            return response()->json([
                'message' => 'Certificate not found. the id is : ' . $id . 'it type is ' . gettype($id),
            ], 404);
        }

        $certificate->update([
            'statut_certificat' => $validated['statut_certificat'],
        ]);

        return response()->json([
            'message' => 'Certificate status updated successfully.',
            'certificate' => [
                'id' => $certificate->id_certificat_medical,
                'status' => $certificate->statut_certificat,
            ],
        ]);
    }
}
