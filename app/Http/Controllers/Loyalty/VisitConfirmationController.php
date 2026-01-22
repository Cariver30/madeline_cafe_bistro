<?php

namespace App\Http\Controllers\Loyalty;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyVisit;
use App\Support\Loyalty\LoyaltyRewardService;
use Illuminate\Http\Request;

class VisitConfirmationController extends Controller
{
    public function show(string $token)
    {
        $visit = LoyaltyVisit::where('qr_token', $token)->firstOrFail();

        abort_if($visit->status !== 'pending', 410);

        return view('loyalty.confirm', compact('visit'));
    }

    public function store(Request $request, string $token)
    {
        $visit = LoyaltyVisit::where('qr_token', $token)->firstOrFail();

        abort_if($visit->status !== 'pending', 410);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
        ]);

        $expectedEmail = strtolower(trim($visit->expected_email));
        $incomingEmail = strtolower(trim($data['email']));

        if (
            $expectedEmail !== $incomingEmail ||
            trim($visit->expected_phone) !== trim($data['phone']) ||
            strcasecmp($visit->expected_name, $data['name']) !== 0
        ) {
            return back()->withErrors([
                'name' => 'Los datos no coinciden con la autorización del mesero. Verifica nombre, correo y teléfono.',
            ]);
        }

        app(LoyaltyRewardService::class)->confirmVisit($visit, $data);

        return redirect()->route('loyalty.confirm.thanks');
    }

    public function thanks()
    {
        return view('loyalty.thanks');
    }

}
