<?php

namespace App\Http\Controllers\Loyalty;

use App\Http\Controllers\Controller;
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
        $visits = LoyaltyVisit::where('server_id', auth()->id())
            ->latest()
            ->limit(10)
            ->get();

        return view('loyalty.server.dashboard', compact('settings', 'visits'));
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

    protected function authorizeServer(): void
    {
        abort_unless(auth()->check() && auth()->user()->isServer() && auth()->user()->isActive(), 403);
    }
}
