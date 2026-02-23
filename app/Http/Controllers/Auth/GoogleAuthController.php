<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JwtService;
use Google\Client as GoogleClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function __construct(
        private readonly JwtService $jwt
    ) {}

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $idToken = trim($request->id_token);

        if (substr_count($idToken, '.') !== 2) {
            return response()->json([
                'message' => 'Geçersiz id_token biçimi. Google erişim belirteci (access_token) değil, Google ile girişten alınan kimlik belirtecini (credential veya id_token) kullanın.',
            ], 400);
        }

        $client = new GoogleClient([
            'client_id' => config('services.google.client_id'),
        ]);

        try {
            $payload = $client->verifyIdToken($idToken);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Geçersiz veya hatalı Google kimlik belirteci. Google ile girişten alınan id_token gönderdiğinizden emin olun.',
            ], 401);
        }

        if (! $payload) {
            return response()->json(['message' => 'Geçersiz Google belirteci'], 401);
        }

        if ($payload['aud'] !== config('services.google.client_id')) {
            return response()->json(['message' => 'Geçersiz hedef kitle (audience)'], 401);
        }

        $fullName = trim($payload['name'] ?? '');
        $nameParts = $fullName !== '' ? explode(' ', $fullName, 2) : [];
        $name = $nameParts[0] ?? explode('@', $payload['email'])[0];
        $surname = $nameParts[1] ?? null;

        $user = User::updateOrCreate(
            ['email' => $payload['email']],
            [
                'name' => $name,
                'surname' => $surname,
                'google_id' => $payload['sub'],
                'avatar' => $payload['picture'] ?? null,
                'password' => bcrypt(Str::random(32)),
            ]
        );

        $token = $this->jwt->createToken($user);

        return response()->json([
            'message' => 'Giriş başarılı',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl', 60) * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
            ],
        ]);
    }
}
