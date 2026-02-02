<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class MobileAuthController extends Controller
{
    protected array $allowedRoles = ['server', 'manager', 'pos', 'kitchen', 'host'];

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        /** @var ?User $user */
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->isActive() || !$user->hasRole($this->allowedRoles)) {
            return response()->json([
                'message' => 'No tienes acceso a la app móvil.',
            ], Response::HTTP_FORBIDDEN);
        }

        $plainToken = Str::random(80);
        $user->forceFill([
            'api_token' => hash('sha256', $plainToken),
        ])->save();

        return response()->json([
            'token' => $plainToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        /** @var ?User $user */
        $user = $request->user();

        if ($user) {
            $user->forceFill(['api_token' => null])->save();
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }
}
