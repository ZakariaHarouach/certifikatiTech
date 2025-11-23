<?php

namespace App\Http\Controllers;

use App\Models\PersonModel;
use App\Models\PasswordResetCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Request password reset - sends 6-digit code to email.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Check if email exists in main email or spare email
        $person = PersonModel::where('email', $validated['email'])
            ->orWhere('spare_email', $validated['email'])
            ->first();

        if (!$person) {
            return response()->json([
                'message' => 'Email not found in our system.',
            ], 404);
        }

        // Generate 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Invalidate previous codes for this email
        PasswordResetCode::where('email', $validated['email'])
            ->where('used', false)
            ->update(['used' => true]);

        // Create new reset code (expires in 15 minutes)
        PasswordResetCode::create([
            'email' => $validated['email'],
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(15),
            'used' => false,
        ]);

        // Send email with code
        try {
            Mail::raw("Your password reset code is: {$code}\n\nThis code will expire in 15 minutes.", function ($message) use ($validated) {
                $message->to($validated['email'])
                    ->subject('Password Reset Code');
            });
        } catch (\Exception $e) {
            // Log error but don't expose it to user
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Password reset code has been sent to your email.',
        ], 200);
    }

    /**
     * Verify the reset code.
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $resetCode = PasswordResetCode::where('email', $validated['email'])
            ->where('code', $validated['code'])
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$resetCode) {
            return response()->json([
                'message' => 'Invalid or expired code. Please request a new code.',
            ], 400);
        }

        // Mark code as used
        $resetCode->update(['used' => true]);

        // Generate a secure token for password reset (valid for 10 minutes)
        $resetToken = Str::random(64);
        
        // Store token in session or cache (using cache for simplicity)
        cache()->put("password_reset_token_{$resetToken}", $validated['email'], now()->addMinutes(10));

        return response()->json([
            'message' => 'Code verified successfully.',
            'reset_token' => $resetToken,
        ], 200);
    }

    /**
     * Reset password using the reset token.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reset_token' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8'],
        ]);

        // Get email from cache using token
        $email = cache()->get("password_reset_token_{$validated['reset_token']}");

        if (!$email) {
            return response()->json([
                'message' => 'Invalid or expired reset token.',
            ], 400);
        }

        // Find person by email (check both main and spare email)
        $person = PersonModel::where('email', $email)
            ->orWhere('spare_email', $email)
            ->first();

        if (!$person) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        // Update password
        $person->update([
            'mot_de_passe' => Hash::make($validated['new_password']),
        ]);

        // Remove the token from cache
        cache()->forget("password_reset_token_{$validated['reset_token']}");

        return response()->json([
            'message' => 'Password has been reset successfully.',
        ], 200);
    }

    /**
     * Update password (requires current password).
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8'],
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->mot_de_passe)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 400);
        }

        // Update password
        $user->update([
            'mot_de_passe' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ], 200);
    }
}
