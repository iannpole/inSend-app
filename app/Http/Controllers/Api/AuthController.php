<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\OtpMail;
use App\Mail\PasswordResetOtpMail;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Exception;

class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $otp = sprintf("%06d", random_int(100000, 999999));

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'phone'    => $request->phone,
            'address'  => $request->address,
            'role'     => 'user',
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $emailSent = false;
        $emailError = null;

        try {
            Mail::to($user->email)->send(new OtpMail($otp, $user->name));
            $emailSent = true;
        } catch (Exception $e) {
            $emailError = $e->getMessage();
            \Illuminate\Support\Facades\Log::error('Gagal kirim OTP email', [
                'email' => $user->email,
                'error' => $emailError,
            ]);
        }

        return response()->json([
            'message' => $emailSent
                ? 'Registrasi berhasil. Silakan cek email Anda untuk kode verifikasi (OTP).'
                : 'Registrasi berhasil, tapi gagal mengirim email OTP. Silakan klik "Kirim Ulang OTP".',
            'require_verification' => true,
            'email' => $user->email,
        ], 201);
    }

    /**
     * POST /api/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        // Cek verifikasi email (kecuali user login via provider social sebelumnya)
        if (!$user->email_verified_at && !$user->auth_provider) {
            return response()->json([
                'message' => 'Email belum diverifikasi. Silakan cek email Anda atau minta kode OTP baru.',
                'require_verification' => true,
                'email' => $user->email,
            ], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user'    => new UserResource($user),
            'token'   => $token,
        ]);
    }

    /**
     * POST /api/auth/verify-email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'otp_code' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // Brute-force protection: max 5 attempts
        if (($user->otp_attempts ?? 0) >= 5) {
            $user->update(['otp_code' => null, 'otp_expires_at' => null, 'otp_attempts' => 0]);
            return response()->json([
                'message' => 'Terlalu banyak percobaan. Silakan minta kode OTP baru.'
            ], 429);
        }

        if ($user->otp_code !== $request->otp_code) {
            $user->increment('otp_attempts');
            return response()->json(['message' => 'Kode OTP tidak valid'], 400);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            $user->update(['otp_attempts' => 0]);
            return response()->json(['message' => 'Kode OTP sudah kadaluarsa. Silakan minta ulang.'], 400);
        }

        // Sukses verifikasi — reset attempts
        $user->update([
            'email_verified_at' => now(),
            'otp_code'          => null,
            'otp_expires_at'    => null,
            'otp_attempts'      => 0,
        ]);

        $user->tokens()->delete();
        $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Verifikasi email berhasil. Anda sudah login.',
            'user'    => new UserResource($user),
            'token'   => $token,
        ]);
    }

    /**
     * POST /api/auth/resend-otp
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email sudah diverifikasi'], 400);
        }

        $otp = sprintf("%06d", random_int(100000, 999999));
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        try {
            Mail::to($user->email)->send(new OtpMail($otp, $user->name));
            return response()->json([
                'message' => 'Kode OTP baru telah dikirim ke email Anda.',
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Gagal mengirim email. Pastikan konfigurasi SMTP benar.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/auth/social-login
     */
    public function socialLogin(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|in:google,apple',
            'token'    => 'required|string',
            'name'     => 'nullable|string',
            'email'    => 'nullable|email',
        ]);

        $provider = $request->provider;
        $token = $request->token;
        
        $verifiedEmail = null;
        $verifiedName = $request->name;

        try {
            if ($provider === 'google') {
                // Verify Google token
                $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $token
                ]);

                if ($response->failed()) {
                    return response()->json(['message' => 'Invalid Google token'], 401);
                }

                $payload = $response->json();
                $verifiedEmail = $payload['email'] ?? null;
                
                if (empty($verifiedName) && isset($payload['name'])) {
                    $verifiedName = $payload['name'];
                }
            } elseif ($provider === 'apple') {
                // Verify Apple token
                $jwksUrl = 'https://appleid.apple.com/auth/keys';
                $keysResponse = Http::get($jwksUrl);
                
                if ($keysResponse->failed()) {
                    return response()->json(['message' => 'Failed to fetch Apple keys'], 500);
                }
                
                $jwks = $keysResponse->json();
                $parsedKeys = JWK::parseKeySet($jwks);
                
                $decoded = JWT::decode($token, $parsedKeys);
                
                if ($decoded->iss !== 'https://appleid.apple.com') {
                    return response()->json(['message' => 'Invalid Apple token issuer'], 401);
                }
                
                $verifiedEmail = $decoded->email ?? $request->email;
            }

            if (!$verifiedEmail) {
                return response()->json(['message' => 'Email not provided by provider'], 400);
            }

            // Find or create user
            $user = User::where('email', $verifiedEmail)->first();

            if (!$user) {
                // Create new user if not exists
                $user = User::create([
                    'name' => $verifiedName ?? explode('@', $verifiedEmail)[0],
                    'email' => $verifiedEmail,
                    'password' => Hash::make(Str::random(24)),
                    'role' => 'user',
                ]);
            }

            $user->tokens()->delete();
            $authToken = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'user'    => new UserResource($user),
                'token'   => $authToken,
            ]);

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Social login failed', [
                'provider' => $provider,
                'ip'       => $request->ip(),
                'error'    => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Verifikasi token gagal. Silakan coba lagi.'], 401);
        }
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * POST /api/auth/forgot-password
     *
     * Sends a password-reset OTP to the user's registered email.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Return success even if user not found to prevent email enumeration
            return response()->json([
                'message' => 'Jika email terdaftar, kode OTP telah dikirim.',
            ]);
        }

        $otp = sprintf("%06d", random_int(100000, 999999));

        $user->update([
            'otp_code'       => $otp,
            'otp_expires_at' => now()->addMinutes(15),
        ]);

        $emailSent = false;
        $emailError = null;

        try {
            Mail::to($user->email)->send(new PasswordResetOtpMail($otp, $user->name));
            $emailSent = true;
        } catch (Exception $e) {
            $emailError = $e->getMessage();
            \Illuminate\Support\Facades\Log::error('Gagal kirim OTP reset password', [
                'email' => $user->email,
                'error' => $emailError,
            ]);
        }

        return response()->json([
            'message'    => $emailSent
                ? 'Kode OTP telah dikirim ke email Anda untuk reset password.'
                : 'Gagal mengirim email OTP. Silakan coba lagi.',
            'email_sent' => $emailSent,
        ]);
    }

    /**
     * POST /api/auth/reset-password
     *
     * Verifies the OTP and sets a new password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'otp_code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        if ($user->otp_code !== $request->otp_code) {
            return response()->json(['message' => 'Kode OTP tidak valid'], 400);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'Kode OTP sudah kadaluarsa. Silakan minta ulang.'], 400);
        }

        // Update password and clear OTP
        $user->update([
            'password'       => $request->password,
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password berhasil direset. Silakan login dengan password baru.',
        ]);
    }
}
