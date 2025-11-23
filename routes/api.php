<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificateAdminController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::apiResource('etudiants', StudentController::class)
    ->parameters(['etudiants' => 'cin_personne']);

Route::apiResource('certificats', CertificateController::class)
    ->parameters(['certificats' => 'id_certificat_medical'])
    ->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

// Certificate Admin APIs (API Key protected)
Route::middleware('api.key')->group(function () {
    Route::get('/admin/certificates', [CertificateAdminController::class, 'getAllCertificates']);
    Route::get('/admin/certificates/pending', [CertificateAdminController::class, 'getPendingCertificates']);
    Route::put('/admin/certificates/{id}/status', [CertificateAdminController::class, 'updateCertificateStatus']);
    Route::post('/admin/certificates/image', [CertificateAdminController::class, 'getCertificateImage']);
});

// Password Reset APIs
Route::post('/password/forgot', [PasswordResetController::class, 'forgotPassword']);
Route::post('/password/verify-code', [PasswordResetController::class, 'verifyCode']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

// Update Password API (requires authentication)
Route::middleware('auth:sanctum')->post('/password/update', [PasswordResetController::class, 'updatePassword']);
// token : 1|z6pBl8en1dZOqbhu7Pn9PFwgyicInvLAjxS4Bo7912dac13c