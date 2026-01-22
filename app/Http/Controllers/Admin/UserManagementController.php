<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ManagerInvitationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
    public function storeManager(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $token = Str::uuid()->toString();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(Str::random(32)),
            'role' => 'manager',
            'active' => true,
            'invitation_token' => Hash::make($token),
            'invitation_sent_at' => now(),
            'invitation_accepted_at' => null,
        ]);

        Mail::to($user->email)->send(new ManagerInvitationMail($user, $token));

        return back()->with('success', 'Gerente invitado. Revisa su correo.');
    }

    public function resendInvitation(User $user)
    {
        abort_unless($user->isManager(), 403);

        $token = Str::uuid()->toString();

        $user->forceFill([
            'invitation_token' => Hash::make($token),
            'invitation_sent_at' => now(),
            'invitation_accepted_at' => null,
        ])->save();

        Mail::to($user->email)->send(new ManagerInvitationMail($user, $token));

        return back()->with('success', 'Invitación reenviada.');
    }

    public function toggleManager(User $user)
    {
        abort_unless($user->isManager(), 403);

        $user->update(['active' => ! $user->active]);

        return back()->with('success', $user->active ? 'Gerente activado.' : 'Gerente bloqueado.');
    }

    public function destroyManager(User $user)
    {
        abort_unless($user->isManager(), 403);

        $user->delete();

        return back()->with('success', 'Gerente eliminado.');
    }
}
