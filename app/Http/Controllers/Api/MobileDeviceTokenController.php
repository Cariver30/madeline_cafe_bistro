<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MobileDeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'platform' => ['required', 'string', Rule::in(['android', 'ios'])],
        ]);

        $user = $request->user();

        DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $user->id,
                'platform' => $data['platform'],
                'last_seen_at' => now(),
            ],
        );

        return response()->json(['message' => 'Token registrado.']);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'token' => ['nullable', 'string', 'max:255'],
        ]);

        $query = DeviceToken::query()->where('user_id', $request->user()->id);

        if (!empty($data['token'])) {
            $query->where('token', $data['token']);
        }

        $query->delete();

        return response()->json(['message' => 'Token eliminado.']);
    }
}
