<?php

namespace App\Http\Controllers\Loyalty;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    public function show(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->query('email'))
            ->whereNotNull('invitation_token')
            ->firstOrFail();

        if (!Hash::check($request->query('token'), $user->invitation_token)) {
            abort(403, 'Invitación inválida o vencida.');
        }

        $roleLabel = $this->resolveRoleLabel($user->role);
        $roleDescription = $this->resolveRoleDescription($user->role);

        return view('loyalty.invitations.show', compact('user', 'roleLabel', 'roleDescription'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        if (!$user->invitation_token || !Hash::check($data['token'], $user->invitation_token)) {
            return back()->withErrors(['token' => 'El enlace expiró, solicita una nueva invitación.']);
        }

        $user->forceFill([
            'password' => $data['password'],
            'invitation_token' => null,
            'invitation_accepted_at' => now(),
        ])->save();

        auth()->login($user);

        if ($user->isServer()) {
            return redirect()->route('loyalty.dashboard')
                ->with('success', 'Acceso activado.');
        }

        if ($user->isManager()) {
            return redirect()->route('admin.new-panel')
                ->with('success', 'Acceso activado.');
        }

        $roleLabel = $this->resolveRoleLabel($user->role);

        return view('loyalty.invitations.activated', [
            'user' => $user,
            'roleLabel' => $roleLabel,
        ]);
    }

    private function resolveRoleLabel(?string $role): string
    {
        return match ($role) {
            'pos' => 'POS',
            'manager' => 'gerente',
            'admin' => 'administrador',
            default => 'mesero',
        };
    }

    private function resolveRoleDescription(?string $role): string
    {
        return match ($role) {
            'pos' => 'tomar órdenes y cobrar desde el sistema POS',
            'manager' => 'administrar el panel y el equipo',
            'admin' => 'control total del sistema',
            default => 'gestionar mesas y fidelización',
        };
    }
}
