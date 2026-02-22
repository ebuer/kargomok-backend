<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly JwtService $jwt
    ) {}

    /**
     * Giriş: email + password ile JWT token döner.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Hatalı email veya şifre. Lütfen giriş bilgilerinizi kontrol edin.'],
            ]);
        }

        $token = $this->jwt->createToken($user);

        return response()->json([
            'message' => 'Giriş başarılı',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl', 60) * 60, // saniye
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Kayıt: yeni kullanıcı oluşturup JWT token döner.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', PasswordRule::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'surname' => $validated['surname'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
        ]);

        $token = $this->jwt->createToken($user);

        return response()->json([
            'message' => 'Kayıt başarılı',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl', 60) * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ], 201);
    }

    /**
     * Giriş yapmış kullanıcı bilgisi (token gerekli).
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Çıkış (istemci token'ı atar; sunucu tarafında ek işlem yok).
     */
    public function logout(): JsonResponse
    {
        return response()->json(['message' => 'Çıkış yapıldı']);
    }

    /**
     * Şifremi unuttum: e-posta ile sıfırlama linki gönderir.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => 'Şifre sıfırlama linki e-posta adresinize gönderildi.',
        ]);
    }

    /**
     * Şifre sıfırlama: token ve yeni şifre ile şifreyi günceller.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'token', 'password'),
            function ($user, $password): void {
                $user->forceFill(['password' => $password])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => 'Şifreniz başarıyla güncellendi.',
        ]);
    }
}
