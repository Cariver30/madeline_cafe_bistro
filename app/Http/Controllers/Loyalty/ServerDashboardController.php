<?php

namespace App\Http\Controllers\Loyalty;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyCustomer;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyVisit;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServerDashboardController extends Controller
{
    public function index()
    {
        $this->authorizeServer();

        $settings = Setting::first();
        $loyaltyRewards = LoyaltyReward::where('active', true)
            ->orderBy('points_required')
            ->get();
        $visits = LoyaltyVisit::where('server_id', auth()->id())
            ->latest()
            ->limit(10)
            ->get();

        return view('loyalty.server.dashboard', compact('settings', 'visits', 'loyaltyRewards'));
    }

    public function storeVisit(Request $request)
    {
        $this->authorizeServer();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
        ]);

        $settings = Setting::first();
        $points = optional($settings)->loyalty_points_per_visit ?? 0;

        $visit = LoyaltyVisit::create([
            'server_id' => auth()->id(),
            'expected_name' => $data['name'],
            'expected_email' => strtolower(trim($data['email'])),
            'expected_phone' => $data['phone'],
            'points_awarded' => $points,
            'qr_token' => Str::uuid()->toString(),
        ]);

        return redirect()->route('loyalty.dashboard')
            ->with('success', 'Visita generada.')
            ->with('active_visit', $visit->qr_token);
    }

    public function lookupCustomer(Request $request)
    {
        $this->authorizeServer();

        $data = $request->validate([
            'lookup_email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($data['lookup_email']));
        $customer = LoyaltyCustomer::where('email', $email)->first();

        if (! $customer) {
            return back()
                ->withErrors(['lookup_email' => 'No encontramos ese correo.'])
                ->withInput(['lookup_email' => $data['lookup_email']]);
        }

        return back()
            ->with('success', 'Cliente encontrado.')
            ->with('loyalty_lookup', [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'points' => $customer->points,
            ]);
    }

    protected function authorizeServer(): void
    {
        abort_unless(auth()->check() && auth()->user()->isServer() && auth()->user()->isActive(), 403);
    }
}
