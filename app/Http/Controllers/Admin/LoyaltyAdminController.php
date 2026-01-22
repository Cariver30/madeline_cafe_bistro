<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ServerInvitationMail;
use App\Models\LoyaltyReward;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LoyaltyAdminController extends Controller
{
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'loyalty_points_per_visit' => ['required', 'integer', 'min:1'],
            'loyalty_terms' => ['nullable', 'string'],
            'loyalty_email_copy' => ['nullable', 'string'],
            'tab_label_menu' => ['nullable', 'string', 'max:255'],
            'tab_label_cocktails' => ['nullable', 'string', 'max:255'],
            'tab_label_wines' => ['nullable', 'string', 'max:255'],
            'tab_label_events' => ['nullable', 'string', 'max:255'],
            'tab_label_loyalty' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = Setting::first() ?? Setting::create($data);
        $settings->fill($data)->save();

        return redirect()->route('admin.new-panel', [
            'section' => 'loyalty-section',
            'open' => 'loyalty-settings',
        ])->with('success', 'Programa de fidelidad actualizado.');
    }

    public function storeReward(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'points_required' => ['required', 'integer', 'min:1'],
        ]);

        LoyaltyReward::create($data);

        return back()->with('success', 'Recompensa creada.');
    }

    public function updateReward(Request $request, LoyaltyReward $reward)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'points_required' => ['required', 'integer', 'min:1'],
            'active' => ['nullable', 'boolean'],
        ]);

        $reward->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'points_required' => $data['points_required'],
            'active' => $request->boolean('active'),
        ]);

        return back()->with('success', 'Recompensa actualizada.');
    }

    public function destroyReward(LoyaltyReward $reward)
    {
        $reward->delete();

        return back()->with('success', 'Recompensa eliminada.');
    }

    public function toggleReward(LoyaltyReward $reward)
    {
        $reward->update(['active' => !$reward->active]);

        return back()->with('success', 'Estado actualizado.');
    }

    public function storeServer(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['nullable', 'string', 'in:server,pos'],
        ]);

        $role = $data['role'] ?? 'server';
        $token = Str::uuid()->toString();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $role,
            'password' => Hash::make(Str::random(16)),
            'invitation_token' => Hash::make($token),
            'invitation_sent_at' => now(),
            'active' => true,
        ]);

        $roleLabel = $role === 'pos' ? 'POS' : 'mesero';
        $roleDescription = $role === 'pos'
            ? 'tomar ordenes y cobrar desde el sistema POS'
            : 'gestionar mesas y el programa de fidelidad';

        Mail::to($user->email)->send(new ServerInvitationMail($user, $token, $roleLabel, $roleDescription));

        return back()->with('success', 'Usuario invitado. Revisa su correo.');
    }

    public function resendInvitation(User $user)
    {
        abort_unless($user->hasRole(['server', 'pos']), 403);

        $token = Str::uuid()->toString();

        $user->forceFill([
            'invitation_token' => Hash::make($token),
            'invitation_sent_at' => now(),
            'invitation_accepted_at' => null,
        ])->save();

        $roleLabel = $user->role === 'pos' ? 'POS' : 'mesero';
        $roleDescription = $user->role === 'pos'
            ? 'tomar ordenes y cobrar desde el sistema POS'
            : 'gestionar mesas y el programa de fidelidad';

        Mail::to($user->email)->send(new ServerInvitationMail($user, $token, $roleLabel, $roleDescription));

        return back()->with('success', 'Invitación reenviada.');
    }

    public function toggleServer(User $user)
    {
        abort_unless($user->hasRole(['server', 'pos']), 403);

        $user->update(['active' => ! $user->active]);

        return back()->with('success', $user->active ? 'Usuario activado.' : 'Usuario bloqueado.');
    }

    public function destroyServer(User $user)
    {
        abort_unless($user->hasRole(['server', 'pos']), 403);

        $user->delete();

        return back()->with('success', 'Usuario eliminado.');
    }
}
