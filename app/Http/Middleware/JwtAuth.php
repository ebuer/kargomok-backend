<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuth
{
    public function __construct(
        private readonly JwtService $jwt
    ) {}

    /**
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (! $header || ! str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Token gerekli'], 401);
        }

        $tokenString = trim(substr($header, 7));
        if ($tokenString === '') {
            return response()->json(['message' => 'Token gerekli'], 401);
        }

        $token = $this->jwt->parseAndValidate($tokenString);
        if ($token === null) {
            return response()->json(['message' => 'Geçersiz veya süresi dolmuş token'], 401);
        }

        $userId = $this->jwt->getUserIdFromToken($token);
        if ($userId === null) {
            return response()->json(['message' => 'Geçersiz token'], 401);
        }

        $user = User::find($userId);
        if ($user === null) {
            return response()->json(['message' => 'Kullanıcı bulunamadı'], 401);
        }

        $request->setUserResolver(fn () => $user);
        auth()->setUser($user);

        return $next($request);
    }
}
